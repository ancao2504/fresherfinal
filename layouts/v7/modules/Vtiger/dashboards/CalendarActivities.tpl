{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}

<div class="dashboardWidgetHeader clearfix">

    {* Modified by Phu Vo on 2019.06.20 to apply custom owner field filter *}
    {* {if $SHARED_USERS|@count gt 0 || $SHARED_GROUPS|@count gt 0}
        {assign var="usersList" value="1"}
    {/if} *}
    
    <div class="title">
        <div class="dashboardTitle"><b>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>

        {* [DashletGuide] Added by Hieu Nguyen on 2022-03-10 to show dashlet guide *}
        {include file="modules/Home/tpls/DashletGuideButton.tpl" LINK_ID=$WIDGET->get('linkid')}
        {* End Hieu Nguyen *}
    </div>
    
    <div class="userList">
        {* Added by Phu Vo to load custom owner field filter *}
        {include file=Vtiger_Owner_UIType::getDashboardFilterTemplateName() OPTIONAL_FILTER=true}
        {* End load custom owner field filter *}

        
        {* <select class="select2 widgetFilter" name="type">
            <option value="{$CURRENTUSER->getId()}" selected>{vtranslate('LBL_MINE',$MODULE_NAME)}</option>
            {foreach key=USER_ID from=$SHARED_USERS item=USER_NAME}
                <option value="{$USER_ID}">{$USER_NAME}</option>
            {/foreach}
            {foreach key=GROUP_ID from=$SHARED_GROUPS item=GROUP_NAME}
                <option value="{$GROUP_ID}">{$GROUP_NAME}</option>
            {/foreach}
            <option value="all">{vtranslate('All', $MODULE_NAME)}</option>
        </select> *}
    </div>

    {* End apply custom owner field filter *}
</div>
<div name="history" class="dashboardWidgetContent" style="padding-top:15px;">
    {include file="dashboards/CalendarActivitiesContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
<div class="widgeticons dashBoardWidgetFooter">
    <div class="footerIcons pull-right">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
</div>