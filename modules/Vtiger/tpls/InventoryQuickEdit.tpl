{* Added By Vu Mai on 2022-09-27 to render inventory form *}
{strip}
	<div class="modal-dialog modal-md quick-edit">
		<div class="modal-content">
			<form id="quick-edit" class="inventory-form form-horizontal record-quick-edit" name="quick_edit">
				{if !empty($RECORD_ID)}
					{assign var=HEADER_TITLE value={vtranslate('LBL_EDITING', $MODULE)}|cat:" "|cat:{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}}
				{else}
					{assign var=HEADER_TITLE value={vtranslate('LBL_QUICK_CREATE', $MODULE)}|cat:" "|cat:{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}}
				{/if}

				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="InventoryQuickEditAjax" />
				<input type="hidden" name="mode" value="saveAjax" />
				<input type="hidden" name="record" value="{$RECORD_ID}" />
				<input type="hidden" name="customer_id" value="{$CUSTOMER_ID}" />
				<input type="hidden" name="customer_type" value="{$CUSTOMER_TYPE}" />
				<input type="hidden" name="line_items" value='{ZEND_JSON::encode($LINE_ITEMS)}' />
				<input type="hidden" name="total" value="{$DATA.final_details.hdnSubTotal}" />
				<input type="hidden" name="final_discount_type" value="{$DATA.final_details.discount_type_final}" />
				<input type="hidden" name="final_discount_percent" value="{$DATA.final_details.discount_percentage_final}" />
				<input type="hidden" name="final_discount_amount" value="{$DATA.final_details.discount_amount_final}" />
				<input type="hidden" name="final_discount_total" value="{$DATA.final_details.discountTotal_final}" />
				<input type="hidden" name="charges" value="{$DATA.final_details.shipping_handling_charge}" />
				<input type="hidden" name="taxes" value='{ZEND_JSON::encode($TAXES)}' />
				<input type="hidden" name="pre_tax_total" value="{$DATA.final_details.preTaxTotal}" />
				<input type="hidden" name="tax_total" value="{$DATA.final_details.tax_totalamount}" />
				<input type="hidden" name="adjustment_total" value="
					{if $DATA.final_details.adjustment lt 0}
						{abs($DATA.final_details.adjustment)}
					{elseif $DATA.final_details.adjustment}
						{$DATA.final_details.adjustment}
					{else}
						0
					{/if}" />
				<input type="hidden" name="grand_total" value="{$DATA.final_details.grandTotal}" />
				<input type="hidden" name="status_field_name" value="{$STATUS_FIELD_NAME}" />
				<input type="hidden" name="is_inventory" value="true" />

				{* For create from Telesale Campaign *}
				{if $CAMPAIGN_ID}
					<input type="hidden" name="campaign_id" value="{$CAMPAIGN_ID}" />
				{/if}

				<div class="modal-body fancyScrollbar">
					<div class="quick-edit-content form-group">
						<!-- Inventory Detail -->
						<div id="inventory-detail">
							<a class="col-md-12 block-header" data-toggle="collapse" href="#inventory-detail-collapse">
								{vtranslate("LBL_{strtoupper($MODULE)}_DETAILS")}
								<i class="far fa-angle-down marginleft-auto"></i>
							</a>
							<div class="collapse in" id="inventory-detail-collapse">
								<!-- Select product and service, select tax mode -->
								<hr class="mt-3 mb-3">
								<div class="flex col-md-12">
									<div class="fieldLabel col-md-5">
										<label>{vtranslate('LBL_SELECT_PRODUCT_OR_SERVICE')}:</label>
									</div>
									<div class="fieldValue col-md-7">
										<input type="text" class="form-control text-left select-item inputElement">
									</div>		
								</div>
								<div class="flex col-md-12 last-field">
									<div class="fieldLabel col-md-5">
										<label>{vtranslate('LBL_TAX_MODE')}:</label>
									</div>
									<div class="fieldValue col-md-7">
										<select class="custom-select tax-type inputElement" name="tax_type">
											<option value="group" {if $DATA.final_details.taxtype == 'group'}selected{/if}>{vtranslate('LBL_GROUP', $MODULE)}</option>
											<option value="individual" {if $DATA.final_details.taxtype == 'individual'}selected{/if}>{$DATA.taxtype}{vtranslate('LBL_INDIVIDUAL', $MODULE)}</option>
										</select>
									</div>
								</div>
								<hr class="mt-3 mb-3">

								<!-- List product -->
								<table id="item-list">
									<tbody id="line-items">
										{if count($LINE_ITEMS) > 0}
											{foreach item=ITEM from=$LINE_ITEMS}
												{include file="modules/Vtiger/tpls/InventoryQuickEditRowItem.tpl" ROW_ITEM=$ITEM DATA=$DATA}
											{/foreach}
										{/if}
										<tr class="text-center no-item-msg">
											<td><span>{vtranslate('LBL_NO_PRODUCTS_OR_SERVICES_YET_MSG')}</span></td>
										</tr>		
									</tbody>
									<tfoot id="template" style="display:none;">
										{include file="modules/Vtiger/tpls/InventoryQuickEditRowItem.tpl"}
									</tfoot>
								</table>
								<hr class="mt-3 mb-3"> 

								<!-- Final Detail -->
								{include file="modules/Vtiger/tpls/InventoryFinalDetail.tpl" FINAL=$DATA.final_details}
							</div>
						</div>	

						<!-- General Info -->
						<div id="genneral-info">
							<a class="col-md-12 block-header" data-toggle="collapse" href="#genneral-info-collapse">
								{vtranslate('LBL_GENERAL_INFORMATION')}
								<i class="far fa-angle-down marginleft-auto"></i>
							</a>
							<div class="collapse in" id="genneral-info-collapse">
								<hr class="mt-3 mb-3">

								{if !empty($DATA.status_field_name)}
									<div class="flex col-md-12">
										<div class="fieldLabel col-md-5">
											<label>{vtranslate('LBL_STATUS')}:<span class="text-danger"> *</span></label>
										</div> 
										<div class="fieldValue col-md-7">
											<select name="{$DATA.status_field_name}" class="inputElement custom-select valid" data-rule-required="true" aria-required="true">
												<option value=""></option>
												{foreach from=$DATA.status_options.options item=OPTION key=KEY}
													<option value="{$OPTION.key}" {if $OPTION.key == $DATA[$DATA.status_field_name]}selected{/if}>{$OPTION.label}</option>
												{/foreach}
											</select>
										</div>
									</div>
								{/if}				

								<div class="flex col-md-12 last-field">
									<div class="fieldLabel col-md-5">
										<label>{vtranslate('LBL_DESCRIPTION')}:</label>
									</div> 
									<div class="fieldValue col-md-7">
										<textarea name="description" rows="2" wrap="soft" class="inputElement form-control text-left">{$DATA.description}</textarea>
									</div>
								</div>
								<hr class="mt-3 mb-0">
							</div>
						</div>

						{* TODO Modify to support any inventory module *}
						<!-- Shipment Details -->
						{if $MODULE == 'SalesOrder'}
							<div id="shipment-details">
								<a class="col-md-12 block-header" data-toggle="collapse" href="#shipment-details-collapse">
									{vtranslate('LBL_SHIPMENT_DETAILS')}
									<i class="far fa-angle-down marginleft-auto"></i>
								</a>
								<div class="collapse in" id="shipment-details-collapse">
									<hr class="mt-3 mb-3">
									<div class="flex col-md-12">
										<div class="fieldLabel col-md-5">
											<label>{vtranslate('LBL_COPY_SHIPPING_ADDRESS')}:</label>
										</div> 
										<div class="fieldValue col-md-7">
											<textarea name="ship_street" rows="2" wrap="soft" class="inputElement form-control text-left">{$DATA.ship_street}</textarea>
										</div>
									</div>
									<div class="flex col-md-12">
										<div class="fieldLabel col-md-5">
											<label>{vtranslate('LBL_RECEIVER_NAME', 'SalesOrder')}:<span class="text-danger" style="display: none;"> *</span></label>
										</div> 
										<div class="fieldValue col-md-7">
											<input name="receiver_name" type="text" value="{$DATA.receiver_name}" class="inputElement form-control valid text-left">
										</div>
									</div>
									<div class="flex col-md-12">
										<div class="fieldLabel col-md-5">
											<label>{vtranslate('LBL_RECEIVER_PHONE', 'SalesOrder')}:<span class="text-danger" style="display: none;"> *</span></label>
										</div> 
										<div class="fieldValue col-md-7">
											<input name="receiver_phone" type="text" value="{$DATA.receiver_phone}" class="inputElement mt-2 form-control valid text-left">
										</div> 
									</div>
									<div class="flex col-md-12 align-item-center last-field">
										<div class="fieldLabel col-md-5">
											<label class="custom-control-label">{vtranslate('LBL_HAS_INVOICE', 'SalesOrder')}:</label>
										</div> 
										<div class="fieldValue col-md-7 custom-control custom-checkbox">
											<input type="checkbox" autocomplete="off" name="has_invoice" class="inputElement custom-control-input" {if $DATA.has_invoice}checked{/if}>
										</div>
									</div>
									<hr class="mt-3 mb-0">
								</div>
							</div>
						{/if}
					</div>
				</div>
				<!-- Footer -->
				<div class="modal-footer footer">
					<center>
						{if $BUTTON_NAME neq null}
							{assign var=BUTTON_LABEL value=$BUTTON_NAME}
						{else}
							{assign var=CUSTOMER_MODULES value=getGlobalVariable('customerModules')}

							{if in_array($MODULE, CUSTOMER_MODULES)}
								{assign var=BUTTON_LABEL value={vtranslate('LBL_UPDATE_CUSTOMER', 'Vtiger')}}
							{else}
								{$replaceParams = ['%module' => vtranslate($MODULE, $MODULE)]}
								{assign var=BUTTON_LABEL value={vtranslate('LBL_UPDATE_MODULE', 'Vtiger', $replaceParams)}}
							{/if}


						{/if}

						{if $RECORD_ID eq null}
							{assign var='EDIT_VIEW_URL' value="{$MODULE_MODEL->getCreateRecordUrl()}"}
							{$replaceParams = ['%module' => vtranslate($MODULE, $MODULE)]}
							{assign var=BUTTON_LABEL value={vtranslate('LBL_CREATE_MODULE', 'Vtiger', $replaceParams)}}

							<script>
								var BUTTON_UPDATE_LABEL = '{vtranslate('LBL_UPDATE_MODULE', 'Vtiger', $replaceParams)}';
							</script>
						{else}
							{assign var='EDIT_VIEW_URL' value="{$MODULE_MODEL->getCreateRecordUrl()}&record={$RECORD_ID}"}
						{/if}
						
						<button class="btn btn-default btn-show-full-form" data-href="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
						<button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-success" type="submit" name="saveButton"><strong>{$BUTTON_LABEL}</strong></button>
						<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</center>
				</div>
			</form>
		</div>
	</div>
	{* Added by Vu Mai on 2022-11-04 to insert inventory script support open popover in mode create *}
	<script src="{vresource_url('layouts/v7/modules/Inventory/resources/Edit.js')}">
{strip}	