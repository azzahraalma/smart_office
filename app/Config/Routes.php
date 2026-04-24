<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ================= AUTH (PUBLIC) =================
$routes->get('/login',  'Auth::login');
$routes->post('/login', 'Auth::loginProcess');
$routes->get('/logout', 'Auth::logout');


// ================= ROUTE LOGIN (semua user yg sudah login) =================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Dashboard::index');

    // Kantor
    $routes->get('/kantor',         'Kantor::index');
    $routes->post('/kantor/update', 'Kantor::update');

    // Absensi karyawan
    $routes->get('/absensi',          'Absensi::index');
    $routes->get('/absensi/riwayat',  'Absensi::riwayat');
    $routes->post('/absen-masuk',     'Absensi::absenMasuk');
    $routes->post('/absen-pulang',    'Absensi::absenPulang');
    $routes->post('/absensi/izin',    'Absensi::izin');

    // Absensi manager
    $routes->get('/absensi/manager',             'Absensi::manager');
    $routes->post('/absensi/approve/(:num)',      'Absensi::approve/$1'); // ← pindah ke sini
    $routes->post('/absensi/reject/(:num)',       'Absensi::reject/$1');  // ← pindah ke sini

    // Idle
    $routes->post('/idle/start', 'Idle::start');
    $routes->post('/idle/stop',  'Idle::stop');

    // Break
    $routes->post('/break/mulai',   'BreakController::mulai');
    $routes->post('/break/selesai', 'BreakController::selesai');

    // Task
    $routes->get('/task',               'Task::index');
    $routes->get('/task/create',        'Task::create');
    $routes->post('/task/store',        'Task::store');
    $routes->get('/task/detail/(:num)', 'Task::detail/$1');
    $routes->post('/task/update-status/(:num)', 'Task::updateStatus/$1');
    $routes->post('/task/upload/(:num)',        'Task::upload/$1');
    $routes->post('/task/delete/(:num)',        'Task::delete/$1');

    // Notification
    $routes->get('/notifications',       'Notification::index');
    $routes->get('/notifications/clear', 'Notification::clear');
});


// ================= MANAGER ONLY =================
$routes->group('', ['filter' => 'manager'], function ($routes) {

    // User management
    $routes->get('/users',                'User::index');
    $routes->get('/users/create',         'Auth::register');
    $routes->post('/users/store',         'Auth::registerProcess');
    $routes->get('/users/edit/(:num)',    'User::edit/$1');
    $routes->post('/users/update/(:num)', 'User::update/$1');
    $routes->post('/users/toggle/(:num)', 'User::toggleStatus/$1');
    $routes->post('/users/delete/(:num)', 'User::delete/$1');
});