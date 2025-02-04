<?php

/*
*	HandlerRegister.php
*	Author: Phu Vo
*	Date: 2021.11.08
*   Purpose: provide handler register for module CPZaloAdsForm
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
    'vtiger.entity.beforemerge',
    'vtiger.entity.aftermerge',
);

$handlerName = 'CPZaloAdsFormHandler';
$batchHandlerName = 'CPZaloAdsFormBatchHandler';