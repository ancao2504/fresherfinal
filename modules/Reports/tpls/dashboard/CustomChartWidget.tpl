{*
    CustomChartWidget.tpl
    Author: Hieu Nguyen
    Date: 2020-03-26
*}

{strip}
    <script type="text/javascript">
        Vtiger_Custom_Widget_Js('PBXManager_CustomChart_Widget_Js', {}, {});
    </script>
    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    
    <div class="dashboardWidgetHeader">
        <div class="title clearfix">
            {include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
        </div>
    </div>
    <div class="dashboardWidgetContent custom-chart-widget">
        {$WIDGET_CONTENT}
    </div>
    <div class="widgeticons dashBoardWidgetFooter">
        <div class="filterContainer">
            {$WIDGET_FILTER}
        </div>
        <div class="footerIcons pull-right">
            {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=!$IS_CHART_REPORT ALLOW_FULL_SCREEN=true}
        </div>
    </div>
{/strip}


















