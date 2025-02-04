<?php

/*
*	HandlerRegister.php
*	Author: Phuc Lu
*	Date: 2019.07.26
*   Purpose: provide handler register for module CPSocialIntegration
*/

$registeredEvents = array(
    'vtiger.entity.beforesave',
    'vtiger.entity.aftersave',
    'vtiger.entity.beforedelete',
    'vtiger.entity.afterdelete',
    'vtiger.entity.afterrestore',
    'vtiger.batchevent.save',
    'vtiger.batchevent.beforedelete',
    'vtiger.batchevent.afterdelete',
    'vtiger.batchevent.beforerestore',
    'vtiger.batchevent.afterrestore',
);

$handlerName = 'CPSocialIntegrationHandler';
$batchHandlerName = 'CPSocialIntegrationBatchHandler';