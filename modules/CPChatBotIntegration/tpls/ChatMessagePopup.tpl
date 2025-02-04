{*
	File ChatMessagePopup.tpl
	Author: Hieu Nguyen
	Date: 2020-04-07
	Purpose: to render chat message popup
*}

{strip}
	<div id="chat-message-modal" class="modal-dialog modal-content hide">
		{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=''}
	
		<form id="chat-message-form" class="form-horizontal" method="POST">
			<input type="hidden" name="channel" value=""/>

			<div class="padding10">
				<div class="form-group">
					<label class="control-label fieldLabel col-sm-3">
						<span>{vtranslate('LBL_CHAT_MESSAGE_MODAL_CHAT_APP', 'CPChatBotIntegration')}</span>
					</label>
					<div class="controls fieldValue col-sm-7">
						<div class="input-group inputElement" style="margin-bottom: 3px; width: 100%">
							<select name="chat_app" class="form-control" data-rule-required="true">
								{foreach item=APP_INFO from=$APP_LIST}
									<option value="{$APP_INFO.bot_id}">{$APP_INFO.bot_name}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label class="fieldLabel col-sm-12">
						<span>{vtranslate('LBL_CHAT_MESSAGE_MODAL_MESSAGE_CONTENT', 'CPChatBotIntegration')}</span>
						&nbsp;
						<span class="redColor">*</span>
					</label>
					<div class="fieldValue col-sm-12">
						<div class="input-group inputElement message">
							<textarea name="message" class="form-control" rows="5" data-rule-required="true" data-rule-maxlength="20000">
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
{/strip}