{* Added by Hieu Nguyen on 2022-10-24 *}
{* Modified by Vu Mai on 2022 on 2022-12-08 to restyle according to mockup *}

{strip}
	<link rel="stylesheet" href="{vresource_url('modules/Campaigns/resources/TelesalesCampaignForm.css')}" />
	<link rel="stylesheet" href="{vresource_url('modules/Campaigns/resources/NewTelesalesCampaignForm.css')}" />
	<script src="{vresource_url('modules/Campaigns/resources/TelesalesCampaignUtils.js')}" async defer></script>
	<script src="{vresource_url('modules/Campaigns/resources/NewTelesalesCampaignForm.js')}" async defer></script>

	<div id="form-content">
		<!-- No step 1 as it is already the main form blocks -->

		<!-- Select MKT Lists -->
		<div class="step step2" style="display:none">
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
								<th style="width:250px">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_MKT_LIST_NAME', $MODULE_NAME)}</th>
								<th style="width:250px">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DESCRIPTION', $MODULE_NAME)}</th>
								<th style="width:150px">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_STATUS', $MODULE_NAME)}</th>
								<th style="width:150px" class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
								<th style="width:75px"></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>

					<div id="data-statistics"></div>
				</div>
			</div>
		</div>

		<!-- Select Users -->
		<div class="step step3" style="display:none">
			<div class="box shadowed">
				<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS', $MODULE_NAME)}</div>
				<div class="box-body">
					<div class="form-group">
						<div class="col-lg-3 text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_USERS_TELESALES_USERS', $MODULE_NAME)}:</div>
						<div class="col-lg-9">
							<input type="text" name="users" class="users-selector" data-users-only="true" data-assignable-users-only="true" style="width:100%" />
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Distribution Options -->
		<div class="step step4" style="display:none">
			<div class="box shadowed">
				<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA', $MODULE_NAME)}</div>
				<div class="box-body">
					<div class="form-group">
						<div class="col-lg-12">
							<label class="align-item-center">
								<input type="checkbox" name="apply_quota" />&nbsp;
								<span class="ml-2">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_APPLY_QUOTA_CHECKBOX', $MODULE_NAME)}</span>&nbsp;
								<span class="info-tooltip ml-2 mt-0" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_APPLY_QUOTA_CHECKBOX_TOOLTIP', $MODULE_NAME)}">
									<i class="far fa-info-circle"></i>
								</span>
							</label>
						</div>
						<div class="col-lg-12 quota-value" style="display:none">
							{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_QUOTA_LIMIT', $MODULE_NAME)}:&nbsp;
							<input type="number" name="quota_limit" class="inputElement ml-3 text-center" data-rule-required="true" data-rule-number="true" data-rule-min="1" style="width:50px" />
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-group">
						<div class="col-lg-12">
							{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS', $MODULE_NAME)}:&nbsp;
							<span id="distributable-count" class="bold ml-2"></span>&nbsp;
							<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS_TOOLTIP', $MODULE_NAME)}">
								<i class="far fa-info-circle"></i>
							</span>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-group">
						<div class="col-lg-2">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTION_METHOD', $MODULE_NAME)}:</div>
						<div class="col-lg-10">
							<label class="mr-3"><input type="radio" name="distribution_method" value="auto" checked />&nbsp;{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTION_METHOD_AUTO', $MODULE_NAME)}</label>
							<label class="ml-3"><input type="radio" name="distribution_method" value="manual" />&nbsp;{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTION_METHOD_MANUAL', $MODULE_NAME)}</label>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-group for-auto">
						<div class="col-lg-2 text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY', $MODULE_NAME)}:</div>
						<div class="col-lg-10">
							<label>
								<input type="radio" name="auto_distribution_priority" value="none" checked />&nbsp;
								{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_NONE', $MODULE_NAME)}&nbsp;
								<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_NONE_TOOLTIP', $MODULE_NAME)}">
									<i class="far fa-info-circle"></i>
								</span>
							</label>
							<br/>
							<label>
								<input type="radio" name="auto_distribution_priority" value="user_currently_assigned_to_customer" />&nbsp;
								{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_KEEP_ASSIGNED_USER', $MODULE_NAME)}&nbsp;
								<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_KEEP_ASSIGNED_USER_TOOLTIP', $MODULE_NAME)}">
									<i class="far fa-info-circle"></i>
								</span>
							</label>
							<br/>
							<label>
								<input type="radio" name="auto_distribution_priority" value="user_has_latest_telesales_call_to_customer" />&nbsp;
								{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_LAST_CALLED_USER', $MODULE_NAME)}&nbsp;
								<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTION_PRIORITY_OPTION_LAST_CALLED_USER_TOOLTIP', $MODULE_NAME)}">
									<i class="far fa-info-circle"></i>
								</span>
							</label>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-group for-manual" style="display:none">
						<div class="col-lg-12">
							{* Modifed by Vu Mai on 2023-03-02 to add column customer percent *}
							<table id="tbl-manual-distribution" class="table table-border-custom" style="width:90%">
								<thead>
									<tr>
										<th style="width:40%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_EMPLOYEE_NAME', $MODULE_NAME)}</th>
										<th style="width:30%" class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DISTRIBUTION_CUSTOMERS_PERCENT', $MODULE_NAME)}</th>
										<th style="width:30%" class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
									</tr>
								</thead>
								<tbody></tbody>
								<tfoot>
									<tr>
										<th class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL', $MODULE_NAME)}</th>
										<th></th>
										<th class="text-right">
											<span id="total-error-msg" class="error-msg"></span>&nbsp;&nbsp;&nbsp;
											<span title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTED_PER_TOTAL_DISTRIBUTABLE_TOOLTIP', $MODULE_NAME)}" data-toggle="tooltip">
												<span id="total-distributed">0</span>/<span id="total-customers"></span>
											</span>
										</th>
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
								<input type="checkbox" name="auto_distribute_new_customers_added_later" />&nbsp;
								<span class="ml-2">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTE_NEW_CUSTOMERS_CHECKBOX', $MODULE_NAME)}</span>&nbsp;
								<span class="info-tooltip ml-2 mt-0" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_AUTO_DISTRIBUTE_NEW_CUSTOMERS_CHECKBOX_TOOLTIP', $MODULE_NAME)}">
									<i class="far fa-info-circle"></i>
								</span>
							</label>
						</div>
						<div class="clearfix"></div>
					</div> -->
				</div>
			</div>
		</div>

		<!-- Estimation -->
		<div class="step step5" style="display:none">
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
{strip}