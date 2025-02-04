{*
	Name: ChatbotIntegrationConfig.tpl
	Author: Phu Vo
	Date: 2020.06.19
	Refactored: Vu Mai on 2022-07-14
	Purpose: Render Chatbot Integration Config
*}

{strip}
	<link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}" />
	<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>

	<div id="config-page" class="row-fluid padding20">
		<form id="settings" data-mode="{$MODE}">

			{* Provider List *}
			{if $MODE == 'ShowList'}
				<div class="box shadowed">
					<div class="box-header">
						<div class="header-title">
							{vtranslate('LBL_CHATBOT_INTEGRATION_CONFIG', $MODULE_NAME)}
						</div>	
						<div class="marginleft-auto">
							<input type="checkbox" name="switch_button" class="bootstrap-switch" {if $CHATBOT_CONFIG.enable}checked{/if}>
						</div>
					</div>
					<div class="box-body">
						<div id="hint-text">
							<div id="active-config-hint-text" class="{if !$CHATBOT_CONFIG.enable}hide{/if}">{vtranslate('LBL_CHATBOT_INTEGRATION_PROVIDER_LIST_HINT_TEXT', $MODULE_NAME, ['%channel' => $CHANNEL_NAME])}</div>
							<div id="inactive-config-hint-text" class="{if $CHATBOT_CONFIG.enable}hide{/if}">{vtranslate('LBL_VENDOR_INTEGRATION_CONFIG_DISABLED_MSG', $MODULE_NAME)}</div>
						</div>
						
						<div id="vendor-list-container" class="box shadowed {if !$CHATBOT_CONFIG.enable}hide{/if}">
							<div class="box-header">
								{vtranslate('LBL_VENDOR_INTEGRATION_CONNECT_VENDOR', $MODULE_NAME)}
							</div>
							<div class="box-body">
								<div id="vendor-search"> 
									<input name="search_input" placeholder="{vtranslate('LBL_CHATBOT_INTEGRATION_SEARCH_PLACEHOLDER', $MODULE_NAME)}" />
									<i class="far fa-search search-icon"></i>
								</div>
								<div id="vendor-list" class="row">
									{foreach key=INDEX item=PROVIDER from=$PROVIDER_LIST}
										{assign var="PROVDER_NAME" value=$PROVIDER->getName()}
										{assign var="PROVIDER_INFO" value=$PROVIDER_INFOS[$PROVDER_NAME]}
										{assign var="IS_ACTIVE" value=$PROVDER_NAME == $ACTIVE_PROVIDER}
										
										<div class="col-md-6 vendor-container">
											<div class="vendor"
												data-name="{$PROVDER_NAME}" 
												data-display-name="{$PROVIDER_INFO.display_name}"
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
									{foreachelse}
										<div id="no-vendor-msg">{vtranslate('LBL_CHATBOT_INTEGRATION_NO_PROVIDER_MSG', $MODULE_NAME, ['%channel' => $CHANNEL_NAME])}</div>
									{/foreach}
								</div>
							</div>
						</div>
					</div>
				</div>
			{/if}

			{* Provider Detail *}
			{if $MODE == 'ShowDetail'}
				<div class="box shadowed">
					<div class="box-header">
						{vtranslate('LBL_CHATBOT_INTEGRATION_CONFIG', $MODULE_NAME)}
					</div>
					<div class="box-body">
						<div id="hint-text">{vtranslate('LBL_CHATBOT_INTEGRATION_PROVIDER_DETAIL_HINT_TEXT', $MODULE_NAME, ['%provider_name' => $PROVIDER_INFO.display_name])}</div>
						
						<div id="vendor-detail" class="box shadowed">
							<div class="box-header">
								<div class="header-title">{vtranslate('LBL_VENDOR_INTEGRATION_CONNECTION_INFO', $MODULE_NAME)}</div>
								<div class="instruction pull-right">
									<a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_INTEGRATION_INSTRUCTION', $MODULE_NAME)}</a>
								</div>
							</div>
							<div class="box-body">
								{assign var='INSTANCE_INFO' value=$PROVIDER_INSTANCE->getInfo()}

								<input type="hidden" name="provider" value="{$INSTANCE_INFO.name}" />
								<input type="hidden" name="provider_name" value="{$PROVIDER_INFO.display_name}" />

								{if $INSTANCE_INFO.chatbot_fields}
									<input type="hidden" name="chatbot_infos" value="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($INSTANCE_INFO.chatbots))}" />
									<input type="hidden" name="chatbots_updated" value="false" />
								{/if}
								
								<div id="logo" class="text-center">
									<img src="{$PROVIDER_INFO.logo_path}" />
								</div>
								<br/>
								
								<!-- General Config -->
								{if $INSTANCE_INFO.config_fields}
									<div id="config-fields" class="box shadowed">
										<div class="box-header">
											{vtranslate('LBL_VENDOR_INTEGRATION_GENERAL_CONFIG', $MODULE_NAME)}
										</div>
										<div class="box-body">
											<div class="fields-container">
												{foreach key=FIELD_NAME item=FIELD_INFO from=$INSTANCE_INFO.config_fields}
													<div class="form-group padding0 align-item-center">
														<div class="col-lg-4 fieldLabel">{$FIELD_INFO.label}</div>
														<div class="col-lg-8">
															<input type="{$FIELD_INFO.type}" name="params[{$FIELD_NAME}]" value="{$INSTANCE_INFO.config_params[$FIELD_NAME]}" class="inputElement" data-rule-required="true" />
														</div>
														<div class="clearFix"></div>
													</div>
												{/foreach}
											</div>	
										</div>
									</div>
								{/if}

								<!-- Chatbot List -->
								{if $INSTANCE_INFO.chatbot_fields}
									<div id="chatbot-list" class="box shadowed">
										<div class="box-body">
											<table id="tbl-chatbots" class="table">
												<thead>
													<tr>
														<th style="width: 90%;">{vtranslate('LBL_CHATBOT_INTEGRATION_CHATBOT_NAME', $MODULE_NAME)}</th>
														<th style="width: 10%;" class="text-center">{vtranslate('LBL_ACTIONS', $MODULE_NAME)}</th>
													</tr>
												</thead>
												<tbody>
													{foreach key=BOT_ID item=BOT_INFO from=$INSTANCE_INFO.chatbots}
														{include file="modules/Settings/Vtiger/tpls/ChatbotIntegrationConfigChatbotRowTemplate.tpl" BOT_INFO=$BOT_INFO}
													{/foreach}
												</tbody>
												<tfoot id="template" style="display:none">
													{include file="modules/Settings/Vtiger/tpls/ChatbotIntegrationConfigChatbotRowTemplate.tpl"}
												</tfoot>
												<tfoot>
													<tr>
														<td colspan="2">
															<a id="btn-add-chatbot" href="javascript:void(0)" class="btn btn-link" onclick="app.controller().showChatbotModal(this)">
																<i class="far fa-plus" aria-hidden="true"></i>
																{vtranslate('LBL_CHATBOT_INTEGRATION_BTN_ADD_CHATBOT', $MODULE_NAME)}
															</a>
														</td>
													</tr>
												</tfoot>
											</table>
										</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-overlay-footer clearfix">
					<div class="row clear-fix">
						<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
							{if $IS_EDIT}<button type="button" id="btn-disconnect" class="btn btn-outline-danger">{vtranslate('LBL_BTN_DISCONNECT', $MODULE_NAME)}</button>&nbsp;{/if}
							<button type="submit" class="btn btn-primary">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>&nbsp;
							<a class="btn btn-default btn-outline" href="index.php?module=Vtiger&parent=Settings&view=ChatbotIntegrationConfig&mode=ShowList">{vtranslate('LBL_BACK', $MODULE_NAME)}</a>
						</div>
					</div> 
				</div>
			{/if}
		</form>
	</div>
{/strip}