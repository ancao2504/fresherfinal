<?php

/*
*	HandlersRegister.php
*	Author: Phu Vo
*	Date: 2020.12.03
*   Purpose: provide handler register for campaigns
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

$handlerName = 'CampaignHandler';
$batchHandlerName = 'CampaignBatchHandler';