<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

// =======================================================
//   API VERSIONAMIENTO (v1)
// =======================================================
$routes->group('api/v1', ['namespace' => 'App\Controllers'], function ($routes) {

    // 1. INVENTARIO
    $routes->group('inventario_qr', static function ($routes) {
        $routes->get('', 'InventarioController::index');
        $routes->post('', 'InventarioController::create');
        $routes->get('(:any)', 'InventarioController::show/$1');
        $routes->patch('(:any)', 'InventarioController::update/$1');
        $routes->delete('(:any)', 'InventarioController::delete/$1');
    });

    // 2. FABRICANTES
    $routes->group('fabricantes', static function ($routes) {
        $routes->get('', 'Fabricantes::index');
        $routes->post('', 'Fabricantes::create');
        $routes->get('(:any)', 'Fabricantes::show/$1');
        $routes->patch('(:any)', 'Fabricantes::update/$1');
        $routes->delete('(:any)', 'Fabricantes::delete/$1');
    });

    // 3. PRODUCTOS
    $routes->group('productos', static function ($routes) {
        $routes->get('', 'Productos::index');
        $routes->post('', 'Productos::create');
        $routes->get('(:any)', 'Productos::show/$1');
        $routes->patch('(:any)', 'Productos::update/$1');
        $routes->delete('(:any)', 'Productos::delete/$1');
    });

    // 4. SERIES
    $routes->group('series', static function ($routes) {
        $routes->get('', 'Series::index');
        $routes->post('', 'Series::create');
        $routes->get('(:any)', 'Series::show/$1');
        $routes->patch('(:any)', 'Series::update/$1');
        $routes->delete('(:any)', 'Series::delete/$1');
    });

});