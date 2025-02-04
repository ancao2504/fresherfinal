{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/DashBoard.php *}
    
{strip}
<div class="dashBoardContainer clearfix">
        {* Added by Phu Vo on 2020.10.14 to display a row for submit dashboard layout edit mode *}
        {if !empty($EDITING_DASHBOARD_ID)}
            {assign var=DASHBOARD_INFO value=Home_DashBoard_Model::getDashboardTemplateById($EDITING_DASHBOARD_ID)}
        {/if}
        {if $DASHBOARD_EDIT_MODE == true}
            <div class="container-fluid dashboard-edit-mode-actions">
                <div class="pull-left">
                    {if !empty($EDITING_DASHBOARD_ID)}
                        <h4 class="fieldLabel" style="margin-bottom: 0px; margin-top: 6px">
                            {vtranslate('LBL_DASHBOARD_EDITING_DASHBOARD_LAYOUT', 'Home')}: "{$DASHBOARD_INFO['name']}"
                        </h4>
                    {/if}
                </div>
                <div class="moreSettings pull-right">
                    <button class="exitEditLayoutMode btn btn-link redColor" type="button" data-dismiss="modal">{vtranslate('LBL_DASHBOARD_EXIT_EDIT_LAYOUT_MODE', 'Home')}</button>
                    {if $DASHBOARD_INFO['status'] == 'Active'}
                        &nbsp;&nbsp;
                        <button class="btn btn-danger applyLayoutToUsers" type="button" title="{vtranslate('LBL_DASHBOARD_APPLY_DASHBOARD_LAYOUT_TO_USERS_TITLE', 'Home')}">
                            <i class="far fa-check" aria-hidden="true"></i>&nbsp;
                            {vtranslate('LBL_DASHBOARD_APPLY_DASHBOARD_LAYOUT_TO_USERS', 'Home')}
                        </button>
                    {/if}
                </div>
                <div style="clear: both"></div>
            </div>
        {/if}
        {* End Phu Vo *}

        {* Added by Hieu Nguyen on 2020-10-12 to provide dashoard tab template for JS *}
        <div id="dashboard-tab-template" style="display: none">
            {include file="layouts/v7/modules/Vtiger/dashboards/DashboardTabTemplate.tpl"}
        </div>
        {* End Hieu Nguyen *}

        <div class="tabContainer">
            <ul class="nav nav-tabs tabs sortable container-fluid">
                {foreach key=index item=TAB_DATA from=$DASHBOARD_TABS}
                    {* Modified by Hieu Nguyen on 2020-10-12 to render dashboard tabs using template *}
                    {include file="layouts/v7/modules/Vtiger/dashboards/DashboardTabTemplate.tpl"}
                    {* End Hieu Nguyen *}
                {/foreach}

                {* Modified by Hieu Nguyen on 2021-08-19 to check if this feature can be displayed *}
                {if !isForbiddenFeature('DashboardManagement')}
                    {* Added by Phu Vo on 2020.10.12 to display config popup icon *}
                    <div class="moreSettings pull-right">
                        {if $CURRENT_USER->id == 1 && $DASHBOARD_EDIT_MODE == false}
                            <button class="btn btn-default configDashboard" type="button" title="{vtranslate('LBL_DASHBOARD_DASHBOARD_CONFIG', 'Home')}" data-toggle="tooltip">
                                <i class="far fa-cog" aria-hidden="true"></i>
                            </button>
                        {/if}
                    </div>
                    {* End Phu Vo *}
                {/if}
                {* End Hieu Nguyen *}

                {* Modified by Phu Vo on 2020-10-29 to show hide base on permission *}
                <div class="moreSettings pull-right">
                    {if Home_DashboardLogic_Helper::canEditDashboard() && !isForbiddenFeature('DashboardEditor')}   {* Modified by Hieu Nguyen on 2022-05-12 to check forbidden feature *}
                        <div class="dropdown dashBoardDropDown">
                            <button class="btn btn-default reArrangeTabs dropdown-toggle" type="button" data-toggle="dropdown">
                                {vtranslate('LBL_MORE', $MODULE)}&nbsp;&nbsp;<i class="far fa-angle-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right dashBoardActions">
                                <li {if count($DASHBOARD_TABS) eq $DASHBOARD_TABS_LIMIT}class="disabled"{/if}>
                                    <a id="addNewDashBoardTab" href="#">{vtranslate('LBL_ADD_NEW_DASHBOARD_TAB', $MODULE)}</a>
                                </li>
                                <li><a id="reArrangeDashboardTabs" href="#">{vtranslate('LBL_REARRANGE_DASHBOARD_TABS', $MODULE)}</a></li>
                            </ul>
                        </div>
                        <button id="saveDashboardTabsOrder" class="btn-success pull-right hide">{vtranslate('LBL_SAVE_DASHBOARD_TABS_ORDER', $MODULE)}</button>
                    {/if}
                </div>
                {* End Phu Vo *}
            </ul>
            <div class="tab-content">
                {foreach key=index item=TAB_DATA from=$DASHBOARD_TABS}
                    <div id="tab_{$TAB_DATA["id"]}" data-tabid="{$TAB_DATA["id"]}" data-tabname="{$TAB_DATA["tabname"]}" class="tab-pane fade {if $TAB_DATA["id"] eq $SELECTED_TAB}in active{/if}">
                        {if $TAB_DATA["id"] eq $SELECTED_TAB}
                            {include file="dashboards/DashBoardTabContents.tpl"|vtemplate_path:$MODULE TABID=$TABID}
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
</div>

{* Added by Hieu Nguyen on 2022-03-10 *}
<link rel="stylesheet" type="text/css" href="{vresource_url('modules/Home/resources/Dashboard.css')}" />
{* End Hieu Nguyen *}
{/strip}