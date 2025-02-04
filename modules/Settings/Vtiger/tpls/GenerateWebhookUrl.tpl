{* Added by Hieu Nguyen on 2021-03-03 *}

{strip}
	<form autocomplete="off" name="config">
		<div class="editViewBody">
			<div class="editViewContents">
				<div class="fieldBlockContainer">
					<h4 class="fieldBlockHeader">{vtranslate('LBL_GENERATE_WEBHOOK_URL_GENERAL_INFO', $MODULE_NAME)}</h4>
					<hr />
					<div class="row">
						<div class="row">
							<div class="col-md-2 fieldLabel">Site URL</div>
							<div id="site_url" class="col-md-10 highlight">{getGlobalVariable('site_URL')}</div>
						</div>
						<div class="row">
							<div class="col-md-2 fieldLabel">Secret Key</div>
							<div id="secret_key" class="col-md-10 highlight">{getGlobalVariable('secretKey')}</div>
						</div>
					</div>
				</div>
				<div class="fieldBlockContainer">
					<h4 class="fieldBlockHeader">{vtranslate('LBL_GENERATE_WEBHOOK_URL', $MODULE_NAME)}</h4>
					<hr />
					<div class="row">
						<select name="integration_type" class="select2" style="width:250px">
							<option value="">{vtranslate('LBL_GENERATE_WEBHOOK_URL_INTEGRATION_TYPE_PLACEHOLDER', $MODULE_NAME)}</option>

							{foreach key=TYPE item=ITEM from=$INTEGRATION_MAPPING}
								<option value="{$TYPE}">{vtranslate(strtoupper("LBL_GENERATE_WEBHOOK_URL_INTEGRATION_TYPE_{$TYPE}"), $MODULE_NAME)}</option>
							{/foreach}
						</select>
						&nbsp;&nbsp;
						<select name="vendor" class="select2" style="width:200px">
							<option value="">{vtranslate('LBL_GENERATE_WEBHOOK_URL_VENDOR_PLACEHOLDER', $MODULE_NAME)}</option>
						</select>
						&nbsp;&nbsp;
						<button type="button" id="generate" class="btn btn-primary" data-validate-msg="{vtranslate('LBL_GENERATE_WEBHOOK_URL_VALIDATE_MSG', $MODULE_NAME)}">{vtranslate('LBL_GENERATE_WEBHOOK_URL_BUTTON_GENERATE', $MODULE_NAME)}</button>
					</div>
					<br />
					<div id="result" class="row">
					</div>
				</div>
			</div>
		</div>
	</form>

	<script type="text/javascript">
		const _INTEGRATION_MAPPING = {json_encode($INTEGRATION_MAPPING)};
		const _WEBHOOK_MAPPING = {json_encode($WEBHOOK_MAPPING)};
	</script>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Settings/Vtiger/resources/GenerateWebhookUrl.css')}"/>
{/strip}