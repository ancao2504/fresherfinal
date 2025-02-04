{* Refactored by Hieu Nguyen on 2022-07-20 *}

{strip}
	<div class="modal-dialog modal-md modal-content modal-edit-chatbot">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

		<form name="chatbot-info" class="form-horizontal">
			<div class="form-content fancyScrollbar padding20">
				<table class="table no-border fieldBlockContainer">
					<tbody>
						{foreach key=FIELD_NAME item=FIELD_INFO from=$CHATBOT_FIELDS}
							<tr>
								<td class="fieldLabel col-lg-4">{vtranslate($FIELD_INFO.label, 'PBXManager')} <span class="redColor">*</span></td>
								<td class="fieldValue col-lg-8"><input name="{$FIELD_NAME}" value="{$CHATBOT_INFO[$FIELD_NAME]}" data-rule-required="true" class="inputElement" /></td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>

			{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}