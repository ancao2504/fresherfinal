{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div id="VtEmailTaskContainer">
		<div class="row">
			<div class="col-sm-12 col-xs-12" style="margin-bottom: 70px;">
				<div class="row form-group" >
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9">
								<input name="fromEmail" class=" fields inputElement" type="text" value="{$TASK_OBJECT->fromEmail}" />
							</div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select id="fromEmailOption" style="min-width: 250px" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$FROM_EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('Reply To',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9">
								<input name="replyTo" class="fields inputElement" type="text" value="{$TASK_OBJECT->replyTo}"/>
							</div>
						</div>
					</div>
					<span class="col-sm-5 col-xs-5">
						<select style="min-width: 250px" class="task-fields select2 overwriteSelection" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</span>
				</div>
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<span class="col-sm-3 col-xs-3">{vtranslate('LBL_TO',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
							<div class="col-sm-9 col-xs-9">
								<input data-rule-required="true" name="recepient" class="fields inputElement" type="text" value="{$TASK_OBJECT->recepient}" />
							</div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select style="min-width: 250px" class="task-fields select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
				<div class="row form-group {if empty($TASK_OBJECT->emailcc)}hide {/if}" id="ccContainer">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9">
								<input class="fields inputElement" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}" />
							</div>
						</div>
					</div>
					<span class="col-sm-5 col-xs-5">
						<select class="task-fields select2" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 250px">
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</span>
				</div>
				<div class="row form-group {if empty($TASK_OBJECT->emailbcc)}hide {/if}" id="bccContainer">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9">
								<input class="fields inputElement" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}" />
							</div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select class="task-fields select2" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 250px">
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
				<div class="row form-group {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
					<div class="col-sm-8 col-xs-8">
						<div class="row">
							<div class="col-sm-3 col-xs-3">&nbsp;</div>
							<div class="col-sm-9 col-xs-9">
								<a class="cursorPointer {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>
								<a class="cursorPointer {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
							</div>
						</div>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
							<div class="col-sm-9 col-xs-9">
								{* Modified by Hieu Nguyen on 2021-11-23 to fix bug all text after character double quote not display correctly *}
								<input data-rule-required="true" name="subject" class="fields inputElement" type="text" name="subject" value="{htmlentities($TASK_OBJECT->subject)}" id="subject" spellcheck="true"/>
								{* End Hieu Nguyen *}
							</div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select style="min-width: 250px" class="task-fields select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$ALL_FIELD_OPTIONS}
						</select>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div style="margin-top: 7px" class="col-sm-3 col-xs-3">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</div>&nbsp;&nbsp;
							<div class="col-sm-8 col-xs-8">
								<select style="min-width: 250px" id="task-fieldnames" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
									<option></option>
									{$ALL_FIELD_OPTIONS}
								</select>
							</div>	
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<div class="row">
							<div style="margin-top: 7px" class="col-sm-3 col-xs-3">{vtranslate('LBL_GENERAL_FIELDS',$QUALIFIED_MODULE)}</div>&nbsp;&nbsp;
							<div class="col-sm-8 col-xs-8">
								<select style="width: 205px" id="task_timefields" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
									<option></option>
									{foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
										<option value="{if strpos(strtolower($META_VARIABLE_VALUE), 'url') === false}${/if}{$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
									{/foreach}	
								</select>
							</div>	
						</div>
					</div>
				</div>
				<div class="row from-group">
					{if $EMAIL_TEMPLATES}
						<div class="col-sm-6 col-xs-6">
							<div class="row">
								<div class="col-sm-3 col-xs-3">{vtranslate('LBL_EMAIL_TEMPLATES','EmailTemplates')}</div>
								<div class="col-sm-9 col-xs-9">
									<select style="min-width: 250px" id="task-emailtemplates" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
										<option></option>
										{foreach from=$EMAIL_TEMPLATES item=EMAIL_TEMPLATE}
											{if !$EMAIL_TEMPLATE->isDeleted()}
												<option value="{$EMAIL_TEMPLATE->get('body')}">{vtranslate($EMAIL_TEMPLATE->get('templatename'),$QUALIFIED_MODULE)}</option>
											{/if}
										{/foreach}	
									</select>
								</div>
							</div>
						</div>
					{/if}
				</div>

                {* Added by Hieu Nguyen on 2020-11-05 to support saving template without purified *}
                <div class="row from-group">
                    <div class="col-md-4 col-lg-4">{vtranslate('LBL_SAFE_CONTENT', $QUALIFIED_MODULE)}</div>
                    <div class="col-md-8 col-lg-8">
                        <input id="safe_content" type="checkbox" class="inputElement" name="safe_content" {if {$TASK_OBJECT->safe_content} == 'on'}checked{/if} />
                        &nbsp;&nbsp;<span style="color:red">{vtranslate('LBL_SAFE_CONTENT_HINT', $QUALIFIED_MODULE)}</span>
                    </div>
                </div>
                <br />
                {* End Hieu Nguyen *}

				<div class="row form-group">
					<div class="col-sm-12 col-xs-12">
						{* Modified by Hieu Nguyen on 2021-11-23 to fix bug all text after character double quote not display correctly *}
						<textarea id="content" name="content">{htmlentities($TASK_OBJECT->content)}</textarea>
						{* End Hieu Nguyen *}
					</div>
				</div>
			</div>
		</div>
	</div>	
{/strip}