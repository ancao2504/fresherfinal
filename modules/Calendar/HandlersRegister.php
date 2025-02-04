<?php

/*
*	HandlerRegister.php
*	Author: Hieu Nguyen
*	Date: 2019-11-27
*   Purpose: register event handler for Calendar
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

$handlerName = 'CalendarHandler';
$batchHandlerName = 'CalendarBatchHandler';