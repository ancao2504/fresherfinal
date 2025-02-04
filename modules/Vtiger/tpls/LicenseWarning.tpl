{* Added by Hieu Nguyen on 2021-09-21 *}

{strip}
	<div id="license-warning" class="row-fluid">
		<div id="warning-message" class="col-md-9 col-lg-9">{$WARNING_MESSAGE}</div>
		<div id="call-to-action" class="col-md-3 col-lg-3">
			<span>Hotline: 1900 29 29 90</span>
			<a class="btn btn-primary" href="{$UPGRADE_FORM_URL}" target="_blank">{vtranslate('LBL_LICENSE_INFO_BUTTON_UPGRADE_LICENSE', 'Vtiger')}</a>
			<button id="btn-close-license-warning" class="btn btn-link"><i class="fal fa-xmark"></i></button>
		</div>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/LicenseWarning.css')}" />
	<script src="{vresource_url('modules/Vtiger/resources/LicenseWarning.js')}" async defer></script>
{strip}