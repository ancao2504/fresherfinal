{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}

    {* Added by Hieu Nguyen on 2019-06-11 to show hidden input for main_owner_id *}
    {if !$RECORD && $RECORD_STRUCTURE_MODEL}
        {assign var="RECORD" value=$RECORD_STRUCTURE_MODEL->getRecord()}
    {/if}

    <input type="hidden" name="main_owner_id" value="{if $RECORD}{$RECORD->fetchedRow['main_owner_id']}{/if}"/>
    {* End Hieu Nguyen *}

	{* Modified by Hieu Nguyen on 2018-07-16 to load custom code *}
	{if $DISPLAY_PARAMS['form'] && $DISPLAY_PARAMS['form']['hiddenFields']}
		{eval var=$DISPLAY_PARAMS['form']['hiddenFields']}
	{/if}

	{if $DISPLAY_PARAMS['scripts'] neq null}
		{eval var=$DISPLAY_PARAMS['scripts']}
	{/if}
	{* End Hieu Nguyen *}

	{* Added by Hieu Nguyen on 2021-08-03 to support custom header in EditView *}
	{if $smarty.request.view == 'Edit'}
		<div class="row-fluid customEditViewHeader">
			{assign var="CUSTOM_HEADER_FILE" value="modules/$MODULE/tpls/EditViewCustomHeader.tpl"}

			{if file_exists($CUSTOM_HEADER_FILE)}
				{include file=$CUSTOM_HEADER_FILE}
			{/if}
		</div>
	{/if}
	{* End Hieu Nguyen *}

	<div name='editContent'>
		{if $DUPLICATE_RECORDS}
			<div class="fieldBlockContainer duplicationMessageContainer">
				<div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
				<div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
			</div>
		{/if}
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
			{if $BLOCK_FIELDS|@count gt 0}
				<div class='fieldBlockContainer' data-block="{$BLOCK_LABEL}">
					<h4 class='fieldBlockHeader'>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
					<hr>
					<table class="table table-borderless">
						<tr>
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
								{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
								{assign var="refrenceListCount" value=count($refrenceList)}
								{if $FIELD_MODEL->isEditable() eq true}
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
									<td class="fieldLabel {$FIELD_NAME} alignMiddle">
										{if $isReferenceField eq "reference"}
											{if $refrenceListCount > 1}
												{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
												{assign var="REFERENCED_MODULE_STRUCTURE" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
												{if !empty($REFERENCED_MODULE_STRUCTURE)}
													{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCTURE->get('name')}
												{/if}
												<select class="select2 referenceModulesList">
													{foreach key=index item=value from=$refrenceList}
														<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
													{/foreach}
												</select>
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										{else if $FIELD_MODEL->get('uitype') eq "83"}
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
											{if $MODULE eq 'Documents' && $FIELD_MODEL->get('label') eq 'File Name'}
												{assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
												{if $FILE_LOCATION_TYPE_FIELD}
													{if $FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}
														{vtranslate("LBL_FILE_URL", $MODULE)}&nbsp;<span class="redColor">*</span>
													{else}
														{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
													{/if}
												{else}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												{/if}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										{/if}
										&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
									</td>
									{if $FIELD_MODEL->get('uitype') neq '83'}
										<td class="fieldValue {$FIELD_NAME}" {if $FIELD_MODEL->getFieldDataType() eq 'boolean'} style="width:25%" {/if} {if in_array($FIELD_MODEL->get('uitype'), ['19', '69']) || ($FIELD_MODEL->getName() == 'recurringtype' && $MODULE == 'Events')} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}> {* Modified by Phu Vo on 2019.08.28 to colspan image field*} {* Modified by Phuc on 2019.11.18 to colspan recurrence field *}
											{* Modified by Hieu Nguyen on 2018-07-16 to load custom code *}
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
							{*If their are odd number of fields in edit then border top is missing so adding the check*}
							{if $COUNTER is odd}
								<td></td>
								<td></td>
							{/if}
						</tr>
					</table>
				</div>
			{/if}
		{/foreach}
	</div>
{/strip}
