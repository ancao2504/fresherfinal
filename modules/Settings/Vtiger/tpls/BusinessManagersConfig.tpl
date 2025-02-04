{* Added by Vu Mai on 2022-08-01 to render business managers config view template *}

{strip}
	<div id="config-page" class="row-fluid padding20">
		<form autocomplete="off" id="config" name="config">
			<div class="box shadowed">
				<div class="box-header">
					{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG', $MODULE_NAME)}	
				</div>
				<div class="box-body">
					{if !isForbiddenFeature('FacebookIntegration')}
						<div class="row form-group">
							<div class="fieldLabel col-md-4 label-align-top">
								{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_FACEBOOK_MANAGERS', $MODULE_NAME)}
								&nbsp;
								<span class="redColor">*</span>
							</div>
							<div class="fieldValue col-md-8">
								<input type="text"
									name="facebook_integration_managers"
									class="inputElement"
									data-assignableUsersOnly="true"
									data-rule-required="true"
									{if !empty($CONFIG.facebook_integration)}
										data-selected-tags="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(Vtiger_Owner_UIType::getCurrentOwners(join(',', $CONFIG.facebook_integration))))}"
									{/if}
								/>
								&nbsp;&nbsp;
								<i class="far fa-info-circle info-tooltip icon-align-top" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_FACEBOOK_MANAGERS_TOOLTIP', $MODULE_NAME)}"></i>
							</div>
						</div>
					{/if}
					
					{if !isForbiddenFeature('ZaloIntegration')}
						<div class="row form-group">
							<div class="fieldLabel col-md-4 label-align-top">
								{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_ZALO_MANAGERS', $MODULE_NAME)}
								&nbsp;
								<span class="redColor">*</span>
							</div>
							<div class="fieldValue col-md-8">
								<input type="text"
									name="zalo_integration_managers"
									class="inputElement"
									data-assignableUsersOnly="true"
									data-rule-required="true"
									{if !empty($CONFIG.zalo_integration)}
										data-selected-tags="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(Vtiger_Owner_UIType::getCurrentOwners(join(',', $CONFIG.zalo_integration))))}"
									{/if}
								/>
								&nbsp;&nbsp;
								<i class="far fa-info-circle info-tooltip icon-align-top" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_ZALO_MANAGERS_TOOLTIP', $MODULE_NAME)}"></i>
							</div>
						</div>
					{/if}

					{if !isForbiddenFeature('TelesalesCampaign')}
						<div class="row form-group">
							<div class="fieldLabel col-md-4 label-align-top">
								{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_TELESALES_MANAGERS', $MODULE_NAME)}
								&nbsp;
								<span class="redColor">*</span>
							</div>
							<div class="fieldValue col-md-8">
								<input type="text"
									name="telesales_campaign_managers"
									class="inputElement"
									data-assignableUsersOnly="true"
									data-rule-required="true"
									{if !empty($CONFIG.telesales_campaign)}
										data-selected-tags="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(Vtiger_Owner_UIType::getCurrentOwners(join(',', $CONFIG.telesales_campaign))))}"
									{/if}
								/>
								&nbsp;&nbsp;
								<i class="far fa-info-circle info-tooltip icon-align-top" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_TELESALES_MANAGERS_TOOLTIP', $MODULE_NAME)}"></i>
							</div>
						</div>
					{/if}

					{if !isForbiddenFeature('LeadsDistribution')}
						<div class="row form-group">
							<div class="fieldLabel col-md-4 label-align-top">
								{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_LEADS_DISTRIBUTION_MANAGERS', $MODULE_NAME)}
								&nbsp;
								<span class="redColor">*</span>
							</div>
							<div class="fieldValue col-md-8">
								<input type="text"
									name="leads_distribution_managers"
									class="inputElement"
									data-assignableUsersOnly="true"
									data-rule-required="true"
									{if !empty($CONFIG.leads_distribution)}
										data-selected-tags="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(Vtiger_Owner_UIType::getCurrentOwners(join(',', $CONFIG.leads_distribution))))}"
									{/if}
								/>
								&nbsp;&nbsp;
								<i class="far fa-info-circle info-tooltip icon-align-top" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_BUSINESS_MANAGERS_CONFIG_LEADS_DISTRIBUTION_MANAGERS_TOOLTIP', $MODULE_NAME)}"></i>
							</div>
						</div>
					{/if}
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