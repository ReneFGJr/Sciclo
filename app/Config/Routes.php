<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->match(['get', 'post'], 'forgot', 'Auth::forgot');
$routes->get('logout', 'Auth::logout');
$routes->get('seal-statistics', 'SealStatistics::index');
$routes->get('about', 'About::about_project');
$routes->get('about/certification', 'About::certification');


$routes->group('application', function($routes) {
    $routes->match(['get', 'post'], '/', 'Application::index');
	$routes->match(['get', 'post'], 'form', 'Application::form');
    $routes->match(['get', 'post'], 'form/(:num)/(:num)/(:num)', 'Application::form/$1/$2/$3');
    $routes->match(['get', 'post'], 'form/(:num)/(:num)', 'Application::form/$1/$2/$3');
    $routes->match(['get', 'post'], 'form/(:num)', 'Application::form/$1/$2/$3');
    $routes->match(['get', 'post'], 'form/select/(:num)', 'Application::selectQuestionnaire/$1');
});

$routes->group('admin', function ($routes) {
    $routes->get('questions', 'Admin\\Questions::index');
    $routes->match(['get', 'post'], 'questions/add', 'Admin\\Questions::add');
    $routes->get('questions/delete/(:num)', 'Admin\\Questions::delete/$1');
    $routes->match(['get', 'post'], 'questions/edit/(:num)', 'Admin\\Questions::edit/$1');
});

$routes->match(['get', 'post'], 'contact', 'About::contact');