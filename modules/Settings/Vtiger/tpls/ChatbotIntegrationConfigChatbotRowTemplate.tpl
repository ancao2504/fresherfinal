{* Added by Hieu Nguyen on 2022-07-21 to render a single row for each chatbot *}

{strip}
	<tr bot-id="{$BOT_INFO.bot_id}" bot-name="{$BOT_INFO.bot_name}">
		<td class="bot-name">{$BOT_INFO.bot_name}</td>
		<td class="text-center">
			<button type="button" class="btn btn-outline-primary btn-edit-chatbot" onclick="app.controller().showChatbotModal(this)" title="{vtranslate('LBL_EDIT', 'Vtiger')}">
				<i class="far fa-pen"></i>
			</button>
			<button type="button" class="btn btn-outline-danger btn-remove-chatbot" onclick="app.controller().removeChatbot(this)" title="{vtranslate('LBL_DELETE', 'Vtiger')}">
				<i class="far fa-trash-alt"></i>
			</button>
		</th>
	</tr>
{/strip}