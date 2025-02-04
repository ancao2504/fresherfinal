{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <!--LIST VIEW RECORD ACTIONS-->

    <div class="table-actions reportListActions">
        {if !$SEARCH_MODE_RESULTS}
            <span class="input" >
                <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"/>
            </span>
        {/if}
        
        {assign var="REPORT_TYPE" value=$LISTVIEW_ENTRY->get('reporttype')}

        {* Commented out this button by Hieu Nguyen on 2021-08-06 *}
        {* {if $REPORT_TYPE eq 'chart'}
            <span>
                <a class="quickView far fa-eye icon action" title="{vtranslate('LBL_QUICK_VIEW', $MODULE)}"></a>
            </span>
        {/if} *}

        {* Moved edit button here by Hieu Nguyen on 2021-08-06 *}
        {if $LISTVIEW_ENTRY->isEditableBySharing()}
            <span>
                <a class="far fa-pen icon action edit" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}" name="editlink" title="{vtranslate('LBL_EDIT', $MODULE)}"></a>
            </span>
        {/if}
        {* End Hieu Nguyen *}

        {assign var="PINNED" value=$LISTVIEW_ENTRY->get('pinned')}
        {if $PINNED neq null && $REPORT_TYPE eq 'chart'}
            {assign var=PIN_CLASS value='vicon-unpin'}
        {elseif $REPORT_TYPE eq 'chart'}
            {assign var=PIN_CLASS value='vicon-pin'}
        {/if}
        
        {* Modified by Hieu Nguyen on 2020-11-25 to show button Pin to Dashboard only if current user can edit dashboard *}
        {if $REPORT_TYPE eq 'chart' && Home_DashboardLogic_Helper::canEditDashboard() && !isForbiddenFeature('PinChartToDashboard')}
            <span class="dropdown">
                <a style="font-size:13px;" 
                    title="{vtranslate('LBL_PIN_CHART_TO_DASHBOARD', $MODULE)}" 
                    class="far icon action {$PIN_CLASS} pinToDashboard" 
                    data-recordid="{$LISTVIEW_ENTRY->get('reportid')}" 
                    data-primemodule="{$LISTVIEW_ENTRY->get('primarymodule')}" 
                    data-dashboard-tab-count="{count($DASHBOARD_TABS)}"
                    {if count($DASHBOARD_TABS) gt 0 && $PIN_CLASS eq 'vicon-pin'}data-toggle='dropdown'{/if}
                ></a>
                <ul class="dropdown-menu dashBoardTabMenu">
                    <li class="dropdown-header popover-title">
                        {vtranslate('LBL_DASHBOARD',$MODULE)}
                    </li>
                    {foreach from=$DASHBOARD_TABS item=TAB_INFO}
                        <li class="dashBoardTab" data-tab-id="{$TAB_INFO.id}">
                            <a href="javascript:void(0);">{$TAB_INFO.tabname}</a>
                        </li>
                    {/foreach}
                </ul>
            </span>
        {/if}
        {* End Hieu Nguyen *}

        {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row actions *}
        {assign var="CUSTOM_ROW_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowActions.tpl"}

        {if file_exists($CUSTOM_ROW_ACTIONS)}
            {include file=$CUSTOM_ROW_ACTIONS}
        {/if}
        {* End Hieu Nguyen *}

        {if $LISTVIEW_ENTRY->isEditableBySharing()}
            <span class="more dropdown action">
                <span href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="far fa-ellipsis-v-alt icon"></i></span>
                <ul class="dropdown-menu">
                    <li><a data-id="{$LISTVIEW_ENTRY->getId()}" class="deleteRecordButton" href="javascript:void(0);">{vtranslate('LBL_DELETE', $MODULE)}</a></li>

                    {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row advanced actions *}
                    {assign var="CUSTOM_ROW_ADVANCED_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowAdvancedActions.tpl"}

                    {if file_exists($CUSTOM_ROW_ADVANCED_ACTIONS)}
                        {include file=$CUSTOM_ROW_ADVANCED_ACTIONS}
                    {/if}
                    {* End Hieu Nguyen *}
                </ul>
            </span>
        {/if}    
        
        <div class="btn-group inline-save hide">
            <button class="button btn-success btn-small save" name="save"><i class="far fa-check"></i></button>
            <button class="button btn-danger btn-small cancel" name="Cancel"><i class="far fa-close"></i></button>
        </div>
    </div>
{/strip}