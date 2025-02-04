{*
Name: Config.tpl
Author: Vu Mai
Date: 2022-08-12
Purpose: Render template for Telesales Campaign Config
*}

{strip}
<script src="{vresource_url('resources/CustomColorPicker.js')}"></script>

<div id="config-page" class="row-fluid">
	<form autocomplete="off" id="config" name="config">
		<div class="box shadowed">
			<div class="box-header">
				{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG', $MODULE_NAME)}
			</div>
			<div class="box-body padding0">
				<div id="config-container">
					<!-- Nav Tabs -->
					<div id="main-tabs-container">
						<ul class="nav nav-tabs tabs">
							<li class="active"><a data-toggle="tab"
									href="#general-config">{vtranslate('LBL_CONFIG_EDITOR', 'Settings:Vtiger')}</a></li>
							<li class=""><a data-toggle="tab"
									href="#customer-status-config">{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_STATUS_CONFIG',
									$MODULE_NAME)}</a></li>
						</ul>
					</div>

					<div class="tab-content">
						<div id="general-config" class="box-body tab-pane active">
							<div class="box shadowed">
								<div class="box-header">
									{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_INFO_COLUMN_CONFIG',
									$MODULE_NAME)}
									&nbsp;&nbsp;
									<span class="info-tooltip" data-toggle="tooltip"
										title="{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_INFO_COLUMN_CONFIG_TOOLTIP', $MODULE_NAME)}"><i
											class="far fa-info-circle"></i></span>
								</div>
								<div class="box-body">
									<div class="row form-group">
										<div class="fieldLabel label-align-top col-md-3">
											{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_INFO',
											$MODULE_NAME)}<span class="redColor"> *</span>
										</div>
										<div class="fieldValue col-md-9 paddingleft0 columnsSelectDiv">
											<select name="call_screen[customer_list_columns][]" id="column-field-list"
												class="inputElement select2" multiple data-rule-required="true"
												placeholder="{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_INFO_PLACEHOLDER', $MODULE_NAME)}">
												{foreach key=FIELD_NAME item=FIELD_LABEL from=$CUSTOMER_FIELDS}
												<option value="{$FIELD_NAME}" {if in_array($FIELD_NAME,
													$CONFIG.call_screen.customer_list_columns)}selected{/if}>
													{vtranslate($FIELD_LABEL)}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="customer-status-config" class="box-body tab-pane">
							<!-- Muc dich chien dich -->
							<div class="row form-group">
								<div class="fieldLabel label-align-top col-md-5">
									{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CAMPAIGN_PURPOSE', $MODULE_NAME)}
									Update3e<span class="redColor"> *</span>
								</div>
								<div class="fieldValue col-md-7 paddingleft0">
									<select name="campaign_purpose" class="inputElement select2 campaign-purpose"
										data-current-value="{$CAMPAIGN_PURPOSE}" data-rule-required="true">
										{foreach key=PURPOSE_KEY item=PURPOSE_ITEM from=$CAMPAIGN_PURPOSE_LIST}
										<option value="{$PURPOSE_KEY}">{$PURPOSE_ITEM}</option>
										{/foreach}
									</select>
								</div>
							</div>

							<div id="" class="breadcrumb text-center" data-step="1">
								<ul class="crumbs">
									<li class="step active step1" data-value="1" style="z-index:9">
										<a href="javascript:void(0)">
											<span class="stepNum">1</span>
											<span
												class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_INIT_STATUS',
												$MODULE_NAME)}</span>
										</a>
									</li>
									<li class="step step2" data-value="2" style="z-index:8">
										<a href="javascript:void(0)">
											<span class="stepNum">2</span>
											<span
												class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_UPDATE_STATUS',
												$MODULE_NAME)}</span>
										</a>
									</li>
								</ul>
							</div>

							<div class="panel box shadowed step step1 active">
								<div class="box-header">
									{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_STATUS_IN_CAMPAIGN_CONFIG',
									$MODULE_NAME)} TYTT
									&nbsp;&nbsp;
									<span class="info-tooltip" data-toggle="tooltip"
										title="{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_STATUS_IN_CAMPAIGN_CONFIG_TOOLTIP', $MODULE_NAME)}"><i
											class="far fa-info-circle"></i></span>
								</div>
								<div class="config-table-container box-body text-center">
									<table id="customer-status-table" class="table">
										<thead>
											<tr class="listViewHeaders">
												<th style="width:40%">
													<span>{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_STATUS',
														$MODULE_NAME)}</span>
												</th>
												<th style="width:15%" class="text-center">
													<span>{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_STATUS_IS_NEW',
														$MODULE_NAME)}</span>
												</th>
												<th style="width:15%" class="text-center">
													<span>{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_STATUS_IS_SUCCESS',
														$MODULE_NAME)}</span>
												</th>
												<th style="width:15%" class="text-center">
													<span>{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_STATUS_IS_FAILED',
														$MODULE_NAME)}</span>
												</th>
												<th style="width:15%" class="text-center">
													<span>{vtranslate('LBL_ACTION')}</span>
												</th>
											</tr>
										<tbody id="customer-status-list">
										</tbody>
										<!-- <tfoot id="template" style="display:none">
											<tr class="customer-status-item ui-sortable-handle">
												<td class="textOverflowEllipsis">
													<span class="pull-left"><i
															class="far fa-grip-lines cursorDrag alignMiddle"></i>&nbsp;&nbsp;
														<span class="picklist-color"></span>
													</span>
												</td>
												<td class="fieldValue" class="text-center">
													<input type="radio" class="inputElement"
														name="customer_status_is_new" />
												</td>
												<td class="fieldValue" class="text-center">
													<input type="radio" class="inputElement"
														name="customer_status_is_success" />
												</td>
												<td class="fieldValue" class="text-center">
													<input type="checkbox" class="inputElement"
														name="customer_status_is_failed" />
												</td>
												<td class="text-center">
													<button type="button"
														class="btn btn-outline-primary btn-edit-status"
														onclick="app.controller().showCustomerStatusModal(this)"
														title="Sửa"><i class="far fa-pen"></i></button>
													<button type="button" class="btn btn-outline-danger"
														onclick="app.controller().showDeleteCutomerStatusModal(this)"
														title="Xóa"><i class="far fa-trash-alt"></i></button>
												</td>
											</tr>
										</tfoot> -->
										<tfoot>
											<tr>
												<td colspan="2" class="btn-container">
													<button type="button" class="btn btn-link"
														onclick="app.controller().showCustomerStatusModal(this)">
														<i class="far fa-plus" aria-hidden="true"></i>
														{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_ADD_CUTOMER_STATUS_BTN',
														$MODULE_NAME)}
													</button>
												</td>
											</tr>
										</tfoot>
										</thead>
									</table>
									<button class="btn btn-sm btn-success next-step"
										type="button">{vtranslate('LBL_NEXT' , 'Settings:Vtiger')}&nbsp;<i
											class="fa-regular fa-angle-right"></i></button>
								</div>
							</div>
							<div class="panel box shadowed step step2">
								<div class="box-header">
									{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CALL_RESULT_TO_STATUS_MAPPING',
									$MODULE_NAME)}
									&nbsp;&nbsp;
									<span class="info-tooltip" data-toggle="tooltip"
										title="{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CALL_RESULT_TO_STATUS_MAPPING_TOOLTIP', $MODULE_NAME)}"><i
											class="far fa-info-circle"></i></span>
								</div>
								<div class="config-table-container box-body text-center">
									<div id="call_result_to_status_mapping"></div>
									<button class="btn btn-sm btn-success prev-step" type="button"><i
											class="fa-regular fa-angle-left"></i>&nbsp;{vtranslate('LBL_BACK',
										'Settings:Vtiger')}</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="config-footer" class="modal-overlay-footer clearfix">
			<div class="row clear-fix">
				<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
					<button type="submit" class="btn btn-primary">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>&nbsp;
					<a class="btn btn-default btn-outline" onclick="history.back()">{vtranslate('LBL_CANCEL',
						$MODULE_NAME)}</a>
				</div>
			</div>
		</div>
	</form>
</div>
{/strip}