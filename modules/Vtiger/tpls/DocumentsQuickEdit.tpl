{strip}
	<input type="hidden" name="document_type" value="{$DOCUMENTS_TYPE}" />

	{if $DOCUMENTS_TYPE == 'I'}
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
					{assign var=FIELD_MODEL value=$RECORD_STRUCTURE['filename']}
					<input type="file" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}" data-rule-required="true" />
				</div>
				&nbsp;&nbsp;&nbsp;<i class="fa fa-info-circle cursorPointer" data-toggle="tooltip" title="{vtranslate('LBL_MAX_UPLOAD_SIZE', $MODULE)} {$MAX_UPLOAD_LIMIT_MB}{vtranslate('MB', $MODULE)}"></i>
			</div>
			<div class="fileDetails">{$FIELD_MODEL->get('fieldvalue')}</div>
		</div>
	{/if}

	<div class="flex col-md-12">
		{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['notes_title']}
		<div class="fieldLabel col-md-4">
			<label class="muted pull-right">
				{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
				{if $FIELD_MODEL->isMandatory() eq true}
					<span class="redColor">*</span>
				{/if}
			</label>
		</div>
		<div class="fieldValue col-md-8" colspan="3">
			{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
		</div>
	</div>

	{if $DOCUMENTS_TYPE == 'E'}
		<div class="flex col-md-12">
			{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['filename']}
			<div class="fieldLabel col-md-4">
				<label class="muted pull-right">
					{vtranslate('LBL_FILE_URL', $MODULE)}&nbsp;
					{if $FIELD_MODEL->isMandatory() eq true}
						<span class="redColor">*</span>
					{/if}
				</label>
			</div>
			<div class="fieldValue col-md-8" colspan="3">
				<input type="text" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}"
					value="{$FIELD_MODEL->get('fieldvalue')}" data-rule-required="true" data-rule-url="true"/>
			</div>
		</div>
	{/if}	

	<div class="flex col-md-12">
		{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['assigned_user_id']}
		<div class="fieldLabel {$FIELD_MODEL->get('name')} col-md-4">
			<label class="muted pull-right">
				{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
				{if $FIELD_MODEL->isMandatory() eq true}
					<span class="redColor">*</span>
				{/if}
			</label>
		</div>
		<div class="fieldValue {$FIELD_MODEL->get('name')} col-md-8">
			{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
		</div>
	</div>

	<div class="flex col-md-12">
		{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['folderid']}
		{if $FIELD_MODELS['folderid']}
			<div class="fieldLabel col-md-4">
				<label class="muted pull-right">
					{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
					{if $FIELD_MODEL->isMandatory() eq true}
						<span class="redColor">*</span>
					{/if}
				</label>
			</div>
			<div class="fieldValue col-md-8">
				{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
			</div>
		{/if}
	</div>

	{if $DOCUMENTS_TYPE == 'I'}
		<div class="flex col-md-12">
			{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['notecontent']}
			{if $FIELD_MODELS['notecontent']}
				<div class="fieldLabel col-md-4">
					<label class="muted pull-right">
						{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
						{if $FIELD_MODEL->isMandatory() eq true}
							<span class="redColor">*</span>
						{/if}
					</label>
				</div>
				<div class="fieldValue col-md-8">
					{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
				</div>
			{/if}
		</div>
	{/if}	

	{assign var=HARDCODED_FIELDS value=','|explode:"filename,assigned_user_id,folderid,notecontent,notes_title"}
	{assign var=COUNTER value=0}

	{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELD_MODELS} 
		{if !in_array($FIELD_NAME,$HARDCODED_FIELDS) && $FIELD_MODEL->isQuickCreateEnabled() && $FIELD_MODEL->isEditable()}
			{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
			{assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
			{assign var="referenceListCount" value=count($referenceList)}

			<div class="flex col-md-12">
				<div class='fieldLabel {$FIELD_NAME} col-md-4'>
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
				</div>
				
				{if $FIELD_MODEL->get('uitype') neq '83'}
					<div class="fieldValue {$FIELD_NAME} col-md-8" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
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
					</div>
				{/if}
			</div>    
		{/if}
	{/foreach}

	{assign var=BUTTON_NAME value={vtranslate('LBL_UPLOAD', $MODULE)}}
	{assign var=BUTTON_ID value="js-upload-document"}
{/strip}