{*
    Name : ChatBotIntegration
    Author : Phu Vo
    Date : 2020.06.19
*}

<form name="configs" style="padding-bottom: 20px;">
    <div class="editViewContents">
        <div class="fieldBlockContainer">
            <h4 class="fieldBlockHeader">{vtranslate('LBL_CHAT_BOT_INTEGRATION_CONFIG', $MODULE_NAME)}</h4>
            <hr />
            <h5 class="config-header">{vtranslate('LBL_CHAT_BOT_GENERAL_CONFIG', $MODULE_NAME)}</h5>
            <hr />
            <div class="formCell">{vtranslate('LBL_CHAT_BOT_DEFAULT_ASSIGNEE_LABEL', $MODULE_NAME)}</div>
            <div class="formValue">
                <div class="select-users-wraper" style="max-width: 340px; display: flex">
                    <input
                        type="text" autocomplete="off" class="inputElement select-users" style="width: 100%"
                        placeholder="{vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG_CHOOSE_USER_PLACEHOLDER', $MODULE_NAME)}"
                        name="default_assignee"
                        data-user-only="true"
                        {if $CONFIG['default_assignee']}data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG['default_assignee']))}'{/if}
                    />
                </div>
            </div>
            <h5 class="config-header">{vtranslate('LBL_CHAT_BOT_CONFIG', $MODULE_NAME)}</h5>
            <hr />
            <table class="configDetails" style="width: 100%">
                <tr>
                    <td class="fieldLabel alignTop">{vtranslate('LBL_CHAT_BOT_CHAT_CHANEL', $MODULE_NAME)}</td>
                    <td class="fieldValue alignTop current_channel">
                        <select class="select2 inputElement" name="current_channel">
                            <option value="">{vtranslate('LBL_CHAT_BOT_CHAT_CHANEL_SELECT', $MODULE_NAME)}</option>
                            <option value="Hana" {if $CONFIG['current_channel'] == 'Hana'}selected{/if}>Hana.ai</option>
                            <option value="BotBanHang" {if $CONFIG['current_channel'] == 'BotBanHang'}selected{/if}>Botbanhang.vn</option>
                            <option value="messnow" {if $CONFIG['current_channel'] == 'messnow'}selected{/if}>Messnow</option>
                        </select>
                    </td>
                    <td class="fieldLabel alignTop"></td>
                    <td class="fieldValue alignTop"></td>
                </tr>
            </table>
            <div id="bot-default" class="bot-channel" style="display: none">
                <div class="formCell">{vtranslate('LBL_CHAT_BOT_SELECT_CHAT_BOT_INFO', $MODULE_NAME)}</div>
            </div>

            <!-- Config for Hana.ai -->
            <div id="Hana" class="bot-channel" style="display: none">
                <div class="formCell">{vtranslate('LBL_CHAT_BOT_CHATBOT_LIST', $MODULE_NAME)}</div>
                <div class="formValue" style="width: 50%">
                    <ul class="form-list">
                        {if $CONFIG['chatbot_config'] && $CONFIG['current_channel'] == 'Hana'}
                            {assign var=index value=0}
                            {foreach from=$CONFIG['chatbot_config']['chatbots'] item=chatbot}
                                <li data-id="{$chatbot['id']}" data-name="{$chatbot['name']}" data-auth_token="{$chatbot['auth_token']}">
                                    <span class="label">{$index + 1}. {$chatbot['name']}</span>
                                    <span class="actions">
                                        <a href="javascript:void(0)" class="btn btn-default editChatbot" onclick="app.controller().showHanaBotModal('edit', this)"><i class="far fa-pen" aria-hidden="true"></i></a>
                                        <a href="javascript:void(0)" class="btn btn-default removeChatbot"><i class="far fa-times" aria-hidden="true"></i></a>
                                    </span>
                                </li>
                                {assign var=index value=$index+1}
                            {/foreach}
                        {/if}
                    </ul>
                    <div class="list-template" style="display: none">
                        <li>
                            <span class="label"></span>
                            <span class="actions">
                                <a href="javascript:void(0)" class="btn btn-default editChatbot" onclick="app.controller().showHanaBotModal('edit', this)"><i class="far fa-pen" aria-hidden="true"></i></a>
                                <a href="javascript:void(0)" class="btn btn-default removeChatbot"><i class="far fa-times" aria-hidden="true"></i></a>
                            </span>
                        </li>
                    </div>
                </div>
                <div class="formCell" style="padding-top: 0px">
                    <a href="javascript:void(0)" class="btn btn-link addChatBot" onclick="app.controller().showHanaBotModal('add')">
                        <i class="far fa-plus" aria-hidden="true" style="margin-right: 4px"></i>
                        {vtranslate('LBL_CHAT_BOT_ADD_CHATBOT', $MODULE_NAME)}
                    </a>
                </div>
            </div>
            <!-- End Config for Hana.ai -->

            <!-- Start Config for botbanghang -->
            <div id="BotBanHang" class="bot-channel" style="display: none">
                <div class="formCell">{vtranslate('LBL_CHAT_BOT_CHATBOT_LIST', $MODULE_NAME)}</div>
                <div class="formValue" style="width: 50%">
                    <ul class="form-list">
                        {if $CONFIG['chatbot_config'] && $CONFIG['current_channel'] == 'BotBanHang'}
                            {assign var=index value=0}
                            {foreach from=$CONFIG['chatbot_config']['chatbots'] item=chatbot}
                                <li data-id="{$chatbot['id']}" data-name="{$chatbot['name']}" data-access_token="{$chatbot['access_token']}">
                                    <span class="label">{$index + 1}. {$chatbot['name']}</span>
                                    <span class="actions">
                                        <a href="javascript:void(0)" class="btn btn-default editChatbot" onclick="app.controller().showBBHBotModal('edit', this)"><i class="far fa-pen" aria-hidden="true"></i></a>
                                        <a href="javascript:void(0)" class="btn btn-default removeChatbot"><i class="far fa-times" aria-hidden="true"></i></a>
                                    </span>
                                </li>
                                {assign var=index value=$index+1}
                            {/foreach}
                        {/if}
                    </ul>
                    <div class="list-template" style="display: none">
                        <li>
                            <span class="label"></span>
                            <span class="actions">
                                <a href="javascript:void(0)" class="btn btn-default editChatbot" onclick="app.controller().showBBHBotModal('edit', this)"><i class="far fa-pen" aria-hidden="true"></i></a>
                                <a href="javascript:void(0)" class="btn btn-default removeChatbot"><i class="far fa-times" aria-hidden="true"></i></a>
                            </span>
                        </li>
                    </div>
                </div>
                <div class="formCell" style="padding-top: 0px">
                    <a href="javascript:void(0)" class="btn btn-link addChatBot" onclick="app.controller().showBBHBotModal('add', this)">
                        <i class="far fa-plus" aria-hidden="true" style="margin-right: 4px"></i>
                        {vtranslate('LBL_CHAT_BOT_ADD_CHATBOT', $MODULE_NAME)}
                    </a>
                </div>
            </div>
            <!-- End Config for botbanghang -->

        </div>
    </div>
    <div class="modal-overlay-footer clearfix">
        <div class="row clear-fix">
            <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
                <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
            </div>
        </div> 
    </div>
</form>
<div style="display: none">
    <div class="editChatbotModal modal-dialog modal-md modal-content">
        {assign var=HEADER_TITLE value=vtranslate('LBL_CHAT_BOT_NEW_CHATBOT', $MODULE_NAME)}
        {include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$HEADER_TITLE}
        <form name="edit_chatbot" class="form-horizontal">
            <div class="form-content fancyScrollbar" style="padding: 15px">
                <table class="table no-border fieldBlockContainer">
                    <tr>
                        <td class="fieldLabel col-lg-4">ID <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input name="id" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                    <tr>
                        <td class="fieldLabel col-lg-4">Name <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input name="name" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                    <tr class="Hana-input bot-input">
                        <td class="fieldLabel col-lg-4">Auth Token <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input type="password" name="auth_token" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                    <tr class="BotBanHang-input bot-input">
                        <td class="fieldLabel col-lg-4">Access Token <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input type="password" name="access_token" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                </table>
            </div>
            {include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
        </form>
    </div>
</div>