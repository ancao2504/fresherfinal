{* Added by Hieu Nguyen on 2021-11-24 *}

{strip}
	<div class="row" style="margin-bottom: 70px">
		<div class="col-lg-9">
			{if !Settings_Workflows_Util_Helper::isCustomerModule($SOURCE_MODULE)}
				<div class="row form-group variable">
					<div class="col-lg-3 fieldLabel">{vtranslate('LBL_UPDATE_MAUTIC_STAGE_TASK_RELATED_CUSTOMER_FIELD', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
					<div class="col-lg-9 fieldValue">
						<select name="related_customer_field" class="inputElement select2" style="width: 300px" data-rule-required="true" data-placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}">
							<option></option>
							{foreach key=KEY item=FIELD_INFO from=Settings_Workflows_Util_Helper::getRelatedCustomerFields($SOURCE_MODULE)}
								<option value="{$FIELD_INFO.name}" {if $FIELD_INFO.name == $TASK_OBJECT->related_customer_field}selected{/if}>{vtranslate($FIELD_INFO.label, $SOURCE_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/if}

			<div class="row form-group variable">
				<div class="col-lg-3 fieldLabel">{vtranslate('LBL_UPDATE_MAUTIC_STAGE_TASK_MAUTIC_STAGE', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
				<div class="col-lg-9 fieldValue">
					<select name="mautic_stage_id" class="inputElement select2" style="width: 300px" data-rule-required="true" data-placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}">
						<option></option>
						{foreach key=KEY item=MKT_LIST_INFO from=CPMauticIntegration_Data_Helper::getAllStages(true)}
							<option value="{$MKT_LIST_INFO.id}" {if $MKT_LIST_INFO.id == $TASK_OBJECT->mautic_stage_id}selected{/if}>{$MKT_LIST_INFO.name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<hr/>
		</div>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url("modules/Settings/Workflows/resources/VTAddToMarketingListTask.css")}"></link>
{/strip}