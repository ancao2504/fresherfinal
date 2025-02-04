{* Added by Hieu Nguyen on 2021-01-06 *}

{strip}
	{if CPSocialIntegration_Chatbox_Helper::isChatboxSupported()}
		{* Added by Phu Vo on 2021.01.13 to include Social Chatbox Popup *}

		{assign var=CALL_CENTER_CONFIG value=getGlobalVariable('callCenterConfig')}
		{assign var=OUT_GOING_CALL_PERMISSION value=PBXManager_Server_Model::checkPermissionForOutgoingCall()}
		{assign var=CUSTOMER_TYPE_CONFIG value=Settings_Vtiger_Config_Model::loadConfig('customer_type', true)}
		{assign var=SOCIAL_CHATBOX_PICKLIST_FIELDS value=CPSocialIntegration_SocialChatboxPopup_Model::getPicklistFields()}
		{assign var=SOCIAL_CHATBOX_META_DATA value=CPSocialIntegration_SocialChatboxPopup_Model::getMetaData()}
		
		<script>var _CUSTOMER_TYPE_CONFIG = {Zend_Json::encode($CUSTOMER_TYPE_CONFIG)};</script>
		<script>var _SOCIAL_CHATBOX_PICKLIST_FIELDS = {Zend_Json::encode($SOCIAL_CHATBOX_PICKLIST_FIELDS)};</script>
		<script>var _SOCIAL_CHATBOX_META_DATA = {Zend_Json::encode($SOCIAL_CHATBOX_META_DATA)};</script>

		<div class="social-chatbox-popup-templates" style="display: none !important">
			<div id="taggingModal" class="modal-dialog modal-md tagging-modal">
				<div class="modal-content">
					{assign var=HEADER_TITLE value="{vtranslate('LBL_SOCIAL_CHATBOX_SELECT_NEW_TAG')}"}
					{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
					<form name="tagging_form" class="form-horizontal" method="post" action="index.php">
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="tagging-input-container">
										<div class="select-tag-wrapper">
											<input name="tags" class="inputElement" />
										</div>
										<div class="create-tag-wrapper">
											<button class="addTag"><i class="far fa-plus" aria-hidden="true"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer ">
							<center>
								<button id="save_syncsetting" class="btn btn-success" name="saveButton">{vtranslate('LBL_SAVE')}</button>
								<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL')}</a>
							</center>
						</div>
					</form>
				</div>
			</div>
			
			<div id="duplicateProcessModal" class="modal-dialog modal-lg social-chatbox-modal-container duplicate-process-modal">
				<div class="modal-content">
					{assign var=HEADER_TITLE value="{vtranslate('LBL_SOCIAL_CHATBOX_DUPLICATE_PROCESS')}"}
					{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
					<form name="duplicate_process" onsubmit="void(0)">
						<div class="modal-body">
							<p>{vtranslate('LBL_SOCIAL_CHATBOX_DUPLICATE_PROCESS_DESCRIPTION')}</p>
							<table class="duplicate-process-table table table-striped table-bordered" style="width: 100%">
								<thead>
									<tr>
										<th class="select" style="width: 10%">{vtranslate('LBL_SOCIAL_CHATBOX_SELECT')}</th>
										<th class="full_name" style="width: 15%">{vtranslate('LBL_SOCIAL_CHATBOX_CUSTOMER_NAME')}</th>
										<th class="email" style="width: 15%">{vtranslate('Email', 'Contacts')}</th>
										<th class="mobile" style="width: 15%">{vtranslate('Mobile', 'Contacts')}</th>
										<th class="module" style="width: 15%">{vtranslate('LBL_SOCIAL_CHATBOX_CUSTOMER_TYPE')}</th>
										<th class="main_owner_id" style="width: 15%">{vtranslate('LBL_MAIN_OWNER_ID')}</th>
										<th class="field_labels" style="width: 15%">{vtranslate('LBL_SOCIAL_CHATBOX_DUPLICATED_INFO')}</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
						<div class="modal-footer ">
							<center>
								<button type="button" class="btn btn-primary linkButton">{vtranslate('LBL_SOCIAL_CHATBOX_LINK')}</button>
								<button type="button" class="btn btn-primary mergeButton">{vtranslate('LBL_SOCIAL_CHATBOX_MERGE')}</button>
								<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL')}</a>
							</center>
						</div>
					</form>
				</div>
			</div>

			<div id="createTagModal" class="modal-dialog modal-sm create-tag-modal">
				<div class="modal-content">
					{assign var=HEADER_TITLE value="{vtranslate('LBL_SOCIAL_CHATBOX_ADD_TAG')}"}
					{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
					<form name="create_tab_form">
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<label class="control-label">{vtranslate('LBL_SOCIAL_CHATBOX_CREATE_NEW_TAG')}</label>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<input type="text" name="tag_name" data-rule-maxsize="25" class="inputElement" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_CREATE_INPUT_TAG')}" />
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<label><input type="checkbox" name="visibility" class="inputElement" /> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_SHARED_TAG')}</label>
								</div>
							</div>
						</div>
						<div class="modal-footer ">
							<center>
								<button id="save_syncsetting" class="btn btn-success" name="saveButton">{vtranslate('LBL_SAVE')}</button>
								<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL')}</a>
							</center>
						</div>
					</form>
				</div>
			</div>

			<div id="transferChat" class="modal-dialog modal-lg modal-content transferChat">
				{assign var=HEADER_TITLE value="{vtranslate('LBL_SOCIAL_CHATBOX_TRANSFER_CHAT')}"}
				{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				<div class="social-chatbox-modal-container transfer-chat-container container-fluid">
					<form name="transfer_chat">
						<table class="transfer-chat-table table table-striped table-bordered" style="width: 100%">
							<thead>
								<tr>
									<th style="width: 20%">{vtranslate('LBL_SOCIAL_CHATBOX_TRANSFER_CHAT')}</th>
									<th style="width: 20%">{vtranslate('LBL_SOCIAL_CHATBOX_USER_FULL_NAME')}</th>
									<th style="width: 20%">{vtranslate('LBL_SOCIAL_CHATBOX_USER_NAME')}</th>
									<th style="width: 20%">{vtranslate('LBL_SOCIAL_CHATBOX_USER_STATUS')}</th>
									<th style="width: 20%">{vtranslate('LBL_SOCIAL_CHATBOX_USER_ROLE')}</th>
								</tr>
								<tr>
									<th class="column-search-wrapper" style="text-align: center">
										<div class="actions-container">
											<button type="button" class="btn btn-success trigerSearch" data-toggle="tooltip" title="{vtranslate('LBL_SEARCH')}">
												<i class="far fa-search" aria-hidden="true"></i>
												<span> {vtranslate('LBL_SEARCH')}</span>
											</button>
											<button type="button" class="btn btn-default clearFilters" data-toggle="tooltip" title="{vtranslate('LBL_CALL_POPUP_TRANSFER_CLEAR_FILTERS', 'PBXManager')}">
												<i class="far fa-eraser" aria-hidden="true"></i>
											</button>
										</div>
									</th>
									<th class="column-search-wrapper"><input class="column-search form-search inputElement" name="full_name" /></th>
									<th class="column-search-wrapper"><input class="column-search form-search inputElement" name="user_name" /></th>
									<th class="column-search-wrapper">
										<select class="inputElement select2 form-search" name="is_online">
											<option value=""></option>
											<option value="1">{vtranslate('LBL_SOCIAL_CHATBOX_ONLINE')}</option>
											<option value="0">{vtranslate('LBL_SOCIAL_CHATBOX_OFFLINE')}</option>
										</select>
									</th>
									<th class="column-search-wrapper"><input class="column-search form-search inputElement" name="rolename" /></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</form>
				</div>
			</div>

			<div id="messageTemplate" class="modal-dialog modal-lg messageTemplate">
				<div class="modal-content">
					{assign var=HEADER_TITLE value="{vtranslate('LBL_SOCIAL_CHATBOX_MESSAGE_TEMPLATE')}"}
					{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
					<form name="message_template" onsubmit="void(0)">
						<div class="modal-body social-chatbox-modal-container">
							<table class="message-template-table table table-striped table-bordered" style="width: 100%">
								<thead>
									<tr>
										<th class="table-row select" style="width: 20%">{vtranslate('LBL_SOCIAL_CHATBOX_SELECT')}</th>
										<th class="table-row question" style="width: 20%">{vtranslate('Question', 'Faq')}</th>
										<th class="table-row faq_answer" style="width: 20%">{vtranslate('Answer', 'Faq')}</th>
										<th class="table-row faqcategories" style="width: 20%">{vtranslate('Category', 'Faq')}</th>
										<th class="table-row createdtime" style="width: 20%">{vtranslate('Created Time', 'Faq')}</th>
									</tr>
									<tr>
										<th class="column-search-wrapper" style="text-align: center">
											<div class="actions-container">
												<button type="button" class="btn btn-success trigerSearch" data-toggle="tooltip" title="{vtranslate('LBL_SEARCH')}">
													<i class="far fa-search" aria-hidden="true"></i>
													<span> {vtranslate('LBL_SEARCH')}</span>
												</button>
												<button type="button" class="btn btn-default clearFilters" data-toggle="tooltip" title="{vtranslate('LBL_CALL_POPUP_TRANSFER_CLEAR_FILTERS', 'PBXManager')}">
													<i class="far fa-eraser" aria-hidden="true"></i>
												</button>
											</div>
										</th>
										<th class="column-search-wrapper"><input class="column-search form-search inputElement" name="question" /></th>
										<th class="column-search-wrapper"><input class="column-search form-search inputElement" name="faq_answer" /></th>
										<th class="column-search-wrapper">
											{assign var=FAQ_CATEGORIES value=Vtiger_Util_Helper::getPickListValues('faqcategories')}
											<select class="inputElement select2 form-search" name="faqcategories">
												<option value="">{vtranslate('LBL_SELECT_OPTION')}</option>
												{foreach from=$FAQ_CATEGORIES item=category}
													<option value="{$category}">{vtranslate($category, 'Faq')}</option>
												{/foreach}
											</select>
										</th>
										{assign var=DATE_FORMAT value=$CURRENT_USER_MODEL->get('date_format')}
										<th class="column-search-wrapper"><input type="text" name="createdtime" class="dateField form-control form-search inputElement" date-fieldtype="date" data-date-format="{$DATE_FORMAT}" data-rule-date="true" /></th>
										{* <th class="column-search-wrapper"><input class="column-search form-search inputElement" name="category" /></th>
										<th class="column-search-wrapper"><input class="column-search form-search inputElement" name="createdtime" /></th> *}
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
						<div class="modal-footer ">
							<center>
								<button type="button" class="btn btn-primary saveButton" name="saveButton">{vtranslate('LBL_SOCIAL_CHATBOX_SELECT')}</button>
							</center>
						</div>
					</form>
				</div>
			</div>

			<div id="play-video" class="modal-dialog modal-lg playVideo">
				<div class="modal-content">
					{assign var=HEADER_TITLE value="{vtranslate('LBL_SOCIAL_CHATBOX_PLAY_MODAL')}"}
					{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
					<div class="modal-body">
						<video controls>
							<source class="video-url" src="" type="video/mp4">
						</video>
					</div>
				</div>
			</div>
		</div>
		
		<div id="social-chatbox-popup" :data-size="size">
			<div class="popup-panel left-panel" v-show="size != 'MINIMIZED'">
				<div class="popup-header left-header">
					<div v-show="left_panel_header_loading" class="header-loading"><i class="far fa-refresh fast-spin"></i></div>
					<div v-show="!left_panel_header_loading" class="left-header-content">
						<div class=social-pages-empty v-show="social_pages_size <= 0">
						</div>
						<div class="social-pages-container" v-show="!social_pages_size <= 0">
							<button class="scroll-left" :data-enabled="social_pages_scroll_left_enabled" @click="socialPagesPrev"><i class="far fa-chevron-left" aria-hidden="true"></i></button>
							<div class="social-pages">
								<div class="social-pages-content">
									<div class="social-page-container"
										v-for="(socialPage, index) in social_pages"
										:key="socialPage.id"
										v-b-tooltip.hover.noninteractive
										:title="socialPage.name"
										:data-active="active_social_page.id == socialPage.id"
										:data-number="index + 1"
									>
										<div class="social-page topbar-icon" @click="loadConversationsBySocialPage(socialPage)">
											<div class="social-page-avatar">
												<img :src="socialPage.avatar" />
											</div>
											<span class="social-page-counter bg-danger text-white badge" v-show="socialPage.unread_count > 0" v-html="socialPage.unread_count < 200 ? socialPage.unread_count : '99+'"></span>
											<div class="social-page-channel-image">
												<img :src="channel_images[socialPage.channel]" />
											</div>
										</div>
									</div>
								</div>
							</div>
							<button class="scroll-right" :data-enabled="social_pages_scroll_right_enabled" @click="socialPagesNext"><i class="far fa-chevron-right" aria-hidden="true"></i></button>
						</div>
					</div>
				</div>
				<div class="popup-body left-body">
					<div class="filter-container">
						<div class="keyword-container">
							<input type="text" name="search_conversations" autocomplete="off" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_SEACH_CONVERSATIONS_PLACEHOLDER')}" v-model="active_social_page.keyword" />
							<i class="far fa-search" aria-hidden="true" @click="filterConversations(false)"></i>
						</div>
						<div class="dropdown customer-type-container">
							<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<i v-if="active_social_page.customer_type == ''" class="far fa-users" v-b-tooltip.hover.noninteractive.right title="{vtranslate('All')}" aria-hidden="true"></i>
								<i v-if="active_social_page.customer_type == 'CPTarget'" v-b-tooltip.hover.noninteractive.right title="{vtranslate('CPTarget', 'CPTarget')}" class="far fa-address-book" aria-hidden="true"></i>
								<i v-if="active_social_page.customer_type == 'Leads'" v-b-tooltip.hover.noninteractive.right title="{vtranslate('Leads', 'Leads')}" class="far fa-user" aria-hidden="true"></i>
								<i v-if="active_social_page.customer_type == 'Contacts'" v-b-tooltip.hover.noninteractive.right title="{vtranslate('Contacts', 'Contacts')}" class="far fa-user-tie" aria-hidden="true" style="font-size: 13px"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-right">
								<li :class="active_social_page.customer_type == '' && 'active'">
									<a href="javascript:void(0)" class="btn btn-default text-left" @click="active_social_page.customer_type = ''">
										<div class="left"><i class="far fa-users" aria-hidden="true"></i> <span>{vtranslate('All')}</span></div>
										<div class="right"><i class="far fa-check" aria-hidden="true"></i></div>
									</a>
								</li>
								<li :class="active_social_page.customer_type == 'CPTarget' && 'active'">
									<a href="javascript:void(0)" class="btn btn-default text-left" @click="active_social_page.customer_type = 'CPTarget'">
										<div class="left"><i class="far fa-address-book" aria-hidden="true"></i> <span>{vtranslate('CPTarget', 'CPTarget')}</span></div>
										<div class="right"><i class="far fa-check" aria-hidden="true"></i></div>
									</a>
								</li>
								<li :class="active_social_page.customer_type == 'Leads' && 'active'">
									<a href="javascript:void(0)" class="btn btn-default text-left" @click="active_social_page.customer_type = 'Leads'">
										<div class="left"><i class="far fa-user" aria-hidden="true"></i> <span>{vtranslate('Leads', 'Leads')}</span></div>
										<div class="right"><i class="far fa-check" aria-hidden="true"></i></div>
									</a>
								</li>
								<li :class="active_social_page.customer_type == 'Contacts' && 'active'">
									<a href="javascript:void(0)" class="btn btn-default text-left" @click="active_social_page.customer_type = 'Contacts'">
										<div class="left"><i class="far fa-user-tie" aria-hidden="true" style="font-size: 13px"></i> <span>{vtranslate('Contacts', 'Contacts')}</span></div>
										<div class="right"><i class="far fa-check" aria-hidden="true"></i></div>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="conversations-container scrollable-container fancyScrollbar" @scroll="handleConversationsScroll">
						<div class="scrollable">
							<div class="conversation-container" v-if="conversations.length == 0">
								<br />
								<div class="no-data text-center">{vtranslate('LBL_SOCIAL_CHATBOX_NO_DATA')}</div>
							</div>
							<div class="conversation-container"
								v-for="conversation in conversations"
								:data-unread="conversation.is_read != '1'"
								:data-active="active_conversation.customer_social_id == conversation.customer_social_id"
								@click="e => conversationClickHandler(e, conversation)"
								v-show="conversation.can_access == '1'"
							>
								<div class="conversation">
									<div class="left">
										<div class="customer-avatar-container">
											<div class="customer-avatar">
												<img :src="getAvatarUrl(conversation.customer_id, conversation.customer_type, conversation.seed)" />
											</div>
											<div v-if="conversation.customer_type" :class="'module-indicator ' + conversation.customer_type">
												<div class="circle-simbol"></div>
											</div>
										</div>
										<div class="mapping-container">
											<span class="mapping-value" v-b-tooltip.hover.noninteractive :title="conversation.mapping_value != '-' ? conversation.mapping_value : ''" v-html="getMappingValue(conversation.mapping_value)"><span>
										</div>
									</div>
									<div class="right">
										<div class="name">
											<div class="left"><span class="customer-name" v-html="conversation.customer_name ||conversation.customer_social_id.toUpperCase()"></span></div>
											<div class="right"><span class="last-msg-time" v-html="formatLastMsgTime(conversation.last_msg_time)"></span></div>
										</div>
										<div class="content">
											<div class="left">
												<div class="outbound" v-show="conversation.last_msg_direction.toUpperCase() == 'OUTBOUND'">
													<i class="far fa-arrow-right" aria-hidden="true"></i>
												</div>
												<div class="last-msg-container">
													<div>
														<p v-if="!conversation.active_typing_msg" v-html="getLastMsg(conversation.last_msg, conversation.active_typing_msg)"></p>
														<p v-if="conversation.active_typing_msg" class="typing-status" v-html="getLastMsg(conversation.last_msg, conversation.active_typing_msg)"></p>
													</div>
												</div>
											</div>
											<div class="right">
												<a v-if="conversation.last_user_id" v-b-tooltip.hover.noninteractive :title="conversation.last_user_name" :href="getUserProfileUrl(conversation.last_user_id)" target="_blank" class="last-user">
													<img :src="getAvatarUrl(conversation.last_user_id, 'Users')" />
												</a>
											</div>
										</div>
										<div class="tags-container tags-sm" v-if="customers_tags_list[conversation.customer_id] && customers_tags_list[conversation.customer_id].length > 0">
											<span v-for="tag in customers_tags_list[conversation.customer_id].slice(0, 2)" class="tag" :data-type="tag.type" v-html="tag.name"></span>
											<div class="dropdown" v-if="customers_tags_list[conversation.customer_id].length > 2">
												<a onclick="void(0)" href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" v-html="'+' + (customers_tags_list[conversation.customer_id].length - 2)"></a>
												<div class="dropdown-menu dropdown-menu-right full-tags-container tags-sm" onclick="e.stoppropagation()">
													<span v-for="tag in customers_tags_list[conversation.customer_id]" class="tag" :data-type="tag.type" v-html="tag.name"></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="chatbox-footer">
						<div class="module-indicator-container">
							<div class="module-indicators">
								<div class="module-indicator CPTarget">
									<div class="circle-simbol"></div>
									<div class="module-name">{vtranslate('SINGLE_CPTarget', 'CPTarget')}</div>
								</div>
								<div class="module-indicator Leads">
									<div class="circle-simbol"></div>
									<div class="module-name">{vtranslate('SINGLE_Leads', 'Leads')}</div>
								</div>
								<div class="module-indicator Contacts">
									<div class="circle-simbol"></div>
									<div class="module-name">{vtranslate('SINGLE_Contacts', 'Contacts')}</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="popup-panel main-panel" v-show="size != 'MINIMIZED'">
				<div class="popup-header main-header">
					<div class="main-header-container">
						<div class="header-container customer-name-container" v-show="active_customer_profile.record_id">
							<span class="customer-name">
								<a target="_blank" :href="getRecordDetailUrl(active_conversation.customer_id, active_conversation.customer_type)" v-html="active_conversation.customer_name || active_conversation.customer_social_id"></a>
							</span>
							<div class="tags-list-container" v-if="active_customer_profile.record_module != 'CPTarget'"><i class="far fa-tag" @click="openTaggingModal" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_SELECT_TAG')}"></i></div>
							<div class="tags-list-container" v-if="active_customer_profile.record_module != 'CPTarget'" v-show="active_customer_profile.tags_list.length > 0">
								<span v-for="tag in active_customer_profile.tags_list.slice(0, 2)" class="tag" :data-type="tag.type" v-html="tag.name"></span>
								<div class="dropdown" v-show="active_customer_profile.tags_list.length > 2">
									<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" v-html="'+' + (active_customer_profile.tags_list.length - 2)"></a>
									<div class="dropdown-menu dropdown-menu-right full-tags-container">
										<span v-for="tag in active_customer_profile.tags_list" class="tag" :data-type="tag.type" v-html="tag.name"></span>
									</div>
								</div>
							</div>
						</div>
						<div class="header-container refresh-conversation-container" v-show="active_customer_profile.record_id">
							<span @click="openTransferChatPopup" v-show="active_customer_profile.has_full_permission == '1'" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_TRANSFER_CHAT_DESCRIPTION')}"><i class="far fa-people-arrows header-btn" aria-hidden="true"></i></span>
							<span class="refresh-conversation" v-b-tooltip.hover.noninteractive.bottom title="{vtranslate('LBL_SOCIAL_CHATBOX_REFRESH_DESCRIPTION')}" @click="refreshActiveConversation">
								<i v-show="!active_conversation.refresing" class="far fa-refresh" aria-hidden="true"></i>
								<i v-show="active_conversation.refresing" class="far fa-refresh fast-spin" aria-hidden="true"></i>
							</span>
						</div>
					</div>
				</div>
				<div class="popup-body main-body">
					<div class="body-container">
						<div class="messages-container body-content">
							<div class="scrollable-container fancyScrollbar" @scroll="handleConversationScroll">
								<div class="scrollable">
									<div class="messages" v-show="main_panel_body_loading == true">
										<div class="status-message"><i class="far fa-refresh fast-spin"></i></div>
									</div>
									<div class="messages" v-show="active_conversation.customer_id && main_panel_body_loading == false && active_conversation.messages.length == 0">
										<div class="status-message">{vtranslate('LBL_SOCIAL_CHATBOX_NO_MESSAGES')}</div>
									</div>
									<div class="messages" v-show="!active_conversation.customer_id && main_panel_body_loading == false">
										<div class="status-message">{vtranslate('LBL_SOCIAL_CHATBOX_NO_CONVERSATION_SELECTED')}</div>
									</div>
									<div class="messages" v-show="active_conversation.customer_id && main_panel_body_loading == false && active_conversation.messages.length > 0">
										<div class="message-container" v-for="message in active_conversation.messages" :data-msg-id="message.msg_id" :data-direction="message.msg_direction.toUpperCase()">
											<div class="message-time-container">
												<span v-html="formatMsgTime(message.msg_time)"></span>
											</div>
											<div class="message-content-container">
												<div class="customer-avatar-container" v-if="message.msg_direction.toUpperCase() == 'INBOUND'">
													<div class="customer-avatar">
														<img :src="getAvatarUrl(active_conversation.customer_id, active_conversation.customer_type, active_conversation.seed)" />
													</div>
												</div>

												<div class="message-content-wrapper">

													<div class="message-attachments-container" v-if="message.msg_type && ['link'].includes(message.msg_type)">
														<div class="message-content" v-for="attachment in message.msg_attachments" :data-type="message.msg_type">
															<div class="link-address">
																<a :href="getMediaUrl(attachment.url)" target="_blank" v-html="getMediaUrl(attachment.url)"></a>
															</div>
															<div class="link-container">
																<a class="link-thumb" target="_blank" :href="getMediaUrl(attachment.url)"S>
																	<div class="link-thumb-image photo-thumb">
																		<img v-if="getMediaUrl(attachment.thumb)" :src="getMediaUrl(attachment.thumb)" />
																	</div>
																	<div class="link-description">
																		<b v-if="attachment.description" v-html="attachment.description"></b>
																	</div>
																</a>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && ['video'].includes(message.msg_type)">
														<div class="message-content" v-for="attachment in message.msg_attachments" :data-type="message.msg_type">
															
															<div class="link-container">
																<a class="link-thumb" href="javascript:void" @click="openPlayVideoModal(attachment)">
																	<div class="link-thumb-image photo-thumb">
																		<img v-if="getMediaUrl(attachment.thumb)" :src="getMediaUrl(attachment.thumb)" />
																		<i class="fal fa-play-circle play-button"></i>
																	</div>
																	<div class="link-description">
																		<b v-if="attachment.description" v-html="attachment.description"></b>
																	</div>
																</a>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && ['image', 'gif'].includes(message.msg_type)">
														<div class="message-content" v-for="attachment in message.msg_attachments" :data-type="message.msg_type">
															<div class="link-container">
																<div class="link-thumb">
																	<div class="link-thumb-image photo-thumb" v-if="['image', 'gif'].includes(attachment.type)">
																		<a class="swipebox" :href="getMediaUrl(attachment.url)" onclick="event.preventDefault()" @mouseover="replayGifImage"><img v-if="attachment.url" :src="getMediaUrl(attachment.url)" /></a>
																	</div>
																	<div class="link-thumb-image photo-thumb" v-if="!['image', 'gif'].includes(attachment.type)">
																		<img v-if="attachment.url" :src="getMediaUrl(attachment.url)" />
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && ['audio'].includes(message.msg_type)" v-for="attachment in message.msg_attachments">
														<div class="attachment-download-container" v-if="message.msg_direction.toUpperCase() == 'OUTBOUND'">
															<div class="attachment-download">
																<a :href="getMediaUrl(attachment.url)" target="_blank" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_DOWNLOAD')}"><i class="far fa-download" aria-hidden="true"></i></a>
															</div>
														</div>
														<div class="message-content" :data-type="message.msg_type">
															<div class="file-container">
																<audio controls>
																	<source class="audio-url" :src="getMediaUrl(attachment.url)" type="audio/mpeg"/>
																</audio>
															</div>
														</div>
														<div class="attachment-download-container" v-if="message.msg_direction.toUpperCase() == 'INBOUND'">
															<div class="attachment-download">
																<a :href="getMediaUrl(attachment.url)" target="_blank" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_DOWNLOAD')}"><i class="far fa-download" aria-hidden="true"></i></a>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && ['file'].includes(message.msg_type)" v-for="attachment in message.msg_attachments">
														<div class="attachment-download-container" v-if="message.msg_direction.toUpperCase() == 'OUTBOUND'">
															<div class="attachment-download">
																<a :href="getMediaUrl(attachment.url)" target="_blank" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_DOWNLOAD')}"><i class="far fa-download" aria-hidden="true"></i></a>
															</div>
														</div>
														<div class="message-content" :data-type="message.msg_type">
															<div class="file-container">
																<div class="file-thumb">
																	<div class="file-thumb-image">
																		<i v-if="attachment.type == 'pdf'" class="fal fa-file-pdf pdf" aria-hidden="true"></i>
																		<i v-if="attachment.type == 'doc'" class="fal fa-file-word word" aria-hidden="true"></i>
																		<i v-if="attachment.type == 'docx'" class="fal fa-file-word word" aria-hidden="true"></i>
																		<i v-if="attachment.type == 'csv'" class="fal fa-file-excel excel" aria-hidden="true"></i>
																		<i v-if="attachment.type == 'xls'" class="fal fa-file-excel excel" aria-hidden="true"></i>
																		<i v-if="attachment.type == 'xlsx'" class="fal fa-file-excel excel" aria-hidden="true"></i>
																		<i v-if="message.msg_type == 'audio'" class="fal fa-file-audio audio" aria-hidden="true"></i>
																	</div>
																	<div class="file-thumb-name-container">
																		<div class="file-thumb-name">
																			<div class="file-name" :href="getMediaUrl(attachment.url)" target="_blank" v-b-tooltip.hover.noninteractive :title="attachment.name" v-html="attachment.name"></div>
																		</div>
																		<div v-if="attachment.size" class="file-size" v-html="getFileSize(attachment.size)"></div>
																	</div>
																</div>
															</div>
														</div>
														<div class="attachment-download-container" v-if="message.msg_direction.toUpperCase() == 'INBOUND'">
															<div class="attachment-download">
																<a :href="getMediaUrl(attachment.url)" target="_blank" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_DOWNLOAD')}"><i class="far fa-download" aria-hidden="true"></i></a>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && message.msg_type == 'list'">
														<div class="message-content" :data-type="message.msg_type">
															<div class="link-container" v-for="(attachment, index) in message.msg_attachments">
																<div class="link-thumb" v-if="index == 0">
																	<a :href="getMediaUrl(attachment.url)" target="_blank" class="link-thumb-image photo-thumb title">
																		<img :src="getMediaUrl(attachment.thumb)" />
																	</a>
																	<div class="link-thumb-title">
																		<a :href="getMediaUrl(attachment.url)" target="_blank"><strong v-html="attachment.title"></strong></a>
																	</div>
																	<div class="link-thumb-description">
																		<p v-html="attachment.description"></p>
																	</div>
																</div>
																<a :href="getMediaUrl(attachment.url)" target="_blank" class="link-thumb list-action" v-if="index != 0">
																	<div class="link-thumb-image photo-thumb list-action-icon">
																		<img width="30" height="30" :src="getMediaUrl(attachment.thumb)" />
																	</div>
																	<div class="link-thumb-title list-action-link" v-html="attachment.title"></div>
																</a>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && message.msg_type == 'card'">
														<div class="message-content" :data-type="message.msg_type">
															<div class="card-container" v-for="(attachment, index) in message.msg_attachments">
																<div class="card-thumbnail">
																	<div class="avatar-container">
																		<img :src="getMediaUrl(attachment.thumb)" />
																	</div>
																</div>
																<div class="card-content">
																	<div class="card-name">
																		<p v-html="message.msg_text"></p>
																	</div>
																	<div class="card-description">
																		<p v-if="attachment.description"><i class="fal fa-id-card"></i> <span v-html="getJsonContent(attachment.description)?.phone"></span></p>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div class="message-attachments-container" v-if="message.msg_type && message.msg_type == 'location'">
														<div class="message-content" :data-type="message.msg_type">
															<div class="link-container" v-for="(attachment, index) in message.msg_attachments">
																<div class="link-address">
																	<a :href="getGoogleMapUrl(attachment.latitude, attachment.longitude)" target="_blank"><i class="far fa-map-marker-alt"></i> <span>{vtranslate('LBL_SOCIAL_CHATBOX_CUSTOMER_SHARE_LOCATION')}</span></a>
																</div>
															</div>
														</div>
													</div>

													<div class="message-content" v-if="message.msg_text && message.msg_type == 'text'" data-type="text">
														<p v-html="getMessageText(message.msg_text)"></p>
													</div>
												
													<div class="message-sender-info-container" v-if="message.msg_direction.toUpperCase() == 'OUTBOUND'">
														<span class="message-sender-name" v-show="message.sender_name">{vtranslate('LBL_SOCIAL_CHATBOX_SEND_BY')}: <a :href="getUserProfileUrl(message.sender_id)" target="_blank" v-html="message.sender_name"></a></span>
														<span class="message-sender-name" v-show="!message.sender_name">{vtranslate('LBL_SOCIAL_CHATBOX_UNDEFINED_SENDER')}</span>
													</div>
												</div>
												
												<div class="user-avatar-container" v-if="message.msg_direction.toUpperCase() == 'OUTBOUND'">
													<div class="customer-avatar">
														<img :src="getAvatarUrl(message.sender_id, 'Users')" />
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="scroll-to-bottom-container" v-show="scroll_to_bottom_visibility">
								<div class="scroll-to-bottom-button" v-show="!having_new_message" @click="scrollMessagesToBottom" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_SCROLL_TO_BOTTOM')}">
									<i class="far fa-angle-down"></i>
								</div>
								<div class="new-message-notify-container" v-show="having_new_message" @click="scrollMessagesToBottom">
									<div class="new-message-notify">
										<span>{vtranslate('LBL_SOCIAL_CHATBOX_NEW_MESSAGE')} <i class="fal fa-angle-double-down"></i></span>
									</div>
								</div>
							</div>
						</div>
						<div class="conversation-status-container" v-show="active_conversation.customer_id">
							<div class="typing-status">
								<span v-html="active_conversation.active_typing_msg"></span>
							</div>
							<div class="outbound-users-history">
								<span href="javascript:void(0)"
									v-show="active_conversation.metadata.outbound_users_history?.length > 5"
									class="dropdown dropup show-all-container"
								>
									<span v-html="String('+').concat(String(active_conversation.metadata.outbound_users_history?.length - 5))"
										class="dropdown-toggle show-all" data-toggle="dropdown"
									></span>
									<div class="dropdown-menu dropdown-menu-up dropdown-menu-right show-all-dropdown">
										<div class="dropdown-title">Các nhân viên đã trò chuyện</div>
										<ul class="fancyScrollbar">
											<li class="outbound-user"
												v-for="(user, index) in active_conversation.metadata.outbound_users_history"
											>
												<a :href="getUserProfileUrl(user.id)" target="_blank">
												<img :src="getAvatarUrl(user.id, 'Users')" />
												<span class="user-name" v-html="user.full_name"></span>
												<span class="chat-time" v-html="formatMsgTime(user.last_msg_time)"></span>
											</li>
										</ul>
									</div>
								</span>
								<a v-for="(user, index) in active_conversation.metadata.outbound_users_history?.slice(-5)"
									:href="getUserProfileUrl(user.id)"
									target="_blank" class="last-user"
									v-b-tooltip
									:title="user.full_name + ' (' + formatMsgTime(user.last_msg_time) + ')'"
								>
									<img :src="getAvatarUrl(user.id, 'Users')" />
								</a>
							</div>
						</div>
						<div class="chatbox-footer">
							<div class="send-message-container" v-show="active_customer_profile.record_id && active_customer_profile.readonly != '1'">
								<div class="send-message">
									<textarea class="message fancyScrollbar" name="message" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_MESSAGE_PLACEHOLDER')}" v-model="active_conversation.message" @keyup="handleTyping" @keydown.enter="sendMessage"></textarea> {* Modified by Vu Mai on 2022-10-21 to support message textarea dynamic height *}
									<div class="attachment-btns">
										<label class="attachment-btn" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_MESSAGE_TEMPLATE_TITLE')}">
											<i class="far fa-comment-dots" @click="openMessageTemplateModal"></i>
										</label>
										<label class="send-image attachment-btn" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_SEND_IMAGE_TITLE')}">
											<i class="far fa-image" aria-hidden="true"></i>
											<input mutiple style="display: none" type="file" @change="sendImage" name="image" accept="image/x-png,image/gif,image/jpeg" />
										</label>
										<label class="send-file attachment-btn" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_SEND_FILE_TITLE')}">
											<i class="far fa-paperclip" aria-hidden="true"></i>
											<input mutiple style="display: none" type="file" @change="sendFile" name="file" accept=".csv, .doc, application/pdf" />
										</label>
									</div>
								</div>
							</div>
							<div class="send-message-readonly-container" v-show="active_customer_profile.readonly == '1'">
								{vtranslate('LBL_SOCIAL_CHATBOX_CUSTOMER_READ_ONLY_MSG')}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="popup-panel right-panel">
				<div class="popup-header right-header" @click="openFromMinimize">
					<div class="header-container customer-name-container">
						<div v-if="size == 'MINIMIZED'" class="customer-name topbar-icon">
							<span>{vtranslate('LBL_SOCIAL_CHATBOX_TITLE')}</span>
							<span class="social-page-counter bg-danger text-white badge" v-show="new_unread > 0" v-html="new_unread < 100 ? new_unread : '99+'"></span>
						</div>
					</div>
					<div class="header-button-wrapper">
						<button class="minimize-popup" @click="minimize" v-show="size == 'NORMALIZED'" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_MINIMIZE')}"><i class="far fa-window-minimize" aria-hidden="true"></i></button>
						<button class="normalize-popup" @click="normalize" v-show="size == 'MINIMIZED'" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_MAXIMIZE')}"><i class="far fa-window-maximize" aria-hidden="true"></i></button>
						<button class="close-popup" @click="close" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_CLOSE')}"><i class="far fa-times" aria-hidden="true"></i></button>
					</div>
				</div>
				<div class="popup-body right-body" v-show="size != 'MINIMIZED'">
					<div class="body-container" v-show="active_customer_profile_mode == 'detail'">
						<div class="detail-container body-content">
							<div class="customer-profile-container">
								<div v-show="active_customer_profile.success && !active_customer_profile.refresing && active_customer_profile.record_id" class="customer-profile">
									<div class="position-absolute customer-type" v-show="typeof active_customer_profile.record_module != 'undefined'">
										<span v-show="active_customer_profile.record_module === 'Contacts'">{vtranslate('Contacts', 'Contacts')}</span>
										<span v-show="active_customer_profile.record_module === 'Leads'">{vtranslate('Leads', 'Leads')}</span>
										<span v-show="active_customer_profile.record_module === 'CPTarget'">{vtranslate('CPTarget', 'CPTarget')}</span>
									</div>
									<div class="customer-avatar-container">
										<div class="customer-avatar">
											<img width="100" :src="getAvatarUrl(active_customer_profile.record_id, active_customer_profile.record_module, active_customer_profile.seed)" />
										</div>
										<div v-if="active_customer_profile.record_module" :class="'module-indicator ' + active_customer_profile.record_module">
											<div class="circle-simbol"></div>
										</div>
									</div>
									<div class="customer-name-container">
										<a v-if="!active_customer_profile.salutationtype && active_customer_profile.full_name" :href="getRecordDetailUrl(active_customer_profile.record_id, active_customer_profile.record_module)" target="_blank" class="customer-name" v-html="active_customer_profile.full_name"></a>
										<a v-if="active_customer_profile.salutationtype && active_customer_profile.full_name" :href="getRecordDetailUrl(active_customer_profile.record_id, active_customer_profile.record_module)" target="_blank" class="customer-name" v-html="picklist_fields.salutationtype[active_customer_profile.salutationtype] + ' ' + active_customer_profile.full_name"></a>
										<a v-if="!active_customer_profile.full_name" :href="getRecordDetailUrl(active_customer_profile.record_id, active_customer_profile.record_module)" target="_blank" class="customer-name" v-html="active_conversation.customer_social_id"></a>
										<a href="javascript:void(0)" class="edit-profile" v-show="active_customer_profile.has_full_permission == '1' && active_customer_profile.record_module != 'CPTarget'" @click="changeCustomerProfileMode('edit')" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_EDIT_PROFILE')}" style="margin-left: 10px"><i class="far fa-pen" aria-hidden="true"></i></a>
									</div>
									<div class="target-only" v-show="active_customer_profile.record_module == 'CPTarget'">
										<div class="text-center">
											<div class="dropdown add-to-crm-container">
												<button class="btn btn-default add-to-crm dropdown-toggle" data-toggle="dropdown">
													<span><i class="far fa-plus" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_ADD_TO_CRM')}</span>
												</button>
												<ul class="dropdown-menu">
													<li @click="changeCustomerProfileMode('edit', 'Leads')">
														<a href="javascript:void(0)" class="btn btn-default">
															<span>{vtranslate('LBL_SOCIAL_CHATBOX_ADD_TO_LEAD')}</span>
														</a>
													</li>
													<li @click="changeCustomerProfileMode('edit', 'Contacts')">
														<a href="javascript:void(0)" class="btn btn-default">
															<span>{vtranslate('LBL_SOCIAL_CHATBOX_ADD_TO_CONTACT')}</span>
														</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<hr v-show="active_customer_profile.record_module != 'CPTarget'" />
									<div class="not-target" v-show="active_customer_profile.record_module != 'CPTarget'">
										<div class="scrollable-container fancyScrollbar">
											<div class="scrollable">
												<div class="relative-comment">
													<h3 class="block-header">{vtranslate('LBL_SOCIAL_CHATBOX_CUSTOMER_PROFILE','ModComments')}</h3>
												</div>
												<div class="customer-bussiness-type" v-show="customer_type_config.customer_type == 'both' && active_customer_profile.record_module == 'Leads'">
													<div class="customer-type-container">
														<div class="customer-type" :data-active="active_customer_profile.leads_business_type == 'Personal Customer'" @click="updateCustomerType('Personal Customer')">Cá nhân</div>
														<div class="customer-type" :data-active="active_customer_profile.leads_business_type == 'Company Customer'" @click="updateCustomerType('Company Customer')">Tổ chức</div>
													</div>
												</div>
												<div class="profile-row phone">
													<div class="profile-row-icon">
														<i class="far fa-phone" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('LBL_MOBILE', 'CPTarget')}"></i>
													</div>
													<div class="profile-row-content">
														{if $OUT_GOING_CALL_PERMISSION && $CALL_CENTER_CONFIG['enable'] eq true}
															<a href="javascript:void(0)" v-show="active_customer_profile.mobile" v-html="active_customer_profile.mobile" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_MAKE_CALL')}" @click="makeCall($event.target, active_customer_profile.mobile, active_customer_profile.record_id)"></a>
														{else}
															<span v-show="active_customer_profile.mobile" v-html="active_customer_profile.mobile"></span>
														{/if}
														<span v-show="!active_customer_profile.mobile" class="no-data">-{vtranslate('LBL_MOBILE', 'CPTarget')}-</span>
													</div>
												</div>
												<div class="profile-row email">
													<div class="profile-row-icon">
														<i class="far fa-envelope" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('LBL_EMAIL', 'CPTarget')}"></i>
													</div>
													<div class="profile-row-content">
														<a v-show="active_customer_profile.email" class="emailField cursorPointer" v-html="active_customer_profile.email" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_SEND_EMAIL')}" href="javascript:void(0)" @click="openEmailComposer(active_customer_profile.record_id, active_customer_profile.record_module)"></a>
														<span v-show="!active_customer_profile.email" class="no-data">-{vtranslate('LBL_EMAIL', 'CPTarget')}-</span>
													</div>
												</div>
												<div class="profile-row full_address text">
													<div class="profile-row-icon">
														<i class="far fa-compass" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('LBL_LANE', 'CPTarget')}"></i>
													</div>
													<div class="profile-row-content">
														<a v-show="active_customer_profile.lane" v-html="active_customer_profile.lane" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_OPEN_MAP')}" :data-module="active_customer_profile.record_module" :data-record="active_customer_profile.record_id" href="javascript:void(0)" @click="showMap"></a>
														<span v-show="!active_customer_profile.lane" class="no-data">-{vtranslate('LBL_SOCIAL_CHATBOX_ADDRESS')}-</span>
													</div>
												</div>
												<div class="profile-row account">
													<div class="profile-row-icon">
														<i class="far fa-building" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('LBL_COMPANY', 'CPTarget')}"></i>
													</div>
													<div class="profile-row-content">
														<a v-show="active_customer_profile.record_module == 'Contacts' && active_customer_profile.company" v-html="active_customer_profile.company" :href="getRecordDetailUrl(active_customer_profile.account_id, 'Accounts')" target="_blank"></a>
														<span v-show="['Leads', 'CPTarget'].includes(active_customer_profile.record_module) && active_customer_profile.company" v-html="active_customer_profile.company"></span>
														<span v-show="!active_customer_profile.account_name && !active_customer_profile.company" class="no-data">-{vtranslate('LBL_COMPANY', 'CPTarget')}-</span>
													</div>
												</div>
												<div class="profile-row owner">
													<div class="profile-row-icon">
														<i class="far fa-user-circle" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('LBL_MAIN_OWNER_ID', 'CPTarget')}"></i>
													</div>
													<div class="profile-row-content">
														<a v-show="active_customer_profile.owner_id && active_customer_profile.owner_type == 'Users'" v-html="active_customer_profile.owner_name" :href="getUserProfileUrl(active_customer_profile.owner_id)" target="_blank"></a>
														<span v-show="active_customer_profile.owner_id && active_customer_profile.owner_type == 'Groups'" v-html="active_customer_profile.owner_name"></span>
														<span v-show="!active_customer_profile.owner_id" class="no-data">-{vtranslate('LBL_MAIN_OWNER_ID', 'CPTarget')}-</span>
													</div>
												</div>
												<div class="profile-row leadsource" v-show="active_customer_profile.record_module != 'CPTarget'">
													<div class="profile-row-icon">
														<i class="fal fa-file-import" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('Lead Source', 'Contacts')}"></i>
													</div>
													<div class="profile-row-content">
														<span v-show="active_customer_profile.leadsource" v-html="picklist_fields.leadsource[active_customer_profile.leadsource]"></span>
														<span v-show="!active_customer_profile.leadsource" class="no-data">-{vtranslate('Lead Source', 'Contacts')}-</span>
													</div>
												</div>
												<div class="profile-row description text">
													<div class="profile-row-icon">
														<i class="far fa-sticky-note" aria-hidden="true" v-b-tooltip.hover.noninteractive.left title="{vtranslate('LBL_DESCRIPTION', 'CPTarget')}"></i>
													</div>
													<div class="profile-row-content">
														<span v-show="active_customer_profile.description" v-b-tooltip.hover.noninteractive :title="active_customer_profile.description" v-html="active_customer_profile.description"></span>
														<span v-show="!active_customer_profile.description" class="no-data">-{vtranslate('LBL_DESCRIPTION', 'CPTarget')}-</span>
													</div>
												</div>
												<hr />
												<div class="relative-comment-container">
													<div class="relative-comment">
														<h3 class="block-header">{vtranslate('ModComments','ModComments')}</h3>
														<div class="comment-container">
															<div class="comment" v-for="comment in active_customer_profile.comments">
																<div class="commenter-avatar"><img :src="getAvatarUrl(comment.userid, 'Users')" /></div>
																<div class="comment-content" v-b-tooltip.hover.noninteractive :title="getCommentTitle(comment)" v-html="comment.commentcontent"></div>
															</div>
															<div class="no-comment" v-if="!active_customer_profile.comments || active_customer_profile.comments.length == 0">
																<span class="no-data">-{vtranslate('LBL_SOCIAL_CHATBOX_NO_COMMENT')}-</span>
															</div>
														</div>
														<div class="all-comment-container text-right" v-show="active_customer_profile.comments && active_customer_profile.comments.length > 0">
															<a :href="getCommentUrl(active_customer_profile.record_id, active_customer_profile.record_module)" target="_blank" class="btn btn-link">{vtranslate('LBL_SOCIAL_CHATBOX_VIEW_ALL')}</a>
														</div>
														<div class="profile-row">
															<div class="profile-row-content">
																<textarea style="display: none" name="commentcontent" v-model="active_customer_comment_form.commentcontent" data-rule-required="true"></textarea>
																<div id="addCommentTextArea" class="commentTextArea fancyScrollbar" contenteditable="true" placeholder="{vtranslate('LBL_WRITE_YOUR_COMMENT_HERE', 'ModComments')}"></div>
															</div>
														</div>
														<div class="profile-row">
															<div class="profile-row-content comment-actions">
																<div class="flex-v-center">
																	<div class="flex-left">
																		<a href="javascript:void(0)">
																			<label class="cursorPointer comment-attachment-btn">
																				<i class="far fa-paperclip" aria-hidden="true"></i><span> {vtranslate('LBL_SOCIAL_CHATBOX_ATTACHMENT')}</span>
																				<input type="file" name="attachements" style="display: none" @change="bindCommentAttachments" /> 
																			</label>
																		</a>
																	</div>
																	<div class="flex-right">
																		<i class="far fa-spinner fa-spin" v-show="posting_comment"></i>
																		<button class="btn btn-primary addAttachment" @click="saveComment" :disabled="posting_comment">{vtranslate('LBL_SOCIAL_CHATBOX_COMMENT_SEND')}</button>
																	</div>
																</div>
															</div>
														</div>
														<div class="profile-row" v-show="active_customer_comment_form.files.length > 0">
															<div class="profile-row-content comment-attachments">
																<div class="comment-attachment" v-for="file in active_customer_comment_form.files">
																	<div class="comment-attachment-actions">
																		<button class="remove-attachment-button" @click="removeCommentAttachment(file)"><i class="far fa-times" aria-hidden="true"></i></button>
																	</div>
																	<div class="comment-attachment-name" v-b-tooltip.hover.noninteractive :title="file.name">
																		<span class="file-name" v-html="file.base_name"></span>
																		<span class="file-extension" v-html="file.ext"></span>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<br />
											</div>
										</div>
									</div>
								</div>
								<div v-show="!active_customer_profile.success && !active_customer_profile.refresing" class="customer-profile">
									<div class="status-message">{vtranslate('LBL_SOCIAL_CHATBOX_ERROR_MSG')}</div>
									<div class="status-message"><a href="javascript:void(0)" @click="loadCustomerProfile()">{vtranslate('LBL_SOCIAL_CHATBOX_REFRESH')}</a></div>
								</div>
								<div v-show="active_customer_profile.refresing" class="customer-profile">
									<div class="status-message"><i class="far fa-refresh fast-spin"></i></div>
								</div>
								<div class="customer-profile">
								</div>
							</div>
						</div>
						<div class="send-message-container chatbox-footer">
							<div class="dropdown dropup actions-container transfer-chat-btn-container" v-show="active_customer_profile.record_id && active_customer_profile.record_module == 'CPTarget'">
								<button class="btn btn-default create-ticket transfer-chat-btn" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_TRANSFER_CHAT_DESCRIPTION')}" @click="openTransferChatPopup">
									<span><i class="far fa-people-arrows" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_TRANSFER_CHAT')}</span>
								</button>
							</div>
							<div class="dropdown dropup actions-container" v-show="active_customer_profile.record_id && active_customer_profile.record_module != 'CPTarget'">
								<button class="btn btn-default create-ticket text-left" @click="openQuickCreatePopup('HelpDesk')">
									<span><i class="far fa-plus" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_TICKET')}<span>
								</button>
								<button class="btn btn-default create-ticket text-left" v-show="active_customer_profile.record_module == 'Contacts'" @click="openQuickCreateSalesOrderPopup">
									<span><i class="fal fa-file-invoice" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_SALES_ORDER')}</span>
								</button>
								<div class="extra-actions-container">
									<button class="btn btn-detaul dropdown-toggle" data-toggle="dropdown"><i class="far fa-ellipsis-v-alt" aria-hidden="true"></i></button>
									<ul class="dropdown-menu dropdown-menu-up">
										<li v-show="active_customer_profile.starred == 0 || !active_customer_profile.starred" @click="toggleStarred(true)">
											<a href="javascript:void(0)" class="btn btn-default text-left">
												<span><i class="far fa-star" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_FOLLOW')}</span>
											</a>
										</li>
										<li v-show="active_customer_profile.starred == 1" @click="toggleStarred(false)">
											<a href="javascript:void(0)" class="btn btn-default text-left">
												<span><i class="far fa-star following" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_FOLLOWING')}</span>
											</a>
										</li>
										<li v-show="active_customer_profile.has_full_permission == '1' && active_customer_profile.record_module == 'Leads'" @click="openConvertLeadPopup">
											<a href="javascript:void(0)" class="btn btn-default text-left">
												<span><i class="far fa-share" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CONVERT')}</span>
											</a>
										</li>
										<li v-show="active_customer_profile.has_full_permission == '1' && active_customer_profile.record_module == 'CPTarget'" @click="convertTarget">
											<a href="javascript:void(0)" class="btn btn-default text-left">
												<span><i class="far fa-share" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CONVERT')}</span>
											</a>
										</li>
										<li v-show="active_customer_profile.record_module != 'CPTarget'">
											<a href="javascript:void(0)" class="btn btn-default text-left" @click="openQuickCreatePotentialPopup">
												<span><i class="far fa-sack-dollar" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_POTENTIAL')}</span>
											</a>
										</li>
										{* Modified by Hieu Nguyen on 2022-09-06 to display create buttons based on user permission *}
										{if Calendar_Module_Model::canCreateActivity('Call')}
											<li>
												<a href="javascript:void(0)" class="btn btn-default text-left" @click="openQuickCreatePopup('Events', 'Call')">
													<span><i class="far fa-phone-alt" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_CALL')}</span>
												</a>
											</li>
										{/if}
										{if Calendar_Module_Model::canCreateActivity('Meeting')}
											<li>
												<a href="javascript:void(0)" class="btn btn-default text-left" @click="openQuickCreatePopup('Events', 'Meeting')">
													<span><i class="far fa-users" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_MEETING')}</span>
												</a>
											</li>
										{/if}
										{if Calendar_Module_Model::canCreateActivity('Task')}
											<li>
												<a href="javascript:void(0)" class="btn btn-default text-left" @click="openQuickCreatePopup('Calendar')">
													<span><i class="far fa-tasks" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_CREATE_TASK')}</span>
												</a>
											</li>
										{/if}
										{if CPSocialIntegration_Config_Helper::isZaloMessageAllowed()}
											<li v-if="active_social_page.channel == 'Zalo'">
												<a href="javascript:void(0)" class="btn btn-default text-left" @click="requestShareZaloContactInfo">
													<span><i class="far fa-id-card" aria-hidden="true"></i> {vtranslate('LBL_SOCIAL_CHATBOX_REQUEST_SHARE_INFO')}</span>
												</a>
											</li>
										{/if}
										{* End Hieu Nguyen *}
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="body-container" v-show="active_customer_profile_mode == 'edit'">
						<div class="detail-container body-content">
							<form onsubmit="false" class="customer-profile-container edit-form">
								<div class="customer-profile">
									<div class="position-absolute customer-type" v-show="typeof active_customer_profile.record_module != 'undefined'">
										<span v-show="active_customer_profile.record_module === 'Contacts'">{vtranslate('Contacts', 'Contacts')}</span>
										<span v-show="active_customer_profile.record_module === 'Leads'">{vtranslate('Leads', 'Leads')}</span>
										<span v-show="active_customer_profile.record_module === 'CPTarget'">{vtranslate('CPTarget', 'CPTarget')}</span>
									</div>
									<div class="customer-avatar-container">
										<div class="customer-avatar">
											<img width="100" :src="customer_profile_form.avatar" />
											<label v-show="active_customer_profile.record_module === 'Contacts'" class="change-avatar">
												<i class="far fa-camera-retro" aria-hidden="true"></i>
												<input type="file" class="inputElement changeAvatarInput" name="imagename[]" accept="image/x-png,image/jpeg" style="display: none"/>
											</label>
										</div>
									</div>
									<hr />
									<div class="scrollable-container fancyScrollbar">
										<div class="scrollable">
											<div class="profile-row name">
												<div class="form-label">
													<i class="far fa-user" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_FULL_NAME')}"></i>
												</div>
												<div class="form-value">
													<div class="salutation-container">
														<select class="inputElement inputSelect" name="salutationtype" v-model="customer_profile_form.salutationtype">
															<option value=""></option>
															<option value="Mr." :selected="customer_profile_form.salutationtype == 'Mr.'">{vtranslate('Mr.')}</option>
															<option value="Ms." :selected="customer_profile_form.salutationtype == 'Ms.'">{vtranslate('Ms.')}</option>
														</select>
													</div>
													<input type="text" class="inputElement" name="lastname" v-model="customer_profile_form.lastname" placeholder="{vtranslate('LBL_LASTNAME', 'CPTarget')}" />
													<input type="text" class="inputElement" name="firstname" v-model="customer_profile_form.firstname" placeholder="{vtranslate('LBL_FIRSTNAME', 'CPTarget')}" />
												</div>
											</div>
											<div class="profile-row mobile">
												<div class="form-label">
													<i class="far fa-mobile" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_MOBILE', 'CPTarget')}"></i>
												</div>
												<div class="form-value">
													<input type="text" class="inputElement" name="mobile" v-model="customer_profile_form.mobile" @blur="handleBlur" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_MOBILE')}" />
													<span class="duplicate-warning" v-if="active_customer_profile.record_module == 'CPTarget' && duplicated_fields.phone" @click="openProcessDuplicatePopup" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_DUPLICATED_FOUND')}">
														<i class="fal fa-exclamation-triangle"></i>
													</span>
												</div>
											</div>
											<div class="profile-row email">
												<div class="form-label">
													<i class="far fa-envelope" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_EMAIL', 'CPTarget')}"></i>
												</div>
												<div class="form-value">
													<input type="text" class="inputElement" name="email" data-rule-email="true" v-model="customer_profile_form.email" @blur="handleBlur" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_EMAIL')}" />
													<span class="duplicate-warning" v-if="active_customer_profile.record_module == 'CPTarget' && duplicated_fields.email" @click="openProcessDuplicatePopup" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_SOCIAL_CHATBOX_DUPLICATED_FOUND')}">
														<i class="fal fa-exclamation-triangle"></i>
													</span>
												</div>
											</div>
											<div class="profile-row address">
												<div class="form-label">
													<i class="far fa-compass" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_LANE', 'CPTarget')}"></i>
												</div>
												<div class="form-value">
													<input type="text" class="inputElement" name="lane" v-model="customer_profile_form.lane" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_ADDRESS')}" />
													<input type="hidden" class="inputElement" name="city" v-model="customer_profile_form.city"/>
													<input type="hidden" class="inputElement" name="state" v-model="customer_profile_form.state" />
													<input type="hidden" class="inputElement" name="code" v-model="customer_profile_form.code" />
													<input type="hidden" class="inputElement" name="country" v-model="customer_profile_form.country" />
												</div>
											</div>
											<div class="profile-row account">
												<div class="form-label">
													<i class="far fa-building" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_COMPANY', 'CPTarget')}"></i>
												</div>
												<div class="form-value company-name" v-if="['CPTarget', 'Leads'].includes(customer_profile_form.record_module)">
													<div class="input-wrapper">
														<input type="text" class="inputElement" name="company" v-model="customer_profile_form.company" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_ACCOUNT')}" @keyup="handleSearchCompany" />
													</div>
													<div class="account-options-container" v-if="acount_search_results && acount_search_results.length > 0">
														<ul class="account-options fancyScrollbar">
															<li class="account-option" v-for="option in acount_search_results" v-html="option.label" @click="selectCompanyName(option.value, option.type)" :title="option.label"></li>
														</ul>
													</div>
												</div>
												<div class="form-value company-name account_id" v-if="customer_profile_form.record_module == 'Contacts'">
													<div class="input-wrapper">
														<input type="text" class="inputElement" name="company" v-model="customer_profile_form.company" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_ACCOUNT')}" @keyup="handleSearchCompany" />
														<input type="hidden" class="inputElement" name="account_id" v-model="customer_profile_form.account_id" />
														<button v-if="!customer_profile_form.account_id" @click="openQuickCreateAccount"><i class="far fa-plus" aria-hidden="true"></i></button>
														<button v-if="customer_profile_form.account_id" @click="clearAccountInfo"><i class="far fa-times" aria-hidden="true"></i></button>
													</div>
													<div class="account-options-container" v-if="acount_search_results && acount_search_results.length > 0">
														<ul class="account-options fancyScrollbar">
															<li class="account-option" v-for="option in acount_search_results" v-html="option.label" @click="selectCompanyName(option.value, option.type, option.id)" :title="option.label"></li>
														</ul>
													</div>
												</div>
											</div>
											<div class="profile-row main_owner_id">
												<div class="form-label">
													<i class="far fa-user-circle" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_MAIN_OWNER_ID', 'CPTarget')}"></i>
												</div>
												<div class="form-value">
													<input type="hidden" class="inputElement" name="main_owner_id" data-user-only="true" data-single-selection="true" data-assignable-users-only="true" v-model="customer_profile_form.owner_id" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_MAIN_OWNER_ID')}" />
												</div>
											</div>
											<div class="profile-row leadsource" v-show="customer_profile_form.record_module != 'CPTarget'">
												<div class="form-label">
													<i class="far fa-file-import" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('Lead Source', 'Contacts')}"></i>
												</div>
												<div class="form-value">
													{assign var=LEAD_SOURCES value=Vtiger_Util_Helper::getPickListValues('leadsource')}
													<select class="inputElement inputSelect" name="leadsource" v-model="customer_profile_form.leadsource">
														<option value="" :selected="!customer_profile_form.leadsource">{vtranslate('LBL_SELECT_OPTION')}</option>
														{foreach from=$LEAD_SOURCES item=leadSource}
															<option value="{$leadSource}" :selected="customer_profile_form.leadsource == '{$leadSource}'">{vtranslate($leadSource, 'Contacts')}</option>
														{/foreach}
													</select>
												</div>
											</div>
											<div class="profile-row description">
												<div class="form-label">
													<i class="far fa-sticky-note" aria-hidden="true" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_DESCRIPTION', 'CPTarget')}"></i>
												</div>
												<div class="form-value">
													<textarea class="inputElement" rows="4" name="description" v-model="customer_profile_form.description" placeholder="{vtranslate('LBL_SOCIAL_CHATBOX_PLACEHOLDER_DESCRIPTION')}"></textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="send-message-container chatbox-footer">
							<div class="profile-actions">
								<button class="cancel btn btn-default cancel" @click="changeCustomerProfileMode('detail')">{vtranslate('LBL_CANCEL')}</button>
								<button class="cancel btn btn-primary submit" @click="saveCustomerProfile">{vtranslate('LBL_SAVE')}</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		{* End Phu Vo *}
		
		{* Added by Phu Vo on 2021.01.13 to include Social Chatbox Popup *}
		<link type="text/css" rel="stylesheet" href="{vresource_url('modules/CPSocialIntegration/resources/SocialChatboxPopup.css')}" />
		<script src="{vresource_url('layouts/v7/modules/Vtiger/resources/Detail.js')}"></script>
		<script src="{vresource_url('layouts/v7/modules/Leads/resources/Detail.js')}"></script>
		<script src="{vresource_url('modules/CPSocialIntegration/resources/SocialChatboxPopup.js')}"></script>
		{* End Phu Vo *}

		{* Connection setup *}
		{assign var='CHATBOX_CONFIG' value=getGlobalVariable('centralizedChatboxConfig')}
		{assign var='CHATBOX_BRIDGE_SERVER_PROTOCOL' value="{if $CHATBOX_CONFIG.chat_bridge.server_ssl}https{else}http{/if}"}
		{assign var='CHATBOX_BRIDGE_SERVER_URL' value="{$CHATBOX_BRIDGE_SERVER_PROTOCOL}://{$CHATBOX_CONFIG.chat_bridge.server_name}:{$CHATBOX_CONFIG.chat_bridge.server_port}"}
		{assign var='CHATBOX_BRIDGE_ACCESS_DOMAIN' value="{$CHATBOX_CONFIG.chat_bridge.access_domain}"}
		{assign var='CHATBOX_BRIDGE_ACCESS_TOKEN' value="{CPSocialIntegration_Chatbox_Helper::getChatboxBridgeAccessToken()}"}
		{assign var='CHATBOX_STORAGE_SERVER_URL' value="{$CHATBOX_CONFIG.chat_storage.service_url}"}

		<script>var _CHATBOX_BRIDGE_SERVER_URL = '{$CHATBOX_BRIDGE_SERVER_URL}';</script>
		<script>var _CHATBOX_BRIDGE_ACCESS_DOMAIN = '{$CHATBOX_BRIDGE_ACCESS_DOMAIN}';</script>
		<script>var _CHATBOX_BRIDGE_ACCESS_TOKEN = '{$CHATBOX_BRIDGE_ACCESS_TOKEN}';</script>
		<script>var _CHATBOX_STORAGE_SERVER_URL = '{$CHATBOX_STORAGE_SERVER_URL}';</script>
		<script src="{vresource_url('resources/libraries/SocketIO/socket.io.js')}"></script>
		<script src="{vresource_url('modules/CPSocialIntegration/resources/SocialChatClient.js')}" async defer></script>
		{* End Connection setup *}
	{/if}
{/strip}