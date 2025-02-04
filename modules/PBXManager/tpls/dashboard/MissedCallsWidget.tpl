{*
    MissedCallsWidget.tpl
    Author: Hieu Nguyen
    Date: 2019-10-23
*}

{strip}
    <link rel="stylesheet" href="{vresource_url("resources/libraries/DataTables/css/jquery.dataTables.min.css")}"></link>
    <script type="text/javascript">
        Vtiger_Custom_Widget_Js('PBXManager_MissedCalls_Widget_Js', {}, {});
    </script>

    <script type="text/javascript" src="{vresource_url("resources/libraries/DataTables/js/jquery.dataTables.min.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/PBXManager/resources/dashboard/MissedCallsWidget.js")}"></script>

    <div class="dashboardWidgetHeader">
        {include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
    <div class="dashboardWidgetContent missed-calls-widget">
        {$WIDGET_CONTENT}
    </div>
    <div class="widgeticons dashBoardWidgetFooter">
        <div class="footerIcons pull-right">
            {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=false}
        </div>
    </div>
{/strip}


















