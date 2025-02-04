{*
	Name: CallCenterConfig.tpl
	Author: Phu Vo
	Date: 2021.07.21
	Refactored: Vu Mai on 2022-07-18
	Purpose: Render Callcenter Integration Config
*}

{strip}
	<link type="text/css" rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}" />
	<link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/DynamicTable/DynamicTable.css')}">
	<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
	<script src="{vresource_url('resources/libraries/DynamicTable/DynamicTable.js')}"></script>

	<div id="config-page" class="row-fluid padding20">
		<form autocomplete="off" id="config" name="config" data-mode="{$MODE}">
			<div class="box shadowed">
				<div class="box-header">
					<div class="header-title">
						{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG', $MODULE_NAME)}
					</div>	
					<div class="marginleft-auto {if $TAB == 'Connection' && $MODE == 'ShowDetail'}hide{/if}">
						<input type="checkbox" name="switch_button" class="bootstrap-switch" {if $CALLCENTER_CONFIG.enable}checked{/if}>
					</div>
				</div>
				<div class="box-body padding0">
					<div id="inactive-config-hint-text" class="box-body {if $CALLCENTER_CONFIG.enable}hide{/if}">{vtranslate('LBL_VENDOR_INTEGRATION_CONFIG_DISABLED_MSG', $MODULE_NAME)}</div>
					<div id="config-container" class="{if !$CALLCENTER_CONFIG.enable}hide{/if}">
						<!-- Nav Tabs -->
						<div id="main-tabs-container">
							<ul class="nav nav-tabs tabs">
								<li class="{if $TAB == 'GeneralConfig'}active{/if}"><a data-tab='GeneralConfig' href="index.php?module=Vtiger&parent=Settings&view=CallCenterConfig&tab=GeneralConfig">{vtranslate('LBL_VENDOR_INTEGRATION_GENERAL_CONFIG', $MODULE_NAME)}</a></li>
								<li class="{if $TAB == 'Connection'}active{/if}"><a data-tab='Connection' href="index.php?module=Vtiger&parent=Settings&view=CallCenterConfig&tab=Connection&mode=ShowList">{vtranslate('LBL_VENDOR_INTEGRATION_CONNECTION', $MODULE_NAME)}</a></li>
							</ul>
						</div>

						<div class="tab-content">
							<!-- General Config -->
							{if $TAB == 'GeneralConfig'}
								<div id="general-config" class="box-body tab-pane active">
									<div id="vendor-detail">
										<!-- General Setting -->
										<div id="general-settings" class="box shadowed">
											<div class="box-header">
												<div class="header-title">
													{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_GENERAL_CONFIGURATION', $MODULE_NAME)}
												</div>
												<div class="instruction pull-right">
													<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/call-center/cau-hinh-chung">{vtranslate('LBL_CONFIGURATION_INSTRUCTION', $MODULE_NAME)}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
												</div>
											</div>
											<div class="box-body">
												<div class="row general-settings">
													<div class="row form-group flex-center-center">
														<div class="fieldLabel col-md-6">{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_OUTBOUND_PREFIX', $MODULE_NAME)}:</div>
														<div class="fieldValue align-item-center col-md-6">
															<input type="number" class="inputElement" name="general[outbound_prefix]" value="{$CONFIG.outbound_prefix}" />&nbsp;&nbsp;<i class="far fa-info-circle info-tooltip" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_OUTBOUND_PREFIX_TOOLTIP', $MODULE_NAME)}"></i>
														</div>
													</div>
													<div class="row form-group flex-center-center">
														<div class="fieldLabel col-md-6">{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_NEW_CUSTOMER_MISSED_CALL_ALERT', $MODULE_NAME)}:</div>
														<div class="fieldValue col-md-6">
															<div class="user-selector-wrapper">
																<input type="text" autocomplete="off" class="inputElement user-selector-input" style="width: 100%"
																	placeholder="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_MISSED_CALL_ALERT_RECEIVER_PLACEHOLDER', $MODULE_NAME)}"
																	name="general[new_customer_missed_call_alert]" 
																	data-value="{$CONFIG.new_customer_missed_call_alert}"
																	data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG.new_customer_missed_call_alert))}'
																	data-user-only="true"
																/>
																<button type="button" class="btn-clear-user far fa-times-circle"></button>
															</div>
														</div>
													</div>
													<div class="row form-group">
														<div class="fieldLabel label-align-top col-md-6">{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_EXISTING_CUSTOMER_MISSED_CALL_ALERT_NO_MAIN_OWNER', $MODULE_NAME)}:</div>
														<div class="fieldValue col-md-6">
															<select class="inputElement select2" name="general[existing_customer_missed_call_alert_no_main_owner]" style="width: 100%">
																<option>{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_MISSED_CALL_ALERT_RECEIVER_PLACEHOLDER', $MODULE_NAME)}</option>
																<option value="group_members" {if $CONFIG.existing_customer_missed_call_alert_no_main_owner eq 'group_members'}selected{/if}>{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_GROUP_MEMBERS', $MODULE_NAME)}</option>
																<option value="specific_user" {if $CONFIG.existing_customer_missed_call_alert_no_main_owner eq 'specific_user'}selected{/if}>{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_SPECIFIC_USER', $MODULE_NAME)}</option>
															</select>
															<div class="fieldValue no-main-owner-specific-owner user-selector-wrapper" style="display: {if $CONFIG.existing_customer_missed_call_alert_no_main_owner neq 'specific_user'}none{else}flex{/if}">
																<input type="text" autocomplete="off" class="inputElement user-selector-input" style="width: 100%"
																	placeholder="Chọn một người dùng"
																	name="general[missed_call_alert_no_main_owner_specific_user]" 
																	data-user-only="true"
																	data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG.missed_call_alert_no_main_owner_specific_user))}'
																/>
																<button type="button" class="btn-clear-user far fa-times-circle"></button>
															</div>
														</div>
													</div>
													<div class="row form-group flex-center-center">
														<div class="fieldLabel col-md-6">{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_MISSED_CALL_EMAIL_TEMPLATE', $MODULE_NAME)}:</div>
														<div class="fieldValue col-md-6">
															<div class="entity-selector-wrapper" data-module="EmailTemplates">
																<input type="hidden" class="entity-selector-input" name="general[missed_call_alert_email_template]" value="{$CONFIG.missed_call_alert_email_template}" />
																<input class="inputElement entity-selector-display disabled" disabled {if !empty($CONFIG.missed_call_alert_email_template)}value="{$EMAIL_TEMPLATE_RECORD->get('subject')}"{/if}/>
																<button type="button" class="btn-entity-deselect cursorPointer"><i class="far fa-times-circle"></i></button>
																<a class="btn-entity-preview cursorPointer" target="_blank" href="index.php?module=EmailTemplates&view=Detail&record={$CONFIG.missed_call_alert_email_template}"><i class="far fa-eye" aria-hidden="true"></i></a>
																<button type="button" class="btn-entity-select cursorPointer"><i class="far fa-search"></i></button>
															</div>
														</div>
													</div>
													<div class="row form-group">
														<div class="fieldLabel label-align-top col-md-6">{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_ACCESS_EXTERNAL_REPORT_ROLES', $MODULE_NAME)}:</div>
														<div class="fieldValue flex col-md-6">
															<select class="inputElement select2" name="general[external_report_allowed_roles]" multiple="true"
																data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG.external_report_allowed_roles))}'
															>
																{foreach from=$ROLE_LIST item=ROLE}
																	{assign var=roleid value=$ROLE->get('roleid')}
																	{assign var=rolename value=$ROLE->get('rolename')}
																	<option value="{$roleid}" {if in_array($roleid, $CONFIG.external_report_allowed_roles)}selected{/if}>{$rolename}</option>
																{/foreach}
															<select>&nbsp;&nbsp;
															<div class="icon-align-top">
																<i class="far fa-info-circle info-tooltip" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_ACCESS_EXTERNAL_REPORT_ROLES_TOOLTIP', $MODULE_NAME)}"></i>
															</div>
														</div>
													</div>
													<div class="row form-group flex-center-center">
														<div class="fieldLabel col-md-6">AMI Version:</div>
														<div class="fieldValue align-item-center col-md-6">
															<input type="text" class="inputElement" name="general[ami_version]" value="{$CALLCENTER_CONFIG.ami_version}" />&nbsp;&nbsp;<i class="far fa-info-circle info-tooltip" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_AMI_VERSION_TOOLTIP', $MODULE_NAME)}"></i>
														</div>
													</div>	
												</div>
											</div>
										</div>

										<!-- Callcenter Bridge -->
										<div id="callcenter-bridge" class="box shadowed">
											<div class="box-header">
												<div class="header-title">{vtranslate('LBL_CALLCENTER_USER_CONFIG_CALLCENTER_BRIDGE', $MODULE_NAME)}</div>&nbsp;&nbsp;
												<span class="info-tooltip" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_CALLCENTER_BRIDGE_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
											</div>
											<div class="box-body">
												<div class="row fields-container">
													<div class="col-md-6">
														<div class="form-group padding0 align-item-center">
															<div class="col-lg-4 fieldLabel">Server Name/IP&nbsp;<span class="redColor">*</span></div>
															<div class="col-lg-8 fieldValue">
																<input type="text" name="bridge[server_name]" value="{$CALLCENTER_CONFIG.bridge.server_name}" class="inputElement" data-rule-required="true" />
															</div>
														</div>
														<div class="form-group padding0 align-item-center">
															<div class="col-lg-4 fieldLabel">Default Port&nbsp;<span class="redColor">*</span></div>
															<div class="col-lg-8 fieldValue">
																<div class="port inline-block">
																	<input type="number" name="bridge[default_port]" value="{$CALLCENTER_CONFIG.bridge.server_port}" class="inputElement" data-rule-required="true" />
																</div>
																<div class="fieldLabel padding0 ssl inline-block">SSL</div>
																<div class="checkbox ssl inline-block">
																	<input type="checkbox" name="bridge[default_port_ssl]" {if $CALLCENTER_CONFIG.bridge.server_ssl}checked{/if} class="form-control">
																</div>
															</div>
														</div>
														<div class="form-group padding0 align-item-center">
															<div class="col-lg-4 fieldLabel">Backend Port</div>
															<div class="col-lg-8 fieldValue">
																<div class="port inline-block">
																	<input type="number" name="bridge[backend_port]" value="{$CALLCENTER_CONFIG.bridge.server_backend_port}" class="inputElement" />
																</div>
																<div class="fieldLabel padding0 ssl inline-block">SSL</div>
																<div class="checkbox ssl inline-block">
																	<input type="checkbox" name="bridge[backend_port_ssl]" {if $CALLCENTER_CONFIG.bridge.server_backend_ssl}checked{/if} class="form-control">
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group padding0 align-item-center">
															<div class="col-lg-4 fieldLabel">Access Domain&nbsp;<span class="redColor">*</span></div>
															<div class="col-lg-8 fieldValue">
																<input type="text" name="bridge[access_domain]" value="{$CALLCENTER_CONFIG.bridge.access_domain}" class="inputElement" data-rule-required="true" />
															</div>
														</div>
														<div class="form-group padding0 align-item-center">
															<div class="col-lg-4 fieldLabel">Private Key&nbsp;<span class="redColor">*</span></div>
															<div class="col-lg-8 fieldValue">
																<input type="password" name="bridge[private_key]" value="{$CALLCENTER_CONFIG.bridge.private_key}" class="inputElement" data-rule-required="true" />
															</div>
														</div>
													</div>
												</div>	
											</div>
										</div>

										<!-- Inbound Call -->
										<div id="inbound-call" class="box shadowed">
											<div class="box-header">
												{vtranslate('LBL_CALLCENTER_USER_CONFIG_INBOUND_CALL', $MODULE_NAME)}&nbsp;&nbsp;
												<span class="info-tooltip" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_INBOUND_CALL_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
											</div>
											<div class="config-table-container box-body">
												<table id="tbl-inbound-call" class="table dynamicTable">
													<thead>
														<tr>
															<th style="width: 45%;">{vtranslate('LBL_CALLCENTER_USER_CONFIG_INBOUND_HOTLINE', $MODULE_NAME)}</th>
															<th style="width: 45%;">{vtranslate('LBL_CALLCENTER_USER_CONFIG_INBOUND_ROLES_AND_SUBORDINATES', $MODULE_NAME)}</th>
															<th style="width: 10%;" class="text-center">{vtranslate('LBL_ACTIONS', $MODULE_NAME)}</th>
														</tr>
													</thead>
													<tbody>
														{if $CALLCENTER_CONFIG.inbound_routing}
															{foreach key=HOTLINE item=INBOUND_ROLE_ID from=$CALLCENTER_CONFIG.inbound_routing name=INBOUND_ROUTING_LOOP}
																{include file='modules/Settings/Vtiger/tpls/CallCenterConfigInboundRoutingRowTemplate.tpl' HOTLINE_NUMBER=$HOTLINE SELECTED_ROLE_ID=$INBOUND_ROLE_ID}
															{/foreach}
														{/if}	
													</tbody>
													<tfoot class="template" style="display:none;">
														{include file='modules/Settings/Vtiger/tpls/CallCenterConfigInboundRoutingRowTemplate.tpl'}
													</tfoot>
													<tfoot>
														<tr>
															<td colspan="2" class="btn-container">
																<button  type="button" class="btn btn-link btnAddRow">
																	<i class="far fa-plus" aria-hidden="true"></i>
																	{vtranslate('LBL_CALLCENTER_USER_CONFIG_BTN_ADD_HOTLINE', $MODULE_NAME)}
																</button>
															</td>
														</tr>
													</tfoot>
												</table>
											</div>
										</div>

										<!-- Outbound Call -->
										<div id="outbound-call" class="box shadowed">
											<div class="box-header">
												{vtranslate('LBL_CALLCENTER_USER_CONFIG_OUTBOUND_CALL', $MODULE_NAME)}&nbsp;&nbsp;
												<span class="info-tooltip" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_OUTBOUND_CALL_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
											</div>
											<div class="config-table-container box-body">
												<table id="tbl-outbound-call" class="table dynamicTable">
													<thead>
														<tr>
															<th style="width: 45%;">{vtranslate('LBL_CALLCENTER_USER_CONFIG_OUTBOUND_HOTLINE', $MODULE_NAME)}</th>
															<th style="width: 45%;">{vtranslate('LBL_CALLCENTER_USER_CONFIG_OUTBOUND_ROLES_APPLY', $MODULE_NAME)}</th>
															<th style="width: 10%;" class="text-center">{vtranslate('LBL_ACTIONS', $MODULE_NAME)}</th>
														</tr>
													</thead>
													<tbody>
														{if $CALLCENTER_CONFIG.outbound_routing}
															{foreach key=HOTLINE item=OUTBOUND_ROLE_IDS from=$CALLCENTER_CONFIG.outbound_routing}
																{include file='modules/Settings/Vtiger/tpls/CallCenterConfigOutboundRoutingRowTemplate.tpl' HOTLINE_NUMBER=$HOTLINE SELECTED_ROLE_IDS=$OUTBOUND_ROLE_IDS}
															{/foreach}
														{/if}	
													</tbody>
													<tfoot class="template" style="display:none;">
														{include file='modules/Settings/Vtiger/tpls/CallCenterConfigOutboundRoutingRowTemplate.tpl'}
													</tfoot>
													<tfoot>
														<tr>
															<td colspan="2" class="btn-container">
																<button  type="button" class="btn btn-link btnAddRow">
																	<i class="far fa-plus" aria-hidden="true"></i>
																	{vtranslate('LBL_CALLCENTER_USER_CONFIG_BTN_ADD_HOTLINE', $MODULE_NAME)}
																</button>
															</td>
														</tr>
													</tfoot>
												</table>
											</div>

											<div id="users-allowed-all-hotlines" class="box-body">
												<span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_USER_ALLOW_CALL_ALL_HOTLINE_LABEL', $MODULE_NAME)}</span>
												<div class="fieldValue">
													<input type="text" autocomplete="off" class="inputElement" style="width: 100%" 
														data-fieldname="click2call_users_can_use_all_hotlines" 
														data-name="click2call_users_can_use_all_hotlines" 
														name="click2call_users_can_use_all_hotlines" 
														data-user-only="true"
														{if $CALLCENTER_CONFIG.click2call_users_can_use_all_hotlines}
															{assign var="SELECTED_USERS" value=join(',', $CALLCENTER_CONFIG.click2call_users_can_use_all_hotlines)}
															data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getSelectedOwnersFromOwnersString($SELECTED_USERS))}'
														{/if}
													/>
												</div>
											</div>
										</div>
									</div>
								</div>
							{/if}	

							<!-- Connection -->
							{if $TAB == 'Connection'}
								<div id="connection" class="box-body tab-pane active">
										<!-- CallCenter List -->
										{if $MODE == 'ShowList'}
											<div id="vendor-list-container">
												<div class="box shadowed">
													<div class="box-body">
														<div id="hint-text">{vtranslate('LBL_CALLCENTER_INTEGRATION_HINT_TEXT', $MODULE_NAME)}</div>
														<div id="providers-nav">
															<ul class="nav nav-tabs tabs">
																<li class="active"><a class="align-item-center" data-toggle="tab" data-type="cloud"><i class="fa-solid fa-cloud"></i>&nbsp;&nbsp;&nbsp;{vtranslate('LBL_CALLCENTER_INTEGRATION_CLOUD_PROVIDERS', $MODULE_NAME)}</a></li>
																<li><a class="align-item-center" data-toggle="tab" data-type="physical"><i class="fa-solid fa-phone-office"></i>&nbsp;&nbsp;&nbsp;{vtranslate('LBL_CALLCENTER_INTEGRATION_PHYSICAL_PROVIDERS', $MODULE_NAME)}</a></li>
															</ul>
														</div>
														<div id="vendor-search">
															<input name="search_input" placeholder="{vtranslate('LBL_VENDOR_INTEGRATION_SEARCH_PLACEHOLDER', $MODULE_NAME)}" />
															<i class="far fa-search search-icon"></i>
														</div>
														<div class="tab-content">
															<div id="vendor-list" class="row cloud">
																{foreach key=TYPE item=PROVIDERS from=$PROVIDER_LIST}
																	{foreach key=PROVIDER_NAME item=PROVIDER from=$PROVIDERS}
																		{assign var="PROVIDER_INFO" value=$PROVIDER_INFOS[$PROVIDER_NAME]}
																		{assign var="IS_ACTIVE" value=$PROVIDER_NAME == $ACTIVE_PROVIDER}

																		<div class="col-md-6 vendor-container {$TYPE}">
																			<div class="vendor"
																				data-name="{$PROVIDER_NAME}"
																				data-display-name="{$PROVIDER_INFO.display_name}"
																				data-type="{$TYPE}"
																				{if $IS_ACTIVE}connected="true"{/if}
																				{if $ACTIVE_PROVIDER}{if $IS_ACTIVE}can-config="true"{/if}{else}can-config="true"{/if}
																			>
																				<div class="vendor-logo">
																					<img src="{$PROVIDER_INFO.logo_path}" />
																				</div>
																				
																				<div class="vendor-info">
																					<div class="vendor-name">
																						<h5>{$PROVIDER_INFO.display_name}</h5>
																						<span class="connection-status">
																							{if $IS_ACTIVE}
																								<i>({vtranslate('LBL_VENDOR_INTEGRATION_STATUS_CONNECTED', $MODULE_NAME)})</i>
																							{else}
																								<i>({vtranslate('LBL_VENDOR_INTEGRATION_STATUS_NOT_CONNECTED', $MODULE_NAME)})</i>
																							{/if}
																						</span>
																					</div>
																					<div class="vendor-description">
																						<div class="actions">
																							{if $IS_ACTIVE}
																								<button type="button" class="btn btn-danger btn-disconnect">{vtranslate('LBL_BTN_DISCONNECT', $MODULE_NAME)}</button>
																							{else}
																								<button type="button" class="btn btn-primary btn-connect" {if !empty($ACTIVE_PROVIDER)}disabled{/if}>{vtranslate('LBL_BTN_CONNECT', $MODULE_NAME)}</button>
																							{/if}
																						</div>
																						<div class="description">
																							<p title="{$PROVIDER_INFO[$INTRO_KEY]}">{$PROVIDER_INFO[$INTRO_KEY]}</p>
																						</div>
																						<div class="instruction">
																							<a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_INTEGRATION_INSTRUCTION', $MODULE_NAME)}</a>
																						</div>
																					</div>
																				</div>
																			</div>
																		</div>
																	{/foreach}	
																{/foreach}
															</div>	
														</div>
													</div>
												</div>
											</div>
										{/if}

										<!-- CallCenter Detail -->
										{if $MODE == 'ShowDetail'}
											<div id="vendor-detail">
												<div id="hint-text">{vtranslate('LBL_CALLCENTER_INTEGRATION_PROVIDER_DETAIL_HINT_TEXT', $MODULE_NAME, ['%provider_name' => $PROVIDER_INFO.display_name])}</div>
												<div class="box shadowed">
													<div class="box-header">
														<div class="header-title">
															{vtranslate('LBL_VENDOR_INTEGRATION_CONNECTION_INFO', $MODULE_NAME)}
														</div>
														<div class="instruction pull-right">
															<a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_INTEGRATION_INSTRUCTION', $MODULE_NAME)}</a>
														</div>
													</div>
													<div class="box-body">
														<input type="hidden" name="provider" value="{$PROVIDER_INSTANCE->getGatewayName()}" />
														<input type="hidden" name="provider_name" value="{$PROVIDER_INFO.display_name}" />
														
														<div id="logo" class="text-center">
															<img src="{$PROVIDER_INFO.logo_path}" />
														</div>
														<br/>

														<!-- Config fields -->
														<div id="config-fields" class="box shadowed">
															<div class="box-body">
																{assign var="PARAMETERS" value=$PROVIDER_INSTANCE->getParameters()}

																{foreach from=$PROVIDER_INSTANCE::getSettingFields() key=FIELD_NAME item=FIELD_TYPE}
																	<div class="form-group">
																		<div class="col-lg-4 fieldLabel">{vtranslate($FIELD_NAME, 'Settings:PBXManager')}&nbsp;<span class="redColor">*</span></div>
																		<div class="col-lg-8 fieldValue"><input type="{$FIELD_TYPE}" name="params[{$FIELD_NAME}]" value="{$PARAMETERS[$FIELD_NAME]}" class="inputElement" data-rule-required="true" /></div>
																		<div class="clearFix"></div>
																	</div>
																{/foreach}
															</div>
														</div>
													</div>
												</div>
											</div>	
										{/if}
									</div>
								</div>
							{/if}
						</div>
					</div>
				</div>
			</div>
			<div id="config-footer" class="modal-overlay-footer clearfix {if !$CALLCENTER_CONFIG.enable || $MODE == 'ShowList'}hide{/if}">
				<div class="row clear-fix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						{if $IS_EDIT}<button type="button" id="btn-disconnect" class="btn btn-outline-danger">{vtranslate('LBL_BTN_DISCONNECT', $MODULE_NAME)}</button>&nbsp;{/if}
						<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>&nbsp;
						{if $TAB == 'GeneralConfig' || ($TAB == 'Connection' && $MODE == 'ShowDetail')}<a class="btn btn-default btn-outline" onclick="history.back()">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>{/if}
					</div>
				</div> 
			</div>
		</form>
	</div>
{/strip}