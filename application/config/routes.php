<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['api/register'] = 'api/register';
$route['api/absen'] = 'api/absen';
$route['api/rest'] = 'api/rest';
$route['api/delete'] = 'api/delete';
$route['api/check_token'] = 'api/check_token';
