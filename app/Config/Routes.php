<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ================= AUTH (PUBLIC) =================
$routes->get('/login',  'Auth::login');
$routes->post('/login', 'Auth::loginProcess');
$routes->get('/logout', 'Auth::logout');


// ================= ROUTE LOGIN =================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Dashboard::index');

    // Kantor
    $routes->get('/kantor',         'Kantor::index');
    $routes->post('/kantor/update', 'Kantor::update');

    // Absensi
    $routes->get('/absensi',         'Absensi::index');
    $routes->get('/absensi/riwayat', 'Absensi::riwayat');
    $routes->post('/absen-masuk',    'Absensi::absenMasuk');
    $routes->post('/absen-pulang',   'Absensi::absenPulang');

    // Idle
    $routes->post('/idle/start', 'Idle::start');
    $routes->post('/idle/stop',  'Idle::stop');

    // Break
    $routes->post('/break/mulai',   'BreakController::mulai');
    $routes->post('/break/selesai', 'BreakController::selesai');

    // ================= TASK =================
    $routes->get('/task',               'Task::index');
    $routes->get('/task/create',        'Task::create');
    $routes->post('/task/store',        'Task::store');
    $routes->get('/task/detail/(:num)', 'Task::detail/$1');

    $routes->post('/task/update-status/(:num)', 'Task::updateStatus/$1');
    $routes->post('/task/upload/(:num)',        'Task::upload/$1');
    $routes->post('/task/delete/(:num)',        'Task::delete/$1');

    // Notification (HARUS DI DALAM GROUP)
    $routes->get('/notifications', 'Notification::index');
});


// ================= MANAGER ONLY =================
$routes->group('', ['filter' => 'manager'], function ($routes) {

    $routes->get('/users',                'User::index');
    $routes->get('/users/create',         'Auth::register');
    $routes->post('/users/store',         'Auth::registerProcess');
    $routes->get('/users/edit/(:num)',    'User::edit/$1');
    $routes->post('/users/update/(:num)', 'User::update/$1');
    $routes->post('/users/toggle/(:num)', 'User::toggleStatus/$1');
    $routes->post('/users/delete/(:num)', 'User::delete/$1');

});