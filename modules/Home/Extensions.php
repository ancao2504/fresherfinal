<?php

// Get current module model
$currentModule = Vtiger_Module::getInstance('Home');

// Add widget [CallCenter] Calls Summary Today into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_CALLS_SUMMARY_TODAY_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=CallsSummaryToday');

// Add widget [CallCenter] Calls Summary This Week into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_CALLS_SUMMARY_THIS_WEEK_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=CallsSummaryThisWeek');

// Add widget [CallCenter] Calls Summary This Month into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_CALLS_SUMMARY_THIS_MONTH_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=CallsSummaryThisMonth');

// Add widget [CallCenter] Report Inbound Calls Purpose Today into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_REPORT_INBOUND_CALLS_PURPOSE_TODAY_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=ReportInboundCallsPurposeToday');

// Add widget [CallCenter] Report Inbound Calls Purpose This Week into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_REPORT_INBOUND_CALLS_PURPOSE_THIS_WEEK_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=ReportInboundCallsPurposeThisWeek');

// Add widget [CallCenter] Report Inbound Calls Purpose This Month into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_REPORT_INBOUND_CALLS_PURPOSE_THIS_MONTH_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=ReportInboundCallsPurposeThisMonth');

// Add widget [CallCenter] Report Outbound Calls Purpose Today into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_REPORT_OUTBOUND_CALLS_PURPOSE_TODAY_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=ReportOutboundCallsPurposeToday');

// Add widget [CallCenter] Report Outbound Calls Purpose This Week into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_REPORT_OUTBOUND_CALLS_PURPOSE_THIS_WEEK_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=ReportOutboundCallsPurposeThisWeek');

// Add widget [CallCenter] Report Outbound Calls Purpose This Month into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_REPORT_OUTBOUND_CALLS_PURPOSE_THIS_MONTH_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=ReportOutboundCallsPurposeThisMonth');

// Add widget [CallCenter] Compare Calls This Week into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_COMPARE_CALLS_THIS_WEEK_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=CompareCallsThisWeek');

// Add widget [CallCenter] Compare Calls This Month into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_COMPARE_CALLS_THIS_MONTH_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=CompareCallsThisMonth');

// Add widget [CallCenter] Compare Calls This Year into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_COMPARE_CALLS_THIS_YEAR_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=CompareCallsThisYear');

// Add widget [CallCenter] Missed Calls into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_MISSED_CALLS_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=MissedCalls');

// Add widget [CallCenter] Planned Calls into Dashboard
$currentModule->addLink('DASHBOARDWIDGET', 'LBL_CALLCENTER_PLANNED_CALLS_WIDGET', 'index.php?module=PBXManager&view=ShowWidget&name=PlannedCalls');