<?php

// Added by Hieu Nguyen on 2021-11-22
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

$handlerName = 'TargetListHandler';
$batchHandlerName = 'TargetListBatchHandler';