{* Added  by Vu Mai on 2022-11-17 to render template for filter field *}

{strip}
	{if $FIELD_NAME == 'assigned_user'}
	{else if $FIELD_NAME == 'salutationtype'}
		{assign var=SALUTATIONTYPES value=Vtiger_Util_Helper::getPickListValues('salutationtype')}
		<select class="inputElement listSearchContributor" multiple name="{$FIELD_NAME}" value="">
			{foreach from=$SALUTATIONTYPES item=ITEM key=KEY}
				<option value="{$ITEM}" {if in_array($ITEM, $SEARCH_PARAMS[$FIELD_NAME])}selected{/if}>{vtranslate($ITEM)}</option>
			{/foreach}
		</select>
	{else if $FIELD_NAME == 'customer_type'}
		<select class="inputElement listSearchContributor" multiple name="{$FIELD_NAME}" value="">
			<option value="CPTarget" {if in_array('CPTarget', $SEARCH_PARAMS[$FIELD_NAME])}selected{/if}>{vtranslate('CPTarget', 'CPTarget')}</option>
			<option value="Leads" {if in_array('Leads', $SEARCH_PARAMS[$FIELD_NAME])}selected{/if}>{vtranslate('Leads', 'Leads')}</option>
			<option value="Contacts" {if in_array('Contacts', $SEARCH_PARAMS[$FIELD_NAME])}selected{/if}>{vtranslate('Contacts', 'Contacts')}</option>
		</select>	
	{else if $FIELD_NAME == 'status'}
		<select class="inputElement listSearchContributor" multiple name="{$FIELD_NAME}" value="">
			{foreach from=$CUSTOMER_STATUS_LIST item=ITEM key=KEY}
			{assign var=LABEL value=CPTelesales_Logic_Helper::generateCustomerStatusLabelKey($CAMPAIGN_PURPOSE, $KEY)}
				<option value="{$KEY}" {if in_array($KEY, $SEARCH_PARAMS[$FIELD_NAME])}selected{/if}>{vtranslate($LABEL, 'CampaignCustomerStatus')}</option>
			{/foreach}
		</select>
	{else if $FIELD_NAME == 'last_call_result'}
		<select class="inputElement listSearchContributor" multiple name="{$FIELD_NAME}" value="">
			{foreach from=$CALL_RESULT_LIST item=ITEM key=KEY}
				<option value="{$KEY}" {if in_array($KEY, $SEARCH_PARAMS[$FIELD_NAME])}selected{/if}>{$ITEM.label}</option>
			{/foreach}
		</select>
	{else if $FIELD_NAME == 'assigned_time' || $FIELD_NAME == 'last_call_time'}
		{assign var="dateFormat" value=Users_Privileges_Model::getCurrentUserModel()->get('date_format')}
		<div class="row-fluid">
			<input type="text" name="{$FIELD_NAME}" class="listSearchContributor inputElement dateField" data-date-format="{$dateFormat}" 
			data-calendar-type="range" value="{$SEARCH_PARAMS[$FIELD_NAME]}" />
		</div>	
	{else if $FIELD_NAME == 'call_count'}
		<input type="number" class="inputElement listSearchContributor" name="{$FIELD_NAME}" value="{$SEARCH_PARAMS[$FIELD_NAME]}" />
	{else}
		<input type="text" class="inputElement listSearchContributor" name="{$FIELD_NAME}" value="{$SEARCH_PARAMS[$FIELD_NAME]}" />
	{/if}
{/strip}
