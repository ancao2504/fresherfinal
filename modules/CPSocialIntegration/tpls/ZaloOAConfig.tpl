{*
	Name: ZaloOAConfig.tpl
	Author: Vu Mai
	Date: 2022-08-02
	Purpose: Render template for Zalo OA Config
*}

{strip}
	<link type="text/css" rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}" />
	<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
	<script src="{vresource_url('resources/UIUtils.js')}"></script>

	<div id="config-page" class="row-fluid">
		<form autocomplete="off" id="config" name="config" data-tab="{$TAB}">
			<div class="box">
				<div class="box-body padding0">
					<div id="config-container">
						<!-- Nav Tabs -->
						<div id="main-tabs-container">
							<ul class="nav nav-tabs tabs">
								<li class="{if $TAB == 'GeneralConfig'}active{/if}"><a data-tab='GeneralConfig' href="index.php?module=CPSocialIntegration&view=ZaloOAConfig&tab=GeneralConfig">{vtranslate('LBL_ZALO_OA_CONFIG_GENERAL_CONFIG', $MODULE_NAME)}</a></li>
								<li class="{if $TAB == 'Connection'}active{/if}"><a data-tab='Connection' href="index.php?module=CPSocialIntegration&view=ZaloOAConfig&tab=Connection">{vtranslate('LBL_ZALO_OA_CONFIG_CONNECTION', $MODULE_NAME)}</a></li>
							</ul>
						</div>

						<div class="tab-content">
							<!-- General Config -->
							{if $TAB == 'GeneralConfig'}
								<div id="general-config" class="box-body tab-pane active">
									<!-- Privilege -->
									<div id="privilege" class="box shadowed">
										<div class="box-header">
											{vtranslate('LBL_ZALO_OA_CONFIG_PRIVILEGE_TITLE', $MODULE_NAME)}
										</div>
										<div class="box-body">
											<div class="row form-group">
												<div class="fieldLabel label-align-top col-md-6">
													{vtranslate('LBL_ZALO_OA_CONFIG_ROLES_ALLOWED_SEND_MESSAGE', $MODULE_NAME)}<span class="redColor"> *</span>
												</div>
												<div class="fieldValue col-md-6 paddingleft0">
													<select name="general[roles_allowed_send_messages][]" class="inputElement select2" multiple="true" data-rule-required="true" placeholder="{vtranslate('LBL_ZALO_OA_CONFIG_ROLES_PLACEHOLDER', $MODULE_NAME)}">
														{foreach from=$ROLE_LIST key=ROLE_ID item=ROLE}
															<option value="{$ROLE_ID}" {if in_array($ROLE_ID, $CONFIG.general.roles_allowed_send_messages)}selected{/if}>{$ROLE->get('rolename')}</option>
														{/foreach}
													</select>
												</div>
											</div>
											<div class="row form-group">
												<div class="fieldLabel label-align-top col-md-6">
													{vtranslate('LBL_ZALO_OA_CONFIG_ROLES_ALLOWED_SEND_BROADCAST', $MODULE_NAME)}<span class="redColor"> *</span>
												</div>
												<div class="fieldValue col-md-6 paddingleft0">
													<select name="general[roles_allowed_send_broadcast][]" class="inputElement select2" multiple="true" data-rule-required="true" placeholder="{vtranslate('LBL_ZALO_OA_CONFIG_ROLES_PLACEHOLDER', $MODULE_NAME)}">
														{foreach from=$ROLE_LIST key=ROLE_ID item=ROLE}
															<option value="{$ROLE_ID}" {if in_array($ROLE_ID, $CONFIG.general.roles_allowed_send_broadcast)}selected{/if}>{$ROLE->get('rolename')}</option>
														{/foreach}
													</select>
												</div>
											</div>
										</div>
									</div>

									<!-- Chat Distribution -->
									<div id="chat-distribution" class="box shadowed">
										<div class="box-header">
											{vtranslate('LBL_ZALO_OA_CONFIG_CHATS_DISTRIBUTION_TITLE', $MODULE_NAME)}&nbsp;&nbsp;
											<span class="info-tooltip" data-toggle="tooltip" title="{vtranslate('LBL_ZALO_OA_CONFIG_CHATS_DISTRIBUTION_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
										</div>
										<div class="row box-body">
											<div class="col-md-6">
												<div class="row form-group">
													<div class="fieldLabel col-md-12 text-left">
														{vtranslate('LBL_ZALO_OA_CONFIG_CHAT_DISTRIBUTION_USERS', $MODULE_NAME)}<span class="redColor"> *</span>
													</div>
													<div class="fieldValue col-md-12">
														<input type="text" id="chat-distribution-users" class="inputElement" 
															name="general[chat_distribution][users]" 
															data-assignableUsersOnly="true" 
															data-rule-required="true"
															{if !empty($CONFIG.general.chat_distribution.users)}
																{assign var='CHAT_AGENTS' value=Vtiger_Owner_UIType::getCurrentOwners($CONFIG.general.chat_distribution.users)} 
																data-selected-tags="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($CHAT_AGENTS))}"
															{/if}
														/>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="row form-group">
													<div class="fieldLabel col-md-12 text-left">
														{vtranslate('LBL_ZALO_OA_CONFIG_CHAT_DISTRIBUTION_TYPE', $MODULE_NAME)}<span class="redColor"> *</span>
													</div>
													<div class="fieldValue col-md-12">
														<div class="distribution-type-item">
															<input type="radio"
																name="general[chat_distribution][method]"
																class="inputElement"
																value="equally_for_selected_users"
																data-rule-required="true"
																{if $CONFIG.general.chat_distribution.method == 'equally_for_selected_users' || empty($CONFIG.general.chat_distribution.method)}checked{/if}
															/>
															<span>{vtranslate('LBL_ZALO_OA_CONFIG_CHAT_DISTRIBUTED_EQUALLY_FOR_SELECTED_USERS', $MODULE_NAME)}</span>
														</div>
														<div class="distribution-type-item">
															<input type="radio"
																name="general[chat_distribution][method]"
																class="inputElement"
																value="equally_for_online_users"
																data-rule-required="true"
																{if $CONFIG.general.chat_distribution.method == 'equally_for_online_users'}checked{/if}
															/>
															<span>{vtranslate('LBL_ZALO_OA_CONFIG_CHAT_DISTRIBUTED_EQUALLY_FOR_ONLINE_USERS_ONLY', $MODULE_NAME)}</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							{/if}

							<!-- Connection -->
							{if $TAB == 'Connection'}
								<div id="connection" class="box-body tab-pane active">
									<div class="box">
										<div class="box-header text-right">
											<div class="action">
												{if !empty($CONFIG.credentials)}
													<button type="button" id="edit-zalo-credentials" class="btn btn-primary" onclick="javascript:void(0)">
														<i class="far fa-pen"></i>
														{vtranslate('LBL_ZALO_OA_CONFIG_EDIT_ZALO_CREDENTIALS_BTN', $MODULE_NAME)}
													</button>
												{/if}
											</div>
											<div class="instruction">
												<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/mang-xa-hoi/zalo/huong-dan-dau-noi">{vtranslate('LBL_CONFIGURATION_INSTRUCTION', 'Settings:Vtiger')}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
											</div>
										</div>
										<div class="box-body {if empty($ZALO_OA_LIST)}flex-center-center empty-list{/if}">
											<input type="hidden" id="credentials" data-credentials="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($CONFIG.credentials))}" />

											{if empty($ZALO_OA_LIST)}
												<div id="logo" class="text-center">
													<img src="modules/CPSocialIntegration/resources/image/logo-zalo.png" />
												</div>
												<span>{vtranslate('LBL_ZALO_OA_CONFIG_NO_ZALO_OA_CONNECTED', $MODULE_NAME)}</span>
												<button type="button" id="connect-zalo-oa" class="btn btn-primary">{vtranslate('LBL_ZALO_OA_CONFIG_CONNECT_ZALO_OA_BTN', $MODULE_NAME)}</button>
											{else}
												<div id="oa-list" class="row">
													{foreach key=KEY item=OA_INFO from=$ZALO_OA_LIST}
														<div class="col-md-4 oa-container" 
															data-oa-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($OA_INFO))}"
															data-request-info-msg="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($REQUEST_INFO_CONFIG[$OA_INFO.id]))}"
														>
															<div class="oa box shadowed">
																<div class="oa-logo">
																	<img src="{if $OA_INFO.token_status == 'active'}{$OA_INFO.avatar}{else}resources/images/zalo.png{/if}" />
																</div>
																<div class="oa-info">
																	<div class="oa-name">
																		<h5>{if $OA_INFO.token_status == 'active'}{$OA_INFO.name}{else}{$OA_INFO.id}{/if}</h5>
																		{if $OA_INFO.error_msg}
																			<i class="far fa-triangle-exclamation" data-toggle="tooltip" data-tippy-content="{$OA_INFO.error_msg}"></i>
																		{/if}
																	</div>
																	<div class="oa-description">
																		<div class="description">
																			<p>{vtranslate('LBL_ZALO_OA_CONFIG_OA_FOLLOWERS_COUNT', $MODULE_NAME)}: <strong>{if $OA_INFO.token_status == 'active'}{formatNumberToUser($OA_INFO.followers_count)}{else}0{/if}</strong></p>
																			<p>{vtranslate('LBL_ZALO_OA_CONFIG_OA_IS_ZALO_SHOP', $MODULE_NAME)}: 
																				<input type="checkbox" name="oa_is_shop" class="is-zalo-shop" {if $OA_INFO.is_shop}checked{/if}>
																			</p>
																		</div>
																	</div>
																	<div class="action align-item-center">
																		{if $OA_INFO.token_status == 'active'}
																			<div id="token-status" class="active">Token Active</div>
																		{else}
																			<div id="token-status" class="expried">Token Expired</div>
																		{/if}
																		<div class="marginleft-auto align-item-center">
																			<input type="checkbox" class="bootstrap-switch toggle-enable-oa" {if $OA_INFO.enabled}checked{/if}>
																			<button type="button" class="btn btn-outline-danger" onclick="app.controller().removeZaloOA(this)" title="Xóa"><i class="far fa-trash-alt"></i></button>
																			<div class="more dropdown">
																				<div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
																					<i class="fa-solid fa-ellipsis-vertical"></i>
																				</div>
																				<ul class="dropdown-menu">
																					<li>
																						<a onclick="app.controller().syncZaloFollowerIds(this)">Lấy IDs người quan tâm</a>
																					</li>
																					<li>
																						<a onclick="app.controller().showConfigRequestInfoMessageModal(this)">Cấu hình tin nhắn yêu cầu cung cấp thông tin</a>
																					</li>
																				</ul>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													{/foreach}	
												</div>
												<button id="add-zalo-oa" type="button" class="btn btn-primary " onclick="javascript:void(0)">
													<i class="far fa-plus" aria-hidden="true"></i>
													{vtranslate('LBL_ZALO_OA_CONFIG_ADD_ZALO_OA_BTN', $MODULE_NAME)}
												</button>
											{/if}
										</div>
									</div>
								</div>
							{/if}
						</div>
					</div>
				</div>
			</div>

			<!-- Footer -->
			<div id="config-footer" class="modal-overlay-footer clearfix {if $TAB == 'Connection'}hide{/if}">
				<div class="row clear-fix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						<button type="submit" class="btn btn-success">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>&nbsp;
						<a class="btn btn-default btn-outline" onclick="history.back()">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
					</div>
				</div> 
			</div>
		</form>
	</div>

	<!-- Credentials Modal -->
	<div class="modal-dialog modal-md modal-content modal-credentials hide">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONNECT_ZALO_OA_TITLE', $MODULE_NAME)}

		<form id="credentials" class="form-horizontal">
			<div class="form-content fancyScrollbar padding20">
				<span class="modal-hint-text col-lg-12">{vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONNECT_ZALO_OA_HINT_TEXT', $MODULE_NAME)}</span>
				<div class="row form-group">
					<div class="fieldLabel text-right col-md-4">
						Zalo App ID<span class="redColor">*</span>
					</div>
					<div class="fieldValue col-md-6 paddingleft0">
						<input type="text" name="zalo_app_id" value="" data-rule-required="true" />
					</div>
				</div>
				<div class="row form-group">
					<div class="fieldLabel text-right col-md-4">
						Secret Key<span class="redColor">*</span>
					</div>
					<div class="fieldValue col-md-6 paddingleft0">
						<input type="password" name="secret_key" value="" data-rule-required="true" />
					</div>
				</div>
			</div>

			{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
		</form>
	</div>

	<!-- Message Config Modal  -->
	<div class="modal-dialog modal-md modal-content modal-config-request-info-message hide">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONFIG_REQUEST_INFO_MESSAGE_TITLE', $MODULE_NAME)}

		<form id="config-request-info-message" class="form-horizontal">
			<div class="form-content fancyScrollbar padding20">
				<span class="modal-hint-text col-lg-12"></span>
				<div class="row form-group">
					<div class="fieldLabel text-right col-md-3">
						{vtranslate('LBL_SUBJECT', 'Vtiger')}&nbsp;
						<span class="redColor">*</span>
					</div>
					<div class="fieldValue col-md-9 paddingleft0">
						<input type="text" name="title" value="" class="inputElement" data-rule-required="true" />
					</div>
				</div>
				<div class="row form-group">
					<div class="fieldLabel text-right label-align-top col-md-3">
						{vtranslate('LBL_NOTEPAD_CONTENT', 'Vtiger')}&nbsp;
						<span class="redColor">*</span>
					</div>
					<div class="fieldValue col-md-9 paddingleft0">
						<textarea name="message" value="" class="inputElement" data-rule-required="true" placeholder="{vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONFIG_REQUEST_INFO_MESSAGE_CONTENT_PLACEHOLDER', $MODULE_NAME)}"></textarea>
					</div>
				</div>
				<div class="row form-group">
					<div class="fieldLabel text-right col-md-3">
						{vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONFIG_REQUEST_INFO_MESSAGE_IMAGE_URL_LABEL', $MODULE_NAME)}&nbsp;
						<span class="redColor">*</span>
					</div>
					<div class="fieldValue col-md-9 paddingleft0">
						<input type="url" name="image_url" value="" class="inputElement" data-rule-required="true" placeholder="{vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONFIG_REQUEST_INFO_MESSAGE_IMAGE_URL_PLACEHOLDER', $MODULE_NAME)}" />
						<span class="display-block">{vtranslate('LBL_ZALO_OA_CONFIG_MODAL_CONFIG_REQUEST_INFO_MESSAGE_IMAGE_URL_HINT_TEXT', $MODULE_NAME)}<span>
					</div>
				</div>
			</div>

			{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}