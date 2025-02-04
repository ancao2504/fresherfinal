{* Added by Hieu Nguyen on 2021-01-13 *}

{strip}
	{* Modified by Phu Vo on 2021.05.21 to style social chat top menu icon *}
	{if CPSocialIntegration_Chatbox_Helper::isChatboxSupported() && CPSocialIntegration_Chatbox_Helper::canUseChatbox()}
		<div>
			{assign var="SOCIAL_CHAT_UNREAD_COUNT" value=CPSocialIntegration_Chatbox_Helper::getTotalUnreadCount()}

			<a href="javascript: void(0)" class="topbar-icon" style="display: inline-block; padding: 5px" data-toggle="tooltip" title="Chatbox" onclick="SocialChatboxPopup.open();">
				<i class="far fa-comment" style="padding: 10px"></i>
				{* Modified by Phu Vo on 2020.03.17 to support handle counter using js *}
				<span id="social-chat-counter" class="bg-danger badge{if $SOCIAL_CHAT_UNREAD_COUNT <= 0} hide{/if}">{$SOCIAL_CHAT_UNREAD_COUNT}</span>
				{* End Phu Vo *}
			</a>
		</div>
	{/if} 
	{* End Phu Vo *}
{/strip}