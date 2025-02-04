{* Added by Vu Mai on 2022-10-12 to render final detail view for inventory form *}

{strip}
	{assign var=CHARGE_AND_CHARGETAX_VALUES value=$FINAL.chargesAndItsTaxes}
	 
	<div class="final-detail">
		<!-- Total temp -->
		<div class="flex col-md-12 mb-2">
			<div class="col-md-7 paddingleft0">
				<label>{vtranslate('LBL_TOTAL')}:</label>
			</div> 
			<div class="text-right col-md-5 temp-price">
				<label>
					{if !empty($FINAL.hdnSubTotal)}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.hdnSubTotal, null, true)}
					{else}
						0
					{/if}
				</label>
			</div>
		</div>

		<!-- Final Discount -->
		<div class="final-discount-container flex col-md-12 mb-2">
			<div class="col-md-7 paddingleft0">
				(-)&nbsp;
				<strong>
					<a href="javascript:void(0)" class="text-danger final-discount">{vtranslate('LBL_OVERALL_DISCOUNT')}&nbsp;
						<span class="discount-detail">
							({if $FINAL.discount_type_final == 'percentage'}
								{$FINAL.discount_percentage_final}%
							{elseif $FINAL.discount_type_final == 'amount'}
								{Vtiger_Currency_UIType::transformDisplayValue($FINAL.discount_amount_final, null, false)}
							{else}	
								0
							{/if})
						</span>		
					</a>
				</strong>
				<div id="final-discount-ui" class="finalDiscountUI validCheck hide">
					{assign var=DISCOUNT_TYPE_FINAL value="zero"}

					{if !empty($FINAL.discount_type_final)}
						{assign var=DISCOUNT_TYPE_FINAL value=$FINAL.discount_type_final }
					{/if}
					
					<p class="popover-title hide">
						{vtranslate('LBL_SET_DISCOUNT_FOR',$MODULE)} : &nbsp; 
						<span class="sub-total-val">
							{if !empty($FINAL.hdnSubTotal)}
								{Vtiger_Currency_UIType::transformDisplayValue($FINAL.hdnSubTotal, null, true)}
							{else}
								0
							{/if}
						</span>
					</p>
					<input type="hidden" id="discount_type_final" name="discount_type_final" value="{$DISCOUNT_TYPE_FINAL}" />
					<input type="hidden" id="discount_total_final" name="discount_total_final" value="{$FINAL.discountTotal_final}" />
					<table width="100%" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
						<tbody>
							<tr>
								<td>
									<div class="field-value">
										<input type="radio" name="discount_final" class="discounts" data-discount-type="zero" {if $DISCOUNT_TYPE_FINAL eq 'zero'}checked{/if} />&nbsp; {vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}
									</div>
								</td>
								<td class="lineOnTop">
									<div class="field-value">
										<!-- Make the discount value as zero -->
										<input type="hidden" class="discount-value" value="0" />
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="field-value">
										<input type="radio" name="discount_final" class="discounts" data-discount-type="percentage" {if $DISCOUNT_TYPE_FINAL eq 'percentage'}checked{/if} />&nbsp; % {vtranslate('LBL_OF_PRICE',$MODULE)}
									</div>
								</td>
								<td>
									<div class="field-value">
										<span class="pull-right">&nbsp;%</span>
										<input type="text" data-rule-positive="true" data-rule-inventory_percentage="true" 
										id="discount_percentage_final" name="discount_percentage_final" 
										value="{$FINAL.discount_percentage_final}" 
										class="discount-percentage span1 pull-right discount-value {if $DISCOUNT_TYPE_FINAL neq 'percentage'}hide{/if}" />
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="field-value">
										<input type="radio" name="discount_final" class="discounts" data-discount-type="amount" {if $DISCOUNT_TYPE_FINAL eq 'amount'}checked{/if} />&nbsp;{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}
									</div>
								</td>
								<td>
									<div class="field-value">
										<input type="text" data-rule-currency="true" onkeyup="formatNumber(this)" 
										id="discount_amount_final" name="discount_amount_final" 
										value="{Vtiger_Currency_UIType::transformDisplayValue($FINAL.discount_amount_final, null, true)}" 
										class="span1 pull-right discount-amount discount-value {if $DISCOUNT_TYPE_FINAL neq 'amount'}hide{/if}" />
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div> 
			<div class="col-md-5">
				<div class="discount-total text-right">
					{if !empty($FINAL.discountTotal_final)}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.discountTotal_final, null, false)}
					{else}
						0
					{/if}
				</div>
			</div>
		</div>

		<!-- Final Charges -->
		<div class="final-charge-container flex col-md-12 mb-2">
			<div class="col-md-7 paddingleft0">
				(+)&nbsp;
				<strong>
					<a href="javascript:void(0)" class="text-primary final-charges">{vtranslate('LBL_CHARGES')}</a>
				</strong>
				<div class="charges-block-container">
					<div id="charges-block" class="validCheck hide charges-block">
						<table width="100%" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
							{foreach key=CHARGE_ID item=CHARGE_MODEL from=$INVENTORY_CHARGES}
								<tr>
									{assign var=CHARGE_VALUE value=$CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['value']}
									{assign var=CHARGE_PERCENT value=0}

									{if $CHARGE_MODEL->get('format') eq 'Percent' && $CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['percent'] neq NULL}
										{assign var=CHARGE_PERCENT value=$CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['percent']}
									{/if}

									<td class="lineOnTop charge-name" data-charge-id="{$CHARGE_ID}">{$CHARGE_MODEL->getName()}</td>
									<td class="lineOnTop">
										{if $CHARGE_MODEL->get('format') eq 'Percent'}
											<div class="flex">
												<input type="text" class="span1 charge-percent" size="5" 
												data-rule-currency=true onkeyup="formatNumber(this)" 
												data-rule-inventory_percentage=true name="charges[{$CHARGE_ID}][percent]" 
												value="{if $CHARGE_PERCENT}{$CHARGE_PERCENT}{else if $RECORD_ID}0{else}{$CHARGE_MODEL->getValue()}{/if}" />&nbsp;%
											</div>	
										{/if}
									</td>
									<td style="text-align: right;" class="lineOnTop">
										<div class="flex">
											<input type="text" class="span1 charge-value" size="5" data-charge-id="{$CHARGE_ID}"
											{if $CHARGE_MODEL->get('format') eq 'Percent'}readonly{/if} 
											data-rule-currency=true onkeyup="formatNumber(this)" name="charges[{$CHARGE_ID}][value]" 
											value="
												{if $CHARGE_VALUE}
													{Vtiger_Currency_UIType::transformDisplayValue($CHARGE_VALUE, null, true)}
												{else if $RECORD_ID}
													0
												{else}
													{$CHARGE_MODEL->getValue() * $USER_MODEL->get('conv_rate')}
												{/if}"
											/>
										</div>	
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</div>	
			</div>
			<div class="col-md-5">
				<div class="charges-total text-right">
					{if $FINAL.shipping_handling_charge}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.shipping_handling_charge)}
					{else}
						0
					{/if}
				</div>
			</div>
		</div>

		<!-- Pre Tax Total -->
		<div class="flex col-md-12 mb-2">
			<div class="col-md-7 paddingleft0">
				<label>{vtranslate('LBL_PRE_TAX_TOTAL')}</label>
			</div>
			<div class="col-md-5 text-right">
				<label class="pre-tax-total">
					{if !empty($FINAL.preTaxTotal)}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.preTaxTotal, null, false)}
					{else}
						0
					{/if}
				</label>
			</div>
		</div>

		<!-- Group Tax -->
		<div class="group-tax-container flex col-md-12 mb-2">
			<div class="col-md-7 paddingleft0">
				(+)&nbsp;
				<strong>
					<a href="javascript:void(0)" class="text-primary group-tax">{vtranslate('LBL_TAX')}</a>
				</strong>
				<div class="hide finalTaxUI validCheck" id="group_tax_div">
					<input type="hidden" class="popover_title" value="{vtranslate('LBL_GROUP_TAX',$MODULE)}" />
					<table width="100%" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
						{foreach item=TAX_DETAIL name=GROUP_TAX_LOOP key=LOOP_COUNT from=$DATA.final_details.taxes}
							<tr>
								<td class="lineOnTop"><div class="field-value">{$TAX_DETAIL.taxlabel}</div></td>
								<td class="lineOnTop">
									<div class="field-value">
										<input type="text" size="5" data-compound-on="{if $TAX_DETAIL['method'] eq 'Compound'}{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_DETAIL['compoundon']))}{/if}" data-name="{$TAX_DETAIL.taxname}"
											name="{$TAX_DETAIL.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.GROUP_TAX_LOOP.iteration}" value="{$TAX_DETAIL.percentage}" class="span1 group-tax-percentage"
											data-rule-positive=true data-rule-inventory_percentage=true />&nbsp;%
									</div>
								</td>
								<td style="text-align: right;" class="lineOnTop">
									<div class="field-value">
										<input type="text" size="6" name="{$TAX_DETAIL.taxname}_group_amount" id="group_tax_amount{$smarty.foreach.GROUP_TAX_LOOP.iteration}" style="cursor:pointer;" value="{Vtiger_Currency_UIType::transformDisplayValue($TAX_DETAIL.amount, null, true)}" readonly class="cursorPointer span1 group-tax-total" />
									</div>
								</td>
							</tr>
						{/foreach}
						<input type="hidden" id="group_tax_count" value="{$smarty.foreach.GROUP_TAX_LOOP.iteration}" />
					</table>
				</div>	
			</div>
			<div class="col-md-5">
				<div class="final-tax-total text-right">
					{if $FINAL.tax_totalamount}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.tax_totalamount, null, true)}
					{else}
						0
					{/if}
				</div>
			</div>
		</div>

		<!-- Taxes on charges -->
		<!-- <div class="final-taxes-charges-container flex col-md-12 mb-2">
			<div class="col-md-7 paddingleft0">
				(+)&nbsp;
				<strong>
					<a href="javascript:void(0)" class="text-primary final-taxes-charges">{vtranslate('LBL_TAXES_ON_CHARGES')}</a>
				</strong>
				<div class="final-taxes-charge">
					<div id="chargeTaxesBlock" class="hidden validCheck chargeTaxesBlock">
						<p class="popover-title hide">
							{vtranslate('LBL_TAXES_ON_CHARGES', $MODULE)} :&nbsp;
							<span id="SHChargeVal" class="SHChargeVal">
								{if $FINAL.shipping_handling_charge}
									{Vtiger_Currency_UIType::transformDisplayValue($FINAL.shipping_handling_charge, false)}
								{else}
									0
								{/if}
							</span>
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

												{assign var=CHARGE_TAX_VALUE value=$SH_TAX_VALUE * $CHARGE_AND_CHARGETAX_VALUES[$CHARGE_ID]['value'] / 100}
											{/if}

											<td class="lineOnTop"><div class="field-value">{$CHARGE_MODEL->getName()} - {$CHARGE_TAX_MODEL->getName()}</div></td>
											<td class="lineOnTop">
												<div class="field-value">
													<input type="text" data-charge-id="{$CHARGE_ID}" data-charge-tax-id="{$CHARGE_TAX_ID}" data-compound-on="{if $CHARGE_TAX_MODEL->getTaxMethod() eq 'Compound'}{$CHARGE_TAX_MODEL->get('compoundon')}{/if}"
														class="span1 charge-tax-percentage" name="charges[{$CHARGE_ID}][taxes][{$CHARGE_TAX_ID}]" value="{$SH_TAX_VALUE}"
														data-rule-positive=true data-rule-inventory_percentage=true />&nbsp;%
												</div>
											</td>
											<td style="text-align: right;" class="lineOnTop">
												<div class="field-value">
													<input type="text" class="span1 chargeTaxValue cursorPointer pull-right chargeTax{$CHARGE_ID}{$CHARGE_TAX_ID}" size="5" value="{Vtiger_Currency_UIType::transformDisplayValue($CHARGE_TAX_VALUE, null, false)}" readonly />&nbsp;
												</div>
											</td>
										</tr>
									{/foreach}
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>	
			</div>
			<div class="col-md-5">
				<div class="charges-taxes text-right">
					{if $FINAL.shtax_totalamount}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.shtax_totalamount)}
					{else}
						0
					{/if}
				</div>
			</div>
		</div> -->

		<!-- Adjustment -->
		<div class="flex col-md-12 mb-2 align-item-center adjustment-container">
			<div class="col-md-7 paddingleft0">
				<label class="mr-2">{vtranslate('LBL_ADJUSTMENT')}</label>
				<span>
					<input type="radio" name="adjustment_type" option="" value="+" {if $FINAL.adjustment gte 0}checked{/if}/>&nbsp;{vtranslate('LBL_PLUS')}&nbsp;&nbsp;
				</span>
				<span>
					<input type="radio" name="adjustment_type" option="" value="-" {if $FINAL.adjustment lt 0}checked{/if}/>&nbsp;{vtranslate('LBL_DEDUCT')}&nbsp;&nbsp;
				</span>
			</div>
			<div class="fieldValue col-md-5">
				<input type="text" name="adjustment_total" onkeyup="formatNumber(this)" class="adjustment-total inputElement text-right" 
				value="
					{if $FINAL.adjustment lt 0}
						{Vtiger_Currency_UIType::transformDisplayValue(abs($FINAL.adjustment), null, true)}
					{elseif $FINAL.adjustment}
						{{Vtiger_Currency_UIType::transformDisplayValue($FINAL.adjustment, null, true)}}
					{else}
						0
					{/if}"/>
			</div>
		</div>
		<hr class="mt-2 mb-2">
		
		<!-- Grand Total -->
		<div class="flex col-md-12 mb-2">
			<div class="col-md-5 paddingleft0">
				<h5><strong>{vtranslate('LBL_GRAND_TOTAL')}:</strong></h5>
			</div> 
			<div class="text-right col-md-7 total">
				<h5 class="text-danger">
					{if !empty($FINAL.grandTotal)}
						{Vtiger_Currency_UIType::transformDisplayValue($FINAL.grandTotal, null, false)}
					{else}
						0
					{/if}
				</h5>
			</div>
		</div>
		<hr class="mt-2 mb-0">
	</div>	
{/strip}