{* Added by Hieu Nguyen on 2021-09-15 *}
{extends file="modules/Vtiger/tpls/LicenseTemplate.tpl"}

{block name="content"}
	<div id="page-activate-license">
		<form id="form-activate-license">
			<div id="form-content">
				<div id="left-side">
					<div class="form-group text-center">
						<img id="logo" src="resources/images/logo-cloudgo.png" /> {* Modified by Vu Mai on 2023-03-15 to change CloudGO logo *}
					</div>
					<div class="form-group">
						<div class="{if $IS_ERROR}text-danger{/if}">{$HINT_TEXT}</div>
					</div>
					<div class="form-group">
						<textarea id="license-code" class="form-control" rows="4" required="true" placeholder="{vtranslate('LBL_LICENSE_ACTIVATE_INPUT_PLACEHOLDER', 'Vtiger')}"></textarea>
					</div>
					<div class="form-group">
						<button type="button" id="btn-submit" class="btn btn-primary">{vtranslate('LBL_LICENSE_ACTIVATE_BUTTON_SUBMIT', 'Vtiger')}</button>
					</div>
					<div class="form-group">
						{* Modified by Vu Mai on 2023-03-03 to load company contact infos from config *}
						<ul id="contact-info">
							{foreach item=ITEM key=KEY from=getGlobalVariable('companyContactInfos')}
								<li><img src="{$ITEM.icon}" /> <a href="{$ITEM.url}">{$ITEM.value}</a></li>
							{/foreach}
						</ul>
						{* End Vu Mai *}
					</div>
				</div>
				<div id="right-side">
					<img src="resources/images/activate-license.png" />
				</div>
			</div>
		</form>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/LicenseActivate.css')}" />
	<script src="{vresource_url('modules/Vtiger/resources/LicenseActivate.js')}"></script>
{/block}