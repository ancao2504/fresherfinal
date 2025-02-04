{* Added by Hieu Nguyen on 2022-10-24 *}
{* Modified by Vu Mai on 2022 on 2022-12-08 to restyle according to mockup *}

{strip}
	<link rel="stylesheet" href="{vresource_url('modules/Campaigns/resources/TelesalesCampaignForm.css')}" />
	<script src="{vresource_url('modules/Campaigns/resources/TelesalesCampaignUtils.js')}" async defer></script>

	<form id="redistribute" method="POST">
		<input type="hidden" name="module" value="Campaigns">
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
		<input type="hidden" name="wizard" value="true" />
		<input type="hidden" name="campaign_name" value="{$RECORD->get('campaignname')}" />
		<input type="hidden" name="campaigns_purpose" value="{$RECORD->get('campaigns_purpose')}" />
		<input type="hidden" name="campaigns_purpose_text" value="{vtranslate($RECORD->get('campaigns_purpose'), $MODULE_NAME)}" />

		<div id="main-box" class="box shadowed">
			<div id="form-title" class="box-header">
				{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_FORM_TITLE', $MODULE_NAME)}&nbsp;
				<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_FORM_TOOLTIP', $MODULE_NAME)}">
					<i class="far fa-info-circle"></i>
				</span>
			</div>
			<div class="box-body">
				<!-- Wizard -->
				<div id="wizard" class="breadcrumb text-center" data-step="1">
					<ul class="crumbs">
						<li class="step active step1" data-step="1" style="z-index:9">
							<a href="javascript:void(0)">
								<span class="stepNum">1</span>
								<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_SELECT_MARKETING_LISTS', $MODULE_NAME)}</span>
							</a>
						</li>
						<li class="step step2" data-step="2" style="z-index:8">
							<a href="javascript:void(0)">
								<span class="stepNum">2</span>
								<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_SELECT_USERS', $MODULE_NAME)}</span>
							</a>
						</li>
						<li class="step step3" data-step="3" style="z-index:7">
							<a href="javascript:void(0)">
								<span class="stepNum">3</span>
								<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DISTRIBUTE_DATA', $MODULE_NAME)}</span>
							</a>
						</li>
						<li class="step step4" data-step="4" style="z-index:6">
							<a href="javascript:void(0)">
								<span class="stepNum">4</span>
								<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_ESTIMATION', $MODULE_NAME)}</span>
							</a>
						</li>
					</ul>
				</div>

				<!-- Form content -->
				<div id="form-content">
					<!-- Select MKT Lists -->
					<div class="step step1">
						<div class="box shadowed">
							<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS', $MODULE_NAME)}</div>
							<div class="box-body">
								<div class="buttons">
									<button type="button" id="btn-select-mkt-list" class="btn btn-default">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_BTN_SELECT_MKT_LISTS', $MODULE_NAME)}</button>
									&nbsp;&nbsp;
								</div>

								<table id="tbl-mkt-lists" class="table table-border-custom">
									<thead>
										<tr>
											<th rowspan="2" style="width:300px">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_MKT_LIST_NAME', $MODULE_NAME)}</th>
											<th colspan="3" class="text-center">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
											<th rowspan="2" style="width:75px"></th>
										</tr>
										<tr>
											<th class="text-right" style="border-left: 1px solid var(--black-5) !important;">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL', $MODULE_NAME)}</th>
											<th class="text-right">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LIST_DISTRIBUTED_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
											{* Modified by Vu Mai on 2023-02-15 to add tooltip *}
											<th class="text-right">
												{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LIST_REMAINING_CUSTOMERS_COUNT', $MODULE_NAME)}
												<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LIST_REMAINING_CUSTOMERS_COUNT_TOOLTIP', 'Campaigns')}">
													<i class="far fa-info-circle"></i>
												</span>
											</th>
											{* End Vu Mai *}
										</tr>
									</thead>
									<tbody>
										{foreach from=$SELECTED_MKT_LISTS key=KEY item=MKT_LIST_INFO}
											{include file='modules/Campaigns/tpls/Telesales/Edit/TableMKTListsRowTemplate.tpl'}
										{/foreach}
									</tbody>
									<tfoot>
										<tr>
											<th class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL', $MODULE_NAME)}</th>
											<th class="text-right"><span id="total-of-totals"></span></th>
											<th class="text-right"><span id="total-of-distributeds"></span></th>
											<th class="text-right"><span id="total-of-remainings"></span></th>
											<th></th>
										</tr>
									</tfoot>
								</table>

								<div id="data-statistics"></div>
							</div>
						</div>
					</div>

					<!-- Select Users -->
					<div class="step step2" style="display:none">
						<div class="box shadowed">
							<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS', $MODULE_NAME)}</div>
							<div class="box-body">
								<div class="form-group">
									<div>{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_ADD_NEW_USER', $MODULE_NAME)}:</div>
									<div>
										<input
											type="text" id="add-user" class="users-selector ml-3 mt-2" style="width:350px" 
											data-single-selection="true" data-users-only="true" data-assignable-users-only="true" 
											data-skip-users="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(array_keys($SELECTED_USERS)))}"
										/>
									</div>
								</div>
								<div class="form-group">
									<div>{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_CURRENT_USERS', $MODULE_NAME)}:</div>
									<div class="ml-3 mt-2">
										<table id="tbl-user-list" class="table table-border-custom" style="width:100%">
											<thead>
												<tr>
													<th rowspan="2" style="width:200px" class="text-center">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_EMPLOYEE_NAME', $MODULE_NAME)}</th>
													<th colspan="3" class="text-center">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_DISTRIBUTED_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
													<th rowspan="2" style="width:50px"></th>
												</tr>
												<tr>
													<th class="text-right" style="border-left: 1px solid var(--black-5) !important;">
														{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_ALREADY_CALLED_CUSTOMERS_COUNT', $MODULE_NAME)}&nbsp;
														<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_ALREADY_CALLED_CUSTOMERS_COUNT_TOOLTIP', $MODULE_NAME)}">
															<i class="far fa-info-circle"></i>
														</span>
													</th>
													<th class="text-right">
														{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_NOT_CALLED_CUSTOMERS_COUNT', $MODULE_NAME)}&nbsp;
														<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_NOT_CALLED_CUSTOMERS_COUNT_TOOLTIP', $MODULE_NAME)}">
															<i class="far fa-info-circle"></i>
														</span>
													</th>
													<th class="text-right">
														{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_TOTAL_DISTRIBUTED_CUSTOMERS_COUNT', $MODULE_NAME)}&nbsp;
														<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_TOTAL_DISTRIBUTED_CUSTOMERS_COUNT_TOOLTIP', $MODULE_NAME)}">
															<i class="far fa-info-circle"></i>
														</span>
													</th>
												</tr>
											</thead>
											<tbody>
												{foreach from=$SELECTED_USERS key=USER_ID item=USER_INFO}
													{include file='modules/Campaigns/tpls/Telesales/Edit/TableUserListRowTemplate.tpl'}
												{/foreach}
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Distribution Options -->
					<div class="step step3" style="display:none">
						<div class="box shadowed">
							<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA', $MODULE_NAME)}</div>
							<div class="box-body">
								<div class="form-group">
									<div class="col-lg-12">
										<label class="align-item-center">
											<input type="checkbox" name="apply_quota" {if $DISTRIBUTION_OPTIONS.apply_quota}checked{/if} />&nbsp;
											<span class="ml-2">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_APPLY_QUOTA_CHECKBOX', $MODULE_NAME)}</span>&nbsp;
											<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_APPLY_QUOTA_CHECKBOX_TOOLTIP', $MODULE_NAME)}">
												<i class="far fa-info-circle"></i>
											</span>
										</label>
									</div>
									<div class="col-lg-12 quota-value" {if !$DISTRIBUTION_OPTIONS.apply_quota}style="display:none"{/if}>
										{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_QUOTA_LIMIT', $MODULE_NAME)}:&nbsp;
										<input type="number" name="quota_limit" class="inputElement ml-3 text-center" value="{$DISTRIBUTION_OPTIONS.quota_limit}" data-rule-required="true" data-rule-number="true" data-rule-min="1" style="width:50px" />&nbsp;
										<span id="quota-error-msg" class="error-msg"></span>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="form-group">
									<div class="col-lg-12">
										{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS', $MODULE_NAME)}:&nbsp;
										<span id="distributable-count" class="bold ml-2"></span>&nbsp;
										<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS_TOOLTIP', $MODULE_NAME)}">
											<i class="far fa-info-circle"></i>
										</span>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="form-group">
									<div class="col-lg-2">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTION_METHOD', $MODULE_NAME)}:</div>
									<div class="col-lg-10">
										<label class="mr-3"><input type="radio" name="distribution_method" value="auto" {if $DISTRIBUTION_OPTIONS.distribution_method == 'auto'}checked{/if} />&nbsp;{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTION_METHOD_AUTO', $MODULE_NAME)}</label>
										<label class="ml-3"><input type="radio" name="distribution_method" value="manual" {if $DISTRIBUTION_OPTIONS.distribution_method == 'manual'}checked{/if} />&nbsp;{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTION_METHOD_MANUAL', $MODULE_NAME)}</label>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="form-group for-auto" {if $DISTRIBUTION_OPTIONS.distribution_method != 'auto'}style="display:none"{/if}>
									<div class="col-lg-2 text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY', $MODULE_NAME)}:</div>
									<div class="col-lg-10">
										<label>
											<input type="radio" name="auto_distribution_priority" value="none" {if $DISTRIBUTION_OPTIONS.auto_distribution_priority == 'none' || !$DISTRIBUTION_OPTIONS.auto_distribution_priority}checked{/if} />&nbsp;
											{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_NONE', $MODULE_NAME)}&nbsp;
											<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_NONE_TOOLTIP', $MODULE_NAME)}">
												<i class="far fa-info-circle"></i>
											</span>
										</label>
										<br/>
										<label>
											<input type="radio" name="auto_distribution_priority" value="user_currently_assigned_to_customer" {if $DISTRIBUTION_OPTIONS.auto_distribution_priority == 'user_currently_assigned_to_customer'}checked{/if} />&nbsp;
											{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_KEEP_ASSIGNED_USER', $MODULE_NAME)}&nbsp;
											<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_KEEP_ASSIGNED_USER_TOOLTIP', $MODULE_NAME)}">
												<i class="far fa-info-circle"></i>
											</span>
										</label>
										<br/>
										<label>
											<input type="radio" name="auto_distribution_priority" value="user_has_latest_telesales_call_to_customer" {if $DISTRIBUTION_OPTIONS.auto_distribution_priority == 'user_has_latest_telesales_call_to_customer'}checked{/if} />&nbsp;
											{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_LAST_CALLED_USER', $MODULE_NAME)}&nbsp;
											<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_LAST_CALLED_USER_TOOLTIP', $MODULE_NAME)}">
												<i class="far fa-info-circle"></i>
											</span>
										</label>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="form-group for-manual" {if $DISTRIBUTION_OPTIONS.distribution_method != 'manual'}style="display:none"{/if}>
									<div class="col-lg-12">
										{* Modifed by Vu Mai on 2023-03-02 to add column customer percent *}
										<table id="tbl-manual-distribution" class="table table-border-custom" style="width:100%">
											<thead>
												<tr>
													<th rowspan="2" style="width:40%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_EMPLOYEE_NAME', $MODULE_NAME)}</th>
													<th colspan="6" style="width:60%" class="text-center">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DISTRIBUTION_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
												</tr>
												<tr>
													<th class="text-right" style="border-left: 1px solid var(--black-5) !important;">
														{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_TOTAL_CURRENT_CUSTOMERS', $MODULE_NAME)}&nbsp;
														<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_TOTAL_CURRENT_CUSTOMERS_TOOLTIP', $MODULE_NAME)}">
															<i class="far fa-info-circle"></i>
														</span>
													</th>
													<th class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DISTRIBUTION_CUSTOMERS_PERCENT', $MODULE_NAME)}</th>
													<th class="text-right">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTE_NEW_CUSTOMERS', $MODULE_NAME)}</th>
													<th class="text-right no-wrap">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_TOTAL_FINAL_CUSTOMERS', $MODULE_NAME)}</th>
												</tr>
											</thead>
											<tbody></tbody>
											<tfoot>
												<tr>
													<th class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL', $MODULE_NAME)}</th>
													<th class="text-right"><span id="total-current-data"></span></th>
													<th></th>
													<th class="text-right">
														<span id="total-new-data" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTED_PER_TOTAL_DISTRIBUTABLE_TOOLTIP', $MODULE_NAME)}" data-toggle="tooltip">
															<span id="total-distributed">0</span>/<span id="total-customers"></span>
														</span>
														<span id="total-error-msg" class="error-msg no-wrap"></span>
													</th>
													<th class="text-right"><span id="total-final-data"></span></th>
												</tr>
											</tfoot>
										</table>
										{* End Vu Mai *}
									</div>
									<div class="clearfix"></div>
								</div>
								<!-- <div class="form-group">
									<div class="col-lg-12">
										<label class="align-item-center">
											<input type="checkbox" name="auto_distribute_new_customers_added_later" {if $DISTRIBUTION_OPTIONS.auto_distribute_new_customers_added_later}checked{/if} />&nbsp;
											<span class="ml-2">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTE_NEW_CUSTOMERS_CHECKBOX', $MODULE_NAME)}</span>&nbsp;
											<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTE_NEW_CUSTOMERS_CHECKBOX_TOOLTIP', $MODULE_NAME)}">
												<i class="far fa-info-circle"></i>
											</span>
										</label>
									</div>
									<div class="clearfix"></div>
								</div> -->
								<div class="clearfix"></div>
							</div>
						</div>
					</div>

					<!-- Estimation -->
					<div class="step step4" style="display:none">
						<div class="box shadowed">
							<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_ESTIMATION', $MODULE_NAME)}</div>
							<div class="box-body">
								<div id="hidden-inputs">
									<input type="hidden" name="mkt_list_ids" />
									<input type="hidden" name="selected_user_ids" />
									<input type="hidden" name="distribution_options" />
								</div>
								<div id="estimation-result"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="clearfix"></div>
			</div>
		</div>

		<!-- Footer buttons -->
		<div id="form-actions" class="modal-overlay-footer clearfix">
			<div class="row clear-fix">
				<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
					<button type="button" id="prev-step" class="btn btn-success" style="display:none"><i class="far fa-angle-left"></i>&nbsp;{vtranslate('LBL_BACK', 'Vtiger')}</button>&nbsp;&nbsp;
					<button type="button" id="next-step" class="btn btn-success">{vtranslate('LBL_NEXT', 'Vtiger')}&nbsp;<i class="far fa-angle-right"></i></button>&nbsp;&nbsp;
					<button type="submit" class="btn btn-success saveButton" style="display:none">{vtranslate('LBL_SAVE', 'Vtiger')}</button>&nbsp;&nbsp;
					<a type="reset" href="javascript:history.back()" class="cancelLink">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
				</div>
			</div> 
		</div>
	</form>

	<!-- Modal transfer data -->
	<div id="modal-transfer-data" class="modal-dialog modal-lg modal-content hide">
		{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=''}

		<form id="transfer-data">
			<div class="modal-body">
				<div class="row for-transfer-only">
					<div class="row">
						{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_ONLY_HINT_TEXT', $MODULE_NAME)}
					</div>
					<div class="row">
						<div class="col-lg-6">
							{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_ONLY_ENTER_NUMER_OF_CUSTOMERS_TO_TRANSFER', $MODULE_NAME)}:
						</div>
						<div class="col-lg-6">
							<input type="number" name="transfer_number" class="inputElement text-center" value="" data-rule-required="true" data-rule-number="true" data-rule-min="1" style="width:50px" />&nbsp;
							<span class="error-msg redColor"></span>
						</div>
					</div>
					<div class="row">
						{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_ONLY_USER_RECEIVE_TRANSFER', $MODULE_NAME)}:
					</div>
				</div>
				<div class="row transfer-receiver">
					<div class="row">
						<div class="col-lg-6">
							<label>
								<input type="radio" name="transfer_to" value="campaign_user" checked />&nbsp;
								{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TO_CAMPAIGN_USER', $MODULE_NAME)}
							</label>
						</div>
						<div class="col-lg-6 transfer-to-campaign-user-input-wrapper">
							<select class="transfer-to-user campaign-user" data-rule-required="true">
								<option value="">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_SELECT_A_USER', $MODULE_NAME)}</option>
							</select>&nbsp;&nbsp;
							<i class="far fa-warning out-of-quota-warning redColor hide" data-toggle="tooltip"></i>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6">
							<label>
								<input type="radio" name="transfer_to" value="other_user" />&nbsp;
								{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TO_OTHER_USER', $MODULE_NAME)}
							</label>
						</div>
						<div class="col-lg-6 transfer-to-other-user-input-wrapper hide">
							<input type="text"
								class="transfer-to-user other-user" data-rule-required="true"
								data-single-selection="true" data-users-only="true" data-assignable-users-only="true" 
								data-skip-users="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(array_keys($SELECTED_USERS)))}"
							/>
						</div>
					</div>
				</div>
				<div class="row transfer-data-type hide">
					<div class="row">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_DATA_TYPE', $MODULE_NAME)}:</div>
					<div class="row">
						<label>
							<input type="radio" name="transfer_data_type" value="all_customers" />&nbsp;
							{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_ALL_CUSTOMERS', $MODULE_NAME)}
						</label>
					</div>
					<div class="row">
						<label>
							<input type="radio" name="transfer_data_type" value="not_called_customers" checked />&nbsp;
							{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_TRANSFER_NOT_CALLED_CUSTOMERS', $MODULE_NAME)}
						</label>
					</div>
				</div>
				<div class="row redColor">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_WARNING_MSG', $MODULE_NAME)}</div>
			</div>

			<div class="modal-footer">
				<center>
					<button type="submit" class="btn btn-danger btn-submit">{vtranslate('LBL_CONFIRM', 'Vtiger')}</button>
					<a type="reset" class="cancelLink" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
				</center>
			</div>
		</form>
	</div>
{strip}