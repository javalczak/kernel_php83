<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;
use App\Repository\PropertyRepository;
use App\Repository\PropertyTypeRepository;
use App\Repository\FeatureRepository;
use App\Repository\ActivityRepository;
use App\Repository\CouponRepository;
use App\Repository\UserRepository;
use App\Schema\PropertySchema;
use App\Schema\FeatureSchema;
use App\Security\Csrf;
use App\Service\PhotoUploader;
use App\Service\PricingCalculator;

/**
 * Host self-service dashboard — separate from both the admin panel
 * (App\Controller\Admin\*, $_SESSION['admin_id']) and the public wizard
 * (App\Controller\NewPropertyController, token-driven drafts). Once a host
 * is logged in ($_SESSION['user_id']), this is where they manage
 * properties they already finished adding, split into the same sections
 * the wizard used to create them (details/location/photos/features/pricing)
 * but edited independently rather than as a linear flow.
 */
final class AccountController extends Controller
{
    private const array SECTIONS = ['details', 'location', 'photos', 'features', 'pricing'];

    public function index(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $userId = (int)$_SESSION['user_id'];
        $repo   = new PropertyRepository($this->db());

        $summary = $repo->findLocationSummaryForUser($userId);

        $countryTotals = [];
        foreach ($summary as $row) {
            $countryTotals[$row['country']] = ($countryTotals[$row['country']] ?? 0) + (int)$row['count'];
        }

        $selectedCountryName = null;
        if (!empty($params['country'])) {
            foreach (array_keys($countryTotals) as $name) {
                if (self::slugify($name) === $params['country']) {
                    $selectedCountryName = $name;
                    break;
                }
            }
            if ($selectedCountryName === null) {
                return Response::notFound('Unknown location.');
            }
        }

        $provinceTotals = [];
        if ($selectedCountryName !== null) {
            foreach ($summary as $row) {
                if ($row['country'] === $selectedCountryName && !empty($row['province'])) {
                    $provinceTotals[$row['province']] = ($provinceTotals[$row['province']] ?? 0) + (int)$row['count'];
                }
            }
        }

        $selectedProvinceName = null;
        if ($selectedCountryName !== null && !empty($params['province'])) {
            foreach (array_keys($provinceTotals) as $name) {
                if (self::slugify($name) === $params['province']) {
                    $selectedProvinceName = $name;
                    break;
                }
            }
            if ($selectedProvinceName === null) {
                return Response::notFound('Unknown location.');
            }
        }

        $locationFilters = [];
        if ($selectedCountryName !== null) {
            $locationFilters['country'] = $selectedCountryName;
        }
        if ($selectedProvinceName !== null) {
            $locationFilters['province'] = $selectedProvinceName;
        }

        $properties = $repo->findAllForUser($userId, $locationFilters);
        $totalSaved = array_sum($countryTotals);

        $countryLinks = [];
        foreach ($countryTotals as $name => $count) {
            $countryLinks[] = ['name' => $name, 'slug' => self::slugify($name), 'count' => $count];
        }

        $provinceLinks = [];
        foreach ($provinceTotals as $name => $count) {
            $provinceLinks[] = ['name' => $name, 'slug' => self::slugify($name), 'count' => $count];
        }

        return $this->render('templates/account/index', [
            'properties'           => $properties,
            'totalProperties'      => $totalSaved,
            'countryLinks'         => $countryLinks,
            'provinceLinks'        => $provinceLinks,
            'selectedCountryName'  => $selectedCountryName,
            'selectedProvinceName' => $selectedProvinceName,
            'plans'                => require BASE_PATH . '/config/pricing.php',
        ]);
    }

