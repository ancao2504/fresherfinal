<?php

/*
    HandlersRegister.php
    Author: Hieu Nguyen
    Date: 2021-04-02
    Purpose: register event handlers for Employees
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

$handlerName = 'EmployeeHandler';
$batchHandlerName = 'EmployeeBatchHandler';