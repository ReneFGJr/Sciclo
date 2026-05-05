
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
$routes->match(['get', 'post'], 'application', 'Application::index');
$routes->get('seal-statistics', 'SealStatistics::index');
$routes->get('about', 'About::about_project');
$routes->get('about/certification', 'About::certification');

$routes->match(['get', 'post'], 'contact', 'About::contact');