    private static function slugify(string $value): string
    {
        return strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));
    }

    public function edit(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $section = (string)($params['section'] ?? 'details');
        if (!in_array($section, self::SECTIONS, true)) {
            return Response::notFound('Unknown section.');
        }

        $propertyRepo = new PropertyRepository($this->db());
        $property     = $this->ownedProperty($propertyRepo, (int)($params['id'] ?? 0));

        if ($property === null) {
            return $this->redirect('/account');
        }

        $error   = null;
        $success = false;

        if ($request->isPost()) {
            if (!Csrf::verify((string)$request->post('csrf_token'))) {
                $error = 'Your session expired, please try again.';
            } else {
                $error = match ($section) {
                    'details'  => $this->saveDetails($request, $propertyRepo, $property),
                    'location' => $this->saveLocation($request, $propertyRepo, $property),
                    'features' => $this->saveFeatures($request, $propertyRepo, $property),
                    'pricing'  => $this->savePricing($request, $propertyRepo, $property),
                    default    => null,
                };

                if ($error === null) {
                    return $this->redirect('/account/properties/' . $property['id'] . '/' . $section . '?saved=1');
                }
            }

            // Re-read so the form reflects whatever didn't get saved.
            $property = $propertyRepo->findById((int)$property['id']);
        } else {
            $success = $request->get('saved') === '1';
        }

        return $this->render('templates/account/edit', [
            'property'      => $property,
            'section'       => $section,
            'error'         => $error,
            'success'       => $success,
            'csrfToken'     => Csrf::token(),
            'types'         => array_values(array_filter(
                (new PropertyTypeRepository($this->db()))->findAll(),
                static fn(array $t): bool => (int)$t['is_active'] === 1
            )),
            'byCategory'      => $this->featuresByCategory(),
            'categoryLabels'  => FeatureSchema::categories(),
            'selectedFeatureIds'  => $propertyRepo->findFeatureIds((int)$property['id']),
            'activities'          => array_values(array_filter(
                (new ActivityRepository($this->db()))->findAll(),
                static fn(array $a): bool => (int)$a['is_active'] === 1
            )),
            'selectedActivityIds' => $propertyRepo->findActivityIds((int)$property['id']),
            'pictures'            => $propertyRepo->findPictures((int)$property['id']),
            'plans'               => require BASE_PATH . '/config/pricing.php',
            'accountDiscountPercent' => $this->accountDiscountPercent((int)$property['user_id']),
        ]);
    }

    public function photoUpload(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $this->json(['error' => 'Not logged in.'], 403);
        }

        $propertyRepo = new PropertyRepository($this->db());
        $property     = $this->ownedProperty($propertyRepo, (int)($params['id'] ?? 0));

        if ($property === null || !Csrf::verify((string)$request->post('csrf_token'))) {
            return $this->json(['ok' => false], 403);
        }

        $files  = $request->files['photos'] ?? null;
        $stored = $files !== null ? PhotoUploader::storeMany($files, (int)$property['id'], $propertyRepo) : [];

        foreach ($stored as &$picture) {
            foreach ($propertyRepo->findPictures((int)$property['id']) as $row) {
                if ((int)$row['id'] === $picture['id']) {
                    $picture['is_cover'] = (bool)$row['is_cover'];
                }
            }
        }

        return $this->json(['ok' => true, 'pictures' => $stored]);
    }

    public function photoDelete(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $this->json(['ok' => false], 403);
        }

        $propertyRepo = new PropertyRepository($this->db());
        $property     = $this->ownedProperty($propertyRepo, (int)($params['id'] ?? 0));

        if ($property === null || !Csrf::verify((string)$request->post('csrf_token'))) {
            return $this->json(['ok' => false], 403);
        }

        $path = $propertyRepo->deletePicture((int)$request->post('picture_id'), (int)$property['id']);
        if ($path !== null) {
            $fullPath = BASE_PATH . $path;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        return $this->json(['ok' => true]);
    }

    public function photoCover(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $this->json(['ok' => false], 403);
        }

        $propertyRepo = new PropertyRepository($this->db());
        $property     = $this->ownedProperty($propertyRepo, (int)($params['id'] ?? 0));

        if ($property === null || !Csrf::verify((string)$request->post('csrf_token'))) {
            return $this->json(['ok' => false], 403);
        }

        $propertyRepo->setCover((int)$request->post('picture_id'), (int)$property['id']);

        return $this->json(['ok' => true]);
    }

    public function photoCaption(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $this->json(['ok' => false], 403);
        }

        $propertyRepo = new PropertyRepository($this->db());
        $property     = $this->ownedProperty($propertyRepo, (int)($params['id'] ?? 0));

        if ($property === null || !Csrf::verify((string)$request->post('csrf_token'))) {
            return $this->json(['ok' => false], 403);
        }

        $caption = trim((string)$request->post('caption'));
        $propertyRepo->setCaption(
            (int)$request->post('picture_id'),
            (int)$property['id'],
            $caption !== '' ? mb_substr($caption, 0, 255) : null
        );

        return $this->json(['ok' => true]);
    }

    public function photoReorder(Request $request, array $params = []): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $this->json(['ok' => false], 403);
        }

        $propertyRepo = new PropertyRepository($this->db());
        $property     = $this->ownedProperty($propertyRepo, (int)($params['id'] ?? 0));

        if ($property === null || !Csrf::verify((string)$request->post('csrf_token'))) {
            return $this->json(['ok' => false], 403);
        }

        $order = array_map('intval', (array)$request->post('order', []));
        $propertyRepo->reorderPictures((int)$property['id'], $order);

        return $this->json(['ok' => true]);
    }

    private function requireLogin(): ?Response
    {
        if (empty($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        return null;
    }

    private function ownedProperty(PropertyRepository $repo, int $id): ?array
    {
        $property = $id > 0 ? $repo->findById($id) : null;

        if ($property === null || (int)($property['user_id'] ?? 0) !== (int)$_SESSION['user_id']) {
            return null;
        }

        return $property;
    }

    private function saveDetails(Request $request, PropertyRepository $repo, array $property): ?string
    {
        $title     = trim((string)$request->post('title'));
        $typeId    = (int)$request->post('property_type_id');
        $privacy   = (int)$request->post('privacy_level');
        $maxGuest  = max(1, (int)$request->post('max_guest'));
        $bedrooms  = max(0, (int)$request->post('bedrooms'));
        $bathrooms = max(0, (int)$request->post('bathrooms'));
        $priceTier = (int)$request->post('price_tier');
        $priceTier = $priceTier >= PropertySchema::PRICE_TIER_MIN && $priceTier <= PropertySchema::PRICE_TIER_MAX
            ? $priceTier
            : null;

        $type = $typeId > 0 ? (new PropertyTypeRepository($this->db()))->findById($typeId) : null;

        if (
            $title === ''
            || $type === null
            || !in_array($privacy, [
                PropertySchema::PRIVACY_ENTIRE_PLACE,
                PropertySchema::PRIVACY_PRIVATE_ROOM,
                PropertySchema::PRIVACY_SHARED_ROOM,
            ], true)
        ) {
            return 'Please fill in a title, a property type and a privacy level.';
        }

        $repo->update((int)$property['id'], [
            'title'             => $title,
            'property_type_id'  => $type['id'],
            'privacy_level'     => $privacy,
            'max_guest'         => $maxGuest,
            'bedrooms'          => $bedrooms,
            'bathrooms'         => $bathrooms,
            'price_tier'        => $priceTier,
            'short_description' => $this->nullableTrim($request->post('short_description')),
            'description'       => $this->nullableTrim($request->post('description')),
        ]);

        return null;
    }

    private function saveLocation(Request $request, PropertyRepository $repo, array $property): ?string
    {
        $country = trim((string)$request->post('country'));
        $city    = trim((string)$request->post('city'));

        if ($country === '' || $city === '') {
            return 'Country and city are required.';
        }

        $repo->update((int)$property['id'], [
            'country'     => $country,
            'province'    => $this->nullableTrim($request->post('province')),
            'city'        => $city,
            'street'      => $this->nullableTrim($request->post('street')),
            'postal_code' => $this->nullableTrim($request->post('postal_code')),
            'latitude'    => $this->nullableFloat($request->post('latitude')),
            'longitude'   => $this->nullableFloat($request->post('longitude')),
        ]);

        return null;
    }

    private function saveFeatures(Request $request, PropertyRepository $repo, array $property): ?string
    {
        $featureIds  = array_map('intval', (array)$request->post('features', []));
        $activityIds = array_map('intval', (array)$request->post('activities', []));

        $repo->syncFeatures((int)$property['id'], array_unique($featureIds));
        $repo->syncActivities((int)$property['id'], array_unique($activityIds));

        return null;
    }

    private function savePricing(Request $request, PropertyRepository $repo, array $property): ?string
    {
        $plans = require BASE_PATH . '/config/pricing.php';
        $plan  = (string)$request->post('plan');

        if (!array_key_exists($plan, $plans)) {
            return 'Please choose a plan.';
        }

        $couponCode = trim((string)$request->post('coupon_code'));
        $couponRepo = new CouponRepository($this->db());
        $coupon     = $couponCode !== '' ? $couponRepo->findByCode($couponCode) : null;

        if ($couponCode !== '' && ($coupon === null || !$couponRepo->isUsable($coupon))) {
            return 'That coupon code is not valid or has already been fully used.';
        }

        $repo->update((int)$property['id'], [
            'plan'        => $plan,
            'coupon_code' => $coupon !== null ? $coupon['code'] : null,
        ]);

        if ($coupon !== null) {
            $couponRepo->incrementUsage((int)$coupon['id']);
        }

        return null;
    }

    private function accountDiscountPercent(int $userId): int
    {
        $userRepo = new UserRepository($this->db());
        $user     = $userRepo->findById($userId);

        if ($user === null) {
            return 0;
        }

        return PricingCalculator::bestDiscountPercent(
            $user['discount_override_percent'] !== null ? (int)$user['discount_override_percent'] : null,
            (bool)$user['is_bulk_account'],
            $userRepo->countProperties($userId),
            null
        );
    }

    /**
     * @return array<string,array>
     */
    private function featuresByCategory(): array
    {
        $byCategory = [];
        foreach ((new FeatureRepository($this->db()))->findAll() as $feature) {
            if ((int)$feature['is_active'] === 1) {
                $byCategory[$feature['category']][] = $feature;
            }
        }

        return $byCategory;
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        $value = trim((string)$value);
        return $value === '' ? null : (float)$value;
    }

    private function json(array $data, int $status = 200): Response
    {
        return (new Response())
            ->setStatusCode($status)
            ->setContent(json_encode($data))
            ->addHeader('Content-Type', 'application/json');
    }
}
