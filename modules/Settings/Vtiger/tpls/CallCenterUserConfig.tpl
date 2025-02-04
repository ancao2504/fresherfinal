{*
	Name: CallCenterUserConfig.tpl
	Author: Phu Vo
	Date: 2021.07.21
*}

{strip}
	<form autocomplete="off" name="settings">
		<input type="hidden" name="module" value="Vtiger" />
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="SaveCallCenterUserConfig" />

		<div class="editViewBody">
			<div class="editViewContents">
				<div class="fieldBlockContainer">
					<h4 class="fieldBlockHeader">{vtranslate('LBL_CALLCENTER_USER_CONFIG_GENERAL_CONFIG', $MODULE_NAME)}</h4>
					<hr />
					<table class="configDetails" style="width: 100%">
						<tbody>
							<tr>
								<td class="fieldLabel alignTop"><span>{vtranslate('CRM Phone Extension', 'Users')}<span></td>
								<td class="fieldValue alignTop">
									<input type="text"
										class="inputElement"
										name="settings[general][phone_crm_extension]"
										value="{$USER_RECORD_MODEL->get('phone_crm_extension')}"
										data-rule-remote-check-duplicate="index.php?module=Users&action=CheckDuplicateAjax"
										data-record-id="{$USER_RECORD_MODEL->getId()}"
										data-check-field="phone_crm_extension"
									/>
								</td>
								<td class="fieldLabel alignTop"></td>
								<td class="fieldValue alignTop"></td>
							</tr>
						</tbody>
					</table>
				</div>

				{if $ACTIVE_CONNECTOR_NAME == 'Stringee'}
					<div class="fieldBlockContainer">
						<h4 class="fieldBlockHeader">{vtranslate('LBL_CALLCENTER_USER_CONFIG_VENDOR_CONFIG', $MODULE_NAME, ['%vendor_name' => $ACTIVE_CONNECTOR_NAME])}</h4>
						<hr />
						<table class="configDetails" style="width: 100%">
							<tbody>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_STRINGEE_MAKE_CALL_USING', $MODULE_NAME)}<span></td>
									<td class="fieldValue alignTop">
										<label class="mr-3 cursorPointer" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_USER_CONFIG_STRINGEE_MAKE_CALL_WEB_PHONE_TOOLTIP', $MODULE_NAME)}">
											<span><input type="radio" name="settings[vendor][preferred_outbound_device]" value="web_phone" class="inputElement" {if empty($VENDOR_CONFIG['preferred_outbound_device']) || $VENDOR_CONFIG['preferred_outbound_device'] eq 'web_phone'}checked{/if} /> <span class="inline-block mt-1">{vtranslate('LBL_CALLCENTER_USER_CONFIG_WEB_PHONE', $MODULE_NAME)}</span><span>
										</label>
										<label class="mr-3 cursorPointer" data-toggle="tooltip" title="{vtranslate('LBL_CALLCENTER_USER_CONFIG_STRINGEE_MAKE_CALL_SIP_PHONE_TOOLTIP', $MODULE_NAME)}">
											<span><input type="radio" name="settings[vendor][preferred_outbound_device]" value="sip_phone" class="inputElement" {if $VENDOR_CONFIG['preferred_outbound_device'] eq 'sip_phone'}checked{/if} /> <span class="inline-block mt-1">{vtranslate('LBL_CALLCENTER_USER_CONFIG_SIP_PHONE', $MODULE_NAME)}</span></span>
										</label>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_CUSTOM_RINGTONE', $MODULE_NAME)}</span></td>
									<td class="fieldValue alignTop">
										<div class="audio-container">
											<div class="audio-upload-input">
												<label class="btn btn-primary upload-btn">
													<span><i class="far fa-laptop"></i> {vtranslate('LBL_UPLOAD')}</span>
													<input type="file" name="custom_ringtone" accept="audio/mp3, audio/wav" />
													<input type="hidden" class="delete-input" name="ringtone_removed" />
												</label>
												<label class="btn btn-danger remove-btn" {if empty($VENDOR_CONFIG['custom_ringtone'])}style="display: none"{/if}>
													<span><i class="far fa-trash-alt" aria-hidden="true"></i> {vtranslate('LBL_REMOVE')}</span>
												</label>
												<div class="uploaded-file-name-wrapper"><p class="uploaded-file-name">{if !empty($VENDOR_CONFIG['custom_ringtone'])}{$VENDOR_CONFIG['custom_ringtone']}{else}{vtranslate('LBL_CALLCENTER_USER_CONFIG_NO_FILE_CHOSEN', $MODULE_NAME)}{/if}</p></div>
												<p class="redColor replace-warning" style="display: none">{vtranslate('LBL_NOTE_EXISTING_ATTACHMENTS_WILL_BE_REPLACED')}</p>
												<p class="redColor remove-warning" style="display: none">{vtranslate('LBL_CALLCENTER_USER_CONFIG_NOTE_EXISTING_ATTACHMENTS_WILL_BE_REMOVE', $MODULE_NAME)}</p>
											</div>
											<div class="audio-upload-player">
												<audio controls="controls" controlsList="nodownload" data-for="custom_ringtone" src="{$UPLOADED_FILE_BASE_64}" type="audio/mp3, audio/wav" {if empty($VENDOR_CONFIG['custom_ringtone'])}style="display: none"{/if}></audio>
											</div>
										</div>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
							</tbody>
						</table>
					</div>
				{/if}
				
				{if $ACTIVE_CONNECTOR_NAME == 'FPTTelecom'}
					<div class="fieldBlockContainer">
						<h4 class="fieldBlockHeader">{vtranslate('LBL_CALLCENTER_USER_CONFIG_VENDOR_CONFIG', $MODULE_NAME, ['%vendor_name' => $ACTIVE_CONNECTOR_NAME])}</h4>
						<hr />
						<table class="configDetails" style="width: 100%">
							<tbody>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_FPT_TELECOM_WEB_ACCESS_PASSWORD', $MODULE_NAME)}<span></td>
									<td class="fieldValue alignTop">
										<input type="password"
											class="input inputElement" 
											placeholder="{vtranslate('LBL_CALLCENTER_USER_CONFIG_FPT_TELECOM_WEB_ACCESS_PASSWORD', $MODULE_NAME)}"
											name="settings[vendor][web_access_password]" value="{$VENDOR_CONFIG['web_access_password']}"
										/>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
							</tbody>
						</table>
					</div>
				{/if}

				{* Added by Hieu Nguyen on 2021-07-21 *}
				{if $ACTIVE_CONNECTOR_NAME == 'BaseBS'}
					<div class="fieldBlockContainer">
						<h4 class="fieldBlockHeader">{vtranslate('LBL_CALLCENTER_USER_CONFIG_VENDOR_CONFIG', $MODULE_NAME, ['%vendor_name' => $ACTIVE_CONNECTOR_NAME])}</h4>
						<hr />
						<table class="configDetails" style="width: 100%">
							<tbody>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_EXTENSION_USERNAME', $MODULE_NAME)}<span></td>
									<td class="fieldValue alignTop">
										<input type="text"
											class="input inputElement" 
											placeholder="{vtranslate('LBL_CALLCENTER_USER_CONFIG_EXTENSION_USERNAME', $MODULE_NAME)}"
											name="settings[vendor][username]" value="{$VENDOR_CONFIG['username']}"
										/>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_EXTENSION_ACCESS_KEY', $MODULE_NAME)}<span></td>
									<td class="fieldValue alignTop">
										<input type="password"
											class="input inputElement" 
											placeholder="{vtranslate('LBL_CALLCENTER_USER_CONFIG_EXTENSION_ACCESS_KEY', $MODULE_NAME)}"
											name="settings[vendor][access_key]" value="{$VENDOR_CONFIG['access_key']}"
										/>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
							</tbody>
						</table>
					</div>
				{/if}
				{* End Hieu Nguyen *}

				{* Added by Hieu Nguyen on 2022-08-08 *}
				{if $ACTIVE_CONNECTOR_NAME == 'VoIP24H'}
					<div class="fieldBlockContainer">
						<h4 class="fieldBlockHeader">{vtranslate('LBL_CALLCENTER_USER_CONFIG_VENDOR_CONFIG', $MODULE_NAME, ['%vendor_name' => $ACTIVE_CONNECTOR_NAME])}</h4>
						<hr />
						<table class="configDetails" style="width: 100%">
							<tbody>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_SIP_SERVER_IP', $MODULE_NAME)}<span></td>
									<td class="fieldValue alignTop">
										<input type="text"
											class="input inputElement" 
											placeholder="{vtranslate('LBL_CALLCENTER_USER_CONFIG_SIP_SERVER_IP', $MODULE_NAME)}"
											name="settings[vendor][sip_server_ip]" value="{$VENDOR_CONFIG['sip_server_ip']}"
										/>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
								<tr>
									<td class="fieldLabel alignTop"><span>{vtranslate('LBL_CALLCENTER_USER_CONFIG_SIP_EXT_PASSWORD', $MODULE_NAME)}<span></td>
									<td class="fieldValue alignTop">
										<input type="password"
											class="input inputElement" 
											placeholder="{vtranslate('LBL_CALLCENTER_USER_CONFIG_SIP_EXT_PASSWORD', $MODULE_NAME)}"
											name="settings[vendor][sip_ext_password]" value="{$VENDOR_CONFIG['sip_ext_password']}"
										/>
									</td>
									<td class="fieldLabel alignTop"></td>
									<td class="fieldValue alignTop"></td>
								</tr>
							</tbody>
						</table>
					</div>
				{/if}
				{* End Hieu Nguyen *}
			</div>
		</div>
		<div class="modal-overlay-footer clearfix">
			<div class="row clear-fix">
				<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
					<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
				</div>
			</div> 
		</div>
	</form>
{/strip}