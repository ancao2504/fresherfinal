{* Added by Hieu Nguyen on 2018-07-31 *}

{strip}
	<div id="moduleBuilderModal" class="modal-dialog modal-content hide">
		{assign var=HEADER_TITLE value={vtranslate('LBL_MODULE_BUILDER_MODAL_TITLE', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		
		<form class="form-horizontal moduleBuilderForm">
			<input type="hidden" name="blockid" /> 

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_NAME', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="moduleName" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_DISPLAY_NAME_EN', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="displayNameEn" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_DISPLAY_NAME_VN', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="displayNameVn" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_MENU_GROUP', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<select name="menuGroup" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%">
						{assign var=MENU_GROUPS value=Vtiger_MenuStructure_Model::getAppMenuList()}
						{foreach key=INDEX item=GROUP_NAME from=$MENU_GROUPS}
							<option value="{$GROUP_NAME}">{vtranslate("LBL_$GROUP_NAME")}</option>
						{/foreach}
					</select>
				</div>
			</div>

			{*--BEGIN: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new, Apply module is Extension*}
			<div class="form-group">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_IS_EXTENSION', $QUALIFIED_MODULE)}</span>
					&nbsp;
				</label>
				<div class="controls col-sm-6">
					<input type="checkbox" name="isExtension" checked class="col-sm-3 inputElement" style="margin-top: 8px;" /> {* Modified by Phu Vo on 2020.08.12 *}
				</div>
			</div>

			<div class="form-group forEntityModule">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_HAS_RELATED_ACTIVITIES_LIST', $QUALIFIED_MODULE)}</span>
					&nbsp;
				</label>
				<div class="controls col-sm-6">
					<input type="checkbox" name="hasActivities" class="col-sm-3 inputElement" style="margin-top: 8px;" /> {* Modified by Phu Vo on 2020.08.12 *}
				</div>
			</div>
			{*--END: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new, Apply module is Extension*}

			{*--BEGIN: Added by Phu Vo on 2002.08.11 -- Add person module option*}
			<div class="form-group forEntityModule">
				<label class="control-label fieldLabel col-sm-6">
					<span>{vtranslate('LBL_MODULE_BUILDER_MODULE_IS_PERSON', $QUALIFIED_MODULE)}</span>
					&nbsp;
				</label>
				<div class="controls col-sm-6">
					<input type="checkbox" name="isPerson" class="col-sm-3 inputElement" style="margin-top: 8px" /> {* Modified by Phu Vo on 2020.08.12 *}
				</div>
			</div>
			{*--END: Added by Phu Vo on 2002.08.11 -- Add person module option*}

			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Settings/ModuleManager/resources/ModuleBuilder.css')}"></link>
	<script type="text/javascript" src="{vresource_url('modules/Settings/ModuleManager/resources/ModuleBuilder.js')}"></script>
{/strip}