{* Added by Hieu Nguyen on 2021-10-28 *}

{strip}
	<div class="row" style="margin-bottom: 70px">
		<div class="col-lg-9">
			<div class="row form-group variable">
				<div class="col-lg-3 fieldLabel">{vtranslate('LBL_ZALO_OA_MESSAGE_TASK_SENDER_LIST', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
				<div class="col-lg-9 fieldValue">
					<select name="sender_id" class="inputElement select2" style="width: 300px" {if $TASK_OBJECT->sender_id == 'all'}disabled{else}data-rule-required="true"{/if} data-placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}">
						<option></option>
						{foreach key=KEY item=OA_INFO from=CPSocialIntegration_Logic_Helper::getZaloOAList()}
							<option value="{$OA_INFO.id}" {if $OA_INFO.id == $TASK_OBJECT->sender_id}selected{/if}>{$OA_INFO.name}</option>
						{/foreach}
					</select>
					&nbsp;
					<label class="cursorPointer"><input type="checkbox" id="send_from_all_oa" name="sender_id" value="all" {if $TASK_OBJECT->sender_id == 'all'}checked{/if} /> {vtranslate('LBL_ZALO_OA_MESSAGE_TASK_SEND_FROM_ALL_OA', $QUALIFIED_MODULE)}</label>
				</div>
			</div>

			{if !Settings_Workflows_Util_Helper::isCustomerModule($SOURCE_MODULE)}
				<div class="row form-group variable">
					<div class="col-lg-3 fieldLabel">{vtranslate('LBL_ZALO_OA_MESSAGE_TASK_RELATED_CUSTOMER_FIELD', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
					<div class="col-lg-9 fieldValue">
						<select name="related_customer_field" data-rule-required="true" class="inputElement select2" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}">
							<option></option>
							{foreach key=KEY item=FIELD_INFO from=Settings_Workflows_Util_Helper::getRelatedCustomerFields($SOURCE_MODULE)}
								<option value="{$FIELD_INFO.name}" {if $FIELD_INFO.name == $TASK_OBJECT->related_customer_field}selected{/if}>{vtranslate($FIELD_INFO.label, $SOURCE_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/if}

			<div class="row form-group variable">
				<div class="col-lg-3 fieldLabel">{vtranslate('LBL_MESSAGE_VARIABLE', $QUALIFIED_MODULE)}</div>
				<div class="col-lg-9 fieldValue">
					<select id="variable" class="inputElement select2" style="width: 300px" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
						<option></option>
						{$ALL_FIELD_OPTIONS}
					</select>
					&nbsp;
					<button type="button" id="btnInsertVariable" class="btn btn-default">{vtranslate('LBL_INSERT_VARIABLE', $QUALIFIED_MODULE)}</button>
				</div>
			</div>

			<div class="row form-group">
				<div class="col-lg-3 fieldLabel">{vtranslate('LBL_MESSAGE_CONTENT', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
				<div class="col-lg-6">
					<textarea name="text_to_send" data-rule-required="true" class="inputElement fields" style="width: 100%; height: 100px">{$TASK_OBJECT->text_to_send}</textarea>
				</div>
			</div>
			<hr/>
		</div>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url("modules/Settings/Workflows/resources/VTZaloOAMessageTask.css")}"></link>
	<script src="{vresource_url("resources/UIUtils.js")}"></script>
	<script src="{vresource_url("modules/Settings/Workflows/resources/VTZaloOAMessageTask.js")}"></script>
{/strip}