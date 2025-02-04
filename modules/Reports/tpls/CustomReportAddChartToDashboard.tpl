{*
    CustomReportAddChartToDashboard.tpl
    Author: Hieu Nguyen
    Date: 2020-03-30
*}

{strip}
    {if Home_DashboardLogic_Helper::canEditDashboard() && !isForbiddenFeature('PinChartToDashboard')}
        <div id="add-chart-to-dashboard" class="btn-group pull-right">
            <button type="button" class="cursorPointer btn btn-default dropdown-toggle" title="{vtranslate('LBL_ADD_CHART_TO_DASHBOARD', 'Reports')}" data-toggle="dropdown" aria-expanded="false">
                <i class="fa vicon-pin" style="font-size: 13px;"></i>
            </button>
            
            <ul class="dropdown-menu">
                <li class="dropdown-header popover-title">{vtranslate('LBL_REPORT_HOMEPAGE', 'Reports')}</li>
                {assign var="DASHBOARD_TABS" value=getActiveDashboardTabs()}

                {foreach from=$DASHBOARD_TABS item=TAB_INFO}
                    <li class="dashboard-tab" data-tab-id="{$TAB_INFO.id}">
                        <a href="javascript:void(0);">{$TAB_INFO.tabname}</a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
{/strip}