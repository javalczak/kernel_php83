<?php
declare(strict_types=1);

namespace App\Fixtures;

use Engine\Database;
use App\Schema\LocationSchema;
use App\Schema\FeatureSchema;
use App\Schema\ActivitySchema;
use App\Schema\PropertyTypeSchema;
use App\Schema\PropertySchema;
use App\Schema\CouponSchema;
use App\Repository\UserRepository;
use App\Repository\PropertyRepository;

final class AppFixtures
{
    /** @var array<int,array{city:string,province:string}> populated by insertCyprus(), reused by insertProperties() */
    private array $cyprusCities = [];

    public function __construct(
        private Database $db
    ) {}

    public function load(): void
    {
        $areaIds = $this->insertAreas();
        $this->insertCyprus($areaIds['Europe']);
        $this->insertFeatures();
        $this->insertActivities();
        $this->insertPropertyTypes();
        $this->insertCoupons();
        $this->insertProperties();
    }

    /**
     * @return array<string,int> area name => location id
     */
    private function insertAreas(): array
    {
        $areas = ['Europe', 'Africa', 'Caribbean', 'North America', 'South America', 'Asia', 'Oceania'];
        $ids   = [];

        foreach ($areas as $sortOrder => $name) {
            $ids[$name] = $this->db->insert(LocationSchema::TABLE, [
                'parent_id'  => null,
                'type'       => 'area',
                'name'       => $name,
                'slug'       => strtolower(str_replace(' ', '-', $name)),
                'is_active'  => 1,
                'sort_order' => $sortOrder,
            ]);
        }

        return $ids;
    }

    /**
     * Cyprus is the launch country — districts and city/place names sourced
     * from sources/hs_2104244.json. Seeded active by default; an admin can
     * still turn individual ones off through the Locations panel.
     */
    private function insertCyprus(int $europeId): void
    {
        $countryId = $this->db->insert(LocationSchema::TABLE, [
            'parent_id'  => $europeId,
            'type'       => 'country',
            'name'       => 'Cyprus',
            'slug'       => 'cyprus',
            'is_active'  => 1,
            'sort_order' => 0,
        ]);

        $districts = [
            'Famagusta District' => [
                'Ayia Napa', 'Bogaz', 'Famagusta', 'Frenaros', 'Protaras', 'Vrysoulles',
            ],
            'Kyrenia District' => [
                'Alagardi', 'Alsancak - Karavas', 'Arapkoy', 'Bahceli', 'Bellapais', 'Catalkoy',
                'Edremit (Trimithi)', 'Esentepe', 'Karaman/Karmi', 'Kayalar', 'Kucukerenkoy',
                'Kyrenia', 'Lapta', 'Yesiltepe',
            ],
            'Larnaka District' => [
                'Agia Anna', 'Agios Theodoros', 'Alethriko', 'Dhekelia', 'Kalavasos', 'Kiti',
                'Larnaca Town Centre', 'MacKenzie Beach', 'Maroni', 'Mazotos', 'Oroklini',
                'Pentakomo', 'Pervolia', 'Psematismenos', 'Pyla', 'Pyrga', 'Skarinou',
            ],
            'Limassol District' => [
                'Arakapas', 'Doros', 'Fasoula', 'Finikaria', 'Kolossi', 'Lofou', 'Moniatis',
                'Parekklisia', 'Pissouri', 'Platres', 'Trimiklini', 'Vouni',
            ],
            'Nicosia District' => [
                'Guzelyurt', 'Kalopanayiotis', 'Kato Pyrgos', 'Limnitis', 'Lythrodontas',
                'Nicosia', 'Nicosia Town', 'Pachyammos', 'Peristerona', 'Spilia', 'Strovolos',
            ],
            'Paphos District' => [
                'Anarita', 'Aphrodite Gardens', 'Argaka', 'Coral Bay', 'Inia', 'Kallepia',
                'Kissonerga', 'Latchi', 'Limni', 'Mandria', 'Pano Arodes', 'Paphos', 'Peyia',
                'Peyia Village', 'Polis', 'Polis Chrysochous', 'Queens Gardens',
                'Secret Valley Resort', 'Tala', 'Tomb of the Kings', 'Universal District',
            ],
        ];

        $districtSort = 0;
        foreach ($districts as $districtName => $cities) {
            $districtId = $this->db->insert(LocationSchema::TABLE, [
                'parent_id'  => $countryId,
                'type'       => 'province',
                'name'       => $districtName,
                'slug'       => $this->slugify($districtName),
                'is_active'  => 1,
                'sort_order' => $districtSort++,
            ]);

            $citySort = 0;
            foreach ($cities as $cityName) {
                $this->db->insert(LocationSchema::TABLE, [
                    'parent_id'  => $districtId,
                    'type'       => 'city',
                    'name'       => $cityName,
                    'slug'       => $this->slugify($cityName),
                    'is_active'  => 1,
                    'sort_order' => $citySort++,
                ]);
                $this->cyprusCities[] = ['city' => $cityName, 'province' => $districtName];
            }
        }
    }

