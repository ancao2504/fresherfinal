<?php

/*
	File: check_session.php
	Author: Hieu Nguyen
	Date: 2022-04-08
	Purpose: to check if the logged in session is alive or not
*/

session_start();
$result = ['session_alive' => true];

if (empty($_SESSION['authenticated_user_id'])) {
	$result = ['session_alive' => false];
}

echo json_encode($result);