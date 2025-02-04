{* Added by Hieu Nguyen on 2022-06-17 *}

{strip}
	<div id="config-page" class="row-fluid padding20">
		<form id="settings" data-mode="{$MODE}">
			{assign var='CHANNELS' value=CPOTTIntegration_Config_Helper::getChannels()}
			{assign var="CHANNEL_NAME" value=$CHANNELS[$ACTIVE_CHANNEL]}

			{* Gateway List *}
			{if $MODE == 'ShowList'}
				<div class="box shadowed">
					<div class="box-header">
						{vtranslate('LBL_OTT_INTEGRATION_CONFIG', $MODULE_NAME)}
					</div>
					<div class="box-body">
						<div class="box shadowed">
							<div class="box-body text-center">
								<span><strong>{vtranslate('LBL_OTT_INTEGRATION_SELECT_CHANNEL', $MODULE_NAME)}</strong></span>&nbsp;&nbsp;
								<select name="channel" class="select2">
									{foreach key='KEY' item='LABEL' from=$CHANNELS}
										<option value="{$KEY}" {if $KEY == $ACTIVE_CHANNEL}selected{/if}>{$LABEL}</option>
									{/foreach}
								<select>
							</div>
						</div>

						<div id="hint-text">{vtranslate('LBL_OTT_INTEGRATION_GATEWAY_LIST_HINT_TEXT', $MODULE_NAME, ['%channel' => $CHANNEL_NAME])}</div>

						<div class="box shadowed">
							<div class="box-header">
								{vtranslate('LBL_OTT_INTEGRATION_CONNECT_TO_GATEWAY', $MODULE_NAME)}
							</div>
							<div class="box-body">
								<div id="vendor-search">
									<input name="search_input" placeholder="{vtranslate('LBL_OTT_INTEGRATION_SEARCH_PLACEHOLDER', $MODULE_NAME)}" />
									<i class="far fa-search search-icon"></i>
								</div>
								<div id="vendor-list" class="row">
									{foreach key=INDEX item=GATEWAY from=$GATEWAY_LIST}
										{assign var="GATEWAY_NAME" value=$GATEWAY->getName()}
										{assign var="PROVIDER_INFO" value=$PROVIDER_INFOS[$GATEWAY_NAME]}
										{assign var="IS_ACTIVE" value=$GATEWAY_NAME == $ACTIVE_GATEWAY}
										
										<div class="col-md-6 vendor-container">
											<div class="vendor" 
												data-name="{$GATEWAY_NAME}" 
												data-display-name="{$PROVIDER_INFO.display_name}" 
												{if $IS_ACTIVE}connected="true"{/if} 
												{if $ACTIVE_GATEWAY}{if $IS_ACTIVE}can-config="true"{/if}{else}can-config="true"{/if} 
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
																<button type="button" class="btn btn-primary btn-connect" {if !empty($ACTIVE_GATEWAY)}disabled{/if}>{vtranslate('LBL_BTN_CONNECT', $MODULE_NAME)}</button>
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
										<div id="no-vendor-msg">{vtranslate('LBL_OTT_INTEGRATION_NO_GATEWAY_MSG', $MODULE_NAME, ['%channel' => $CHANNEL_NAME])}</div>
									{/foreach}
								</div>
							</div>
						</div>
					</div>
				</div>
			{/if}

			{* Gateway Detail *}
			{if $MODE == 'ShowDetail'}
				{assign var="GATEWAY_NAME" value=$GATEWAY_INSTANCE->getName()}

				<div class="box shadowed">
					<div class="box-header">{vtranslate('LBL_OTT_INTEGRATION_CONFIG', $MODULE_NAME)}</h4></div>
					<div class="box-body">
						<div id="hint-text">{vtranslate('LBL_OTT_INTEGRATION_GATEWAY_DETAIL_HINT_TEXT', $MODULE_NAME, ['%gateway' => $PROVIDER_INFO.display_name, '%channel' => $CHANNEL_NAME])}</div>
						
						<div id="vendor-detail" class="box shadowed" data-channel-name="{$CHANNEL_NAME}" data-gateway-name="{$PROVIDER_INFO.display_name}">
							<div class="box-header">
								<div class="header-title">{vtranslate('LBL_VENDOR_INTEGRATION_CONNECTION_INFO', $MODULE_NAME)}</div>
								<div class="instruction pull-right">
									<a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_INTEGRATION_INSTRUCTION', $MODULE_NAME)}</a>
								</div>
							</div>
							<div class="box-body">
								<div id="logo" class="text-center">
									<img src="{$PROVIDER_INFO.logo_path}" />
								</div>
								<br/>
								
								<div id="config-fields" class="box shadowed">
									<div class="box-body">
										{assign var='GATEWAY_INFO' value=$GATEWAY_INSTANCE->getInfo()}
										<input type="hidden" name="channel" value="{$ACTIVE_CHANNEL}" />
										<input type="hidden" name="gateway" value="{$GATEWAY_NAME}" />

										{foreach key=FIELD_NAME item=FIELD_INFO from=$GATEWAY_INFO.config_fields}
											<div class="form-group">
												<div class="col-lg-4 fieldLabel">{$FIELD_INFO.label}&nbsp;<span class="redColor">*</span></div>
												<div class="col-lg-8"><input type="{$FIELD_INFO.type}" name="config[{$FIELD_NAME}]" value="{$GATEWAY_INFO.config_params[$FIELD_NAME]}" class="inputElement" data-rule-required="true" {$FIELD_INFO.validation}/></div>
												<div class="clearFix"></div>
											</div>
										{/foreach}
									</div>
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
							<a class="btn btn-default btn-outline" href="index.php?module=Vtiger&parent=Settings&view=OTTIntegrationConfig&mode=ShowList">{vtranslate('LBL_BACK', $MODULE_NAME)}</a>
						</div>
					</div> 
				</div>
			{/if}
		</form>
	</div>
{strip}