{* Added by Hieu Nguyen on 2022-06-15 *}

{strip}
	<div id="config-page" class="row-fluid padding20">
		<form id="settings">
			<!-- Oauth -->
			<div class="box shadowed">
				<div class="box-header">
					<div class="header-title">
						{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_OAUTH', $MODULE_NAME)}&nbsp;&nbsp;
						<span data-toggle="tooltip" title="{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_OAUTH_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
					</div>
					<div class="guide-url pull-right marginleft-auto"> <!-- Modify by Vu Mai on 2022-07-18 -->
						<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/tich-hop-google/dong-bo-contacts-and-calendar">{vtranslate('LBL_INTEGRATION_INSTRUCTION', $MODULE_NAME)}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
					</div>
				</div>
				<div class="box-body">
					<table class="table no-border fieldBlockContainer">
						<tr>
							<td class="fieldLabel">Client ID</td>
							<td class="fieldValue"><input type="text" name="config[oauth][client_id]" class="inputElement" value="{$CONFIG.oauth.client_id}" /></td>
						</tr>
						<tr>
							<td class="fieldLabel">Client Secret</td>
							<td class="fieldValue"><input type="password" name="config[oauth][client_secret]" class="inputElement" value="{$CONFIG.oauth.client_secret}" /></td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- Maps & Places -->
			<div class="box shadowed">
				<div class="box-header">
					<div class="header-title">
						{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_MAPS_AND_PLACES', $MODULE_NAME)}&nbsp;&nbsp;
						<span data-toggle="tooltip" title="{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_MAPS_AND_PLACES_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
					</div>
					<div class="guide-url pull-right  marginleft-auto"> <!-- Modify by Vu Mai on 2022-07-18 -->
						<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/tich-hop-google/google-maps">{vtranslate('LBL_CONFIGURATION_INSTRUCTION', $MODULE_NAME)}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
					</div>
				</div>
				<div class="box-body">
					<table class="table no-border fieldBlockContainer">
						<tr>
							<td class="fieldLabel">Maps & Places API Key</td>
							<td class="fieldValue">
								<input type="text" name="config[maps][maps_and_places_api_key]" class="inputElement" value="{$CONFIG.maps.maps_and_places_api_key}" />&nbsp;&nbsp;
								<span data-toggle="tooltip" title="{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_MAPS_AND_PLACES_API_KEY_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
							</td>
						</tr>
						<tr>
							<td class="fieldLabel">Geocoding API Key</td>
							<td class="fieldValue">
								<input type="text" name="config[maps][geocoding_api_key]" class="inputElement" value="{$CONFIG.maps.geocoding_api_key}" />&nbsp;&nbsp;
								<span data-toggle="tooltip" title="{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_GEOCODING_API_KEY_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- Recaptcha -->
			<div class="box shadowed">
				<div class="box-header">
					<div class="header-title">
						{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_RECAPTCHA', $MODULE_NAME)}&nbsp;&nbsp;
						<span data-toggle="tooltip" title="{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_RECAPTCHA_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
					</div>
					<div class="guide-url pull-right  marginleft-auto"> <!-- Modify by Vu Mai on 2022-07-18 -->
						<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/tich-hop-google/google-recaptcha">{vtranslate('LBL_CONFIGURATION_INSTRUCTION', $MODULE_NAME)}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
					</div>
				</div>
				<div class="box-body">
					<table class="table no-border fieldBlockContainer">
						<tr>
							<td class="fieldLabel">Site Key&nbsp;<span class="redColor">*</span></td>
							<td class="fieldValue"><input type="text" name="config[recaptcha][site_key]" class="inputElement" value="{$CONFIG.recaptcha.site_key}" data-rule-required="true" /></td>
						</tr>
						<tr>
							<td class="fieldLabel">Secret Key&nbsp;<span class="redColor">*</span></td>
							<td class="fieldValue"><input type="password" name="config[recaptcha][secret_key]" class="inputElement" value="{$CONFIG.recaptcha.secret_key}" data-rule-required="true" /></td>
						</tr>
					</table>
				</div>
			</div>

			<!-- Firebase -->
			<div class="box shadowed">
				<div class="box-header">
					<div class="header-title"> 
						{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_FIREBASE', $MODULE_NAME)}&nbsp;&nbsp;
						<span data-toggle="tooltip" title="{vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG_FIREBASE_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
					</div>
					<div class="guide-url pull-right marginleft-auto"> <!-- Modify by Vu Mai on 2022-07-18 -->
						<a target="_blank" href="https://docs.onlinecrm.vn/tich-hop/tich-hop-google/google-firebase">{vtranslate('LBL_CONFIGURATION_INSTRUCTION', $MODULE_NAME)}</a> <!-- Modify by Vu Mai on 2023-03-10 -->
					</div>
				</div>
				<div class="box-body">
					<table class="table no-border fieldBlockContainer">
						<tr>
							<td class="fieldLabel">FCM Sender ID&nbsp;<span class="redColor">*</span></td>
							<td class="fieldValue"><input type="text" name="config[firebase][fcm_sender_id]" class="inputElement" value="{$CONFIG.firebase.fcm_sender_id}" data-rule-required="true" /></td>
						</tr>
						<tr>
							<td class="fieldLabel">FCM Server Key&nbsp;<span class="redColor">*</span></td>
							<td class="fieldValue"><input type="password" name="config[firebase][fcm_server_key]" class="inputElement" value="{$CONFIG.firebase.fcm_server_key}" data-rule-required="true" /></td>
						</tr>
					</table>
				</div>
			</div>

			<div class="modal-overlay-footer clearfix">
				<div class="row clear-fix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
						<a class="cancelLink" href="javascript:history.back()">{vtranslate('LBL_CANCEL')}</a>
					</div>
				</div> 
			</div>
		</form>
	</div>
{strip}