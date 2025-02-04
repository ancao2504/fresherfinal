<?php

/*
*	HandlersRegister.php
*	Author: Hieu Nguyen
*	Date: 2020-06-26
*   Purpose: provide handler register for Documents
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

$handlerName = 'DocumentsHandler';
$batchHandlerName = 'DocumentsBatchHandler';