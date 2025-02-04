<?php

/*
*	HandlerRegister.php
*	Author: Phuc Lu
*	Date: 2020.03.05
*   Purpose: provide handler register for module Potentials
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

$handlerName = 'PotentialsHandler';
$batchHandlerName = 'PotentialsBatchHandler';