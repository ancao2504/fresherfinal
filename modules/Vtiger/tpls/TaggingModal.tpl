{* Added by Vu Mai on 2022-09-06 to render tagging modal and create tag modal *}
<!-- Tagging Modal -->
<div id="tagging-modal" class="modal-dialog modal-md">
	<div class="modal-content">
		{assign var=HEADER_TITLE value="{vtranslate('LBL_CUSTOM_TAG_SELECT_TAGS')}"}
		{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

		<form name="tagging_form" class="form-horizontal" method="post" action="index.php">
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-12">
						<div class="tagging-input-container">
							<div class="select-tag-wrapper">
								<input name="tags" class="inputElement" data-selected-tags="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($SELECTED_TAGS))}" />
							</div>
							<div class="create-tag-wrapper">
								<button class="add-tag-btn"><i class="far fa-plus" aria-hidden="true"></i></button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer ">
				<center>
					<button class="btn btn-success" name="saveButton">{vtranslate('LBL_SAVE')}</button>
					<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL')}</a>
				</center>
			</div>
		</form>
	</div>
</div>

<!-- Create Tag Modal -->
<div id="create-tag-modal" class="modal-dialog modal-sm create-tag-modal" style="display:none;">
	<div class="modal-content">
		{assign var=HEADER_TITLE value="{vtranslate('LBL_CUSTOM_TAG_ADD_TAG')}"}
		{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

		<form name="create_tag_form">
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-12">
						<label class="control-label">{vtranslate('LBL_CUSTOM_TAG_CREATE_NEW_TAG')}</label>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<input type="text" name="tag_name" data-rule-maxsize="25" class="inputElement" placeholder="{vtranslate('LBL_CUSTOM_TAG_CREATE_INPUT_TAG')}" />
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<label><input type="checkbox" name="visibility" class="inputElement" /> {vtranslate('LBL_CUSTOM_TAG_CREATE_SHARED_TAG')}</label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<center>
					<button class="btn btn-success" name="saveButton">{vtranslate('LBL_SAVE')}</button>
					<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL')}</a>
				</center>
			</div>
		</form>
	</div>
</div>