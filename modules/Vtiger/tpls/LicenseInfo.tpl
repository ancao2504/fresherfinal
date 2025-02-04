{* Added by Hieu Nguyen on 2021-09-15 *}
{extends file="modules/Vtiger/tpls/LicenseTemplate.tpl"}

{block name="content"}
	<div id="page-license-info">
		{* Header Section *}
		<div id="header" class="row-fluid">
			<div id="left-side" class="col-md-4">
				<img id="logo" src="resources/images/logo-cloudgo.png" /> {* Modified by Vu Mai on 2023-03-15 to change CloudGO logo *}
			</div>
			<div id="right-side" class="col-md-8">
				<span class="text-primary">Hotline: 1900 29 29 90</span>
				<button id="back-to-crm" class="btn btn-default text-primary" onclick="location.href='{$SITE_URL}'"><i class="far fa-dashboard"></i> {vtranslate('LBL_LICENSE_INFO_BUTTON_HOMEPAGE', 'Vtiger')}</button>
				<button id="update-license" class="btn btn-primary" data-toggle="modal" data-target="#modal-update-license">{vtranslate('LBL_LICENSE_INFO_BUTTON_UPDATE_LICENSE', 'Vtiger')}</button>
			</div>
		</div>

		{* Activate License *}
		<div id="modal-update-license" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog modal-dialog-centered modal-md">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{vtranslate('LBL_LICENSE_ACTIVATE_NEW_LICENSE_TITLE', 'Vtiger')}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<i class="far fa-xmark fa-lg"></i>
						</button>
					</div>
					<div class="modal-body">
						<form id="form-update-license">
							<div class="form-group text-center">
								<img src="resources/images/activate-license.png" />
							</div>
							<div class="form-group">
								<div>{vtranslate('LBL_LICENSE_ACTIVATE_NEW_LICENSE_HINT_TEXT', 'Vtiger')}</div>
							</div>
							<div class="form-group">
								<textarea id="license-code" class="form-control" rows="4" required="true" placeholder="{vtranslate('LBL_LICENSE_ACTIVATE_INPUT_PLACEHOLDER', 'Vtiger')}"></textarea>
							</div>
							<div class="form-group">
								<button type="submit" id="btn-submit" class="btn btn-primary">{vtranslate('LBL_LICENSE_ACTIVATE_BUTTON_SUBMIT', 'Vtiger')}</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		{* Main Content *}
		<div id="content" class="row-fluid">
			{* Package Info *}
			<div id="package-info" class="box">
				<div class="box-content">
					<h3 id="greeting" class="text-center">{vtranslate('LBL_LICENSE_INFO_GREETING_MSG', 'Vtiger', ['%account_name' => $LICENSE.account_name])}</h3>

					<div id="info">
						<table id="tbl-info">
							<tr>
								<td class="text-blur" width="15%">{vtranslate('LBL_LICENSE_INFO_PACKAGE_NAME', 'Vtiger')}</td>
								<td width="18%">
									{if $LICENSE.lifetime_license}<span class="lifetime"><i class="far fa-crown"></i> Lifetime License</span><br/>{/if}
									<span>{$FULL_LICENSE_INFO.package_name}</span>
								</td>
								<td class="text-blur" width="15%">{vtranslate('LBL_LICENSE_INFO_MAX_STORAGE', 'Vtiger')}</td>
								<td width="18%">
									{if $LICENSE.max_storage == -1}
										<span class="unlimit">{vtranslate('LBL_LICENSE_INFO_UNLIMITED', 'Vtiger')}</span>
									{else}
										{$CURRENT_USED_STORAGE} GB / {$LICENSE.max_storage} GB
										<span class="helptext" data-toggle="tooltip" data-html="true" title="{$MAX_STORAGE_TOOLTIP}"><i class="far fa-circle-info"></i></span>
									{/if}
								</td>
								<td class="text-blur" width="15%">{vtranslate('LBL_LICENSE_INFO_START_DATE', 'Vtiger')}</td>
								<td width="18%">{DateTimeField::convertToUserFormat($LICENSE.start_date)}</td>
							</tr>
							<tr>
								<td class="text-blur">{vtranslate('LBL_LICENSE_INFO_PRODUCT_NAME', 'Vtiger')}</td>
								<td>{$FULL_LICENSE_INFO.product_name}</td>
								<td class="text-blur">{vtranslate('LBL_LICENSE_INFO_MAX_USERS', 'Vtiger')}</td>
								<td>
									{if $LICENSE.max_normal_users == -1}
										<span class="unlimit">{vtranslate('LBL_LICENSE_INFO_UNLIMITED', 'Vtiger')}</span>
									{else}
										{$CURRENT_USERS_COUNT} / {$LICENSE.max_normal_users}
										{* <span class="helptext" data-toggle="tooltip" data-html="true" title="{$MAX_USERS_TOOLTIP}"><i class="far fa-circle-info"></i></span> *}
										{if $ABNORMAL_USERS_COUNT_WARNING}
											<span class="warning">
												<span class="helptext" data-toggle="tooltip" data-html="true" title="{$ABNORMAL_USERS_COUNT_WARNING}"><i class="far fa-warning"></i></span>
											</span>
										{/if}
									{/if}
								</td>
								<td class="text-blur">{vtranslate('LBL_LICENSE_INFO_EXPIRE_DATE', 'Vtiger')}</td>
								<td>
									{if $LICENSE.lifetime_license}
										<span class="unlimit">{vtranslate('LBL_LICENSE_INFO_UNLIMITED', 'Vtiger')}</span>
									{else}
										{if $REMAINING_DAYS_WARINING}
											<span class="warning">{DateTimeField::convertToUserFormat($LICENSE.expire_date)} <span class="helptext" data-toggle="tooltip" data-html="true" title="{$REMAINING_DAYS_WARINING}"><i class="far fa-warning"></i></span>
										{else}
											{DateTimeField::convertToUserFormat($LICENSE.expire_date)}
										{/if}
									{/if}
								</td>
							</tr>
							<tr>
								<td class="text-blur">{vtranslate('LBL_LICENSE_INFO_DOMAIN_NAME', 'Vtiger')}</td>
								<td>
									{if $LICENCED_DOMAIN_INVALID_WARNING}
										<span class="warning">{$LICENSE.domain_name} <span class="helptext" data-toggle="tooltip" data-html="true" title="{$LICENCED_DOMAIN_INVALID_WARNING}"><i class="far fa-warning"></i></span>
									{else}
										{$LICENSE.domain_name}
									{/if}
								</td>
								<td class="text-blur">{vtranslate('LBL_LICENSE_INFO_MAX_CUSTOMERS', 'Vtiger')}</td>
								<td>
									{if $LICENSE.max_customers == -1}
										<span class="unlimit">{vtranslate('LBL_LICENSE_INFO_UNLIMITED', 'Vtiger')}</span>
									{else}
										{$CURRENT_CUSTOMERS_COUNT} / {number_format($LICENSE.max_customers)}
										<span class="helptext" data-toggle="tooltip" data-html="true" title="{$MAX_CUSTOMERS_TOOLTIP}"><i class="far fa-circle-info"></i></span>
									{/if}
								</td>
								<td class="text-blur">{vtranslate('LBL_LICENSE_INFO_UPATED_DATE', 'Vtiger')}</td>
								<td>{DateTimeField::convertToUserFormat($LICENSE.updated_date)}</td>
							</tr>
							<tr>
								<td class="text-blur" width="15%">{vtranslate('LBL_LICENSE_INFO_STATUS', 'Vtiger')}</td>
								<td><span class="highlight">{vtranslate('LBL_LICENSE_INFO_STATUS_ACTIVE', 'Vtiger')}</span></td>
								<td class="text-blur" width="15%">{if $LICENSE.max_mobile_users !== 0}{vtranslate('LBL_LICENSE_INFO_MAX_MOBILE_USERS', 'Vtiger')}{/if}</td>
								<td>
									{if $LICENSE.max_mobile_users !== 0}
										{if $LICENSE.max_mobile_users == -1}
											<span class="unlimit">{vtranslate('LBL_LICENSE_INFO_UNLIMITED', 'Vtiger')}</span>
										{else}
											{$CURRENT_MOBILE_USERS_COUNT} / {number_format($LICENSE.max_mobile_users)}
										{/if}
									{/if}
								</td>
								<td class="text-blur" width="15%">{if $LICENSE.max_portal_customers !== 0}{vtranslate('LBL_LICENSE_INFO_MAX_PORTAL_CUSTOMERS', 'Vtiger')}{/if}</td>
								<td>
									{if $LICENSE.max_portal_customers !== 0}
										{if $LICENSE.max_portal_customers == -1}
											<span class="unlimit">{vtranslate('LBL_LICENSE_INFO_UNLIMITED', 'Vtiger')}</span>
										{else}
											{$CURRENT_PORTAL_CUSTOMERS_COUNT} / {number_format($LICENSE.max_portal_customers)}
										{/if}
									{/if}
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>

			{* Package Features *}
			<div id="package-features" class="box has-footer">
				<div class="box-content">
					<div id="tab">
						<ul class="nav nav-tabs">
							<li class="nav-item active">
								<a class="nav-link" data-toggle="tab" href="#tab-addition-products">{vtranslate('LBL_LICENSE_INFO_TAB_ADDITION_PRODUCTS', 'Vtiger')}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-addition-features">{vtranslate('LBL_LICENSE_INFO_TAB_ADDITION_FEATURES', 'Vtiger')}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-addition-services">{vtranslate('LBL_LICENSE_INFO_TAB_ADDITION_SERVICES', 'Vtiger')}</a>
							</li>
						</ul>
						<a id="show-all-packages" href="{$FULL_LICENSE_INFO.product_ref_link}" target="_blank">{vtranslate('LBL_LICENSE_INFO_SHOW_ALL_PACKAGES', 'Vtiger')} <i class="far fa-chevrons-right fa-sm"></i></a>
					</div>

					<div id="tab-content" class="tab-content">
						<!-- Package Info -->
						<div id="tab-addition-products" class="tab-pane active">
							{if $ADDITION_PRODUCTS_HTML}
								{$ADDITION_PRODUCTS_HTML}
							{else}
								<div class="no-data">
									<img src="resources/images/no-data.png" />
								</div>
							{/if}

							<div class="clear-fix"></div>
						</div>

						<!-- Addition Features -->
						<div id="tab-addition-features"class="tab-pane">
							{if $ADDITION_FEATURES_HTML}
								{$ADDITION_FEATURES_HTML}
							{else}
								<div class="no-data">
									<img src="resources/images/no-data.png" />
									<div class="text-center text-blur">{vtranslate('LBL_LICENSE_INFO_TAB_ADDITION_FEATURES_NOT_SUPPORTED', 'Vtiger')}</div>
								</div>
							{/if}

							<div class="clear-fix"></div>
						</div>

						<!-- Addition Services -->
						<div id="tab-addition-services"class="tab-pane">
							{if $ADDITION_SERVICES_HTML}
								{$ADDITION_SERVICES_HTML}
							{else}
								<div class="no-data">
									<img src="resources/images/no-data.png" />
								</div>
							{/if}

							<div class="clear-fix"></div>
						</div>
					</div>
				</div>
				<div class="box-footer">
					<span class="bold"><i class="far fa-check icon-blue"></i> {vtranslate('LBL_LICENSE_INFO_BOUGHT', 'Vtiger')}<span/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="bold"><i class="far fa-xmark icon-red"></i> {vtranslate('LBL_LICENSE_INFO_NOT_BUY', 'Vtiger')}</span>
				</div>
			</div>

			<div class="clearFix"></div>
		</div>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/LicenseInfo.css')}" />
	<script src="{vresource_url('modules/Vtiger/resources/LicenseInfo.js')}"></script>
{/block}