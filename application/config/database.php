<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;
$hostname ='localhost';

// this is digifoods database
// $username ='cloudbilling_digifoods';
// $password ='PBat90CUuVyGM';
// $database ='cloudbilling_digifoods';	

// this is demo database
// $username ='cloudbilling_demo';
// $password ='FSfb967scUYus';
// $database ='cloudbilling_demo_stable';

// this is demo database for localhost
$username ='root';
$password ='';
$database ='cloudbilling_v2';

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $hostname,
	'username' => $username,
	'password' => $password,
	'database' => $database,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);