    private function insertFeatures(): void
    {
        // Categories per the founder's koncept.txt: essential / comfort / outdoor / luxury / service.
        $features = [
            'essential' => ['wifi' => 'Wifi', 'air_conditioning' => 'Air conditioning', 'parking' => 'Parking'],
            'comfort'   => ['balcony' => 'Balcony', 'sauna' => 'Sauna'],
            'outdoor'   => ['pool' => 'Pool', 'bbq' => 'BBQ', 'cycling' => 'Cycling', 'fishing' => 'Fishing'],
            'luxury'    => ['spa_relaxing' => 'Spa & relaxing'],
            'service'   => ['restaurant' => 'Restaurant'],
        ];

        foreach ($features as $category => $items) {
            $sortOrder = 0;
            foreach ($items as $code => $name) {
                $this->db->insert(FeatureSchema::TABLE, [
                    'code'       => $code,
                    'name'       => $name,
                    'category'   => $category,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    /**
     * "What can guests do nearby" — separate dictionary from Feature (see
     * App\Schema\ActivitySchema), per the founder's 2026-06-24 decision to
     * eventually compose search bundles from these.
     */
    private function insertActivities(): void
    {
        $activities = [
            'golf'          => 'Golf',
            'water_sports'  => 'Water sports',
            'diving'        => 'Diving',
            'hiking'        => 'Hiking',
            'wine_tasting'  => 'Wine tasting',
            'nightlife'     => 'Nightlife',
            'tennis'        => 'Tennis',
            'horse_riding'  => 'Horse riding',
        ];

        $sortOrder = 0;
        foreach ($activities as $code => $name) {
            $this->db->insert(ActivitySchema::TABLE, [
                'code'       => $code,
                'name'       => $name,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function insertPropertyTypes(): void
    {
        $types = [
            'apartment' => 'Apartment',
            'villa'     => 'Villa',
            'cottage'   => 'Cottage',
            'house'     => 'House',
            'cabin'     => 'Cabin',
            'boat'      => 'Boat',
            'tent'      => 'Tent',
            'loft'      => 'Loft',
            'bungalow'  => 'Bungalow',
            'farm_stay' => 'Farm stay',
        ];

        $sortOrder = 0;
        foreach ($types as $code => $name) {
            $this->db->insert(PropertyTypeSchema::TABLE, [
                'code'       => $code,
                'name'       => $name,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function insertCoupons(): void
    {
        // FOUNDINGHOST: the first 100 hosts go online for free — bounded,
        // unlike a literal lifetime plan, so it can't become an open-ended
        // liability. WELCOME51: the founder's general marketing discount,
        // handed out personally on Facebook/email/etc.
        $this->db->insert(CouponSchema::TABLE, [
            'code'             => 'FOUNDINGHOST',
            'discount_percent' => 100,
            'max_uses'         => 100,
        ]);

        $this->db->insert(CouponSchema::TABLE, [
            'code'             => 'WELCOME51',
            'discount_percent' => 51,
            'max_uses'         => null,
        ]);
    }

    /**
     * Dev demo content: dozens of published Cyprus listings built from real
     * photo sets dropped in sources/pictures/{n}/ (gitignored — not shipped,
     * so this silently no-ops on a fresh checkout/prod where that folder
     * doesn't exist).
     */
    private function insertProperties(): void
    {
        $picturesDir = BASE_PATH . '/sources/pictures';
        if (!is_dir($picturesDir)) {
            return;
        }

        $folders = glob($picturesDir . '/*', GLOB_ONLYDIR);
        if (empty($folders)) {
            return;
        }
        natsort($folders);
        $folders = array_values($folders);
        shuffle($folders);
        $folders = array_slice($folders, 0, 70);

        $propertyTypeIds = array_column(
            $this->db->fetchAll("SELECT id FROM " . PropertyTypeSchema::TABLE),
            'id'
        );
        $featureIds = array_column(
            $this->db->fetchAll("SELECT id FROM " . FeatureSchema::TABLE),
            'id'
        );
        $activityIds = array_column(
            $this->db->fetchAll("SELECT id FROM " . ActivitySchema::TABLE),
            'id'
        );

        $userRepo     = new UserRepository($this->db);
        $propertyRepo = new PropertyRepository($this->db);

        $adjectives = ['Sunny', 'Charming', 'Cosy', 'Modern', 'Traditional', 'Breezy', 'Hillside', 'Seafront', 'Quiet', 'Stylish', 'Renovated', 'Spacious'];
        $nouns      = ['Retreat', 'Getaway', 'Hideaway', 'Escape', 'Stay', 'Nest', 'Haven'];

        // Not real geocoding — just enough for the listpage map to have
        // believable pins. Each district's town-centre coordinate, jittered
        // a little per property so they don't all stack on one point.
        $districtCenters = [
            'Famagusta District' => [35.1264, 33.9420],
            'Kyrenia District'   => [35.3414, 33.3152],
            'Larnaka District'   => [34.9229, 33.6233],
            'Limassol District'  => [34.7071, 33.0226],
            'Nicosia District'   => [35.1856, 33.3823],
            'Paphos District'    => [34.7720, 32.4297],
        ];

        foreach ($folders as $i => $folder) {
            $location = $this->cyprusCities[array_rand($this->cyprusCities)];
            $typeId   = (int)$propertyTypeIds[array_rand($propertyTypeIds)];

            [$centerLat, $centerLng] = $districtCenters[$location['province']] ?? [35.1264, 33.4299];
            $latitude  = $centerLat + (mt_rand(-500, 500) / 10000);
            $longitude = $centerLng + (mt_rand(-500, 500) / 10000);

            $title = sprintf(
                '%s %s in %s',
                $adjectives[array_rand($adjectives)],
                $nouns[array_rand($nouns)],
                $location['city']
            );
            $slug = $this->slugify($title) . '-' . ($i + 1);

            $hostNumber = $i + 1;
            $userId = $userRepo->findOrCreate("demo-host{$hostNumber}@example.com", "Demo Host {$hostNumber}");

            $privacyRoll = mt_rand(1, 100);
            $privacyLevel = match (true) {
                $privacyRoll <= 70 => PropertySchema::PRIVACY_ENTIRE_PLACE,
                $privacyRoll <= 90 => PropertySchema::PRIVACY_PRIVATE_ROOM,
                default            => PropertySchema::PRIVACY_SHARED_ROOM,
            };

            $propertyId = $propertyRepo->create([
                'user_id'           => $userId,
                'property_type_id'  => $typeId,
                'title'             => $title,
                'slug'              => $slug,
                'privacy_level'     => $privacyLevel,
                'max_guest'         => mt_rand(2, 8),
                'bedrooms'          => mt_rand(1, 4),
                'bathrooms'         => mt_rand(1, 3),
                'country'           => 'Cyprus',
                'province'          => $location['province'],
                'city'              => $location['city'],
                'latitude'          => $latitude,
                'longitude'         => $longitude,
                'short_description' => "A {$title} close to everything {$location['city']} has to offer.",
                'description'       => "Discover this lovely property in {$location['city']}, {$location['province']}. "
                    . "Perfect for travellers who want a comfortable, no-commission stay in Cyprus. "
                    . "Book directly with the host and skip the booking fees.",
                'status'            => 'published',
                'preview_token'     => bin2hex(random_bytes(16)),
            ]);

            $pickedFeatures = (array)array_rand(array_flip($featureIds), min(count($featureIds), mt_rand(2, 5)));
            $propertyRepo->attachFeatures($propertyId, $pickedFeatures);

            // Roughly half the demo listings have nothing nearby tagged —
            // matches the real world, where not every host fills this in.
            if (mt_rand(0, 1) === 1) {
                $pickedActivities = (array)array_rand(array_flip($activityIds), min(count($activityIds), mt_rand(1, 4)));
                $propertyRepo->attachActivities($propertyId, $pickedActivities);
            }

            $this->copyFixturePictures($folder, $propertyId, $propertyRepo);
        }
    }

    private function copyFixturePictures(string $folder, int $propertyId, PropertyRepository $propertyRepo): void
    {
        $files = array_values(array_diff(scandir($folder) ?: [], ['.', '..']));
        shuffle($files);
        $files = array_slice($files, 0, min(10, count($files)));

        if (empty($files)) {
            return;
        }

        $destDir = BASE_PATH . '/uploads/properties/' . $propertyId;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        foreach ($files as $sortOrder => $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $destName  = bin2hex(random_bytes(8)) . '.' . $extension;
            copy($folder . '/' . $file, $destDir . '/' . $destName);

            $propertyRepo->addPicture(
                $propertyId,
                '/uploads/properties/' . $propertyId . '/' . $destName,
                $sortOrder,
                $sortOrder === 0
            );
        }
    }

    private function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}
