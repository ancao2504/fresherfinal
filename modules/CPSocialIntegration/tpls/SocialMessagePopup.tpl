{*
	File SocialMessagePopup.tpl
	Author: Hieu Nguyen
	Date: 2019-07-16
	Purpose: to render social message popup
*}

{strip}
	{assign var="FB_MESSAGE_ALLOWED" value=CPSocialIntegration_Config_Helper::isFbMessageAllowed()}
	{assign var="ZALO_MESSAGE_ALLOWED" value=CPSocialIntegration_Config_Helper::isZaloMessageAllowed()}

	{if $FB_MESSAGE_ALLOWED eq true || $ZALO_MESSAGE_ALLOWED eq true}
		<div id="socialMessageModal" class="modal-dialog modal-content hide">
			{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=''}
		
			<form id="socialMessageForm" class="form-horizontal" method="POST">
				<input type="hidden" name="channel" value=""/>
				<input type="hidden" name="list_params" value=""/>

				<div class="padding10">
					<h6>{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_HINT', 'CPSocialIntegration')}</h6>
					<br>

					<div class="form-group">
						<label class="control-label fieldLabel col-sm-3">
							<span>{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_SENDER', 'CPSocialIntegration')}</span>
						</label>
						<div class="controls fieldValue col-sm-6">
							<div class="input-group " style="margin-bottom: 3px; width: 100%">
								<select name="sender" class="form-control inputElement" data-rule-required="true"/>
								</select>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label fieldLabel col-sm-3">
							<span>{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_MESSAGE_TEMPLATE', 'CPSocialIntegration')}</span>
						</label>
						<div class="controls fieldValue col-sm-6">
							<div class="input-group inputElement" style="margin-bottom: 3px">
								<select name="template" class="form-control"/>
									<option value="">-</option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<a href="index.php?module=CPSocialMessageTemplate&view=Edit" target="_blank" class="btn btn-primary text-nowrap">{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_CREATE_TEMPLATE', 'CPSocialIntegration')}</a>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label fieldLabel col-sm-3">
							<span>{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_VARIABLE', 'CPSocialIntegration')}</span>
						</label>
						<div class="controls fieldValue col-sm-6">
							<div class="input-group inputElement" style="margin-bottom: 3px">
								<select name="variable" class="form-control"/>
									<option value="">-</option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<button type="button" id="btnInsertVariable" class="btn btn-primary text-nowrap">{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_INSERT_VARIABLE', 'CPSocialIntegration')}</button>
						</div>
					</div>

					<div class="form-group">
						<label class="fieldLabel col-sm-12">
							<span>{vtranslate('LBL_SOCIAL_MESSAGE_MODAL_MESSAGE_CONTENT', 'CPSocialIntegration')}</span>
							&nbsp;
							<span class="redColor">*</span>
						</label>
						<div class="fieldValue col-sm-12">
							<div class="input-group inputElement" style="margin-bottom: 3px">
								<textarea name="message" class="form-control" rows="5" data-rule-required="true" data-rule-maxlength="20000"/>
								</textarea>
							</div>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<center>
						<button class="btn btn-success" type="submit" name="btnSend">{vtranslate('LBL_SEND', 'Vtiger')}</button>
						<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
					</center>
				</div>
			</form>
		</div>
	{/if}
{/strip}