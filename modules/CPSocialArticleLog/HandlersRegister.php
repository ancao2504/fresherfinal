<?php

/*
*	Handlers Register structure
*	Author: Phu Vo
*	Date: 2019.08.21
*/

$registeredEvents = array(
    'vtiger.batchevent.save',
    'vtiger.batchevent.beforedelete',
    'vtiger.batchevent.afterdelete',
    'vtiger.batchevent.beforerestore',
    'vtiger.batchevent.afterrestore',
);

// $handlerName = 'CPSocialArticleLogHandler';
$batchHandlerName = 'CPSocialArticleLogBatchHandler';