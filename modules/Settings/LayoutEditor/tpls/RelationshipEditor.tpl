{* Added by Hieu Nguyen on 2018-07-31 *}

{strip}
	{* Modified by Hieu Nguyen on 2021-10-04 to display button Add Relationship for developer only *}
	{if isDeveloperMode()}
		<div class="btn-group">
			<button type="button" id="btnAddRelationship" class="btn btn-primary">
				{vtranslate('LBL_BTN_ADD_RELATIONSHIP', $QUALIFIED_MODULE)}
			</button>
		</div>
	{/if}
	{* End Hieu Nguyen *}

	<div id="relationshipEditorModal" class="modal-dialog modal-content hide">
		{assign var=HEADER_TITLE value={vtranslate('LBL_RELATIONSHIP_EDITOR_MODAL_TITLE', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		
		<form class="form-horizontal relationshipEditorForm padding10" method="POST">
			<input type="hidden" name="leftSideModule" value="{$SELECTED_MODULE_NAME}"/>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_LEFT_SIDE_MODULE', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6" style="margin-top: 7px">
					<span class="label label-primary" style="font-size: 110%">{vtranslate($SELECTED_MODULE_NAME, $SELECTED_MODULE_NAME)}</span>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_RIGHT_SIDE_MODULE', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<select name="rightSideModule" class="inputElement" data-rule-required="true" style="width: 75%">
						<option value="">--</option>
						{foreach key=INDEX item=MODULE_NAME from=$MODULE_LIST}
							<option value="{$MODULE_NAME}">{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_RELATE_TYPE', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<select name="relationType" class="inputElement" data-rule-required="true" style="width: 75%">
						<option value="">--</option>	
						<option value="1:1" disabled>1:1</option>
						<option value="1:N">1:N</option>
						<option value="N:N">N:N</option>
					</select>
				</div>
			</div>

			<div class="form-group" style="display: none">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_LISTING_FUNCTION_NAME', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="listingFunctionName" class="inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>
			
			<div class="form-group" style="display: none">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_LEFT_SIDE_MODULE_REFERENCE_FIELD', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="leftSideReferenceField" class="inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group" style="display: none">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_RIGHT_SIDE_MODULE_REFERENCE_FIELD', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="rightSideReferenceField" class="inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_RELATION_LABEL_KEY', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="relationLabelKey" class="inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_RELATION_LABEL_DISPLAY_EN', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="relationLabelDisplayEn" class="inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label fieldLabel col-sm-5">
					<span>{vtranslate('LBL_RELATIONSHIP_EDITOR_RELATION_LABEL_DISPLAY_VN', $QUALIFIED_MODULE)}</span>
					&nbsp;
					<span class="redColor">*</span>
				</label>
				<div class="controls col-sm-6">
					<input type="text" name="relationLabelDisplayVn" class="inputElement" data-rule-required="true" style="width: 75%" />
				</div>
			</div>

			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>

	<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Settings/LayoutEditor/resources/RelationshipEditor.css')}"></link>
	<script type="text/javascript" src="{vresource_url('modules/Settings/LayoutEditor/resources/RelationshipEditor.js')}"></script>
{/strip}