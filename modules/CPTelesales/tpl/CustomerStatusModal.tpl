{* Created by Vu Mai on 2022-08-17 to render customer status modal *}
{strip}
<div class="modal-dialog modal-md modal-content modal-customer-status">
	{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

	<form id="customer-status" class="form-horizontal">
		{if $TYPE != 'delete'}
		<div class="form-content fancyScrollbar padding20">
			<input type="hidden" name="current_value" value="{$CURRENT_VALUE}" />
			<input type="hidden" name="status_array"
				value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode(CPTelesales_Config_Helper::getCustomerStatusLableKeyListForDropwdown($CAMPAIGN_PURPOSE)))}" />
			<div class="row form-group">
				<div class="fieldLabel text-right col-md-4">{vtranslate('LBL_ITEMS', 'Settings:Picklist')} hello<span
						class="redColor">*</span></div>
				<div class="fieldValue col-md-8 paddingleft0">
					<input type="text" name="value" value="{$CURRENT_VALUE}" data-rule-required="true" />
					<span class="display-block">{vtranslate('LBL_ITEM_VALUE_INPUT_HINT', 'Settings:Picklist')}<span>
				</div>
			</div>
			<div class="row form-group">
				<div class="fieldLabel text-right col-md-4">{vtranslate('LBL_ITEM_LABEL_DISPLAY_EN',
					'Settings:Picklist')}<span class="redColor">*</span></div>
				<div class="fieldValue col-md-8 paddingleft0">
					<input type="text" name="label_display_en" value="{$LABEL_DISPLAY_EN}" data-rule-required="true" />
				</div>
			</div>
			<div class="row form-group">
				<div class="fieldLabel text-right col-md-4">{vtranslate('LBL_ITEM_LABEL_DISPLAY_VN',
					'Settings:Picklist')}<span class="redColor">*</span></div>
				<div class="fieldValue col-md-8 paddingleft0">
					<input type="text" name="label_display_vn" value="{$LABEL_DISPLAY_VN}" data-rule-required="true" />
				</div>
			</div>
			<div class="row form-group">
				<div class="fieldLabel text-right col-md-4">{vtranslate('LBL_SELECT_COLOR', 'Vtiger')}</div>
				<div class="fieldValue col-md-8 paddingleft0">
					<!-- <input name="color" value="{$CURRENT_COLOR}" data-rule-required="true" /> -->
					<input name="color" data-rule-required="true" />

				</div>
			</div>
		</div>
		{/if}

		{if $TYPE == 'delete'}
		<div class="form-content fancyScrollbar padding20">
			<div class="row form-group">
				<div class="fieldLabel text-right col-md-4">
					{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_STATUS_IS_SELECTED', 'CPTelesales')}</div>
				<div class="fieldValue col-md-8">
					<span>{vtranslate(CPTelesales_Logic_Helper::generateCustomerStatusLabelKey($CAMPAIGN_PURPOSE,
						$SELECTED_STATUS), 'CampaignCustomerStatus')}<span>
				</div>
			</div>
			<div class="row form-group">
				<div class="fieldLabel text-right col-md-4">{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_STATUS_IS_SWAP',
					'CPTelesales')}</div>
				<div class="fieldValue col-md-8">
					<select name="swap_status" class="inputElement text-left select2" data-rule-required="true">
						{foreach key=KEY item=LABEL from=$CUSTOMER_STATUS_LABEL_KEY_LIST}
						<option value="{$KEY}">{$LABEL}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{/if}

		{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
	</form>
</div>
{/strip}