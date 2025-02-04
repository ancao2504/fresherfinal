{* Added by Vu Mai on 2022-10-12 to render item template in line item of inventory form *}

{strip}
	<tr class="flex" data-id="{$ROW_ITEM.id}">
		<td class="product-name">
			<button data-toggle="tooltip" title="{vtranslate('LBL_DELETE')}" type="button" class="btn mr-1 remove-item btn-outline-danger btn-sm"><i aria-hidden="true" class="fa fa-close"></i></button>
			<label class="mt-3 inline-product-name" data-toggle="tooltip" title="{$ROW_ITEM.label}">{$ROW_ITEM.label}</label>
		</td> 
		<td class="fieldValue quantity">
			<input type="number" name="quantity" min="0" value="{if $ROW_ITEM.quantity}{$ROW_ITEM.quantity}{else}1{/if}" class="mt-2 text-left quantity-input form-control valid" aria-invalid="false">
		</td> 
		<td class="fieldValue price">
			<div class="flex-wraper">
				<span>x</span>
				<input type="text" name="item_price" class="readonly mt-2 ml-1 text-right item-price valid" value="{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.price, null, false)}" />
			</div>
			<div class="item-discount-container ml-3 mt-2">
				<strong>
					<a href="javascript:void(0)" class="individual-discount text-danger">(-)&nbsp;{vtranslate('LBL_DISCOUNT')} 
						<span class="item_discount">(
							{if $ROW_ITEM.discount_type == 'percentage'}
								{$ROW_ITEM.discount_percent}%
							{elseif $ROW_ITEM.discount_type == 'amount'}
								{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.discount_amount, null, false)}
							{else}	
								0
							{/if}
						)</span>:
					</a> 
				</strong>
			</div>
			<div class="discountUI validCheck hide" id="discount-div">
				{assign var="DISCOUNT_TYPE" value="zero"}

				{if !empty($ROW_ITEM.discount_type)}
					{assign var="DISCOUNT_TYPE" value=$ROW_ITEM.discount_type}
				{/if}

				<p class="popover-title hide">
					{vtranslate('LBL_SET_DISCOUNT_FOR',$MODULE)} : &nbsp; 
					<span class="sub-total-val">
						{if !empty($ROW_ITEM.total)}
							{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.total, null, false)}
						{else}
							0
						{/if}
					</span>
				</p>
				<input type="hidden" id="item-discount-type" name="item_discount_type" value="{$DISCOUNT_TYPE}" class="item-discount-type" />
				<input type="hidden" id="item-discount-total" name="item_discount_total" value="{$ROW_ITEM.discount_total}" class="item-discount-total" />
				<table width="100%" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
					<tr>
						<td>
							<div>
								<input type="radio" name="discount" class="discounts" data-discount-type="zero" />
								&nbsp;
								{vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}
							</div>
						</td>
						<td>
							<div class="field-value">
								<!-- Make the discount value as zero -->
								<input type="hidden" class="discount-value" value="0" />
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="field-value">
								<input type="radio" name="discount" class="discounts" data-discount-type="percentage" />
								&nbsp; %
								{vtranslate('LBL_OF_PRICE',$MODULE)}
							</div>
						</td>
						<td>
							<div class="field-value">
								<span class="pull-right">&nbsp;%</span>
								<input type="text" data-rule-positive="true" data-rule-inventory_percentage="true" id="discount-percentage" 
								name="discount_percentage" value="{$ROW_ITEM.discount_percent}" 
								class="discount-percentage span1 pull-right discount-value {if $DISCOUNT_TYPE != 'percentage'}hide{/if}" />
							</div>
						</td>
					</tr>
					<tr>
						<td class="LineItemDirectPriceReduction">
							<div class="field-value">
								<input type="radio" name="discount" class="discounts" data-discount-type="amount" />
								&nbsp;
								{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}
							</div>
						</td>
						<td>
							<div class="field-value">
								<input type="text" data-rule-currency="true" id="discount-amount" 
								name="discount_amount" onkeyup="formatNumber(this)" 
								value="{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.discount_amount, null, true)}" 
								class="span1 pull-right discount-amount discount-value {if $DISCOUNT_TYPE != 'amount'}hide{/if}" />
							</div>
						</td>
					</tr>
				</table>
			</div>	
			<div class="ml-3 mt-2"><strong>{vtranslate('LBL_TOTAL_AFTER_DISCOUNT')} :</strong></div>
			<div class="item-tax-container ml-3 mt-2">
				<strong><a href="javascript:void(0)" class="individual-tax text-primary" data-original-title="" title="">(+)&nbsp;{vtranslate('LBL_TAX')} :</a></strong>
			</div>
			<span class="taxDivContainer">
				<div class="taxUI hide" id="tax_div">
					<p class="popover-title hide">
						{vtranslate('LBL_SET_TAX_FOR',$MODULE)} : &nbsp; 
						<span class="sub-total-val">
							{if !empty($ROW_ITEM.total_after_discount)}
								{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.total_after_discount, null, false)}
							{else}
								0
							{/if}
						</span>
					</p>
					{if count($ROW_ITEM.taxes) > 0}
						<div class="individualTaxDiv">
							<!-- we will form the table with all taxes -->
							<table width="100%" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table">
								{foreach key=TAX_ROW_NO item=TAX_DATA from=$ROW_ITEM.taxes}
									{assign var="taxname" value=$TAX_DATA.taxname|cat:"_percentage"|cat:$row_no}
									{assign var="tax_id_name" value="hidden_tax"|cat:$TAX_ROW_NO+1|cat:"_percentage"|cat:$row_no}
									{assign var="taxlabel" value=$TAX_DATA.taxlabel|cat:"_percentage"|cat:$row_no}
									{assign var="popup_tax_rowname" value="popup_tax_row"|cat:$row_no}
									
									<tr>
										<td>&nbsp;&nbsp;{$TAX_DATA.taxlabel}</td>
										<td class="field-value text-right">
											<input type="text" data-name="{$TAX_DATA.taxname}" data-rule-positive=true data-rule-inventory_percentage=true name="{$taxname}" id="{$taxname}" 
											value="{Vtiger_Currency_UIType::transformDisplayValue($TAX_DATA.percentage, null, true)}" 
											data-compound-on="{if $TAX_DATA.method eq 'Compound'}{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_DATA.compoundon))}{/if}" 
											data-regions-list="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_DATA.regionsList))}" class="span1 tax-percentage" />&nbsp;%
										</td>
										<td class="field-value text-right">
											<input type="text" data-name="{$TAX_DATA.taxname}" name="{$popup_tax_rowname}" class="cursorPointer span1 tax-total taxTotal{$TAX_DATA.taxid}" 
											value="{Vtiger_Currency_UIType::transformDisplayValue($TAX_DATA.amount, null, true)}" readonly />
										</td>
									</tr>
								{/foreach}
							</table>
						</div>
					{else}
						<div class="individualTaxDiv">
							<!-- we will form the table with all taxes -->
							<table width="100%" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table">
								{foreach key=TAX_ROW_NO item=TAX_DATA from=$DATA.final_details.taxes}
									{assign var="taxname" value=$TAX_DATA.taxname|cat:"_percentage"|cat:$row_no}
									{assign var="tax_id_name" value="hidden_tax"|cat:$TAX_ROW_NO+1|cat:"_percentage"|cat:$row_no}
									{assign var="taxlabel" value=$TAX_DATA.taxlabel|cat:"_percentage"|cat:$row_no}
									{assign var="popup_tax_rowname" value="popup_tax_row"|cat:$row_no}
									
									<tr>
										<td>&nbsp;&nbsp;{$TAX_DATA.taxlabel}</td>
										<td class="field-value text-right">
											<input type="text" data-name="{$TAX_DATA.taxname}" data-rule-positive=true data-rule-inventory_percentage=true name="{$taxname}" id="{$taxname}" 
											value="{Vtiger_Currency_UIType::transformDisplayValue($TAX_DATA.percentage, null, true)}" 
											data-compound-on="{if $TAX_DATA.method eq 'Compound'}{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_DATA.compoundon))}{/if}" 
											data-regions-list="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_DATA.regionsList))}" class="span1 tax-percentage" />&nbsp;%
										</td>
										<td class="field-value text-right">
											<input type="text" data-name="{$TAX_DATA.taxname}" name="{$popup_tax_rowname}" class="cursorPointer span1 tax-total taxTotal{$TAX_DATA.taxid}" 
											value="{Vtiger_Currency_UIType::transformDisplayValue($TAX_DATA.amount, null, true)}" readonly />
										</td>
									</tr>
								{/foreach}
							</table>
						</div>	
					{/if}
				</div>
			</span>
		</td> 
		<td class="fieldValue total">
			<div class="flex-wraper">
				<div class="form-control readonly mt-2 total-value">{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.total, null, false)}</div>
			</div>
			<div class="item-discount-total mt-2 text-right text-danger">{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.discount_total, null, false)}</div>
			<div class="item-total-after-discount mt-2 text-right">{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.total_after_discount, null, false)}</div>
			<div class="item-tax-total item-tax-container mt-2 text-right text-primary">{Vtiger_Currency_UIType::transformDisplayValue($ROW_ITEM.tax_total, null, false)}</div>
		</td>
	</tr>
{/strip}