{* Added by Hieu Nguyen on 2021-06-24 *}

{strip}
<!DOCTYPE html>
<html>
	<head>
		<title>License</title>
		<link rel="manifest" href="manifest.json">
		<link rel="shortcut icon" href="layouts/v7/resources/Images/logo_favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/bootstrap.min.css')}" />
		<link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/font-awesome/css/font-awesome.min.css')}" />
		<link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/resources/fonts/fontawsome6/css/all.css')}" />
		<link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css')}" />
		<link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css')}" />

		<script type="text/javascript">
			var _META = { module: '', view: '', parent: '', notifier: '', app: '' };	// To bypass the app controller
		</script>

		<script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
		<script src="{vresource_url('layouts/v7/lib/jquery/jquery.class.min.js')}"></script>
		<script src="{vresource_url('layouts/v7/lib/todc/js/bootstrap.js')}"></script>
		<script src="{vresource_url('libraries/jquery/jstorage.min.js')}"></script>
        <script src="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.js')}"></script>
		<script src="{vresource_url('layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js')}"></script>
		<script src="{vresource_url('layouts/v7/lib/jquery/select2/select2.min.js')}"></script>
        <script src="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js')}"></script>
    	<script src="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js')}"></script>
        <script src="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js')}"></script>
        <script src="{vresource_url('layouts/v7/lib/bootbox/bootbox.js')}"></script>
		<script src="{vresource_url('layouts/v7/resources/helper.js')}"></script>
        <script src="{vresource_url('layouts/v7/resources/application.js')}"></script>
		<script src="{vresource_url('layouts/v7/modules/Vtiger/resources/Class.js')}"></script>
		<script src="{vresource_url('layouts/v7/modules/Vtiger/resources/Utils.js')}"></script>
        <script src="{vresource_url('layouts/v7/modules/Vtiger/resources/Base.js')}"></script>
        <script src="{vresource_url('layouts/v7/modules/Vtiger/resources/Vtiger.js')}"></script>
		<script src="{vresource_url('layouts/v7/modules/Vtiger/resources/validation.js')}"></script>
		<script src="{vresource_url('resources/libraries/Poper/2.9.2/popper.min.js')}"></script>
		<script src="{vresource_url('resources/libraries/Tippy/6.3.1/tippy-bundle.umd.js')}"></script>
		
	</head>
	<body>
		<div id="js_strings" class="hide noprint">{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($JS_LANGUAGE_STRINGS))}</div>
		
		{block name="content"}{/block}
	</body>
</html>
{/strip}