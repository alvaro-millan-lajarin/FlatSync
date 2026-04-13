<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Auth (web + API comparten los mismos endpoints)
$routes->get('/login',    'AuthController::login');
$routes->post('/login',   'AuthController::loginPost');
$routes->get('/register', 'AuthController::register');
$routes->post('/register','AuthController::registerPost');
$routes->get('/logout',   'AuthController::logout');
$routes->post('/logout',  'AuthController::logout');

// API — rutas exclusivas para la app móvil
$routes->post('/api/login',    'AuthController::loginPost');
$routes->post('/api/register', 'AuthController::registerPost');
$routes->post('/api/logout',   'AuthController::logout');
$routes->get('/api/homes',     'HomesController::select');
$routes->post('/api/homes/join',   'HomesController::joinPost');
$routes->post('/api/homes/create', 'HomesController::createPost');
$routes->get('/api/expenses',      'ExpensesController::index');
$routes->post('/api/expenses',     'ExpensesController::store');
$routes->post('/api/expenses/(:num)/delete', 'ExpensesController::delete/$1');
$routes->get('/api/expenses/balance',        'ExpensesController::balance');
$routes->get('/api/chores',                  'ChoresController::index');
$routes->post('/api/chores',                 'ChoresController::store');
$routes->post('/api/chores/(:num)/done',     'ChoresController::markDone/$1');
$routes->post('/api/chores/(:num)/delete',   'ChoresController::delete/$1');
$routes->get('/api/chat',                    'ChatController::index');
$routes->post('/api/chat',                   'ChatController::send');
$routes->get('/api/chat/poll',               'ChatController::poll');
$routes->get('/api/members',                 'MembersController::index');

// Landing & Dashboard
$routes->get('/',          'HomeController::landing');
$routes->get('/dashboard', 'HomeController::index');

// Chores
$routes->get('/chores',                          'ChoresController::index');
$routes->post('/chores/store',                   'ChoresController::store');
$routes->post('/chores/mark-done/(:num)',         'ChoresController::markDone/$1');
$routes->post('/chores/update/(:num)',            'ChoresController::update/$1');
$routes->post('/chores/delete/(:num)',            'ChoresController::delete/$1');
$routes->get('/chores/swap-requests',            'ChoresController::swapRequests');
$routes->post('/chores/swap/request',            'ChoresController::swapRequest');
$routes->post('/chores/swap/(:num)/accept',      'ChoresController::swapAccept/$1');
$routes->post('/chores/swap/(:num)/decline',     'ChoresController::swapDecline/$1');
$routes->post('/chores/swap/(:num)/cancel',      'ChoresController::swapCancel/$1');

// Expenses
$routes->get('/expenses',              'ExpensesController::index');
$routes->post('/expenses/store',       'ExpensesController::store');
$routes->post('/expenses/update/(:num)','ExpensesController::update/$1');
$routes->post('/expenses/delete/(:num)','ExpensesController::delete/$1');
$routes->post('/expenses/settle',      'ExpensesController::settle');
$routes->get('/expenses/balance',      'ExpensesController::balance');
$routes->get('/expenses/summary',      'ExpensesController::summary');
$routes->get('/expenses/export',       'ExpensesController::export');

// Services
$routes->get('/services',        'ServicesController::index');
$routes->get('/services/nearby', 'ServicesController::nearby');

// Members
$routes->get('/members', 'MembersController::index');

// Profile
$routes->get('/profile',      'ProfileController::index');
$routes->get('/profile/edit', 'ProfileController::edit');
$routes->post('/profile/edit','ProfileController::update');

// Chat
$routes->get('/chat',           'ChatController::index');
$routes->post('/chat/send',     'ChatController::send');
$routes->get('/chat/poll',      'ChatController::poll');
$routes->post('/chat/notes/store',           'ChatController::noteStore');
$routes->post('/chat/notes/delete/(:num)',   'ChatController::noteDelete/$1');
$routes->post('/chat/message/delete/(:num)', 'ChatController::messageDelete/$1');

// Homes (multi-sesión)
$routes->get('/homes',                'HomesController::select');
$routes->get('/homes/join',           'HomesController::joinGet');
$routes->post('/homes/join',          'HomesController::joinPost');
$routes->get('/homes/create',         'HomesController::createGet');
$routes->post('/homes/create',        'HomesController::createPost');
$routes->post('/homes/switch/(:num)',      'HomesController::switchHome/$1');
$routes->get('/homes/leave',               'HomesController::leaveSession');
$routes->post('/homes/(:num)/leave-home',  'HomesController::leaveHome/$1');
$routes->post('/homes/(:num)/delete',      'HomesController::deleteHome/$1');
