{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{* Modified by Phu Vo on 2021.08.30*}
{strip}
	<div class="app-exception-container">
		<div class="app-exception">
			<div class="body">
				<div class="left-side">
					<div class="icon">
						<svg style="width: 47px; height: 47px; fill: #cb2134;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM48 256c0-48.71 16.95-93.47 45.11-128.1l291.9 291.9C349.5 447 304.7 464 256 464C141.3 464 48 370.7 48 256zM418.9 384.1L127 93.11C162.5 64.95 207.3 48 256 48c114.7 0 208 93.31 208 208C464 304.7 447 349.5 418.9 384.1z"/></svg>
					</div>
				</div>
				<div class="main-side">
					<div class="genHeaderSmall title">
						{vtranslate($TITLE)}
					</div>
					<div class="genHeaderSmall content">
						{vtranslate($MESSAGE)}
					</div>
				</div>
			</div>
			<div class="footer">
				<div class="actions">
					<a href='javascript:window.history.back();'>{vtranslate('LBL_GO_BACK')}</a>
				</div>
			</div>
		</div>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/AppException.css')}">

	<script>
		setTimeout(function () {
			const backDrop = document.querySelector('.modal-backdrop');

			if (backDrop == null) return;
			
			backDrop.addEventListener('click', function () {
				if (typeof app != 'undefined') {
					document.querySelector('.app-exception-container').remove();
					app.helper.hideModal();
				}
			});
		}, 1000);
	</script>
{/strip}