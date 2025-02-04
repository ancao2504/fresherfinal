{*
	Name: CustomerStatusList.tpl
	Author: Vu Mai
	Date: 2022-08-16
	Purpose: Render template for customer status list
*}

{strip}
	{if !empty($CUSTOMER_STATUS_LIST)}
		<tr><td colspan="5"><!-- Placeholder role to allow drag-and-drop for last elements --></td></tr>
	{/if}

	{foreach key=STATUS item=STATUS_INFO from=$CUSTOMER_STATUS_LIST}
		{assign var="STATUS_LABEL" value=CPTelesales_Logic_Helper::generateCustomerStatusLabelKey($CAMPAIGN_PURPOSE, $STATUS)}
		{assign var=TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($STATUS_INFO.color)}

		<tr class="customer-status-item" data-current-value="{$STATUS}">
			<td class="textOverflowEllipsis">
				<span class="pull-left"><i class="far fa-grip-lines cursorDrag alignMiddle"></i>&nbsp;&nbsp;
					<span class="picklist-color" style="background-color:{$STATUS_INFO.color};color:{$TEXT_COLOR}">
						{vtranslate($STATUS_LABEL, 'CampaignCustomerStatus')}
					</span>
				</span>
			</td>
			<td class="fieldValue" class="text-center">
				<input type="radio" class="inputElement" value="{$STATUS}" name="customer_status_is_new" {if $STATUS_INFO.is_new}checked{/if} />
			</td>
			<td class="fieldValue" class="text-center">
				<input type="radio" class="inputElement" value="{$STATUS}" name="customer_status_is_success" {if $STATUS_INFO.is_success}checked{/if} />
			</td>
			<td class="fieldValue" class="text-center">
				<input type="checkbox" class="inputElement" value="{$STATUS}" name="customer_status_is_failed" {if $STATUS_INFO.is_failed}checked{/if} />
			</td>
			<td class="text-center">
				<button type="button" class="btn btn-outline-primary btn-edit-status" onclick="app.controller().showCustomerStatusModal(this)" title="Sửa"><i class="far fa-pen"></i></button>
				<button type="button" class="btn btn-outline-danger" onclick="app.controller().showDeleteCutomerStatusModal(this)" title="Xóa"><i class="far fa-trash-alt"></i></button>
			</td>
		</tr>
	{/foreach}
{/strip}	