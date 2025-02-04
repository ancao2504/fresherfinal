<?php

/*
	Script: hash_password.php
	Author: Hieu Nguyen
	Date: 2022-04-25
	Purpose: to generate password hash for auto install process
	Usage: for CLI only
*/

ini_set('display_errors', 'Off');
error_reporting(~E_ALL);

if (PHP_SAPI != 'cli') die('This script is for CLI only!!!');
require_once('modules/Users/Users.php');

// Parse options
$shortOptions = 'u:p:';
$longOptions = ['username:', 'password:'];
$options = getopt($shortOptions, $longOptions);
$username = $options['u'] ?? $options['username'];
$password = $options['p'] ?? $options['password'];

// Generate password hash
$user = new Users();
$user->column_fields['user_name'] = $username;
$encrtypedPassword = $user->encrypt_password($password, $user->DEFAULT_PASSWORD_CRYPT_TYPE);

// Return result
$result = [
	'md5_hash' => md5($password),
	'encrypted_password' => $encrtypedPassword
];

echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);