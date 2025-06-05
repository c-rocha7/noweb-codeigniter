<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Rotas de autenticação
$route['api/auth/login']['POST'] = 'api/auth/login';
$route['api/auth/register']['POST'] = 'api/auth/register';
$route['api/auth/logout']['POST'] = 'api/auth/logout';
$route['api/auth/me']['GET'] = 'api/auth/me';

// Rotas de usuários (protegidas)
$route['api/users']['GET'] = 'api/users/index';
$route['api/users/(:num)']['GET'] = 'api/users/show/$1';
$route['api/users']['POST'] = 'api/users/create';
$route['api/users/(:num)']['PUT'] = 'api/users/update/$1';
$route['api/users/(:num)']['DELETE'] = 'api/users/delete/$1';

// Rotas de notícias
$route['api/news']['GET'] = 'api/news/index';
$route['api/news/categories']['GET'] = 'api/news/categories';
$route['api/news/my']['GET'] = 'api/news/my_news';
$route['api/news/(:num)']['GET'] = 'api/news/show/$1';
$route['api/news']['POST'] = 'api/news/create';
$route['api/news/(:num)']['PUT'] = 'api/news/update/$1';
$route['api/news/(:num)']['DELETE'] = 'api/news/delete/$1';
