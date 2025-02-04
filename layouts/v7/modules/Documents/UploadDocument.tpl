{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
	<div class="modal-dialog modal-lg modelContainer"> {* Edited by Phu Vo on 2019.06.18 to resize model to large size *}
		{assign var=HEADER_TITLE value={vtranslate('LBL_UPLOAD_TO_VTIGER', $MODULE)}}
		<div class="modal-content"> {* Edited by Phu Vo on 2019.06.18 => Make it have default behavior *}

			<form class="form-horizontal recordEditView" name="upload" method="post" action="index.php">
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				<div class="modal-body">
					<div class="uploadview-content container-fluid">
						<div class="uploadcontrols row">
							<div id="upload" data-filelocationtype="I">
								{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
									<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
								{/if}
								<input type="hidden" name="module" value="{$MODULE}" />
								<input type="hidden" name="action" value="SaveAjax" />
								<input type="hidden" name="document_source" value="Vtiger" />
								{if $RELATION_OPERATOR eq 'true'}
									<input type="hidden" name="relationOperation" value="{$RELATION_OPERATOR}" />
									<input type="hidden" name="sourceModule" value="{$PARENT_MODULE}" />
									<input type="hidden" name="sourceRecord" value="{$PARENT_ID}" />
									{if $RELATION_FIELD_NAME}
										<input type="hidden" name="{$RELATION_FIELD_NAME}" value="{$PARENT_ID}" /> 
									{/if}
								{/if}

								<input type="hidden" name="max_upload_limit" value="{$MAX_UPLOAD_LIMIT_BYTES}" />
								<input type="hidden" name="max_upload_limit_mb" value="{$MAX_UPLOAD_LIMIT_MB}" />

								<div id="dragandrophandler" class="dragdrop-dotted">
									<div style="font-size:175%;">
										<span class="fa fa-upload"></span>&nbsp;&nbsp;
										{vtranslate('LBL_DRAG_&_DROP_FILE_HERE', $MODULE)}
									</div>
									<div style="margin-top: 1%;text-transform: uppercase;margin-bottom: 2%;">
										{vtranslate('LBL_OR', $MODULE)}
									</div>
									<div>
										<div class="fileUploadBtn btn btn-primary">
											<span><i class="fa fa-laptop"></i> {vtranslate('LBL_SELECT_FILE_FROM_COMPUTER', $MODULE)}</span>
											{assign var=FIELD_MODEL value=$FIELD_MODELS['filename']}
											<input type="file" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_VALUE}" data-rule-required="true" />
										</div>
										&nbsp;&nbsp;&nbsp;<i class="fa fa-info-circle cursorPointer" data-toggle="tooltip" title="{vtranslate('LBL_MAX_UPLOAD_SIZE', $MODULE)} {$MAX_UPLOAD_LIMIT_MB}{vtranslate('MB', $MODULE)}"></i>
									</div>
									<div class="fileDetails"></div>
								</div>

								<table class="massEditTable table no-border">
									<tr>
										{assign var="FIELD_MODEL" value=$FIELD_MODELS['notes_title']}
										<td class="fieldLabel col-lg-2">
											<label class="muted pull-right">
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
												{if $FIELD_MODEL->isMandatory() eq true}
													<span class="redColor">*</span>
												{/if}
											</label>
										</td>
										<td class="fieldValue col-lg-4" colspan="3">
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</td>
									</tr>
									<tr>
										{assign var="FIELD_MODEL" value=$FIELD_MODELS['assigned_user_id']}
										<td class="fieldLabel {$FIELD_MODEL->get('name')} col-lg-2"> {* Edited by Phu Vo on 2019.06.17 to add field name to wrapper*}
											<label class="muted pull-right">
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
												{if $FIELD_MODEL->isMandatory() eq true}
													<span class="redColor">*</span>
												{/if}
											</label>
										</td>
										<td class="fieldValue {$FIELD_MODEL->get('name')} col-lg-4"> {* Edited by Phu Vo on 2019.06.17 to add field name to wrapper*}
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</td>

										{assign var="FIELD_MODEL" value=$FIELD_MODELS['folderid']}
										{if $FIELD_MODELS['folderid']}
											<td class="fieldLabel col-lg-2">
												<label class="muted pull-right">
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
													{if $FIELD_MODEL->isMandatory() eq true}
														<span class="redColor">*</span>
													{/if}
												</label>
											</td>
											<td class="fieldValue col-lg-4">
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
											</td>
										{/if}
									</tr>
									<tr>
										{assign var="FIELD_MODEL" value=$FIELD_MODELS['notecontent']}
										{if $FIELD_MODELS['notecontent']}
											<td class="fieldLabel col-lg-2" colspan="1">
												<label class="muted pull-right">
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
													{if $FIELD_MODEL->isMandatory() eq true}
														<span class="redColor">*</span>
													{/if}
												</label>
											</td>
											<td class="fieldValue col-lg-4" colspan="3">
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
											</td>
										{/if}
									</tr>
									<tr>
										{assign var=HARDCODED_FIELDS value=','|explode:"filename,assigned_user_id,folderid,notecontent,notes_title"}
										{assign var=COUNTER value=0}
										{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELD_MODELS} 
											{if !in_array($FIELD_NAME,$HARDCODED_FIELDS) && $FIELD_MODEL->isQuickCreateEnabled() && $FIELD_MODEL->isEditable()} {* [Vtiger] Edited by Phu Vo on 2019.06.17 to check editable on preference fields *}
												{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
												{assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
												{assign var="referenceListCount" value=count($referenceList)}
												{if $FIELD_MODEL->get('uitype') eq "19"}
													{if $COUNTER eq '1'}
														<td></td><td></td></tr><tr>
														{assign var=COUNTER value=0}
													{/if}
												{/if}
												{if $COUNTER eq 2}
												</tr><tr>
													{assign var=COUNTER value=1}
												{else}
													{assign var=COUNTER value=$COUNTER+1}
												{/if}
												<td class='fieldLabel {$FIELD_NAME} col-lg-2'>
													{if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
														{if $isReferenceField eq "reference"}
															{if $referenceListCount > 1}
																{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
																{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
																{if !empty($REFERENCED_MODULE_STRUCT)}
																	{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
																{/if}
																<span class="pull-right">
																	<select style="width:150px;" class="select2 referenceModulesList {if $FIELD_MODEL->isMandatory() eq true}reference-mandatory{/if}">
																		{foreach key=index item=value from=$referenceList}
																			<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
																		{/foreach}
																	</select>
																</span>
															{else}
																<label class="muted pull-right">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
															{/if}
														{else if $FIELD_MODEL->get('uitype') eq '83'}
															{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
															{if $TAXCLASS_DETAILS}
																{assign 'taxCount' count($TAXCLASS_DETAILS)%2}
																{if $taxCount eq 0}
																	{if $COUNTER eq 2}
																		{assign var=COUNTER value=1}
																	{else}
																		{assign var=COUNTER value=2}
																	{/if}
																{/if}
															{/if}
														{else}
															{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
														{/if}
														{if $isReferenceField neq "reference"}</label>{/if}
												</td>
												{if $FIELD_MODEL->get('uitype') neq '83'}
													<td class="fieldValue {$FIELD_NAME} col-lg-4" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
														{* Modified by Hieu Nguyen on 2019-11-21 to load custom code *}
                                                        {if $DISPLAY_PARAMS['fields'][$FIELD_NAME] && $DISPLAY_PARAMS['fields'][$FIELD_NAME]['customTemplate']}
                                                            {eval var=$DISPLAY_PARAMS['fields'][$FIELD_NAME]['customTemplate']}
                                                        {else}
                                                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                                        {/if}
                                                        {* End Hieu Nguyen *}

                                                        {* [FieldGuide] Added by Hieu Nguyen on 2021-01-20 *}
                                                        {include file="modules/Vtiger/tpls/FieldGuideIcon.tpl" FIELD_MODEL=$FIELD_MODEL}
                                                        {* End Hieu Nguyen *}
													</td>
												{/if}
											{/if}
										{/foreach}
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
				{assign var=BUTTON_NAME value={vtranslate('LBL_UPLOAD', $MODULE)}}
				{assign var=BUTTON_ID value="js-upload-document"}
				{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}
