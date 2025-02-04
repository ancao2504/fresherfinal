{* Added by Vu Mai on 2022-09-19 to render quick edit form *}
{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>

		<script type="text/javascript">
            jQuery.getScript('{$jsModel->getSrc()}', () => { console.log('Script ' + '{$jsModel->getSrc()}' + ' loaded') });
        </script>
	{/foreach}

	{if $DISPLAY_PARAMS['form'] && $DISPLAY_PARAMS['form']['hiddenFields']}
		{eval var=$DISPLAY_PARAMS['form']['hiddenFields']}
	{/if}

	{if $DISPLAY_PARAMS['scripts'] neq null}
		<div class="custom-scripts">
			{eval var=$DISPLAY_PARAMS['scripts']}
		</div>	
	{/if}

	<div class="modal-dialog modal-lg quick-edit">
		<div class="modal-content">
			<form class="form-horizontal record-quick-edit" id="quick-edit" name="quick_edit" method="post" action="index.php">
				{if !empty($RECORD_ID)}
					{assign var=HEADER_TITLE value={vtranslate('LBL_EDITING', $MODULE)}|cat:" "|cat:{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}}
				{else}
					{assign var=HEADER_TITLE value={vtranslate('LBL_QUICK_CREATE', $MODULE)}|cat:" "|cat:{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}}
				{/if}

				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

				<div class="modal-body fancyScrollbar">
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}

					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="SaveAjax">
					<input type="hidden" name="record" value="{$RECORD_ID}" />
					<input type="hidden" name="document_source" value="Vtiger" />
					<input type="hidden" name="max_upload_limit" value="{$MAX_UPLOAD_LIMIT_BYTES}" />
					<input type="hidden" name="max_upload_limit_mb" value="{$MAX_UPLOAD_LIMIT_MB}" />

					{assign var="RECORD_STRUCTURE_MODEL" value=$QUICK_EDIT_CONTENTS[$MODULE]['recordStructureModel']}
					{assign var="RECORD_STRUCTURE" value=$QUICK_EDIT_CONTENTS[$MODULE]['recordStructure']}
					{assign var="MODULE_MODEL" value=$QUICK_EDIT_CONTENTS[$MODULE]['moduleModel']}

					<div class="quick-edit-content {$MODULE}">
						<div class="row form-group">
						
							{assign var=COUNTER value=0}
							{assign var=INDEX value=0}

							{if $MODULE == 'Documents'}
								{include file="modules/Vtiger/tpls/DocumentsQuickEdit.tpl"}
							{else}
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
									{if $FIELD_MODEL->get('uitype') neq '83'}
										<div class="flex col-md-6 {if $INDEX == 0}{assign var=INDEX value=1}left{else}{assign var=INDEX value=0}right{/if}">
											{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
											{assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
											{assign var="referenceListCount" value=count($referenceList)}

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
															<select class="select2 referenceModulesList">
																{foreach key=index item=value from=$referenceList}
																	<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
																{/foreach}
															</select>
														</span>
													{else}
														<label class="muted pull-right">{vtranslate($FIELD_MODEL->get('label'), $MODULE)} &nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
													{/if}
												{else}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
												{/if}

												{if $isReferenceField neq "reference"}</label>{/if}
											</div>
											<div class="fieldValue {$FIELD_NAME} col-md-8" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
												{if $DISPLAY_PARAMS['fields'][$FIELD_NAME] && $DISPLAY_PARAMS['fields'][$FIELD_NAME]['customTemplate']}
													{eval var=$DISPLAY_PARAMS['fields'][$FIELD_NAME]['customTemplate']}
												{else}
													{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
												{/if}

												{include file="modules/Vtiger/tpls/FieldGuideIcon.tpl" FIELD_MODEL=$FIELD_MODEL}
											</div>
										</div>
									{else}
										{include file='layouts/v7/modules/Vtiger/uitypes/ProductTaxEdit.tpl' COUNTER=$COUNTER MODULE=$MODULE PULL_RIGHT=true}
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
									{/if}	
								{/foreach}
							{/if}	
						</div>
					</div>
				</div>
				<div class="modal-footer footer">
					<center>
						{if $BUTTON_NAME neq null}
							{assign var=BUTTON_LABEL value=$BUTTON_NAME}
						{else}
							{assign var=CUSTOMER_MODULES value=getGlobalVariable('customerModules')}

							{if in_array($MODULE, $CUSTOMER_MODULES)}
								{assign var=BUTTON_LABEL value={vtranslate('LBL_UPDATE_CUSTOMER', 'Vtiger')}}
							{else}
								{$replaceParams = ['%module' => vtranslate($MODULE, $MODULE)]}
								{assign var=BUTTON_LABEL value={vtranslate('LBL_UPDATE_MODULE', 'Vtiger', $replaceParams)}}
							{/if}
						{/if}

						{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
						<button class="btn btn-default btn-show-full-form" data-href="{$EDIT_VIEW_URL}&record={$RECORD_ID}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
						<button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-success" type="submit" name="saveButton"><strong>{$BUTTON_LABEL}</strong></button>
						<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</center>
				</div>
			</form>
		</div>

		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var quickcreate_uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type;
								}
								return false;
							}
						},
					};
				})();
			</script>
		{/if}
	</div>
{/strip}
