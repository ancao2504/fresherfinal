{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}

{strip}
	{assign var=LINEITEM_FIELDS value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
	{if $LINEITEM_FIELDS['image']}
		{assign var=IMAGE_EDITABLE value=$LINEITEM_FIELDS['image']->isEditable()}
	{if $IMAGE_EDITABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['productid']}
	{assign var=PRODUCT_EDITABLE value=$LINEITEM_FIELDS['productid']->isEditable()}
{if $PRODUCT_EDITABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['quantity']}
	{assign var=QUANTITY_EDITABLE value=$LINEITEM_FIELDS['quantity']->isEditable()}
{if $QUANTITY_EDITABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['purchase_cost']}
	{assign var=PURCHASE_COST_EDITABLE value=$LINEITEM_FIELDS['purchase_cost']->isEditable()}
{if $PURCHASE_COST_EDITABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['listprice']}
	{assign var=LIST_PRICE_EDITABLE value=$LINEITEM_FIELDS['listprice']->isEditable()}
{if $LIST_PRICE_EDITABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['margin']}
	{assign var=MARGIN_EDITABLE value=$LINEITEM_FIELDS['margin']->isEditable()}
{if $MARGIN_EDITABLE}{assign var=COL_SPAN3 value=($COL_SPAN3)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['comment']}
	{assign var=COMMENT_EDITABLE value=$LINEITEM_FIELDS['comment']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['discount_amount']}
	{assign var=ITEM_DISCOUNT_AMOUNT_EDITABLE value=$LINEITEM_FIELDS['discount_amount']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['discount_percent']}
	{assign var=ITEM_DISCOUNT_PERCENT_EDITABLE value=$LINEITEM_FIELDS['discount_percent']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['hdnS_H_Percent']}
	{assign var=SH_PERCENT_EDITABLE value=$LINEITEM_FIELDS['hdnS_H_Percent']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountAmount']}
	{assign var=DISCOUNT_AMOUNT_EDITABLE value=$LINEITEM_FIELDS['hdnDiscountAmount']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountPercent']}
	{assign var=DISCOUNT_PERCENT_EDITABLE value=$LINEITEM_FIELDS['hdnDiscountPercent']->isEditable()}
{/if}

<!-- Modified By Kelvin Thang -- OnlineCRM -- Date: 2018-09-17 -->
<!--{assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}-->
{assign var="FINAL" value=$RELATED_PRODUCTS.1.1.final_details}

{assign var="IS_INDIVIDUAL_TAX_TYPE" value=false}
{assign var="IS_GROUP_TAX_TYPE" value=true}

{if $TAX_TYPE eq 'individual'}
	{assign var="IS_GROUP_TAX_TYPE" value=false}
	{assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
{/if}

<input type="hidden" class="numberOfCurrencyDecimal" value="{$USER_MODEL->get('no_of_currency_decimals')}" />
<input type="hidden" name="totalProductCount" id="totalProductCount" value="{$row_no}" />
<input type="hidden" name="subtotal" id="subtotal" value="" />
<input type="hidden" name="total" id="total" value="" />

<div name='editContent'>
	{assign var=LINE_ITEM_BLOCK_LABEL value="LBL_ITEM_DETAILS"}
	{assign var=BLOCK_FIELDS value=$RECORD_STRUCTURE.$LINE_ITEM_BLOCK_LABEL}
	{assign var=BLOCK_LABEL value=$LINE_ITEM_BLOCK_LABEL}
	{if $BLOCK_FIELDS|@count gt 0}
		<div class='fieldBlockContainer'>

            {*--Begin: Modified by Kelvin Thang on 2020-02-29 - Change layout and css for applied the feature "ComboProduct" module CPSTemplates *}
			<div class="row" style="font-size: 13px; margin: 3px;">
				<div class="col-lg-6 col-md-6 col-sm-6">
					<div class="row">
						<div class="col-lg-2 col-md-2 col-sm-2">
							<h4 class='fieldBlockHeader' style="margin-top:5px;">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
						</div>

                        {*--Begin: Added by Kelvin Thang on 2020-02-29 -Init UI For CPComboProducts*}
						<div class="col-lg-5 col-md-5 col-sm-5 CPComboProducts" style="top: 3px; padding-left: 0px; padding-right: 0px;">
                            {assign var=COMBO_PRODUCTS_IGNORE_MODULES value="{getGlobalVariable('comboProductsIgnoreModules')}"}
							{if !in_array($MODULE, $COMBO_PRODUCTS_IGNORE_MODULES) && $COMBO_PRODUCTS.active }
								<span class="pull-left">
									<i class="far fa-cubes"></i>&nbsp;
									<label>{vtranslate('CPComboProducts', 'CPComboProducts')}</label>&nbsp;

                                    <select class="select2" id="combo_products_template" name="combo_products_template" style="width: 130px;">
										<option value="0">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
										{foreach key=CP_TEMPLATE_ID item=CP_TEMPLATE_INFO from=$COMBO_PRODUCTS.templates}
											<option value="{$CP_TEMPLATE_INFO['id']}">{$CP_TEMPLATE_INFO['name']}</option>
										{/foreach}
									</select>

								</span>
							{/if}
						</div>
                        {*--End Added by Kelvin Thang on 2020-02-29 -Init UI For CPComboProducts*}

						<div class="col-lg-5 col-md-5 col-sm-5" style="top: 3px; padding-left: 0px; padding-right: 0px;">
							{if $LINEITEM_FIELDS['region_id'] && $LINEITEM_FIELDS['region_id']->isEditable()}
								<span class="pull-right">
									<i class="far fa-info-circle"></i>&nbsp;
									<label>{vtranslate($LINEITEM_FIELDS['region_id']->get('label'), $MODULE)}</label>&nbsp;
									<select class="select2" id="region_id" name="region_id" style="width: 130px;">
										<option value="0" data-info="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($DEFAULT_TAX_REGION_INFO))}">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
										{foreach key=TAX_REGION_ID item=TAX_REGION_INFO from=$TAX_REGIONS}
											<option value="{$TAX_REGION_ID}" data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_REGION_INFO))}' {if $TAX_REGION_ID eq $RECORD->get('region_id')}selected{/if}>{$TAX_REGION_INFO['name']}</option>
										{/foreach}
									</select>
									<input type="hidden" id="prevRegionId" value="{$RECORD->get('region_id')}" />
									&nbsp;&nbsp;<a class="far fa-wrench" href="index.php?module=Vtiger&parent=Settings&view=TaxIndex" target="_blank" style="vertical-align:middle;"></a>
								</span>
							{/if}
						</div>
					</div>
				</div>

                <div class="col-lg-3 col-md-3 col-sm-3" style="top: 3px;">
                    <center>
                        <i class="far fa-info-circle"></i>&nbsp;
                        <label>{vtranslate('LBL_CURRENCY',$MODULE)}</label>&nbsp;
                        {assign var=SELECTED_CURRENCY value=$CURRENCINFO}
                        {* Lookup the currency information if not yet set - create mode *}
                        {if $SELECTED_CURRENCY eq ''}
                            {assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
                            {foreach item=currency_details from=$CURRENCIES}
                                {if $currency_details.curid eq $USER_CURRENCY_ID}
                                    {assign var=SELECTED_CURRENCY value=$currency_details}
                                {/if}
                            {/foreach}
                        {/if}

                        <select class="select2" id="currency_id" name="currency_id" style="width: 150px;">
                            {foreach item=currency_details key=count from=$CURRENCIES}
                                <option value="{$currency_details.curid}" class="textShadowNone" data-conversion-rate="{$currency_details.conversionrate}" {if $SELECTED_CURRENCY.currency_id eq $currency_details.curid} selected {/if}>
                                    {$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})
                                </option>
                            {/foreach}
                        </select>

                        {assign var="RECORD_CURRENCY_RATE" value=$RECORD_STRUCTURE_MODEL->getRecord()->get('conversion_rate')}
                        {if $RECORD_CURRENCY_RATE eq ''}
                            {assign var="RECORD_CURRENCY_RATE" value=$SELECTED_CURRENCY.conversionrate}
                        {/if}
                        <input type="hidden" name="conversion_rate" id="conversion_rate" value="{$RECORD_CURRENCY_RATE}" />
                        <input type="hidden" value="{$SELECTED_CURRENCY.currency_id}" id="prev_selected_currency_id" />
                        <!-- TODO : To get default currency in even better way than depending on first element -->
                        <input type="hidden" id="default_currency_id" value="{$CURRENCIES.0.curid}" />
                        <input type="hidden" value="{$SELECTED_CURRENCY.currency_id}" id="selectedCurrencyId" />
                    </center>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3" style="top: 3px;">
                    <div style="float: right;">
                        <i class="far fa-info-circle"></i>&nbsp;
                        <label>{vtranslate('LBL_TAX_MODE',$MODULE)}</label>&nbsp;
                        <select class="select2 lineItemTax" id="taxtype" name="taxtype" style="width: 150px;">
                            <option value="individual" {if $IS_INDIVIDUAL_TAX_TYPE}selected{/if}>{vtranslate('LBL_INDIVIDUAL', $MODULE)}</option>
                            <option value="group" {if $IS_GROUP_TAX_TYPE}selected{/if}>{vtranslate('LBL_GROUP', $MODULE)}</option>
                        </select>
                    </div>
                </div>
			</div>
            {*--END: Modified by Kelvin Thang on 2020-02-29 - Change layout and css for applied the feature "ComboProduct" module CPSTemplates *}

            <!--Added by Kelvin Thang -- OnlineCRM -- 2018-09-10 -->
            <div class="lineitemTableContainer">
                {foreach key=section_no item=RELATED_PRODUCTS_SECTION from=$RELATED_PRODUCTS}
                    <div class="lineItemTab">
                        {include file="partials/lineItemTabContent.tpl"|@vtemplate_path:'Inventory' section_no=$section_no data=$RELATED_PRODUCTS_SECTION IGNORE_UI_REGISTRATION=true}
                    </div>
                {/foreach}

                {if count($RELATED_PRODUCTS) eq 0 and ($PRODUCT_ACTIVE eq 'true' || $SERVICE_ACTIVE eq 'true')}
                    <div class="lineItemTab">
                        {include file="partials/lineItemTabContent.tpl"|@vtemplate_path:'Inventory' section_no=0 row_no=1 create = 1 data=[] IGNORE_UI_REGISTRATION=true}
                    </div>
                {/if}

            </div>

			<br>
			<div class="btn-group">
				<button type="button" class="btn btn-default addSection" id="addSection" data-module-name="Products">
					<i class="far fa-plus" style="margin-right: 5px;"></i><strong>{vtranslate('LBL_ADD_SECTION',$MODULE)}</strong>
				</button>
			</div>

		</div>

        <!--Added by Kelvin Thang -- OnlineCRM -- 2018-09-10 -->
		<div class="lineItemTabCloneCopy hide">
			{include file="partials/lineItemTabContent.tpl"|@vtemplate_path:'Inventory' template = 1 section_no=0 data=[] RELATED_PRODUCTS_SECTION=[] IGNORE_UI_REGISTRATION=true}
		</div>

		<!--<br>
		<div>
			<div>
				{if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
					<div class="btn-toolbar">
						<span class="btn-group">
							<button type="button" class="btn btn-default" id="addProduct" data-module-name="Products" >
								<i class="far fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
							</button>
						</span>
						<span class="btn-group">
							<button type="button" class="btn btn-default" id="addService" data-module-name="Services" >
								<i class="far fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
							</button>
						</span>
					</div>
				{elseif $PRODUCT_ACTIVE eq 'true'}
					<div class="btn-group">
						<button type="button" class="btn btn-default" id="addProduct" data-module-name="Products">
							<i class="far fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
						</button>
					</div>
				{elseif $SERVICE_ACTIVE eq 'true'}
					<div class="btn-group">
						<button type="button" class="btn btn-default" id="addService" data-module-name="Services">
							<i class="far fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
						</button>
					</div>
				{/if}
			</div>
		</div>-->

		<br>
		<div class="fieldBlockContainer">
			<table class="table table-bordered blockContainer lineItemTable" id="lineItemResult">
				<tr>
					<td width="83%">
                        <!-- Modified by Kelvin Thang -- OnlineCRM -- Date: 2018-07-10-->
						<div class="pull-right fieldTotal">
                            <strong>{vtranslate('LBL_ITEMS_TOTAL',$MODULE)}</strong>
                        </div>
					</td>
					<td>
						<div id="netTotal" class="pull-right netTotal fieldTotal">{if !empty($FINAL.hdnSubTotal)}{Vtiger_Currency_UIType::transformDisplayValue($FINAL.hdnSubTotal, null, true)}{else}0{/if}</div>
					</td>
				</tr>
				{if $DISCOUNT_AMOUNT_EDITABLE || $DISCOUNT_PERCENT_EDITABLE}
					<tr>
						<td width="83%">
							<span class="pull-right">(-)&nbsp;
								<strong>
                                    <!-- Modified By Kelvin Thang -- OnlineCRM -- Date: 2018-07-09-->
                                    <a class="inventoryLineItemEdits" href="javascript:void(0)" id="finalDiscount">{vtranslate('LBL_OVERALL_DISCOUNT',$MODULE)}&nbsp;
										<span id="overallDiscount">
											{if $DISCOUNT_PERCENT_EDITABLE && $FINAL.discount_type_final eq 'percentage'}
												({$FINAL.discount_percentage_final}%)
											{else if $DISCOUNT_AMOUNT_EDITABLE && $FINAL.discount_type_final eq 'amount'}
												({Vtiger_Currency_UIType::transformDisplayValue($FINAL.discount_amount_final, null, true)})
											{else}
												(0)
											{/if}
										</span>
                                    </a>
								</strong>
							</span>
						</td>
						<td>
							<!--Added by Kelvin Thang -- OnlineCRM - Date: 2018-07-23-->
							<span id="discountTotal_final" class="pull-right discountTotal_final">{if $FINAL.discountTotal_final}{Vtiger_Currency_UIType::transformDisplayValue($FINAL.discountTotal_final, null, true)}{else}0{/if}</span>

							<!-- Popup Discount Div -->
							<div id="finalDiscountUI" class="finalDiscountUI validCheck hide">
								{assign var=DISCOUNT_TYPE_FINAL value="zero"}
								{if !empty($FINAL.discount_type_final)}
									{assign var=DISCOUNT_TYPE_FINAL value=$FINAL.discount_type_final }
								{/if}
								<input type="hidden" id="discount_type_final" name="discount_type_final" value="{$DISCOUNT_TYPE_FINAL}" />
								<p class="popover_title hide">
									{vtranslate('LBL_SET_DISCOUNT_FOR',$MODULE)} : <span class="subTotalVal">{if !empty($FINAL.hdnSubTotal)}{Vtiger_Currency_UIType::transformDisplayValue($FINAL.hdnSubTotal, null, true)}{else}0{/if}</span>
								</p>
								<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
									<tbody>
										<tr>
											<td>
												<div class="field-value">
													<input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="zero" {if $DISCOUNT_TYPE_FINAL eq 'zero'}checked{/if} />&nbsp; {vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}
												</div>
											</td>
											<td class="lineOnTop">
												<div class="field-value">
													<!-- Make the discount value as zero -->
													<input type="hidden" class="discountVal" value="0" />
												</div>
											</td>
										</tr>
										{if $DISCOUNT_PERCENT_EDITABLE}
											<tr>
												<td>
													<div class="field-value">
														<input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="percentage" {if $DISCOUNT_TYPE_FINAL eq 'percentage'}checked{/if} />&nbsp; % {vtranslate('LBL_OF_PRICE',$MODULE)}
													</div>
												</td>
												<td>
													<div class="field-value">
														<span class="pull-right">&nbsp;%</span><input type="text" data-rule-positive=true data-rule-inventory_percentage=true id="discount_percentage_final" name="discount_percentage_final" value="{$FINAL.discount_percentage_final}" class="discount_percentage_final span1 pull-right discountVal {if $DISCOUNT_TYPE_FINAL neq 'percentage'}hide{/if}" />
													</div>
												</td>
											</tr>
										{/if}
										{if $DISCOUNT_AMOUNT_EDITABLE}
											<tr>
												<td>
													<div class="field-value">
														<input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="amount" {if $DISCOUNT_TYPE_FINAL eq 'amount'}checked{/if} />&nbsp;{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}
													</div>
												</td>

												<!-- Modified By Kelvin Thang -- OnlineCRM -- Date: 2018-07-13-->
												<td>
													<div class="field-value">
														<input type="text" data-rule-currency=true onkeyup="formatNumber(this)" id="discount_amount_final" name="discount_amount_final" value="{Vtiger_Currency_UIType::transformDisplayValue($FINAL.discount_amount_final, null, true)}" class="span1 pull-right discount_amount_final discountVal {if $DISCOUNT_TYPE_FINAL neq 'amount'}hide{/if}" />
													</div>
												</td>
											</tr>
										{/if}
									</tbody>
								</table>
							</div>
							<!-- End Popup Div -->
						</td>
					</tr>
				{/if}
				{if $SH_PERCENT_EDITABLE}
					{assign var=CHARGE_AND_CHARGETAX_VALUES value=$FINAL.chargesAndItsTaxes}
					<tr>
						<td width="83%">
                            <!-- Modified By Kelvin Thang -- Date: 2018-07-09-->
							<span class="pull-right">(+)&nbsp;<strong>
                                    <a class="inventoryLineItemEdits" href="javascript:void(0)" id="charges">{vtranslate('LBL_CHARGES',$MODULE)}</a>
                                </strong></span>
							<div id="chargesBlock" class="validCheck hide chargesBlock">
								<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
									{foreach key=CHARGE_ID item=CHARGE_MODEL from=$INVENTORY_CHARGES}
										<tr>
											{assign var=CHARGE_VALUE value=$CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['value']}
											{assign var=CHARGE_PERCENT value=0}
											{if $CHARGE_MODEL->get('format') eq 'Percent' && $CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['percent'] neq NULL}
												{assign var=CHARGE_PERCENT value=$CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['percent']}
											{/if}

											<td class="lineOnTop chargeName" data-charge-id="{$CHARGE_ID}">{$CHARGE_MODEL->getName()}</td>
											<!-- Modified By Kelvin Thang -- Date: 2018-07-13-->
											<td class="lineOnTop">
												{if $CHARGE_MODEL->get('format') eq 'Percent'}
													<input type="text" class="span1 chargePercent" size="5" data-rule-currency=true onkeyup="formatNumber(this)" data-rule-inventory_percentage=true name="charges[{$CHARGE_ID}][percent]" value="{if $CHARGE_PERCENT}{$CHARGE_PERCENT}{else if $RECORD_ID}0{else}{$CHARGE_MODEL->getValue()}{/if}" />&nbsp;%
												{/if}
											</td>
											<!-- Modified By Kelvin Thang -- Date: 2018-07-13-->
											<td style="text-align: right;" class="lineOnTop">
												<!--<input type="text" class="span1 chargeValue" size="5" {if $CHARGE_MODEL->get('format') eq 'Percent'}readonly{/if} data-rule-currency=true onkeyup="formatNumber(this)" name="charges[{$CHARGE_ID}][value]" value="{if $CHARGE_VALUE}{$CHARGE_VALUE}{else if $RECORD_ID}0{else}{$CHARGE_MODEL->getValue() * $USER_MODEL->get('conv_rate')}{/if}" />&nbsp;-->
												<input type="text" class="span1 chargeValue" size="5" {if $CHARGE_MODEL->get('format') eq 'Percent'}readonly{/if} data-rule-currency=true onkeyup="formatNumber(this)" name="charges[{$CHARGE_ID}][value]" value="{if $CHARGE_VALUE}{Vtiger_Currency_UIType::transformDisplayValue($CHARGE_VALUE, null, true)}{else if $RECORD_ID}0{else}{$CHARGE_MODEL->getValue() * $USER_MODEL->get('conv_rate')}{/if}" />&nbsp;
												<!--<input type="text" class="span1 chargeValue" size="5" {if $CHARGE_MODEL->get('format') eq 'Percent'}readonly{/if} data-rule-currency=true onkeyup="formatNumber(this)" name="charges[{$CHARGE_ID}][value]" value="{if $CHARGE_VALUE}{$CHARGE_VALUE}{else if $RECORD_ID}0{else}{Vtiger_Currency_UIType::transformDisplayValue(($CHARGE_MODEL->getValue() * $USER_MODEL->get('conv_rate')), null, true)}{/if}" />&nbsp;-->
											</td>
										</tr>
									{/foreach}
								</table>
							</div>
						</td>
						<td>
							<input type="hidden" class="lineItemInputBox" id="chargesTotal" name="shipping_handling_charge" value="{if $FINAL.shipping_handling_charge}{$FINAL.shipping_handling_charge}{else}0{/if}" />
							<span id="chargesTotalDisplay" class="pull-right chargesTotalDisplay">{if $FINAL.shipping_handling_charge}{$FINAL.shipping_handling_charge}{else}0{/if}</span>
						</td>
					</tr>
				{/if}
				<tr>
					<td width="83%">
						<span class="pull-right fieldTotal" >
                            <strong>{vtranslate('LBL_PRE_TAX_TOTAL', $MODULE)}</strong>
                        </span>
					</td>
					<td>
						{assign var=PRE_TAX_TOTAL value=$FINAL.preTaxTotal}
						<span class="pull-right fieldTotal" id="preTaxTotal">{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0{/if}</span>
						<input type="hidden" id="pre_tax_total" name="pre_tax_total" value="{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0{/if}"/>
					</td>
				</tr>
				<!-- Group Tax - starts -->

				<tr id="group_tax_row" valign="top" class="{if $IS_INDIVIDUAL_TAX_TYPE}hide{/if}">
					<td width="83%">
						<span class="pull-right">
                            <!-- Modified By Kelvin Thang -- Date: 2018-07-09-->
                            (+)&nbsp;<strong>
                                <a class="inventoryLineItemEdits" href="javascript:void(0)" id="finalTax">{vtranslate('LBL_TAX',$MODULE)}</a>
                            </strong>
                        </span>
						<!-- Pop Div For Group TAX -->
						<div class="hide finalTaxUI validCheck" id="group_tax_div">
							<input type="hidden" class="popover_title" value="{vtranslate('LBL_GROUP_TAX',$MODULE)}" />
							<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
								{foreach item=tax_detail name=group_tax_loop key=loop_count from=$TAXES}
									<tr>
										<td class="lineOnTop"><div class="field-value">{$tax_detail.taxlabel}</div></td>
										<td class="lineOnTop">
											<div class="field-value">
												<input type="text" size="5" data-compound-on="{if $tax_detail['method'] eq 'Compound'}{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($tax_detail['compoundon']))}{/if}"
													name="{$tax_detail.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.group_tax_loop.iteration}" value="{$tax_detail.percentage}" class="span1 groupTaxPercentage"
													data-rule-positive=true data-rule-inventory_percentage=true />&nbsp;%
											</div>
										</td>
										<td style="text-align: right;" class="lineOnTop">
											<div class="field-value">
												<input type="text" size="6" name="{$tax_detail.taxname}_group_amount" id="group_tax_amount{$smarty.foreach.group_tax_loop.iteration}" style="cursor:pointer;" value="{Vtiger_Currency_UIType::transformDisplayValue($tax_detail.amount, null, true)}" readonly class="cursorPointer span1 groupTaxTotal" />
											</div>
										</td>
									</tr>
								{/foreach}
								<input type="hidden" id="group_tax_count" value="{$smarty.foreach.group_tax_loop.iteration}" />
							</table>
						</div>
						<!-- End Popup Div Group Tax -->
					</td>
					<td><span id="tax_final" class="pull-right tax_final">{if $FINAL.tax_totalamount}{Vtiger_Currency_UIType::transformDisplayValue($FINAL.tax_totalamount, null, true)}{else}0{/if}</span></td>
				</tr>
				<!-- Group Tax - ends -->
				{if $SH_PERCENT_EDITABLE}
					<!-- Commen by Vu Mai on 2023-02-14 to hide charge tax field -->
					<!--<tr>
						<td width="83%">-->
                            <!-- Modified By Kelvin Thang -- Date: 2018-07-09-->
							<!--<span class="pull-right">
                                (+)&nbsp;<strong>
                                    <a class="inventoryLineItemEdits" href="javascript:void(0)" id="chargeTaxes">{vtranslate('LBL_TAXES_ON_CHARGES',$MODULE)} </a>
                                </strong>
                            </span>-->

							<!-- Pop Div For Shipping and Handling TAX -->
							<!--<div id="chargeTaxesBlock" class="hide validCheck chargeTaxesBlock">
								<p class="popover_title hide">
									{vtranslate('LBL_TAXES_ON_CHARGES', $MODULE)} : <span id="SHChargeVal" class="SHChargeVal">{if $FINAL.shipping_handling_charge}{$FINAL.shipping_handling_charge}{else}0{/if}</span>
								</p>
								<table class="table table-nobordered popupTable">
									<tbody>
										{foreach key=CHARGE_ID item=CHARGE_MODEL from=$INVENTORY_CHARGES}
											{foreach key=CHARGE_TAX_ID item=CHARGE_TAX_MODEL from=$RECORD->getChargeTaxModelsList($CHARGE_ID)}
												{if !isset($CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['taxes'][$CHARGE_TAX_ID]) && $CHARGE_TAX_MODEL->isDeleted()}
													{continue}
												{/if}
												{if !$RECORD_ID && $CHARGE_TAX_MODEL->isDeleted()}
													{continue}
												{/if}
												<tr>
													{assign var=SH_TAX_VALUE value=$CHARGE_TAX_MODEL->getTax()}
													{if $CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['value'] neq NULL}
														{assign var=SH_TAX_VALUE value=0}
														{if $CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['taxes'][$CHARGE_TAX_ID]}
															{assign var=SH_TAX_VALUE value=$CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['taxes'][$CHARGE_TAX_ID]}
														{/if}
													{/if}

													<td class="lineOnTop"><div class="field-value">{$CHARGE_MODEL->getName()} - {$CHARGE_TAX_MODEL->getName()}</div></td>
													<td class="lineOnTop">
														<div class="field-value">
															<input type="text" data-charge-id="{$CHARGE_ID}" data-compound-on="{if $CHARGE_TAX_MODEL->getTaxMethod() eq 'Compound'}{$CHARGE_TAX_MODEL->get('compoundon')}{/if}"
																class="span1 chargeTaxPercentage" name="charges[{$CHARGE_ID}][taxes][{$CHARGE_TAX_ID}]" value="{$SH_TAX_VALUE}"
																data-rule-positive=true data-rule-inventory_percentage=true />&nbsp;%
														</div>
													</td>
													<td style="text-align: right;" class="lineOnTop">
														<div class="field-value">
															<input type="text" class="span1 chargeTaxValue cursorPointer pull-right chargeTax{$CHARGE_ID}{$CHARGE_TAX_ID}" size="5" value="0" readonly />&nbsp;
														</div>
													</td>
												</tr>
											{/foreach}
										{/foreach}
									</tbody>
								</table>
							</div>-->
							<!-- End Popup Div for Shipping and Handling TAX -->
						<!--</td>
						<td>
							<input type="hidden" id="chargeTaxTotalHidden" class="chargeTaxTotal" name="s_h_percent" value="{if $FINAL.shtax_totalamount}{$FINAL.shtax_totalamount}{else}0{/if}" />
							<span class="pull-right" id="chargeTaxTotal">{if $FINAL.shtax_totalamount}{$FINAL.shtax_totalamount}{else}0{/if}</span>
						</td>
					</tr>-->
					<!-- End Vu Mai -->
					<!--<tr>
						<td width="83%">

							<span class="pull-right">
                                (-)&nbsp;<strong>
                                    <a class="inventoryLineItemEdits" href="javascript:void(0)" id="deductTaxes">{vtranslate('LBL_DEDUCTED_TAXES',$MODULE)} </a>
                                </strong>
                            </span>

							<div id="deductTaxesBlock" class="hide validCheck deductTaxesBlock">
								<table class="table table-nobordered popupTable">
									<tbody>
										{foreach key=DEDUCTED_TAX_ID item=DEDUCTED_TAX_INFO from=$DEDUCTED_TAXES}
											<tr>
												<td class="lineOnTop">{$DEDUCTED_TAX_INFO['taxlabel']}</td>
												<td class="lineOnTop">
													<input type="text" class="span1 deductTaxPercentage" name="{$DEDUCTED_TAX_INFO['taxname']}_group_percentage" value="{if $DEDUCTED_TAX_INFO['selected'] || !$RECORD_ID}{$DEDUCTED_TAX_INFO['percentage']}{else}0{/if}"
														   data-rule-positive=true data-rule-inventory_percentage=true />&nbsp;%
												</td>
												<td style="text-align: right;" class="lineOnTop">
													<input type="text" class="span1 deductTaxValue cursorPointer pull-right" name="{$DEDUCTED_TAX_INFO['taxname']}_group_amount" size="5" readonly value="{$DEDUCTED_TAX_INFO['amount']}"/>&nbsp;
												</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
						</td>
						<td>
							<span class="pull-right" id="deductTaxesTotalAmount">{if $FINAL.deductTaxesTotalAmount}{$FINAL.deductTaxesTotalAmount}{else}0{/if}</span>
						</td>
					</tr>-->
				{/if}

				<tr valign="top">
					<td width="83%" class="adjusment-type-container">
						<div class="pull-right">
							<!--<strong>{vtranslate('LBL_ADJUSTMENT',$MODULE)}&nbsp;&nbsp;</strong>-->
							<span>{vtranslate('LBL_ADJUSTMENT',$MODULE)}&nbsp;&nbsp;</span>
							<span>
								<!-- Modified By Kelvin Thang -- Date: 2018-07-07-->
								<input type="radio" name="adjustmentType" option value="+" {if $FINAL.adjustment gte 0}checked{/if}>&nbsp;{vtranslate('LBL_PLUS',$MODULE)}&nbsp;&nbsp;
							</span>
							<span>
								<input type="radio" name="adjustmentType" option value="-" {if $FINAL.adjustment lt 0}checked{/if}>&nbsp;{vtranslate('LBL_DEDUCT',$MODULE)}
							</span>
						</div>
					</td>
					<!-- Modified by Kelvin Thang -- Date: 2018-07-13-->
					<td>
						<span class="pull-right">
							<input id="adjustment" name="adjustment" type="text" onkeyup="formatNumber(this)" data-rule-currency="true" class="lineItemInputBox form-control 00"
								   value="{if $FINAL.adjustment lt 0}{Vtiger_Currency_UIType::transformDisplayValue(abs($FINAL.adjustment), null, true)}{elseif $FINAL.adjustment}{{Vtiger_Currency_UIType::transformDisplayValue($FINAL.adjustment, null, true)}}{else}0{/if}">


<!--
								   value="
								   {if $FINAL.adjustment lt 0}
								   {abs($FINAL.adjustment)}
								   {elseif $FINAL.adjustment}
								   {Vtiger_Currency_UIType::transformDisplayValue($FINAL.adjustment, null, true)}
									{else}0{/if}">
							<input id="adjustment" name="adjustment" type="text" data-rule-positive="true" class="lineItemInputBox form-control" value="{if $FINAL.adjustment lt 0}{abs($FINAL.adjustment)}{elseif $FINAL.adjustment}{$FINAL.adjustment}{else}0{/if}">-->
						</span>
					</td>
				</tr>
				<tr valign="top">
					<td width="83%">
						<span class="pull-right fieldTotal">
                            <strong>{vtranslate('LBL_GRAND_TOTAL',$MODULE)}</strong>
                        </span>
					</td>
					<td  class="grandTotal" >
						<span id="grandTotal" name="grandTotal" class="pull-right grandTotal fieldTotal">{$FINAL.grandTotal}</span>
					</td>
				</tr>
				{* Remove module invoice $MODULE eq 'Invoice' by Phuc on 2019.08.01 *}
				{* Remove mPhuc on 2019.10.07 *}
				<!--{if $MODULE eq 'PurchaseOrder'}
					<tr valign="top">
						<td width="83%" >
							<div class="pull-right">
								{if $MODULE eq 'Invoice'}
									<strong>{vtranslate('LBL_RECEIVED',$MODULE)}</strong>
								{else}
									<strong>{vtranslate('LBL_PAID',$MODULE)}</strong>
								{/if}
							</div>
						</td>
						<td>
							{if $MODULE eq 'Invoice'}
								<span class="pull-right"><input id="received" name="received" onkeyup="formatNumber(this)" type="text" class="lineItemInputBox form-control" value="{if $RECORD->getDisplayValue('received') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('received')}{else}0{/if}"></span>
								{else}
								<span class="pull-right"><input id="paid" name="paid" onkeyup="formatNumber(this)" type="text" class="lineItemInputBox" value="{if $RECORD->getDisplayValue('paid') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('paid')}{else}0{/if}"></span>
								{/if}
						</td>
					</tr>
					<tr valign="top">
						<td width="83%" >
							<div class="pull-right">
								<strong>{vtranslate('LBL_BALANCE',$MODULE)}</strong>
							</div>
						</td>
						<td>
							<span class="pull-right"><input id="balance" name="balance" type="text" class="lineItemInputBox form-control" value="{if $RECORD->getDisplayValue('balance') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('balance')}{else}0{/if}" readonly></span>
						</td>
					</tr>
				{/if}-->
			</table>
		</div>
	{/if}
</div>
