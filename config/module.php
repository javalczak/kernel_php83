<?php
declare(strict_types=1);

use App\Controller\HelloWorldController;
use App\Controller\HomeController;
use App\Controller\SearchController;
use App\Controller\ShortlistController;
use App\Controller\PreviewController;
use App\Controller\ListingController;
use App\Controller\Admin\AuthController as AdminAuthController;
use App\Controller\Admin\DashboardController as AdminDashboardController;
use App\Controller\Admin\LocationController as AdminLocationController;
use App\Controller\Admin\FeatureController as AdminFeatureController;
use App\Controller\Admin\ActivityController as AdminActivityController;
use App\Controller\Admin\PropertyTypeController as AdminPropertyTypeController;
use App\Controller\Admin\PropertyController as AdminPropertyController;
use App\Controller\Admin\CouponController as AdminCouponController;
use App\Controller\Admin\ContentPageController as AdminContentPageController;
use App\Controller\Admin\HostController as AdminHostController;
use App\Controller\PageController;
use App\Controller\AuthController;
use App\Controller\GettingReadyController;
use App\Controller\NewPropertyController;
use App\Controller\AccountController;

return [
    'name'    => 'fiestalettings',
    'version' => '0.1.0',
    'path'    => __DIR__,
    'routes'  => [
        ['method' => 'GET',  'path' => '/', 'controller' => HelloWorldController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/location-suggest', 'controller' => SearchController::class, 'action' => 'suggest'],
        ['method' => 'GET',  'path' => '/search', 'controller' => SearchController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/search/{country}', 'controller' => SearchController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/search/{country}/{province}', 'controller' => SearchController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/search/{country}/{province}/{city}', 'controller' => SearchController::class, 'action' => 'index'],

        ['method' => 'GET',  'path' => '/shortlist', 'controller' => ShortlistController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/shortlist/{country}', 'controller' => ShortlistController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/shortlist/{country}/{province}', 'controller' => ShortlistController::class, 'action' => 'index'],

        ['method' => 'GET',  'path' => '/admin/login',  'controller' => AdminAuthController::class, 'action' => 'login'],
        ['method' => 'POST', 'path' => '/admin/login',  'controller' => AdminAuthController::class, 'action' => 'login'],
        ['method' => 'GET',  'path' => '/admin/logout', 'controller' => AdminAuthController::class, 'action' => 'logout'],

        ['method' => 'GET',  'path' => '/admin', 'controller' => AdminDashboardController::class, 'action' => 'index'],

        ['method' => 'GET',  'path' => '/admin/locations',        'controller' => AdminLocationController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/admin/locations/add',    'controller' => AdminLocationController::class, 'action' => 'add'],
        ['method' => 'POST', 'path' => '/admin/locations/toggle', 'controller' => AdminLocationController::class, 'action' => 'toggle'],

        ['method' => 'GET',  'path' => '/admin/features',         'controller' => AdminFeatureController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/admin/features/add',     'controller' => AdminFeatureController::class, 'action' => 'add'],
        ['method' => 'POST', 'path' => '/admin/features/toggle',  'controller' => AdminFeatureController::class, 'action' => 'toggle'],
        ['method' => 'GET',  'path' => '/admin/features/pending', 'controller' => AdminFeatureController::class, 'action' => 'pending'],
        ['method' => 'POST', 'path' => '/admin/features/approve', 'controller' => AdminFeatureController::class, 'action' => 'approve'],
        ['method' => 'POST', 'path' => '/admin/features/reject',  'controller' => AdminFeatureController::class, 'action' => 'reject'],

        ['method' => 'GET',  'path' => '/admin/activities',        'controller' => AdminActivityController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/admin/activities/add',    'controller' => AdminActivityController::class, 'action' => 'add'],
        ['method' => 'POST', 'path' => '/admin/activities/toggle', 'controller' => AdminActivityController::class, 'action' => 'toggle'],

        ['method' => 'GET',  'path' => '/admin/property-types',        'controller' => AdminPropertyTypeController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/admin/property-types/add',    'controller' => AdminPropertyTypeController::class, 'action' => 'add'],
        ['method' => 'POST', 'path' => '/admin/property-types/toggle', 'controller' => AdminPropertyTypeController::class, 'action' => 'toggle'],

        ['method' => 'GET',  'path' => '/admin/properties',     'controller' => AdminPropertyController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/admin/properties/new', 'controller' => AdminPropertyController::class, 'action' => 'new'],
        ['method' => 'POST', 'path' => '/admin/properties',     'controller' => AdminPropertyController::class, 'action' => 'create'],

        ['method' => 'GET',  'path' => '/admin/properties/{id}/edit',          'controller' => AdminPropertyController::class, 'action' => 'edit'],
        ['method' => 'POST', 'path' => '/admin/properties/{id}/edit',          'controller' => AdminPropertyController::class, 'action' => 'update'],
        ['method' => 'POST', 'path' => '/admin/properties/{id}/photos/delete', 'controller' => AdminPropertyController::class, 'action' => 'deletePhoto'],

        ['method' => 'GET',  'path' => '/admin/coupons',        'controller' => AdminCouponController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/admin/coupons/add',    'controller' => AdminCouponController::class, 'action' => 'add'],
        ['method' => 'POST', 'path' => '/admin/coupons/toggle', 'controller' => AdminCouponController::class, 'action' => 'toggle'],

        ['method' => 'GET',  'path' => '/admin/hosts',        'controller' => AdminHostController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/admin/hosts/update', 'controller' => AdminHostController::class, 'action' => 'update'],

        ['method' => 'GET',  'path' => '/admin/content-pages',             'controller' => AdminContentPageController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/admin/content-pages/new',         'controller' => AdminContentPageController::class, 'action' => 'new'],
        ['method' => 'POST', 'path' => '/admin/content-pages',             'controller' => AdminContentPageController::class, 'action' => 'create'],
        ['method' => 'GET',  'path' => '/admin/content-pages/{id}/edit',   'controller' => AdminContentPageController::class, 'action' => 'edit'],
        ['method' => 'POST', 'path' => '/admin/content-pages/{id}/edit',   'controller' => AdminContentPageController::class, 'action' => 'update'],
        ['method' => 'POST', 'path' => '/admin/content-pages/delete',      'controller' => AdminContentPageController::class, 'action' => 'delete'],
        ['method' => 'POST', 'path' => '/admin/content-pages/upload-image', 'controller' => AdminContentPageController::class, 'action' => 'uploadImage'],

        ['method' => 'GET',  'path' => '/preview/{token}', 'controller' => PreviewController::class, 'action' => 'show'],
        ['method' => 'GET',  'path' => '/listing/{slug}',  'controller' => ListingController::class, 'action' => 'show'],
        ['method' => 'GET',  'path' => '/pages/{slug}',    'controller' => PageController::class, 'action' => 'show'],

        ['method' => 'GET',  'path' => '/register', 'controller' => AuthController::class, 'action' => 'register'],
        ['method' => 'POST', 'path' => '/register', 'controller' => AuthController::class, 'action' => 'register'],
        ['method' => 'GET',  'path' => '/login',     'controller' => AuthController::class, 'action' => 'login'],
        ['method' => 'POST', 'path' => '/login',     'controller' => AuthController::class, 'action' => 'login'],
        ['method' => 'GET',  'path' => '/logout',    'controller' => AuthController::class, 'action' => 'logout'],

        ['method' => 'GET',  'path' => '/getting-ready', 'controller' => GettingReadyController::class, 'action' => 'show'],
        ['method' => 'POST', 'path' => '/getting-ready', 'controller' => GettingReadyController::class, 'action' => 'show'],

        ['method' => 'GET',  'path' => '/new-property',                 'controller' => NewPropertyController::class, 'action' => 'landing'],
        ['method' => 'GET',  'path' => '/new-property/location-options', 'controller' => NewPropertyController::class, 'action' => 'locationOptions'],

        ['method' => 'POST', 'path' => '/new-property/gallery/upload',  'controller' => NewPropertyController::class, 'action' => 'galleryUpload'],
        ['method' => 'POST', 'path' => '/new-property/gallery/delete',  'controller' => NewPropertyController::class, 'action' => 'galleryDelete'],
        ['method' => 'POST', 'path' => '/new-property/gallery/caption', 'controller' => NewPropertyController::class, 'action' => 'galleryCaption'],
        ['method' => 'POST', 'path' => '/new-property/gallery/cover',   'controller' => NewPropertyController::class, 'action' => 'galleryCover'],
        ['method' => 'POST', 'path' => '/new-property/gallery/reorder', 'controller' => NewPropertyController::class, 'action' => 'galleryReorder'],

        ['method' => 'POST', 'path' => '/new-property/go-online/apply-coupon', 'controller' => NewPropertyController::class, 'action' => 'applyCoupon'],

        ['method' => 'GET',  'path' => '/new-property/{step}', 'controller' => NewPropertyController::class, 'action' => 'step'],
        ['method' => 'POST', 'path' => '/new-property/{step}', 'controller' => NewPropertyController::class, 'action' => 'step'],

        ['method' => 'GET',  'path' => '/account', 'controller' => AccountController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/account/{country}', 'controller' => AccountController::class, 'action' => 'index'],
        ['method' => 'GET',  'path' => '/account/{country}/{province}', 'controller' => AccountController::class, 'action' => 'index'],
        ['method' => 'POST', 'path' => '/account/properties/{id}/photos/upload', 'controller' => AccountController::class, 'action' => 'photoUpload'],
        ['method' => 'POST', 'path' => '/account/properties/{id}/photos/delete', 'controller' => AccountController::class, 'action' => 'photoDelete'],
        ['method' => 'POST', 'path' => '/account/properties/{id}/photos/cover',  'controller' => AccountController::class, 'action' => 'photoCover'],
        ['method' => 'POST', 'path' => '/account/properties/{id}/photos/caption', 'controller' => AccountController::class, 'action' => 'photoCaption'],
        ['method' => 'POST', 'path' => '/account/properties/{id}/photos/reorder', 'controller' => AccountController::class, 'action' => 'photoReorder'],
        ['method' => 'GET',  'path' => '/account/properties/{id}/{section}', 'controller' => AccountController::class, 'action' => 'edit'],
        ['method' => 'POST', 'path' => '/account/properties/{id}/{section}', 'controller' => AccountController::class, 'action' => 'edit'],
    ],
];
