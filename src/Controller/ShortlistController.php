<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;
use App\Repository\PropertyRepository;

/**
 * The shortlist itself lives entirely in a browser cookie (a JSON array of
 * property ids) — there's no host/visitor account tying it together, so
 * this controller just reads whatever ids the cookie currently holds and
 * renders them. Adding/removing happens client-side (see assets behaviour
 * inlined in _header.php) without a page reload; this page is for viewing
 * the saved set, filtering it by location, and removing one at a time.
 */
final class ShortlistController extends Controller
{
    public function index(Request $request, array $params = []): Response
    {
        $ids = self::idsFromCookie($request->cookies['shortlist'] ?? null);

        $propertyRepo = new PropertyRepository($this->db());

        $summary = $propertyRepo->findLocationSummaryForIds($ids);

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

        $filters = ['ids' => $ids];
        if ($selectedCountryName !== null) {
            $filters['country'] = $selectedCountryName;
        }
        if ($selectedProvinceName !== null) {
            $filters['province'] = $selectedProvinceName;
        }

        $properties = empty($ids) ? [] : $propertyRepo->findPublishedFiltered($filters, 200, 0);
        $total      = empty($ids) ? 0 : $propertyRepo->countPublishedFiltered($filters);

        $propertyIds        = array_map(static fn(array $p): int => (int)$p['id'], $properties);
        $picturesByProperty = $propertyRepo->findPicturesGrouped($propertyIds, 8);

        $countryLinks = [];
        foreach ($countryTotals as $name => $count) {
            $countryLinks[] = ['name' => $name, 'slug' => self::slugify($name), 'count' => $count];
        }

        $provinceLinks = [];
        foreach ($provinceTotals as $name => $count) {
            $provinceLinks[] = ['name' => $name, 'slug' => self::slugify($name), 'count' => $count];
        }

        return $this->render('templates/shortlist/index', [
            'properties'           => $properties,
            'picturesByProperty'   => $picturesByProperty,
            'total'                => $total,
            'savedCount'           => count($ids),
            'countryLinks'         => $countryLinks,
            'provinceLinks'        => $provinceLinks,
            'selectedCountryName'  => $selectedCountryName,
            'selectedProvinceName' => $selectedProvinceName,
        ]);
    }

    /**
     * @return int[]
     */
    private static function idsFromCookie(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }

    private static function slugify(string $value): string
    {
        return strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));
    }
}
