{* Added by Hieu Nguyen on 2021-12-07 *}

{strip}
	<div class="row" style="margin-bottom: 70px">
		<div class="col-lg-9">
			{if !Settings_Workflows_Util_Helper::isCustomerModule($SOURCE_MODULE)}
				<div class="row form-group variable">
					<div class="col-lg-3 fieldLabel">{vtranslate('LBL_UNLINK_CUSTOMER_TAGS_TASK_RELATED_CUSTOMER_FIELD', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
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

			{if Settings_Workflows_Util_Helper::isInventoryModule($SOURCE_MODULE)}
				<div class="row form-group variable">
					<div class="col-lg-3 fieldLabel">{vtranslate('LBL_UNLINK_CUSTOMER_TAGS_TASK_GET_TAGS_FROM_PRODUCTS_AND_SERVICES', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
					<div class="col-lg-9 fieldValue">
						<input type="checkbox" name="get_tags_from_products_services" value="1" {if $TASK_OBJECT->get_tags_from_products_services == '1'}checked{/if} />
					</div>
				</div>
			{/if}

			<div class="row form-group variable">
				<div class="col-lg-3 fieldLabel">{vtranslate('LBL_UNLINK_CUSTOMER_TAGS_TASK_TAG_LIST', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
				<div class="col-lg-9 fieldValue">
					<select multiple name="tag_ids" class="inputElement select2" style="width: 300px" data-rule-required="true" data-placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}">
						<option></option>
						{foreach key=KEY item=TAG_INFO from=Vtiger_Tag_Model::getAllPublicTags()}
							<option value="{$TAG_INFO.id}" {if $TAG_INFO.id == $TASK_OBJECT->tag_ids || in_array($TAG_INFO.id, $TASK_OBJECT->tag_ids)}selected{/if}>{$TAG_INFO.tag}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="alert alert-danger">
				<b>{vtranslate('LBL_NOTE', $QUALIFIED_MODULE)}:</b>&nbsp;{vtranslate('LBL_UNLINK_CUSTOMER_TAGS_TASK_TAGS_HINT', $QUALIFIED_MODULE)}
			</div>
			<hr/>
		</div>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url("modules/Settings/Workflows/resources/VTUnlinkCustomerTagsTask.css")}"></link>
	<script src="{vresource_url("modules/Settings/Workflows/resources/VTUnlinkCustomerTagsTask.js")}"></script>
{/strip}