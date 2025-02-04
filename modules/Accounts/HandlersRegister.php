<?php

/*
*	Sample Handlers Register structure
*	Author: Hieu Nguyen
*	Date: 2018-07-19
*	Usage: Copy this file into a new file named HandlersRegister.php to handle register event handlers for Account.
		Do that the same for other modules (DON'T FORGET to modify the $handlerName to match with that module. Ex: ContactHandler)
	Note: REMOVE this comment on the file you create youself to avoid messing comments
*/

$registeredEvents = array(
	'vtiger.entity.beforesave',
	'vtiger.entity.aftersave',
	'vtiger.entity.beforedelete',
	'vtiger.entity.afterdelete',
	'vtiger.batchevent.save',
	'vtiger.batchevent.beforedelete',
	'vtiger.batchevent.afterdelete',
	'vtiger.batchevent.beforerestore',
	'vtiger.batchevent.afterrestore',
);

$handlerName = 'AccountHandler';
$batchHandlerName = 'AccountBatchHandler';