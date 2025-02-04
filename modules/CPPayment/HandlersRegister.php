<?php

/*
*	HandlerRegister.php
*	Author: Phuc Lu
*	Date: 2019.08.15
*   Purpose: provide handler register for module CPReceipt
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

$handlerName = 'CPPaymentHandler';
$batchHandlerName = 'CPPaymentBatchHandler';