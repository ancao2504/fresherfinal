{*
    CallsSummaryThisWeekWidget.tpl
    Author: Hieu Nguyen
    Date: 2019-10-01
*}

{strip}
    <link rel="stylesheet" href="modules/PBXManager/resources/dashboard/CallsSummaryWidget.css" />
    <script type="text/javascript">
        Vtiger_Custom_Widget_Js('PBXManager_CallsSummaryThisWeek_Widget_Js', {}, {});
    </script>

    <div class="dashboardWidgetHeader">
        {include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
    <div class="dashboardWidgetContent calls-summary-widget">
        {$WIDGET_CONTENT}
    </div>
    <div class="widgeticons dashBoardWidgetFooter">
        {include file="modules/PBXManager/tpls/dashboard/CallsSummaryWidgetFooter.tpl"}
    </div>
{/strip}


















