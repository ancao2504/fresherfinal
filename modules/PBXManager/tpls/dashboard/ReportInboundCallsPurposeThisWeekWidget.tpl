{*
    ReportInboundCallsPurposeThisWeekWidget.tpl
    Author: Hieu Nguyen
    Date: 2020-02-18
*}

{strip}
    <script type="text/javascript">
        Vtiger_Custom_Widget_Js('PBXManager_ReportInboundCallsPurposeThisWeek_Widget_Js', {}, {});
    </script>
    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/PBXManager/resources/dashboard/ReportCallsPurposeWidget.js")}"></script>

    <div class="dashboardWidgetHeader">
        {include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
    <div class="dashboardWidgetContent report-inbound-calls-purpose-this-week-widget">
        {$WIDGET_CONTENT}
    </div>
    <div class="widgeticons dashBoardWidgetFooter">
        <div class="footerIcons pull-right">
            {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=false ALLOW_FULL_SCREEN=true}
        </div>
    </div> 
{/strip}


















