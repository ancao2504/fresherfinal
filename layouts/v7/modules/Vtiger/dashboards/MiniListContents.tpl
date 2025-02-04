{************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
<div style='padding-top: 0;margin-bottom: 2%;padding-right:15px;'>
    <input type="hidden" id="widget_{$WIDGET->get('id')}_currentPage" value="{$CURRENT_PAGE}">
	{* Comupte the nubmer of columns required *}
	{assign var="SPANSIZE" value=12}
	{assign var=HEADER_COUNT value=$MINILIST_WIDGET_MODEL->getHeaderCount()}
	{if $HEADER_COUNT}
        {*-- Modified By Kelvin Thang on 2020-01-07 config set max selection size minilist wizard in dashboard*}
		{assign var="SPANSIZE" value=round(12/$HEADER_COUNT)}
	{/if}

	<div class="row" style="padding:5px">
		{assign var=HEADER_FIELDS value=$MINILIST_WIDGET_MODEL->getHeaders()}
		{foreach item=FIELD from=$HEADER_FIELDS}
		<div class="minilist-header-wrapper" style="width: {(1/$HEADER_COUNT)*100}%"><strong>{vtranslate($FIELD->get('label'),$BASE_MODULE)}</strong></div> {* Modified by Phu Vo on 2021.11.28 *}
		{/foreach}
	</div>

	{assign var="MINILIST_WIDGET_RECORDS" value=$MINILIST_WIDGET_MODEL->getRecords()}

	{foreach item=RECORD from=$MINILIST_WIDGET_RECORDS}
	<div class="row miniListContent" style="padding:5px">
		{foreach item=FIELD key=NAME from=$HEADER_FIELDS name="minilistWidgetModelRowHeaders"}
			{* [CustomOwnerField] Issue 870: Modified by Phu Vo on 2019.11.22 *}
			<div class="minilist-content-wrapper" style="width: {(1/$HEADER_COUNT)*100}%" {* Modified by Phu Vo on 2021.11.28 *}
				{if $FIELD->getFieldDataType() == 'owner'}
					title="{strip_tags(Vtiger_Owner_UIType::getCurrentOwnersForDisplay($RECORD->getRaw($FIELD->get('column')), false, false))}"
				{else}
					title="{strip_tags($RECORD->getDisplayValue($NAME))}"
				{/if}
				style="padding-right: 5px;"
			>
				<span class="textOverflowEllipsis">
					{if $FIELD->get('uitype') eq '71' || ($FIELD->get('uitype') eq '72' && $FIELD->getName() eq 'unit_price')}
						{assign var=CURRENCY_ID value=$USER_MODEL->get('currency_id')}
						{if $FIELD->get('uitype') eq '72' && $NAME eq 'unit_price'}
							{assign var=CURRENCY_ID value=getProductBaseCurrency($RECORD_ID, $RECORD->getModuleName())}
						{/if}
						{assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($CURRENCY_ID)}
						{if $RECORD->get($NAME) neq NULL}
							{CurrencyField::appendCurrencySymbol($RECORD->get($NAME), $CURRENCY_INFO['symbol'])}
						{/if}
					{elseif $FIELD->getFieldDataType() == 'owner'}
						{Vtiger_Owner_UIType::getCurrentOwnersForDisplay($RECORD->getRaw($FIELD->get('column')), false, false)}
					{else}
						{* Modified by Hieu Nguyen on 2022-07-05 to support click-to-call at MiniList *}
						{assign var='RAW_VALUE' value=$RECORD->getRaw($FIELD->get('column'))}

						{if $FIELD->getFieldDataType() == 'phone'}
							{if PBXManager_Logic_Helper::isRelateModuleField($NAME)}
								{assign var='RELATED_MODULE_NAME' value=PBXManager_Logic_Helper::getModuleNameFromRelateModuleFieldName($NAME)}
								{assign var='RELATED_RECORD_ID' value=PBXManager_Logic_Helper::getRecordIdFromRelateModuleFieldName($NAME, $RECORD)}
								{assign var='RAW_VALUE' value=PBXManager_Logic_Helper::getRelatedModuleFieldValueForMiniList($NAME, $RECORD)}
								
								{$FIELD->getDisplayValue($RAW_VALUE)}

								{if PBXManager_Logic_Helper::isClick2CallEnabled($RELATED_MODULE_NAME)}
									{PBXManager_Logic_Helper::renderButtonCall($RAW_VALUE, $RELATED_RECORD_ID)}
								{/if}
							{else}
								{$FIELD->getDisplayValue($RAW_VALUE)}

								{if PBXManager_Logic_Helper::isClick2CallEnabled($RECORD->getModuleName())}
									{PBXManager_Logic_Helper::renderButtonCall($RAW_VALUE, $RECORD->getId())}
								{/if}
							{/if}
						{else}
							{$FIELD->getDisplayValue($RAW_VALUE)}
						{/if}
						{* End Hieu Nguyen *}
					{/if}
				</span>
				{if $smarty.foreach.minilistWidgetModelRowHeaders.last}
					<a href="{$RECORD->getDetailViewUrl()}" class="pull-right"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS',$MODULE_NAME)}" class="far fa-list"></i></a>
				{/if}
			</div>
			{* End Phu Vo *}
		{/foreach}
	</div>
	{/foreach}
    
    {if $MORE_EXISTS}
        <div class="moreLinkDiv" style="padding-top:10px;padding-bottom:5px;">
            <a class="miniListMoreLink" data-linkid="{$WIDGET->get('linkid')}" data-widgetid="{$WIDGET->get('id')}" onclick="Vtiger_MiniList_Widget_Js.registerMoreClickEvent(event);">{vtranslate('LBL_MORE')}...</a>
        </div>
    {/if}
</div>