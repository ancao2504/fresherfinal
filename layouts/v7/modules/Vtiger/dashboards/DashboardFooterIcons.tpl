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
{if $SETTING_EXIST}
<a name="dfilter">
	<i class='far fa-filter' border='0' align="absmiddle" title="{vtranslate('LBL_FILTER')}" alt="{vtranslate('LBL_FILTER')}"/>
</a>
{/if}
{if !empty($CHART_TYPE)}
    {assign var=CHART_DATA value=ZEND_JSON::decode($DATA)}
    {assign var=CHART_VALUES value=$CHART_DATA['values']}
{/if}
{if (!empty($DATA) && !empty($CHART_TYPE) && !empty($CHART_VALUES)) || $ALLOW_FULL_SCREEN}  {* Modified by Hieu Nguyen on 2019-10-01 to fix bug full screen button always enable by default *}
<a href="javascript:void(0);" name="widgetFullScreen">
	<i class="far fa-arrows-alt" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_FULLSCREEN')}" alt="{vtranslate('LBL_FULLSCREEN')}"></i>
</a>
{/if}
{if !empty($CHART_TYPE) && $REPORT_MODEL->isEditable() eq true}
<a href="{$REPORT_MODEL->getEditViewUrl()}" name="customizeChartReportWidget">
	<i class="far fa-edit" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_CUSTOMIZE',$MODULE)}" alt="{vtranslate('LBL_CUSTOMIZE',$MODULE)}"></i>
</a>
{/if}
<a href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
	<i class="far fa-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
</a>
{if !$WIDGET->isDefault() && Home_DashboardLogic_Helper::canEditDashboard() && !isForbiddenFeature('DashboardEditor')}	{* Modified by Hieu Nguyen on 2022-05-12 to check forbidden feature *}
	<a name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
		<i class="far fa-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REMOVE')}" alt="{vtranslate('LBL_REMOVE')}"></i>
	</a>
{/if}