<?php

/*
*	HandlersRegister.php
*	Author: Phuc Lu
*	Date: 2020.03.31
*   Purpose: provide handler register for CPMauticContactHistory
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

$handlerName = 'CPMauticContactHistoryHandler';
$batchHandlerName = 'CPMauticContactHistoryBatchHandler';