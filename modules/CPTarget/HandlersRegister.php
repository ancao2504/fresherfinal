<?php

/*
*	HandlerRegister.php
*	Author: Phuc Lu
*	Date: 2019.08.13
*   Purpose: provide handler register for module CPTarget
*/

$registeredEvents = array(
    'vtiger.entity.beforesave',
    'vtiger.entity.aftersave',
    'vtiger.entity.beforedelete',
    'vtiger.entity.afterdelete',
    'vtiger.cptarget.converttarget',
    'vtiger.batchevent.save',
    'vtiger.batchevent.beforedelete',
    'vtiger.batchevent.afterdelete',
    'vtiger.batchevent.beforerestore',
    'vtiger.batchevent.afterrestore',
    'vtiger.entity.beforemerge',
    'vtiger.entity.aftermerge',
);

$handlerName = 'CPTargetHandler';
$batchHandlerName = 'CPTargetBatchHandler';