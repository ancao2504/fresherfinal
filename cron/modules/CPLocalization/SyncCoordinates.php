<?php

/*
*	SyncCoordinates.php
*	Author: Phuc Lu
*	Date: 2020.07.14
*   Purpose: create function for cron to get coordinate for all modules
*/

vimport('includes.runtime.Globals');
include_once('modules/CPLocalization/models/Service.php');
CPLocalization_Service_Model::syncCoordinates();