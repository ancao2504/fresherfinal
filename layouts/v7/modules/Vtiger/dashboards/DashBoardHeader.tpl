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

{* Added by Hieu Nguyen on 2022-05-12 *}
{assign var='CAN_EDIT_DASHBOARD' value=(Home_DashboardLogic_Helper::canEditDashboard() && !isForbiddenFeature('DashboardEditor'))}
{* End Hieu Nguyen *}

{* Added by Phu Vo on 2020.10.30 *}
<script>
	var _CAN_EDIT_DASHBOARD = {if $CAN_EDIT_DASHBOARD}true{else}false{/if};
</script>
{* End Phu Vo *}

<div class='dashboardHeading container-fluid'>
	<div class="buttonGroups pull-right">
		<div class="btn-group">
			{* Modified by Phu Vo on 2020.10.16 *}
			{if $MODULE_PERMISSION && $CAN_EDIT_DASHBOARD}	{* Modified by Hieu Nguyen on 2022-05-12 to check forbidden feature *}
				<button class="btn btn-default removeAllWidget" data-tabname="{$TAB_DATA["tabname"]}"
					data-tabid="{$TAB_DATA["id"]}" data-templateid="{$EDITING_DASHBOARD_ID}"
				>
					<i class="far fa-times-circle" aria-hidden="true"></i>&nbsp;
					{vtranslate('LBL_DASHBOARD_REMOVE_ALL_WIDGET', 'Home')}
				</button>
				<button class="btn btn-default addButton showAddWidgetModal">
					<i class="far fa-plus" aria-hidden="true"></i>&nbsp;
					{vtranslate('LBL_ADD_WIDGET', 'Home')}&nbsp;&nbsp;
				</button>
			{/if}
			{* End Phu Vo *}
		</div>
	</div>
</div>