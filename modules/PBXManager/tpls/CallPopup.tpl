{* Added by Phu Vo on 2019.12.31 *}

{strip}
    {if PBXManager_Config_Helper::isCallCenterEnabled()}
        {* Refactored by Hieu Nguyen on 2022-08-01 *}
        {assign var='FULL_NAME_CONFIG' value=getGlobalVariable('fullNameConfig')}
        {assign var=CURRENT_USER value=vglobal('current_user')}
        {assign var=CURRENT_USER_ID value=$CURRENT_USER->id}
        {* End Hieu Nguyen *}

        <div id="callCenterPackage">
            {* Call Popup *}
            <div id="callCenterTemplate" style="display: none">
				{* Modified by Vu Mai on 2022-09-08 to refactor layout *}
                <div id="callTemplate" class="call-popup">
					<div class="left-side">
						<div class="call-header">
							<div class="customer-avatar">
								<img class="cir-ava fa-ava fa-to-phone fa-fix" src="resources/images/no_ava.png" data-ui="customer_avatar"/>
								<div class="cir-ava fa-ava fa-to-phone fa-fix account-ava" style="display: none">
									<i class="far fa-building"></i>
								</div>
								<div class="abs-animate"><i class="cc-animate border-bound animate-cir"></i></div>
							</div>
							<div class="customer-summary">
								<div class="flex">
									<h3 class="info-name" data-ui="customer_name" data-ui-title="true"></h3>
									<button class="edit-customer-info"><i class="fa-regular fa-pen-to-square"></i></button>
								</div>
								<h4 class="info-company close-stage" data-ui="account_name" data-ui-title="true"></h4> 
								<h3 class="info-title" data-ui="subject" data-parser="callTitleParser" data-ui-title="true"></h3>
								<h4 class="info-number" data-ui-title="true">
									<span class="customer-number" data-ui="customer_number"></span>
									<i class="far fa-exclamation-triangle warning-free-call" data-toggle="tooltip" title="{vtranslate('LBL_CALL_POPUP_FROM_FREE_WEB_BTN_WARNING', 'PBXManager')}" aria-hidden="true"></i>
								</h4>
								<h5 class="info-call-direction" data-ui="direction"></h5>
								<h4 class="info-company" data-ui="account_name" data-ui-title="true"></h4> 
								<h4 class="hotline-container hotline-container-ringing">{vtranslate('LBL_CALL_POPUP_HOTLINE', 'PBXManager')}: <span class="hotline" data-ui="hotline"></span></h4>
								<div class="assign">
									<i class="far fa-user" aria-hidden="true"></i>
									<span> </span>
									<span class="assign-name" data-ui="assigned_user_name" data-ui-title="true"></span>
									<span> </span>
									<span class="ext-num" data-ui="assigned_user_ext"></span>
								</div>
							</div>
							<div class="call-status">
								<div class="popup-actions">
									<button name="restore"><i class="far fa-external-link-square" aria-hidden="true"></i></button>
									<button name="minimize"><i class="far fa-window-minimize" aria-hidden="true"></i></button>
									<button name="maximize"><i class="far fa-window-maximize" aria-hidden="true"></i></button>
									<button name="normalmize"><i class="far fa-window-restore" aria-hidden="true"></i></button>
									<button name="close"><i class="far fa-times" aria-hidden="true"></i></button>
								</div>
								<div class="call-direction-wraper">
									<h4 class="call-direction" data-ui="direction"></h4>
									<div class="hotline-container"><i class="fal fa-phone-office hotline-tooltip" data-toggle="tooltip"></i></div>
								</div>
								<div class="timer">
									<div class="time">
										<div class="durHour" data-ui="duration" data-parser="callDurationHours">00</div>
										<div class="durMin" data-ui="duration" data-parser="callDurationMinutes">00</div>
										<div class="durSec" data-ui="duration" data-parser="callDurationSeconds">00</div>
									</div>
									<div class="time-description">
										<div class="hour">{vtranslate('LBL_CALL_POPUP_HOURS', 'PBXManager')}</div>
										<div class="min">{vtranslate('LBL_CALL_POPUP_MINUTES', 'PBXManager')}</div>
										<div class="sec">{vtranslate('LBL_CALL_POPUP_SECONDS', 'PBXManager')}</div>
									</div>
								</div>
								<div class="connection-status"><i class="fas fa-circle" aria-hidden="true"></i> <span data-ui="state" data-parser="callStateMapping" class="call-state"></span></div>
							</div>
							<div class="extra-info"></div>
							{* Added by Vu Mai on 2022-09-06 to include custom tag layout *}
							{include file="modules/Vtiger/tpls/CustomTag.tpl"}
							{* End Vu Mai *}
						</div>
						<div class="call-body">
							<ul class="call-tabs fancyScrollbar" data-tabs="call">
								<li class="call-tab active" data-tab="call-log">{vtranslate('LBL_CALL_POPUP_LOG_CALL', 'PBXManager')}</li>
								<li class="call-tab" data-tab="call-list" data-trigger="ajax-view">
									<span>{vtranslate('LBL_CALL_POPUP_CALL', 'PBXManager')}</span>
									<span class="counter" data-ui="call_list_count" data-parser="counterParser"></span>
								</li>
								<li class="call-tab contactsOnly" data-tab="salesorder-list" data-trigger="ajax-view">
									<span>{vtranslate('LBL_CALL_POPUP_SALES_ORDER', 'PBXManager')}</span>
									<span class="counter" data-ui="salesorder_list_count" data-parser="counterParser"></span>
								</li>
								<li class="call-tab contactsOnly" data-tab="ticket-list" data-trigger="ajax-view">
									<span>{vtranslate('LBL_CALL_POPUP_TICKET', 'PBXManager')}</span>
									<span class="counter" data-ui="ticket_list_count" data-parser="counterParser"></span>
								</li>
								<li class="call-tab accountsOnly" data-tab="salesorder-list" data-trigger="ajax-view">
									<span>{vtranslate('LBL_CALL_POPUP_SALES_ORDER', 'PBXManager')}</span>
									<span class="counter" data-ui="salesorder_list_count" data-parser="counterParser"></span>
								</li>
								<li class="call-tab accountsOnly" data-tab="ticket-list" data-trigger="ajax-view">
									<span>{vtranslate('LBL_CALL_POPUP_TICKET', 'PBXManager')}</span>
									<span class="counter" data-ui="ticket_list_count" data-parser="counterParser"></span>
								</li>
								{* Added by Vu Mai on 2022-09-08 to add comment tab header *}
								<li class="call-tab" data-tab="comment-list" data-trigger="ajax-view">
									<span>{vtranslate('LBL_COMMENT')}</span>
									<span class="counter" data-ui="comment_list_count" data-parser="counterParser"></span>
								</li>
								{* End Vu Mai *}
								<li class="call-tab" data-tab="faq-list">{vtranslate('LBL_CALL_POPUP_FAQS', 'PBXManager')}</li>
							</ul>
							<div class="call-tab-content main-form-container" data-tabs="call">
								<div class="call-tab-pane main-form fancyScrollbar" data-tab="call-log">
									<form class="form-horizontal callLog" name="call_log" data-fetch-customer-info="false">
										<input type="hidden" name="module" value="PBXManager" />
										<input type="hidden" name="action" value="CallPopupAjax" />
										<input type="hidden" name="mode" value="saveCallLog" />
										<input type="hidden" name="pbx_call_id" data-ui="call_id" />
										<input type="hidden" name="call_log_id" data-ui="call_log_id" />
										<input type="hidden" name="start_time" data-ui="start_time" />
										<input type="hidden" name="end_time" data-ui="end_time" />
										<input type="hidden" name="customer_id" data-ui="customer_id" />
										<input type="hidden" name="customer_type" data-ui="customer_type" />
										<input type="hidden" name="direction" data-ui="direction" data-parser="raw"/>
										<input type="hidden" name="account_id" data-ui="account_id" />
										<input type="hidden" name="account_id_raw" data-ui="account_id" />
										{* <section class="wraper note">
											<input name="subject" data-ui="subject" data-parser="raw" data-onchange="subject" class="call-center-title" placeholder="{vtranslate('LBL_CALL_POPUP_SUBJECT', 'PBXManager')} *" spellcheck="false" autocomplete="off" data-rule-required="true" />
											<textarea name="description" class="call-center-note" placeholder="{vtranslate('LBL_CALL_POPUP_DESCRIPTION', 'PBXManager')}" spellcheck="false" autocomplete="off" data-ui="description" data-onchange="description"></textarea>
										</section> *}
										<table class="table no-border fieldBlockContainer">
											<tr class="accountsOnly">
												<td class="fieldLabel"><label>{vtranslate('SINGLE_Contacts', 'Contacts')}</label></td>
												<td class="fieldValue relatedContactId">
													<div class="referencefield-wrapper ">
														<input name="popupReferenceModule" type="hidden" value="Contacts">
														<div class="input-group">
															<input name="contact_id" type="hidden" value="" class="sourceField" data-displayvalue="">
															<input id="contact_id_display" name="contact_id_display" data-fieldname="contact_id" data-fieldtype="reference" type="text" class="marginLeftZero autoComplete inputElement ui-autocomplete-input" value="" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" autocomplete="off">
															<a href="#" class="clearReferenceSelection hide"><i class="far fa-times-circle"></i></a>
															<span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT')}">
																<i id="Events_editView_fieldName_contact_id_select" class="far fa-search"></i>
															</span>
														</div>
														<span class="cursorPointer clearfix quickCreateBtn" title="{vtranslate('LBL_CREATE')}" data-module="Contacts">
															<i id="Events_editView_fieldName_contact_id_create" class="far fa-plus"></i>
														</span>
													</div>
												</td>
											</tr>
											<tr class="forInbound">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_INBOUND_CALL_PURPOSE', 'PBXManager')}{if isMandatory('events_inbound_call_purpose', 'Events')} <span class="redColor">*</span>{/if}</label>
												</td>
												<td class="fieldValue inputGroup">
													{assign var=EVENTS_INBOUND_CALL_PURPOSE value=Vtiger_Util_Helper::getPickListValues('events_inbound_call_purpose')}
													<select name="events_inbound_call_purpose"
														class="inputElement select2"
														{if isMandatory('events_inbound_call_purpose', 'Events')}data-rule-required="true"{/if}
													>
														<option value="">{vtranslate('LBL_CALL_POPUP_DROPDOWN_SELECT_AN_OPTION', 'PBXManager')}</option>
														{foreach from=$EVENTS_INBOUND_CALL_PURPOSE item=purpose}
															<option value="{$purpose}">{vtranslate($purpose, 'Events')}</option>
														{/foreach}
													</select>
													<input type="text"
														name="events_inbound_call_purpose_other"
														data-other-purpose="true"
														class="inputElement toggleOnInboundPurposeOther"
														placeholder="{vtranslate('LBL_EVENTS_INBOUND_CALL_PURPOSE_OTHER', 'Events')}"
														data-rule-required="true"
													/>
												</td>
											</tr>
											<tr class="forOutbound">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_CALL_OUTBOUND_PURPOSE', 'PBXManager')}{if isMandatory('events_call_purpose', 'Events')} <span class="redColor">*</span>{/if}</label>
												</td>
												<td class="fieldValue inputGroup">
													{assign var=EVENTS_CALL_PURPOSE value=Vtiger_Util_Helper::getPickListValues('events_call_purpose')}
													<select name="events_call_purpose"
														class="inputElement select2"
														{if isMandatory('events_call_purpose', 'Events')}data-rule-required="true"{/if}
													>
														<option value="">{vtranslate('LBL_CALL_POPUP_DROPDOWN_SELECT_AN_OPTION', 'PBXManager')}</option>
														{foreach from=$EVENTS_CALL_PURPOSE item=purpose}
															<option value="{$purpose}">{vtranslate($purpose, 'Events')}</option>
														{/foreach}
													</select>
													<input type="text" name="events_call_purpose_other" data-other-purpose="true" class="inputElement toggleOnPurposeOther" placeholder="{vtranslate('LBL_EVENTS_CALL_PURPOSE_OTHER', 'Events')}" data-rule-required="true" />
												</td>
											</tr>
											<tr>
												<td class="fieldLabel subject">
													<label>{vtranslate('Subject', 'Events')}{if isMandatory('subject', 'Events')} <span class="redColor">*</span>{/if}</label>
												</td>
												<td class="fieldValue subject">
													<input type="text"
														{if isMandatory('subject', 'Events')}data-rule-required="true"{/if}
														name="subject" data-ui="subject"
														data-parser="raw"
														data-onchange="subject"
														class="inputElement"
													/>
												</td>
											</tr>
											<tr>
												<td class="fieldLabel description">
													<label>{vtranslate('Description', 'Events')}{if isMandatory('description', 'Events')} <span class="redColor">*</span>{/if}</label>
												</td>
												<td class="fieldValue description">
													<textarea name="description" class="inputElement" data-ui="description" data-onchange="description" {if isMandatory('description', 'Events')}data-rule-required="true"{/if}></textarea>
												</td>
											</tr>
											<tr>
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_SAVING_MODE', 'PBXManager')}{if isMandatory('visibility', 'Events')} <span class="redColor">*</span>{/if}</label>
												</td>
												<td class="fieldValue">
													{assign var=VISIBILITY value=Calendar_Module_Model::getSharedType($CURRENT_USER_ID)}
													<label><input type="radio" name="visibility" value="Public" {if $VISIBILITY neq 'private'}checked{/if} {if isMandatory('visibility', 'Events')}data-rule-required="true"{/if} /> <span>{vtranslate('LBL_CALL_POPUP_SAVING_PUBLIC', 'PBXManager')}</span></label>
													<span>&nbsp&nbsp</span>
													<label><input type="radio" name="visibility" value="Private" {if $VISIBILITY eq 'private'}checked{/if} {if isMandatory('visibility', 'Events')}data-rule-required="true"{/if} /> <span>{vtranslate('LBL_CALL_POPUP_SAVING_PRIVATE', 'PBXManager')}</span></label>
												</td>
											</tr>
											<tr>
												<td class="fieldLabel">
													<label class="call_result_label">{vtranslate('LBL_CALL_POPUP_CALL_RESULT', 'PBXManager')} {if isMandatory('events_call_result', 'Events')}<span class="redColor">*</span>{/if}</label>	{* Modified by Vu Mai on 2022-12-20 to add class for call result label*}
												</td>
												<td class="fieldValue">
													{assign var=EVENTS_CALL_RESULT value=Vtiger_Util_Helper::getPickListValues('events_call_result')}
													<select name="events_call_result"
														class="inputElement select2"
														data-onchange="events_call_result"
														{if isMandatory('events_call_result', 'Events')}data-rule-required="true"{/if}
													>
														<option value="">{vtranslate('LBL_CALL_POPUP_DROPDOWN_SELECT_AN_OPTION', 'PBXManager')}</option>
														{foreach from=$EVENTS_CALL_RESULT item=result}
															<option value="{$result}">{vtranslate($result, 'Events')}</option>
														{/foreach}
													</select>
												</td>
											</tr>
											<tr class="toggleCallResultCallBack">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_CALL_BACK_TIME', 'PBXManager')}</label>
												</td>
												<td class="fieldValue">
													<div class="call-back-time disableBaseOnTimeOther">
														<div class="input-group select-time inputElement">
															<input name="select_time" class="inputElement" />
															<span class="input-group-addon" style="width: 30px;"><i class="far fa-clock"></i></span>
														</div>
														<div class="input-wraper select-moment">
															<input name="select_moment" class="inputElement" />
														</div>
													</div>
													<div class="call-back-time-other">
														<div class="call-back-time-other-toggle">
															<label><input type="checkbox" name="call_back_time_other" /> {vtranslate('LBL_CALL_POPUP_CALL_BACK_OTHER_TIME', 'PBXManager')}</label>
														</div>
														<div class="call-back-time-other-content activeBaseOnTimeOther">
															<div class="inlineOnLarge">
																<div class="input-group input-wraper date-start inputElement" style="margin-bottom: 3px">
																	<input type="text" class="dateField form-control" name="date_start" data-rule-required="true" data-date-format="{$CURRENT_USER->date_format}" />
																	<span class="input-group-addon"><i class="far fa-calendar "></i></span>
																</div>
															</div>
															<div class="inlineOnLarge">
																<div class="input-group input-wraper time-start inputElement time">
																	<input type="text" class="timepicker-default form-control ui-timepicker-input" name="time_start" data-rule-required="true" />
																	<span class="input-group-addon" style="width: 30px;"><i class="far fa-clock"></i></span>
																</div>
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr class="toggleCallResultCustomerInterested">
												<td class="fieldLabel name">
													<label>{vtranslate('LBL_CALL_POPUP_NAME', 'PBXManager')}</label>
												</td>
												<td class="fieldValue name">
													{assign var=SALUTATIONTYPES value=Vtiger_Util_Helper::getPickListValues('salutationtype')}
													<select name="salutationtype" class="inputElement select2">
														<option value="">{vtranslate('LBL_CALL_POPUP_SALUTATION_TYPE_PLACEHOLDER', 'PBXManager')}</option>
														{foreach from=$SALUTATIONTYPES item=salutationtype}
															<option value="{$salutationtype}">{vtranslate($salutationtype, 'Contacts')}</option>
														{/foreach}
													</select>
													{if $FULL_NAME_CONFIG['full_name_order'][0] == 'firstname'}
														<input type="text" name="firstname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'firstname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_FIRST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'firstname'} *{/if}" />
														<input type="text" name="lastname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'lastname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_LAST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'lastname'} *{/if}" />
													{else}
														<input type="text" name="lastname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'lastname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_LAST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'lastname'} *{/if}" />
														<input type="text" name="firstname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'firstname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_FIRST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'firstname'} *{/if}" />
													{/if}
												</td>
											</tr>
											<tr class="toggleCallResultCustomerInterested">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_PHONE_NUMBER', 'PBXManager')}</label>
												</td>
												<td class="fieldValue">
													<input type="text" name="mobile_phone" data-ui="customer_number" class="inputElement" />
												</td>
											</tr>
											<tr class="toggleCallResultCustomerInterested">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_EMAIL', 'PBXManager')}</label>
												</td>
												<td class="fieldValue">
													<input type="text" class="inputElement" name="email" data-rule-email="true" />
												</td>
											</tr>
											<tr class="toggleCallResultCustomerInterested account_id">
												<td class="fieldLabel">
													<input class="hidden" type="hidden" name="module" value="Contacts" />
													<label>{vtranslate('LBL_CALL_POPUP_ACCOUNT_NAME', 'PBXManager')}</label>
												</td>
												<td class="fieldValue controls">
													<div class="referencefield-wrapper forContacts">
														<input name="popupReferenceModule" type="hidden" value="Accounts">
														<div class="input-group account_id">
															<input name="account_id" type="hidden" value="" class="sourceField" data-displayvalue="" required>
															<input name="account_id_display" 
																data-fieldname="account_id" data-fieldtype="reference" type="text" 
																class="marginLeftZero autoComplete inputElement ui-autocomplete-input" value="" 
																placeholder="{vtranslate('LBL_TYPE_SEARCH')}" autocomplete="off"
															>
															<a href="#" class="clearReferenceSelection hide"><i class="far fa-times-circle"></i></a>
															<span class="input-group-addon relatedPopup cursorPointer" title="Select"><i id="Contacts_editView_fieldName_account_id_select" class="far fa-search"></i></span>
														</div>
														<span class="createReferenceRecord cursorPointer clearfix" title="Thêm mới"><i id="Contacts_editView_fieldName_account_id_create" class="far fa-plus"></i></span>
													</div>
													<div class="forLeads forDefault">
														<input type="text" name="company" class="inputElement" />
													</div>
												</td>
											</tr>
											<tr class="toggleCallResultCustomerInterested">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_SELECT_PRODUCTS', 'PBXManager')}</label>
												</td>
												<td class="fieldValue product_ids">
													<input type="hidden" name="product_ids" class="inputElement select2"/>
												</td>
											</tr>
											<tr class="toggleCallResultCustomerInterested">
												<td class="fieldLabel">
													<label>{vtranslate('LBL_CALL_POPUP_SELECT_SERVICES', 'PBXManager')}</label>
												</td>
												<td class="fieldValue service_ids">
													<input type="hidden" name="service_ids" class="inputElement select2"/>
												</td>
											</tr>
										</table>
									</form>
								</div>
								<div class="call-tab-pane related-tab" data-tab="call-list" data-module="Calendar" data-activity-type="Call">
									<div class="call-tab-content fancyScrollbar"></div>
									<div class="call-tab-empty"><div class="content">{vtranslate('LBL_CALL_POPUP_ON_POPUP_ERROR_MESSAGE', 'PBXManager')}</div></div>
									<div class="call-tab-loading"><i class="far fa-refresh fa-spin"></i></div>
								</div>
								<div class="call-tab-pane related-tab" data-tab="salesorder-list" data-module="SalesOrder">
									<div class="call-tab-content fancyScrollbar"></div>
									<div class="call-tab-empty"><div class="content">{vtranslate('LBL_CALL_POPUP_ON_POPUP_ERROR_MESSAGE', 'PBXManager')}</div></div>
									<div class="call-tab-loading"><i class="far fa-refresh fa-spin"></i></div>
								</div>
								<div class="call-tab-pane related-tab" data-tab="ticket-list" data-module="HelpDesk">
									<div class="call-tab-content fancyScrollbar"></div>
									<div class="call-tab-empty"><div class="content">{vtranslate('LBL_CALL_POPUP_ON_POPUP_ERROR_MESSAGE', 'PBXManager')}</div></div>
									<div class="call-tab-loading"><i class="far fa-refresh fa-spin"></i></div>
								</div>
								{* Added by Vu Mai on 2022-09-08 to add comment tab content *}
								<div class="call-tab-pane comment-tab related-tab" data-tab="comment-list">
									<div class="call-tab-content fancyScrollbar"></div>
									<div class="call-tab-empty"><div class="content">{vtranslate('LBL_CALL_POPUP_ON_POPUP_ERROR_MESSAGE', 'PBXManager')}</div></div>
									<div class="call-tab-loading"><i class="far fa-refresh fa-spin"></i></div>
								</div>
								{* End Vu Mai *}
								<div class="call-tab-pane faq-tab" data-tab="faq-list" data-module="Faq">
									<div class="faq-tab-content fancyScrollbar">
										<form class="filter-form">
											<table class="table no-border">
												<tr>
													<td class="fieldValue col-lg-12">
														<div class="input-group">
															<input type="text" name="keyword" class="inputElement" placeholder="{vtranslate('LBL_CALL_POPUP_SEARCH_FAQ_PLACEHOLDER', 'PBXManager')}" data-onchange="faq_keyword" />
															<span class="input-group-addon searchButton cursorPointer" title="Search">
																<i class="far fa-search"></i>
															</span>
														</div>
													</td>
												</tr>
											</table>
										</form>
										<div class="faq-result-display">
											<div class="faq-tab-result-content fancyScrollbar"></div>
											<div class="faq-tab-empty"><div class="content">{vtranslate('LBL_CALL_POPUP_ON_POPUP_ERROR_MESSAGE', 'PBXManager')}</div></div>
											<div class="faq-tab-loading"><i class="far fa-refresh fa-spin"></i></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="call-footer footer">
							<div class="quickcreate-btns">
								<div class="quickcreate-customer">
									<button class="btn btn-default createCustomer">{vtranslate('LBL_CALL_POPUP_CREATE_CUSTOMER', 'PBXManager')}</button>
								</div>
								{* Modified by Vu Mai on 2022-09-07 to update dropdown item and list sort *}
								<div class="btn-group dropup quickcreate-related">
									<button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="far fa-ellipsis-v"></i></button>
									<ul class="dropdown-menu">
										{* Modified by Hieu Nguyen on 2022-09-06 to display create buttons based on user permission *}
										{if Calendar_Module_Model::canCreateActivity('Call')}
											<li class="dropdown-item">
												<a href="javascript:void(0)" class="quickCreateBtn" data-module="Events" data-activity="Call">{vtranslate('Call', 'Calendar')}</a>
											</li>
										{/if}
										{if Calendar_Module_Model::canCreateActivity('Meeting')}
											<li class="dropdown-item" data-module="Events" data-activity="Meeting">
												<a href="javascript:void(0)" class="quickCreateBtn" data-module="Events" data-activity="Meeting">{vtranslate('Meeting', 'Calendar')}</a>
											</li>
										{/if}
										{if Calendar_Module_Model::canCreateActivity('Task')}
											<li class="dropdown-item">
												<a href="javascript:void(0)" class="quickCreateBtn" data-module="Calendar">{vtranslate('Task', 'Calendar')}</a>
											</li>
										{/if}
										{* End Hieu Nguyen *}

										{* Modified by Hieu Nguyen on 2021-08-20 to check if this feature can be displayed *}
										{if !isForbiddenFeature('CaptureTicketsViaCallCenter')}
											<li class="dropdown-item">
												<a href="javascript:void(0)" class="quickCreateBtn" data-module="HelpDesk">{vtranslate('SINGLE_HelpDesk', 'HelpDesk')}</a>
											</li>
										{/if}
										{* End Hieu Nguyen *}

										<li class="dropdown-item createPotential contactsOnly">
											<a href="javascript:void(0)" class="quickCreateBtn" data-module="Potentials">{vtranslate('SINGLE_Potentials', 'Potentials')}</a>
										</li>
										<li class="dropdown-item contactsOnly salesorder-hide-option telesales-hide-option">
											<a href="javascript:void(0)" class="quickCreateBtn" data-module="SalesOrder">{vtranslate('SINGLE_SalesOrder', 'SalesOrder')}</a>
										</li>

										{if !isForbiddenFeature('SendMessageViaSMS')}
											<li class="dropdown-item btn-send-msg">
												<a onclick="javascript:void(0)" data-action-url="{getMassActionUrl("send_sms_ott", "_MODULE_")}" data-channel="SMS">{vtranslate('LBL_SEND_SMS')}</a>
											</li>
										{/if}

										<li class="dropdown-item btn-send-msg">
											<a onclick="javascript:void(0)" data-action-url="{getMassActionUrl("send_email", "_MODULE_")}">{vtranslate('LBL_SEND_EMAIL')}</a>
										</li>

										{if !isForbiddenFeature('SendMessageViaZaloZNS')}
											<li class="dropdown-item btn-send-msg">
												<a onclick="javascript:void(0)" data-action-url="{getMassActionUrl("send_sms_ott", "_MODULE_")}" data-channel="Zalo">{vtranslate('LBL_SEND_ZALO_OTT_MESSAGE')}</a>
											</li>
										{/if}
										
										{if !isForbiddenFeature('SendMessageViaZaloOA') && CPSocialIntegration_Config_Helper::isZaloMessageAllowed()}
											<li class="dropdown-item btn-send-msg">
												<a onclick="javascript:void(0);" data-type="Social" data-channel="Zalo">{vtranslate('LBL_SOCIAL_INTEGRATION_SEND_ZALO_MESSAGE')}</a>
											</li>
										{/if}

										<li class="dropdown-item accountsOnly">
											<a href="javascript:void(0)" class="quickCreateBtn" data-module="Quotes">{vtranslate('SINGLE_Quotes', 'Quotes')}</a>
										</li>
										<li class="dropdown-item accountsOnly">
											<a href="javascript:void(0)" class="quickCreateBtn" data-module="SalesOrder">{vtranslate('SINGLE_SalesOrder', 'SalesOrder')}</a>
										</li>
									</ul>
								</div>
								{* End Vu Mai *}

								{* Added by Vu Mai on 2022-05-10 to add buttons quick edit target record beside button more *}
								<button class="btn btn-default edit-target-info helpdesk-only" data-target="HelpDesk">
									<i class="far fa-pen mr-2"></i>
									{vtranslate('LBL_CALL_POPUP_EDIT_HELPDESK', 'PBXManager')}
								</button>
								<button class="btn btn-default edit-target-info salesorder-only" data-target="SalesOrder">
									<i class="far fa-pen mr-2"></i>
									{vtranslate('LBL_CALL_POPUP_EDIT_SALESORDER', 'PBXManager')}
								</button>
								<button class="btn btn-default edit-target-info potentials-only" data-target="Potentials">
									<i class="far fa-pen mr-2"></i>
									{vtranslate('LBL_CALL_POPUP_EDIT_POTENTIALS', 'PBXManager')}
								</button>
								{* End Vu Mai *}
							</div>
							<div class="handle-btns">
								<div class="btn-wrapper answerBtn-wrapper">
									<button class="btn btn-success answerBtn round-btn"><i class="far fa-phone" aria-hidden="true"></i></button>
									<span class="btn-label">{vtranslate('LBL_CALL_POPUP_ANSWER_PHONE', 'PBXManager')}</span>
								</div>
								<div class="btn-wrapper endCallBtn-wrapper decline">
									<button class="btn btn-danger endCallBtn decline round-btn"><span><img class="icon-image" src="modules/PBXManager/resources/images/call_end.png" width="16" /></span></button>
									<span class="btn-label">{vtranslate('LBL_CALL_POPUP_DECLINE_PHONE', 'PBXManager')}</span>
								</div>
								<div class="btn-wrapper endCallBtn-wrapper hangup">
									<button class="btn btn-danger endCallBtn hangup round-btn"><span><img class="icon-image" src="modules/PBXManager/resources/images/call_end.png" width="16" /></span></button>
									<span class="btn-label">{vtranslate('LBL_CALL_POPUP_HANGUP_PHONE', 'PBXManager')}</span>
								</div>
							</div>
							<div class="extra-btns">
								<button class="btn btn-default showTransferCallModal" title="{vtranslate('LBL_CALL_POPUP_TRANSFER_CALL_DESCRIPTION', 'PBXManager')}"><img src="modules/PBXManager/resources/images/transfer-call.png" width="16" /> {vtranslate('LBL_CALL_POPUP_TRANSFER_CALL', 'PBXManager')}</button>
								<button class="btn btn-default muteIncommingCall" title="{vtranslate('LBL_CALL_POPUP_MUTE_CALL_DESCRIPTION', 'PBXManager')}"><img src="modules/PBXManager/resources/images/silence.png" width="14" /> {vtranslate('LBL_CALL_POPUP_MUTE_CALL', 'PBXManager')}</button>
							</div>
							<div class="action-btns">
								<button class="openFaqFullSearchPopup btn btn-default">{vtranslate('LBL_CALL_POPUP_FAQ_FULL_SEARCH_BUTTON', 'PBXManager')}</button>
								<button class="btn btn-danger endCallBtn hangup"><span><img class="icon-image" src="modules/PBXManager/resources/images/call_end.png" width="14" /></span> {vtranslate('LBL_CALL_POPUP_HANGUP_PHONE', 'PBXManager')}</button>
								<button name="close" class="urgent-close"><i class="far fa-times"></i></button>
								<button name="close" class="btn btn-danger" disabled style="display: none"><i class="far fa-close"></i> {vtranslate('LBL_CLOSE')}</button>
								
								{* Modified by Hieu Nguyen on 2021-08-20 to check if this feature can be displayed *}
								{if !isForbiddenFeature('CaptureTicketsViaCallCenter')}
									<button name="save_call_log_with_ticket" class="btn btn-success saveLogBtn">{vtranslate('LBL_CALL_POPUP_SAVE_AND_CREATE_TICKET', 'PBXManager')}</button>
								{/if}
								{* End Hieu Nguyen *}
								
								<button name="save_call_log" class="btn btn-success saveLogBtn">{vtranslate('LBL_SAVE')}</button>
							</div>
						</div>
					</div>
					<div class="right-side" style="display:none;">
						<div class="tab-header">
							<ul class="nav nav-tabs tabs">
							</ul>
						</div>
						<div class="tab-content"></div>
					</div>
                </div>
				{* End Vu Mai *}

                <div id="syncCustomerInfo" class="modal-dialog modal-lg modal-content syncCustomerPopup">
                    {assign var=HEADER_TITLE value={vtranslate('LBL_CALL_POPUP_SYNC_CUSTOMER_INFO_TITLE', 'PBXManager')}}
                    {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                    <ul class="call-tabs fancyScrollbar" data-tabs="create-customer">
                        <li class="call-tab active" data-tab="new-customer">{vtranslate('LBL_CALL_POPUP_QUICK_CREATE_CUSTOMER', 'PBXManager')}</li>
                        <li class="call-tab" data-tab="exist-customer">{vtranslate('LBL_CALL_POPUP_SEARCH_CUSTOMER', 'PBXManager')}</li>
                    </ul>
                    <div class="call-tab-content create-customer-container" data-tabs="create-customer">
                        <div class="call-tab-pane active fancyScrollbar new-customer" data-tab="new-customer">
                            <form name="quick_create" class="form-horiontal">
                                <input type="hidden" name="module" value="PBXManager" />
                                <input type="hidden" name="action" value="CallPopupAjax" />
                                <input type="hidden" name="mode" value="saveCustomer" />
                                <input type="hidden" name="pbx_call_id" />
                                <div class="form-content fancyScrollbar">
                                    <table class="table no-border fieldBlockContainer">
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_CUSTOMER_TYPE', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <label><input type="radio" name="customer_type" value="Leads" checked> {vtranslate('Leads', 'Leads')}</label>
                                                <span>&nbsp&nbsp&nbsp</span>
                                                <label><input type="radio" name="customer_type" value="Contacts"> {vtranslate('Contacts', 'Contacts')}</label>
                                            </td>
                                            <td class="fieldLabel col-lg-2"></td>
                                            <td class="fieldValue col-lg-4"></td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel salutationtype col-lg-3">
                                                <label>{vtranslate('LBL_CALL_POPUP_NAME', 'PBXManager')}</label>
                                                {assign var=SALUTATIONTYPES value=Vtiger_Util_Helper::getPickListValues('salutationtype')}
                                                <select name="salutationtype" class="inputElement select2">
                                                    <option value="">{vtranslate('LBL_CALL_POPUP_SALUTATION_TYPE_PLACEHOLDER', 'PBXManager')}</option>
                                                    {foreach from=$SALUTATIONTYPES item=salutationtype}
                                                        <option value="{$salutationtype}">{vtranslate($salutationtype, 'Contacts')}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td class="fieldValue name col-lg-3">
                                                {if $FULL_NAME_CONFIG['full_name_order'][0] == 'firstname'}
                                                    <input type="text" name="firstname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'firstname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_FIRST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'firstname'} *{/if}" />
                                                    <input type="text" name="lastname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'lastname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_LAST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'lastname'} *{/if}" />
                                                {else}
                                                    <input type="text" name="lastname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'lastname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_LAST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'lastname'} *{/if}" />
                                                    <input type="text" name="firstname" class="inputElement" {if $FULL_NAME_CONFIG['required_field'] == 'firstname'}data-rule-required="true"{/if} placeholder="{vtranslate('LBL_CALL_POPUP_CUSTOMER_FIRST_NAME', 'PBXManager')}{if $FULL_NAME_CONFIG['required_field'] == 'firstname'} *{/if}" />
                                                {/if}
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_PHONE_NUMBER', 'PBXManager')} <span class="redColor">*</span></label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" name="mobile" data-rule-required="true" class="inputElement" readonly required />
                                            </td>
                                        </tr>
                                        <tr class="toggleBaseOnContact">
                                            <td class="fieldLabel col-lg-2">
                                                <input class="hidden" type="hidden" name="module" value="Contacts" />
                                                <label>{vtranslate('LBL_CALL_POPUP_ACCOUNT_NAME', 'PBXManager')}{if isMandatory('account_id', 'Contacts')} <span class="redColor">*</span>{/if}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4 controls">
                                                <div class="referencefield-wrapper">
                                                    <input name="popupReferenceModule" type="hidden" value="Accounts">
                                                    <div class="input-group">
                                                        <input name="account_id" data-ui="account_id" type="hidden" value="" class="sourceField" data-displayvalue="" required {if isMandatory('account_id', 'Contacts')}data-rule-required="true"{/if}>
                                                        <input name="account_id_display" 
                                                            data-fieldname="account_id" data-fieldtype="reference" type="text" 
                                                            class="marginLeftZero autoComplete inputElement ui-autocomplete-input" value="" 
                                                            placeholder="{vtranslate('LBL_TYPE_SEARCH')}" autocomplete="off"
                                                        >
                                                        <a href="#" class="clearReferenceSelection hide"><i class="far fa-times-circle"></i></a>
                                                        <span class="input-group-addon relatedPopup cursorPointer" title="Select"><i id="Contacts_editView_fieldName_account_id_select" class="far fa-search"></i></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_BIRTHDAY', 'PBXManager')}{if isMandatory('birthday', 'Contacts')} <span class="redColor">*</span>{/if}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4 controls field-datepicker">
                                                <div class="input-group inputElement" style="margin-bottom: 3px">
                                                    <input type="text"name="birthday" class="form-control datePicker dateField"
                                                        autocomplete="off" {if isMandatory('birthday', 'Contacts')}data-rule-required="true"{/if}
                                                        data-date-format="{$CURRENT_USER->date_format}" data-rule-lessThanToday="true"
                                                    />
                                                    <span class="input-group-addon"><i class="far fa-calendar "></i></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_CUSTOMER_TITLE', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" class="inputElement" name="title" />
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_EMAIL', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" class="inputElement" name="email" data-rule-email="true" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_ADDRESS_STREET', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" class="inputElement" name="primary_address_street" />
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_ADDRESS_CITY', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" class="inputElement" name="primary_address_city" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_ADDRESS_STATE', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" class="inputElement" name="primary_address_state" />
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_ADDRESS_COUNTRY', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" class="inputElement" name="primary_address_country" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_DESCRIPTION', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-10" colspan="3">
                                                <textarea name="description" class="inputElement textAreaElement" style="width: 100%"></textarea>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
                            </form>
                        </div>
                        <div class="call-tab-pane fancyScrollbar" data-tab="exist-customer">
                            <form name="search_customer" class="form-horiontal">
                                <input type="hidden" name="module" value="PBXManager" />
                                <input type="hidden" name="action" value="CallPopupAjax" />
                                <input type="hidden" name="mode" value="searchCustomer" />
                                <div class="form-content fancyScrollbar">
                                    <table class="table no-border fieldBlockContainer">
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_NAME', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" name="customer_name" class="inputElement" data-rule-optional-min-length="3" />
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_PHONE_NUMBER', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" name="customer_number" class="inputElement" data-rule-optional-min-length="3" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_EMAIL', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" name="customer_email" class="inputElement" data-rule-optional-min-length="3" />
                                            </td>
                                            <td class="fieldLabel col-lg-2">
                                                <label>{vtranslate('LBL_CALL_POPUP_ADDRESS_STREET', 'PBXManager')}</label>
                                            </td>
                                            <td class="fieldValue col-lg-4">
                                                <input type="text" name="customer_address" class="inputElement" data-rule-optional-min-length="3" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="col-lg-12" style="text-align: center">
                                                <button type="submit" name="search" class="btn btn-success">{vtranslate('LBL_SEARCH')}</button>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="customerSearchResult table table-striped table-bordered" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>{vtranslate('LBL_CALL_POPUP_CUSTOMER_TYPE', 'PBXManager')}</th>
                                                <th>{vtranslate('LBL_CALL_POPUP_CUSTOMER_NAME', 'PBXManager')}</th>
                                                <th>{vtranslate('LBL_CALL_POPUP_ASSIGNED_USER_NAME', 'PBXManager')}</th>
                                                <th>{vtranslate('LBL_CALL_POPUP_ACCOUNT', 'PBXManager')}</th>
                                                <th>{vtranslate('LBL_CALL_POPUP_CUSTOMER_NUMBER', 'PBXManager')}</th>
                                                <th>{vtranslate('LBL_CALL_POPUP_SEARCH_CUSTOMER_ACTION', 'PBXManager')}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="transferCall" class="modal-dialog modal-lg modal-content transferCall">
                    {assign var=HEADER_TITLE value={vtranslate('LBL_CALL_POPUP_TRANSFER_CALL', 'PBXManager')}}
                    {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                    <div class="transfer-call-container container-fluid">
                        <form name="transfer_call" onsubmit="void(0)">
                            <table class="transfer-call-table table table-striped table-bordered" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">{vtranslate('LBL_CALL_POPUP_TRANSFER_CALL_USER_DISPLAY_NAME_HEADER', 'PBXManager')}</th>
                                        <th style="width: 20%">{vtranslate('LBL_CALL_POPUP_TRANSFER_CALL_USER_EMAIL_HEADER', 'PBXManager')}</th>
                                        <th style="width: 20%">{vtranslate('LBL_CALL_POPUP_TRANSFER_CALL_USER_ROLE_HEADER', 'PBXManager')}</th>
                                        <th style="width: 18%">{vtranslate('LBL_CALL_POPUP_TRANSFER_CALL_EXT_HEADER', 'PBXManager')}</th>
                                        <th style="width: 12%">{vtranslate('LBL_CALL_POPUP_TRANSFER_CALL_ACTIONS_HEADER', 'PBXManager')}</th>
                                    </tr>
                                    <tr>
                                        <th class="column-search-wrapper"><input class="column-search" name="display_name" /></th>
                                        <th class="column-search-wrapper"><input class="column-search" name="email" /></th>
                                        <th class="column-search-wrapper"><input class="column-search" name="role" /></th>
                                        <th class="column-search-wrapper"><input class="column-search" name="ext" /></th>
                                        <th class="column-search-wrapper" style="text-align: center">
                                            <button type="button" class="btn btn-default clearFilters" data-toggle="tooltip" title="{vtranslate('LBL_CALL_POPUP_TRANSFER_CLEAR_FILTERS', 'PBXManager')}">
                                                <i class="far fa-eraser" aria-hidden="true"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </form>
                    </div>
                    <div class="modal-footer ">
                        <center>
                            <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </center>
                    </div>
                </div>

            </div>
            <div id="callCenterContainer">
                <div class="call-popups fancyScrollbar"></div>
            </div>
        </div>

        {* Refactored by Hieu Nguyen on 2022-08-01 *}
        {assign var='CALL_CENTER_CONFIG' value=getGlobalVariable('callCenterConfig')}
        {assign var='CALL_CENTER_BRIDGE_SERVER_PROTOCOL' value="{if $CALL_CENTER_CONFIG.bridge.server_ssl}https{else}http{/if}"}
        {assign var='CALL_CENTER_BRIDGE_SERVER_URL' value="{$CALL_CENTER_BRIDGE_SERVER_PROTOCOL}://{$CALL_CENTER_CONFIG.bridge.server_name}:{$CALL_CENTER_CONFIG.bridge.server_port}"}
        {assign var='CALL_CENTER_BRIDGE_ACCESS_DOMAIN' value="{$CALL_CENTER_CONFIG.bridge.access_domain}"}
        {assign var='CALL_CENTER_BRIDGE_ACCESS_TOKEN' value="{PBXManager_Logic_Helper::getCallCenterBridgeAccessToken()}"}
        {assign var='CALL_CENTER_GATEWAY_NAME' value="{PBXManager_Logic_Helper::getGatewayName()}"}
        {assign var='CALL_CENTER_CAN_TRANSFER_CALL' value="{PBXManager_Logic_Helper::canTransferCall()}"}
        {assign var='CALL_CENTER_DEFAULT_OUTBOUND_HOTLINE' value="{PBXManager_Logic_Helper::getDefaultOutboundHotline()}"}
        {assign var='CALL_CENTER_WEB_PHONE_TOKEN' value="{PBXManager_Logic_Helper::getWebPhoneToken()}"}
        {assign var='CALL_CENTER_WEB_PHONE_CUSTOM_RING_TONE_URL' value="{PBXManager_Logic_Helper::getWebPhoneCustomRingToneUrl()}"}
        {assign var='CALL_CENTER_PREFERRED_OUTBOUND_DEVICE' value="{PBXManager_Logic_Helper::getPreferredOutboundDevice()}"}
        {assign var='CALL_CENTER_PHONE_COUNTRY_CODES' value="{Zend_Json::encode(getGlobalVariable('countryCodes'))}"}
        {assign var='CALL_CENTER_CLICK2CALL_ENABLED_MODULE' value="{Zend_Json::encode($CALL_CENTER_CONFIG.click2call_enabled_modules)}"}
        {assign var='PERSONAL_CUSTOMER_ID' value="{Accounts_Data_Helper::getPersonalAccountId()}"}
        {* End Hieu Nguyen *}

        {* [HotlineSelector] Added by Hieu Nguyen on 2021-12-15 *}
        {assign var='OUTBOUND_HOTLINES' value=PBXManager_Logic_Helper::getOutboundHotlines()}

        {if count($OUTBOUND_HOTLINES) > 1}
            <div id="click2call-hotline-selector" class="hide">
                <ul>
                    {foreach from=$OUTBOUND_HOTLINES item=HOTLINE key=KEY}
                        <li><span class="hotline">{$HOTLINE}</span>&nbsp;&nbsp;<button name="btn-select" class="btn btn-sm btn-primary btn-select" value="{$HOTLINE}">Chọn</button></li>
                    {/foreach}
                </ul>
            </div>
        {/if}
        {* End Hieu Nguyen *}
        
        <link type="text/css" rel="stylesheet" href="{vresource_url('modules/PBXManager/resources/CallPopup.css')}"/>

        <script>var _CALL_CENTER_BRIDGE_SERVER_URL = '{$CALL_CENTER_BRIDGE_SERVER_URL}';</script>
        <script>var _CALL_CENTER_BRIDGE_ACCESS_DOMAIN = '{$CALL_CENTER_BRIDGE_ACCESS_DOMAIN}';</script>
        <script>var _CALL_CENTER_BRIDGE_ACCESS_TOKEN = '{$CALL_CENTER_BRIDGE_ACCESS_TOKEN}';</script>
        <script>var _PERSONAL_CUSTOMER_ID = '{$PERSONAL_CUSTOMER_ID}';</script>

        {* [WebPhone] Added by Hieu Nguyen on 2020-04-17 *}
        <script>var _CALL_CENTER_GATEWAY_NAME = '{$CALL_CENTER_GATEWAY_NAME}';</script>
        <script>var _CALL_CENTER_CAN_TRANSFER_CALL = {if $CALL_CENTER_CAN_TRANSFER_CALL}true{else}false{/if};</script>
        <script>var _CALL_CENTER_DEFAULT_OUTBOUND_HOTLINE = '{$CALL_CENTER_DEFAULT_OUTBOUND_HOTLINE}';</script>
        <script>var _CALL_CENTER_WEB_PHONE_TOKEN = '{$CALL_CENTER_WEB_PHONE_TOKEN}';</script>
        <script>var _CALL_CENTER_WEB_PHONE_CUSTOM_RING_TONE_URL = '{$CALL_CENTER_WEB_PHONE_CUSTOM_RING_TONE_URL}';</script>
        <script>var _CALL_CENTER_PREFERRED_OUTBOUND_DEVICE = '{$CALL_CENTER_PREFERRED_OUTBOUND_DEVICE}';</script>
        <script>var _CALL_CENTER_PHONE_COUNTRY_CODES = {$CALL_CENTER_PHONE_COUNTRY_CODES};</script>
        <script>var _CALL_CENTER_CLICK2CALL_ENABLED_MODULE = {$CALL_CENTER_CLICK2CALL_ENABLED_MODULE};</script>
        {* End Hieu Nguyen *}

        <script src="{vresource_url('resources/libraries/SocketIO/socket.io.js')}"></script>

        {* [StringeeWebPhone] Added by Hieu Nguyen on 2020-04-17 *}
        {if $CALL_CENTER_GATEWAY_NAME == 'Stringee'}
            <script type="text/javascript" src="resources/libraries/Stringee/StringeeSDK.js"></script>
            <script type="text/javascript" src="https://static.stringee.com/web_phone/lastest/js/StringeeSoftPhone-lastest.js"></script>
        {/if}
        {* End Hieu Nguyen *}

        {* [VCSWebClient] Added by Hieu Nguyen on 2021-10-05 *}
        {if $CALL_CENTER_GATEWAY_NAME == 'VCS'}
            <script type="text/javascript" src="https://vcs1.3cx.asia:8000/pbx/pbx_3cx.js"></script>
        {/if}
        {* End Hieu Nguyen *}

        {* [CallCenterHandler] Added by Hieu Nguyen on 2020-05-15 *}
        {if !isForbiddenFeature('CallCenterIntegration') && $CALL_CENTER_CONFIG.enable eq true}
            <script src="{vresource_url('modules/PBXManager/resources/CallCenterClient.js')}" async defer></script>
        {/if}
        {* End Hieu Nguyen *}

        <script src="{vresource_url('modules/PBXManager/resources/CallPopup.js')}" async defer></script>	

		{* Added by Vu Mai on 2022-10-03 to add Detail.js *}
        <script src="{vresource_url('resources/SMSNotifierHelper.js')}" async defer></script>
		
        {if $smarty.request.view != 'Detail'}
			<script src="{vresource_url('layouts/v7/modules/Vtiger/resources/Detail.js')}"></script>
			<input type="hidden" id="recordId" value="" />
		{/if}
		{* End Vu Mai *}
    {/if}
{/strip}