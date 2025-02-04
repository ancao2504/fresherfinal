<?php

/*
*	HandlerRegister.php
*	Author: Phu Vo
*	Date: 2020.05.23
*   Purpose: provide handler register for module CPEventRegistration
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

$handlerName = 'CPEventRegistrationHandler';
$batchHandlerName = 'CPEventRegistrationBatchHandler';