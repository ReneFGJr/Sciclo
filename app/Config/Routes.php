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
$routes->group('about', function ($routes) {
    $routes->get('/', 'About::about_project');
    $routes->get('certification', 'About::certification');
    $routes->get('faq', 'About::faq');
    $routes->get('contact', 'About::contact');
});
$routes->get('faq', 'About::faq');
$routes->get('glossary', 'About::glossary');

$routes->group('application', function($routes) {
    $routes->match(['get', 'post'], '/', 'Application::index');
	$routes->match(['get', 'post'], 'form', 'Application::form');
    $routes->match(['get', 'post'], 'form/(:num)/(:num)/(:num)', 'Application::form/$1/$2/$3');
    $routes->match(['get', 'post'], 'form/(:num)/(:num)', 'Application::form/$1/$2');
    $routes->match(['get', 'post'], 'form/(:num)', 'Application::form/$1');
    $routes->match(['get', 'post'], 'form/select/(:num)', 'Application::selectQuestionnaire/$1');
    $routes->post('submit_questionnaire', 'Application::submitQuestionnaire');
    $routes->post('answer/save', 'Application::saveQuestionnaireAnswer');
    $routes->post('evidence/save', 'Application::saveEvidence');
    $routes->post('evidence/delete/(:num)', 'Application::deleteEvidence/$1');
});

$routes->group('admin', function ($routes) {
    $routes->get('questions', 'Admin\\Questions::index');
    $routes->match(['get', 'post'], 'questions/add', 'Admin\\Questions::add');
    $routes->get('questions/delete/(:num)', 'Admin\\Questions::delete/$1');
    $routes->match(['get', 'post'], 'questions/edit/(:num)', 'Admin\\Questions::edit/$1');
    $routes->get('guide-requirements', 'Admin\\GuideRequirements::index');
    $routes->get('glossario', 'Admin\\Glossario::index');
    $routes->match(['get', 'post'], 'glossario/create', 'Admin\\Glossario::create');
    $routes->match(['get', 'post'], 'glossario/edit/(:num)', 'Admin\\Glossario::edit/$1');
    $routes->get('glossario/delete/(:num)', 'Admin\\Glossario::delete/$1');
    $routes->get('faq', 'Admin\\Faq::index');
    $routes->match(['get', 'post'], 'faq/create', 'Admin\\Faq::create');
    $routes->get('faq/delete/(:num)', 'Admin\\Faq::delete/$1');
    $routes->match(['get', 'post'], 'faq/edit/(:num)', 'Admin\\Faq::edit/$1');
});

$routes->match(['get', 'post'], 'contact', 'About::contact');