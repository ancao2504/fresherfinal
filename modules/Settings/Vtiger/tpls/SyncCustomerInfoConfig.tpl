{*
	Name: SyncCustomerInfo.tpl
	Author: Phu Vo
	Date: 2020.06.24
*}

<link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}"/>
<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>

<form autocomplete="off" name="configs" style="padding-bottom: 20px;">
	<div class="editViewBody">
		<h4 class="fieldBlockHeader">{vtranslate('LBL_SYNC_CUSTOMER_INFO_CONFIG_TITLE', $MODULE_NAME)}</h4>
	</div>

	<div class="sync-customer-info-config-container">
		<ul class="nav nav-tabs tabs">
			<li class="active"><a data-toggle="tab" href="#general-config">{vtranslate('LBL_SYNC_CUSTOMER_INFO_GENERAL_CONFIG', $MODULE_NAME)}</a></li>
			<li><a data-toggle="tab" href="#user-distribution">{vtranslate('LBL_SYNC_CUSTOMER_INFO_ASSIGNER_DISTRIBUTION_CONFIG', $MODULE_NAME)}</a></li>
			<li><a data-toggle="tab" href="#auto-ticket">{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_CONFIG', $MODULE_NAME)}</a></li>
		</ul>

		<br/>
		
		<div class="tab-content">
			<div id="general-config" class="tab-pane fade active in">
				<div class="editViewContents">
					<div class="fieldBlockContainer">
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_SYNC_CRITERIA_LABEL', $MODULE_NAME)}:</div>
						<div class="formValue">
							<select class="select2 inputElement" data-name="criteria" name="configs[criteria]">
								<option value="">{vtranslate('LBL_SYNC_CUSTOMER_INFO_SELECT_CRITERIA_FIELD', $MODULE_NAME)}</option>
								<option value="phone" {if $CONFIGS['criteria'] == 'phone'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_PHONE', $MODULE_NAME)}</option>
								<option value="email" {if $CONFIGS['criteria'] == 'email'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_EMAIL', $MODULE_NAME)}</option>
								<option value="phone_or_email" {if $CONFIGS['criteria'] == 'phone_or_email'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_PHONE_OR_EMAIL', $MODULE_NAME)}</option>
								<option value="phone_and_email" {if $CONFIGS['criteria'] == 'phone_and_email'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_PHONE_AND_EMAIL', $MODULE_NAME)}</option>
							</select>
							<span>&nbsp;(1)</span>
						</div>
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_MATCHED_CRITERIA_ACTION', $MODULE_NAME)}:</div>
						<div class="formValue">
							<select class="select2 inputElement" data-name="matched_criteria_action" name="configs[matched_criteria_action]">
								<option value="Create Lead" {if $CONFIGS['matched_criteria_action'] == 'Create Lead'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_CREATE_LEAD', $MODULE_NAME)}</option>
								<option value="Create Contact" {if $CONFIGS['matched_criteria_action'] == 'Create Contact'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_CREATE_CONTACT', $MODULE_NAME)}</option>
							</select>
							<span>&nbsp;(2)</span>
						</div>
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_NOT_MATCHED_CRITERIA_ACTION', $MODULE_NAME)}:</div>
						<div class="formValue">
							<select class="select2 inputElement" data-name="not_matched_criteria_action" name="configs[not_matched_criteria_action]">
								<option value="Create Target" {if $CONFIGS['not_matched_criteria_action'] == 'Create Target'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_CREATE_TARGET', $MODULE_NAME)}</option>
								<option value="Create Lead" {if $CONFIGS['not_matched_criteria_action'] == 'Create Lead'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_CREATE_LEAD', $MODULE_NAME)}</option>
								<option value="Ignore" {if $CONFIGS['not_matched_criteria_action'] == 'Ignore'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_IGNORE', $MODULE_NAME)}</option>
							</select>
							<span>&nbsp;(3)</span>
						</div>
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_DUPLICATED_MATCHED_CRITERIA', $MODULE_NAME)}:</div>
						<div class="formValue">
							<select class="select2 inputElement" data-name="existed_customer_match_criteria" name="configs[existed_customer_match_criteria]">
								<option value="Convert" {if $CONFIGS['existed_customer_match_criteria'] == 'Convert'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_CONVERT', $MODULE_NAME)}</option>
								<option value="Ignore" {if $CONFIGS['existed_customer_match_criteria'] == 'Ignore'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_IGNORE', $MODULE_NAME)}</option>
							</select>
							<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
								<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
									<div class="custom-popover-content" style="display: none">
										<i><b>{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_CONVERT', $MODULE_NAME)}</b>: {vtranslate('LBL_SYNC_CUSTOMER_INFO_DUPLICATED_MATCHED_CRITERIA_CONVERT_DESCRIPTION', $MODULE_NAME)}</i></br>
										<i><b>{vtranslate('LBL_SYNC_CUSTOMER_INFO_IGNORE', $MODULE_NAME)}</b>: {vtranslate('LBL_SYNC_CUSTOMER_INFO_DUPLICATED_MATCHED_CRITERIA_IGNORE_DESCRIPTION', $MODULE_NAME)}</i>
									</div>
								</i>
							</div>
						</div>
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_DUPLICATED_DETECTED_ACTION', $MODULE_NAME)}:</div>
						<div class="formValue">
							<select class="select2 inputElement" data-name="duplicated_action" name="configs[duplicated_action]">
								<option value="Update" {if $CONFIGS['duplicated_action'] == 'Update'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_UPDATE', $MODULE_NAME)}</option>
								<option value="Override" {if $CONFIGS['duplicated_action'] == 'Override'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_OVERRIDE', $MODULE_NAME)}</option>
								<option value="Ignore" {if $CONFIGS['duplicated_action'] == 'Ignore'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_IGNORE', $MODULE_NAME)}</option>
							</select>
							<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
								<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
									<div class="custom-popover-content" style="display: none">
										<i><b>{vtranslate('LBL_SYNC_CUSTOMER_INFO_UPDATE', $MODULE_NAME)}</b>: {vtranslate('LBL_SYNC_CUSTOMER_INFO_UPDATE_DESCRIPTION', $MODULE_NAME)}</i></br>
										<i><b>{vtranslate('LBL_SYNC_CUSTOMER_INFO_OVERRIDE', $MODULE_NAME)}</b>: {vtranslate('LBL_SYNC_CUSTOMER_INFO_OVERRIDE_DESCRIPTION', $MODULE_NAME)}</i></br>
										<i><b>{vtranslate('LBL_SYNC_CUSTOMER_INFO_IGNORE', $MODULE_NAME)}</b>: {vtranslate('LBL_SYNC_CUSTOMER_INFO_IGNORE_DESCRIPTION', $MODULE_NAME)}</i>
									</div>
								</i>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="user-distribution" class="tab-pane fade">
				<div class="editViewContents">
					<div class="fieldBlockContainer">
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_ASSIGNERS_LABEL', $MODULE_NAME)}:</div>
						<div class="formValue">
							<div class="distribution-assigned-users-wrapper">
								<input type="text"
									data-name="assigners_distribution"
									multiple="true"
									class="assigned-users inputElement"
									data-assignableUsersOnly="true"
									name="configs[assigners_distribution]"
									{if !empty($CONFIGS['assigners_distribution'])} 
										data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIGS['assigners_distribution']))}'
									{/if}
								/>
								<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
									<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
										<div class="custom-popover-content" style="display: none">
											{vtranslate('LBL_SYNC_CUSTOMER_INFO_ASSIGNERS_DESCRIPTION', $MODULE_NAME)}
										</div>
									</i>
								</div>
							</div>
						</div>
						<div class="formCell">{vtranslate('LBL_SYNC_CUSTOMER_INFO_DISTRIBUTION_METHOD_LABEL', $MODULE_NAME)}:</div>
						<div class="formValue">
							<select class="select2 inputElement" data-name="distribution_method" name="configs[distribution_method]">
								<option value="round_robin" {if $CONFIGS['distribution_method'] == 'round_robin'}selected{/if}>{vtranslate('LBL_SYNC_CUSTOMER_INFO_DISTRIBUTION_ROUND_ROBIN', $MODULE_NAME)}</option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div id="auto-ticket" class="tab-pane fade">
				<div class="editViewContents">
					<div class="fieldBlockContainer">
						{if !isForbiddenFeature('CaptureTicketsViaLeadCapture')}
							<div class="formValue">
								<div class="checkbox-wraper">
									<input type="checkbox" name="configs[auto_create_ticket_for_lead_capture]" class="bootstrap-switch hide"  {if $CONFIGS['auto_create_ticket_for_lead_capture'] eq '1'}checked{/if} />
								</div>
								<div class="checkbox-label">
									{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_LANDING_PAGE', $MODULE_NAME)}
								</div>
								<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
									<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
										<div class="custom-popover-content" style="display: none">
											{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_LANDING_PAGE_DESCRIPTION', $MODULE_NAME)}
										</div>
									</i>
								</div>
							</div>
						{/if}

						{if !isForbiddenFeature('ZaloIntegration')}
							<div class="formValue">
								<div class="checkbox-wraper">
									<input type="checkbox" name="configs[auto_create_ticket_for_zalo_oa]" class="bootstrap-switch hide"  {if $CONFIGS['auto_create_ticket_for_zalo_oa'] eq '1'}checked{/if} />
								</div>
								<div class="checkbox-label">
									{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_ZALO_OA', $MODULE_NAME)}
								</div>
								<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
									<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
										<div class="custom-popover-content" style="display: none">
											{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_ZALO_OA_DESCRIPTION', $MODULE_NAME)}
										</div>
									</i>
								</div>
							</div>
						{/if}

						{if !isForbiddenFeature('ZaloIntegration') && !isForbiddenFeature('ModuleCPZaloAdsForm')}
							<div class="formValue">
								<div class="checkbox-wraper">
									<input type="checkbox" name="configs[auto_create_ticket_for_zalo_ads_form]" class="bootstrap-switch hide"  {if $CONFIGS['auto_create_ticket_for_zalo_ads_form'] eq '1'}checked{/if} />
								</div>
								<div class="checkbox-label">
									{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_ZALO_ADS_FORM', $MODULE_NAME)}
								</div>
								<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
									<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
										<div class="custom-popover-content" style="display: none">
											{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_ZALO_ADS_FORM_DESCRIPTION', $MODULE_NAME)}
										</div>
									</i>
								</div>
							</div>
						{/if}

						{if !isForbiddenFeature('CaptureTicketsViaChatbot')}
							<div class="formValue">
								<div class="checkbox-wraper">
									<input type="checkbox" name="configs[auto_create_ticket_for_chatbot]" class="bootstrap-switch hide"  {if $CONFIGS['auto_create_ticket_for_chatbot'] eq '1'}checked{/if} />
								</div>
								<div class="checkbox-label">
									{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_CHATBOT', $MODULE_NAME)}
								</div>
								<div class="custom-popover-wrapper" style="display: inline-block; padding: 2px 4px" title="{vtranslate('Description')}">
									<i class="far fa-question-circle tooltip-helper custom-popover" aria-hidden="true">
										<div class="custom-popover-content" style="display: none">
											{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_CHATBOT_DESCRIPTION', $MODULE_NAME)}
										</div>
									</i>
								</div>
							</div>
						{/if}

						{* <div class="formValue">
							<div class="checkbox-wraper">
								<input type="checkbox" name="configs[auto_create_ticket_for_facebook_ads_form]" class="bootstrap-switch hide"  {if $CONFIGS['auto_create_ticket_for_facebook_ads_form'] eq '1'}checked{/if} />
							</div>
							<div class="checkbox-label">
								{vtranslate('LBL_SYNC_CUSTOMER_INFO_AUTO_TICKET_FACEBOOK_ADS_FORM', $MODULE_NAME)}
							</div>
						</div> *}
					</div>
				</div>
			</div>
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