{*
    CompareCallsThisYearWidget.tpl
    Author: Hieu Nguyen
    Date: 2019-10-26
*}

{strip}
    <script type="text/javascript">
        Vtiger_Custom_Widget_Js('PBXManager_CompareCallsThisYear_Widget_Js', {}, {});
    </script>
    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/PBXManager/resources/dashboard/CompareCallsWidget.js")}"></script>

    <div class="dashboardWidgetHeader">
        {include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
    <div class="dashboardWidgetContent compare-calls-this-year-widget">
        {$WIDGET_CONTENT}
    </div>
    <div class="widgeticons dashBoardWidgetFooter">
        {include file="modules/PBXManager/tpls/dashboard/CompareCallsWidgetFooter.tpl"}
    </div>
{/strip}


















