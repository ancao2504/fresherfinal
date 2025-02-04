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
{strip}    
    {foreach key=index item=cssModel from=$STYLES}
        <link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
    {/foreach}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}
        
    <div class="title clearfix">
        {* BEGIN-- Modified by Phu Vo on 2020.08.25 to support html tag on dashboard title*}
        <div class="dashboardTitle" style="width: 25em;"><b>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
        {* END-- Modified by Phu Vo on 2020.08.25 to support html tag on dashboard title*}

        {* [DashletGuide] Added by Hieu Nguyen on 2022-03-10 to show dashlet guide *}
        {include file="modules/Home/tpls/DashletGuideButton.tpl" LINK_ID=$WIDGET->get('linkid') REPORT_ID=$WIDGET->get('reportid')}
        {* End Hieu Nguyen *}
    </div>
{/strip}