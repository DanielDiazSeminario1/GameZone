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
    $routes->group('inventario', static function ($routes) {
        $routes->get('', 'InventarioController::index');
        $routes->post('', 'InventarioController::create');
        $routes->get('sku/(:any)', 'InventarioController::showsku/$1');
        $routes->get('(:any)', 'InventarioController::show/$1');
        $routes->patch('(:any)', 'InventarioController::update/$1');
        $routes->delete('(:any)', 'InventarioController::delete/$1');
    });

    // 2. AREA
    $routes->group('area', static function ($routes) {
        $routes->get('', 'AreaController::index');
        $routes->post('', 'AreaController::create');
        $routes->get('(:any)', 'AreaController::show/$1');
        $routes->patch('(:any)', 'AreaController::update/$1');
        $routes->delete('(:any)', 'AreaController::delete/$1');
    });

    // 3. CATEGORIA (👇 AGREGADO AQUÍ CON EL MISMO FORMATO)
    $routes->group('categoria', static function ($routes) {
        $routes->get('', 'CategoriaController::index');       // GET api/v1/categoria
        $routes->post('', 'CategoriaController::create');     // POST api/v1/categoria
        $routes->get('(:any)', 'CategoriaController::show/$1');    // GET api/v1/categoria/{uuid}
        $routes->patch('(:any)', 'CategoriaController::update/$1'); // PATCH api/v1/categoria/{uuid}
        $routes->delete('(:any)', 'CategoriaController::delete/$1'); // DELETE api/v1/categoria/{uuid}
    });

});
// =======================================================