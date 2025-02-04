{* Created by Vu Mai on 2022-11-17 to render customer status update option modal *}

{strip}
	<div id="customer-status-update-option-modal" class="modal-dialog modal-md">
		<div class="modal-content">
			{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$MODAL_TITLE}

			<form name="update_option_form">
				<div class="modal-body">
					<div class="form-content fancyScrollbar padding20">
						<div class="flex form-group">
							<div class="fieldValue"><input type="radio" class="inputElement" value="old_data" name="update_option" data-rule-required="true" /></div>
							<div class="fieldLabel col-md-8">{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CHANGE_OLD_DATA_CALLED', 'CPTelesales')}</div>
						</div>
						<div class="flex form-group">
							<div class="fieldValue"><input type="radio" class="inputElement" value="new_data" name="update_option" data-rule-required="true" /></div>
							<div class="fieldLabel col-md-8">{vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_CHANGE_ONLY_FOR_NEW_CALL_DATA', 'CPTelesales')}</div>
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
{/strip}