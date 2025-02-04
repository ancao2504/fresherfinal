{* Added by Vu Mai on 2022-07-29 to render Centralized ChatBox Config template *}

{strip}
	<link type="text/css" rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}" />
	<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>

	<div id="config-page" class="row-fluid padding20">
		<form autocomplete="off" id="config" name="config">
			<div class="box shadowed">
				<div class="box-header">
					<div class="header-title">
						{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG', $MODULE_NAME)}
					</div>
					<div class="marginleft-auto">
						<input type="checkbox" name="switch_button" class="bootstrap-switch" {if $CONFIG.enable}checked{/if}>
					</div>
				</div>
				<div class="box-body">
					<div id="inactive-config-hint-text" class="box-body {if $CONFIG.enable}hide{/if}">{vtranslate('LBL_VENDOR_INTEGRATION_CONFIG_DISABLED_MSG', $MODULE_NAME)}</div>
					<div id="config-container" class="{if !$CONFIG.enable}hide{/if}">
						<!-- General Config -->
						<div id="general-config" class="box shadowed">
							<div class="box-header">
								<div class="header-title">
									{vtranslate('LBL_VENDOR_INTEGRATION_GENERAL_CONFIG', $MODULE_NAME)}
								</div>
								<div class="instruction pull-right marginleft-auto">
									<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/chatbot">{vtranslate('LBL_CONFIGURATION_INSTRUCTION', $MODULE_NAME)}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
								</div>
							</div>
							<div class="box-body">
								<div class="row form-group">
									<div class="fieldLabel col-sm-12">
										{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG_PERMISSTION_CHAT_ADMIN', $MODULE_NAME)}
										&nbsp;
										<span class="redColor">*</span>
										&nbsp;&nbsp;
										<i class="far fa-info-circle info-tooltip" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG_CHAT_ADMIN_TOOLTIP', $MODULE_NAME)}"></i>
									</div>
									<div class="fieldValue col-sm-12">
										<input type="text"
											name="chat_admins"
											class="inputElement"
											data-assignableUsersOnly="true"
											data-rule-required="true"
											{if !empty($CONFIG.chat_admins)}
												data-selected-tags='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(Vtiger_Owner_UIType::getCurrentOwners(join(',', $CONFIG.chat_admins))))}'
											{/if}
										/>
									</div>
								</div>
							</div>
						</div>

						<!-- Chat Config -->
						<div id="chat-config" class="row box-body">
							<!-- Chat Bridge Config -->
							<div id="chat-bridge-config" class="col-md-6 padding0">
								<div class="box shadowed">
									<div class="box-header">
										<div class="header-title">{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG_CHAT_BRIDGE_TITLE', $MODULE_NAME)}</div>&nbsp;&nbsp;
										<span class="info-tooltip" data-toggle="tooltip" title="{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG_CHAT_BRIDGE_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
									</div>
									<div class="box-body">
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Server Name/IP&nbsp;<span class="redColor">*</span></div>
											<div class="col-lg-8 fieldValue">
												<input type="text" name="chat_bridge[server_name]" value="{$CONFIG.chat_bridge.server_name}" class="inputElement" data-rule-required="true" />
											</div>
										</div>
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Default Port&nbsp;<span class="redColor">*</span></div>
											<div class="col-lg-8 fieldValue">
												<div class="port inline-block">
													<input type="number" name="chat_bridge[default_port]" value="{$CONFIG.chat_bridge.server_port}" class="inputElement" data-rule-required="true" />
												</div>
												<div class="fieldLabel padding0 ssl inline-block">SSL</div>
												<div class="checkbox ssl inline-block">
													<input type="checkbox" name="chat_bridge[default_port_ssl]" class="form-control" {if $CONFIG.chat_bridge.server_ssl}checked{/if}>
												</div>
											</div>
										</div>
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Backend Port</div>
											<div class="col-lg-8 fieldValue">
												<div class="port inline-block">
													<input type="number" name="chat_bridge[backend_port]" value="{$CONFIG.chat_bridge.server_backend_port}" class="inputElement" />
												</div>
												<div class="fieldLabel padding0 ssl inline-block">SSL</div>
												<div class="checkbox ssl inline-block">
													<input type="checkbox" name="chat_bridge[backend_port_ssl]" {if $CONFIG.chat_bridge.server_backend_ssl}checked{/if} class="form-control">
												</div>
											</div>
										</div>
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Access Domain&nbsp;<span class="redColor">*</span></div>
											<div class="col-lg-8 fieldValue">
												<input type="text" name="chat_bridge[access_domain]" value="{$CONFIG.chat_bridge.access_domain}" class="inputElement" data-rule-required="true" />
											</div>
										</div>
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Private Key&nbsp;<span class="redColor">*</span></div>
											<div class="col-lg-8 fieldValue">
												<input type="password" name="chat_bridge[private_key]" value="{$CONFIG.chat_bridge.private_key}" class="inputElement" data-rule-required="true" />
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Chat Storage Config -->
							<div id="chat-storage-config" class="col-md-6 paddingright0">
								<div class="box shadowed">
									<div class="box-header">
										<div class="header-title">{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG_CHAT_STORAGE_TITLE', $MODULE_NAME)}</div>&nbsp;&nbsp;
										<span class="info-tooltip" data-toggle="tooltip" title="{vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG_CHAT_STORAGE_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
									</div>
									<div class="box-body">
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Service URL&nbsp;<span class="redColor">*</span></div>
											<div class="col-lg-8 fieldValue">
												<input type="text" name="chat_storage[service_url]" value="{$CONFIG.chat_storage.service_url}" class="inputElement" data-rule-required="true" />
											</div>
										</div>
										<div class="form-group padding0 align-item-center">
											<div class="col-lg-4 fieldLabel">Access Token&nbsp;<span class="redColor">*</span></div>
											<div class="col-lg-8 fieldValue">
												<input type="password" name="chat_storage[access_token]" value="{$CONFIG.chat_storage.access_token}" class="inputElement" data-rule-required="true" />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer -->
			<div id="config-footer" class="modal-overlay-footer clearfix">
				<div class="row clear-fix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						<button type="submit" class="btn btn-success">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>&nbsp;
						<a class="btn btn-default btn-outline" onclick="history.back()">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
