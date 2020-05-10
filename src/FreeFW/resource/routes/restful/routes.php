<?php
require_once(__DIR__ . '/country.php');
require_once(__DIR__ . '/email.php');
require_once(__DIR__ . '/jobqueue.php');
require_once(__DIR__ . '/lang.php');
require_once(__DIR__ . '/notification.php');

use \FreeFW\Router\Route as FFCSTRT;

$localRoutes = [
    'free_f_w.app.document' => [
        FFCSTRT::ROUTE_COLLECTION => 'FreeFW/Core/Application',
        FFCSTRT::ROUTE_COMMENT    => 'Génération complète de la documentation de l\'application',
        FFCSTRT::ROUTE_METHOD     => \FreeFW\Router\Route::METHOD_POST,
        FFCSTRT::ROUTE_URL        => '/v1/app/documentation',
        FFCSTRT::ROUTE_CONTROLLER => 'FreeFW::Controller::Application',
        FFCSTRT::ROUTE_FUNCTION   => 'documentAll',
        FFCSTRT::ROUTE_AUTH       => \FreeFW\Router\Route::AUTH_NONE,
        FFCSTRT::ROUTE_MIDDLEWARE => [],
        FFCSTRT::ROUTE_INCLUDE    => [],
        FFCSTRT::ROUTE_SCOPE      => ['ROOT'],
    ],
    'free_f_w.model.generate' => [
        FFCSTRT::ROUTE_COLLECTION => 'FreeFW/Core/Model',
        FFCSTRT::ROUTE_COMMENT    => 'Génération des fichiers d\'un modèle',
        FFCSTRT::ROUTE_METHOD     => \FreeFW\Router\Route::METHOD_POST,
        FFCSTRT::ROUTE_URL        => '/v1/dev/model/generate',
        FFCSTRT::ROUTE_CONTROLLER => 'FreeFW::Controller::Model',
        FFCSTRT::ROUTE_FUNCTION   => 'createModel',
        FFCSTRT::ROUTE_AUTH       => \FreeFW\Router\Route::AUTH_NONE,
        FFCSTRT::ROUTE_MIDDLEWARE => [],
        FFCSTRT::ROUTE_INCLUDE    => [],
        FFCSTRT::ROUTE_SCOPE      => ['ROOT'],
    ],
    'free_f_w.model.document' => [
        FFCSTRT::ROUTE_COLLECTION => 'FreeFW/Core/Model',
        FFCSTRT::ROUTE_COMMENT    => 'Génération de la documentation d\'un modèle',
        FFCSTRT::ROUTE_METHOD     => \FreeFW\Router\Route::METHOD_POST,
        FFCSTRT::ROUTE_URL        => '/v1/dev/model/document',
        FFCSTRT::ROUTE_CONTROLLER => 'FreeFW::Controller::Model',
        FFCSTRT::ROUTE_FUNCTION   => 'documentModel',
        FFCSTRT::ROUTE_AUTH       => \FreeFW\Router\Route::AUTH_NONE,
        FFCSTRT::ROUTE_MIDDLEWARE => [],
        FFCSTRT::ROUTE_INCLUDE    => [],
        FFCSTRT::ROUTE_SCOPE      => ['ROOT'],
    ],
];
$localRoutes = array_merge(
    $localRoutes,
    $routes_country,
    $routes_email,
    $routes_jobqueue,
    $routes_lang,
    $routes_notification
);
return $localRoutes;