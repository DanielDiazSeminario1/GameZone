<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');
// =======================================================
//   API VERSIONAMIENTO
// =======================================================
$routes->group('api/v1', ['namespace' => 'App\Controllers'], function ($routes) {

    // INVENTARIO
    $routes->group('inventario', static function ($routes) {
        $routes->get('', 'InventarioController::index');
        $routes->post('', 'InventarioController::create');
        $routes->get('(:any)', 'InventarioController::show/$1');
        $routes->patch('(:any)', 'InventarioController::update/$1');
        $routes->delete('(:any)', 'InventarioController::delete/$1');
    });
    // 2. AREA (AGREGA ESTO)
    $routes->group('area', static function ($routes) {
        $routes->get('', 'AreaController::index');       // Para el GET /area
        $routes->post('', 'AreaController::create');     // Para el POST /area
        $routes->get('(:any)', 'AreaController::show/$1');
        $routes->patch('(:any)', 'AreaController::update/$1');
        $routes->delete('(:any)', 'AreaController::delete/$1');
    });
});


// =======================================================
