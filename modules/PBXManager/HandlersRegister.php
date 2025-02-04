<?php

/*
    HandlersRegister.php
    Author: Hieu Nguyen
    Date: 2020-09-08
    Purpose: register event handlers for PBXManager
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

$handlerName = 'PBXManagerHandler';
$batchHandlerName = 'PBXManagerBatchHandler';