{*
    File SocialIntegrationConfig.tpl
    Author: Hieu Nguyen
    Date: 2019-07-08
    Purpose: to render the UI for Social config
*}

{strip}
    <!-- Main Form -->
    <form name="settings">
        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_SOCIAL_CONFIG_TITLE', $MODULE_NAME)}</h4>
                    <hr />

                    <div class="contents tabbable">
                        <ul class="nav nav-tabs marginBottom10px">
                            <li class="channels active"><a data-toggle="tab" href="#channels"><strong>{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS', $MODULE_NAME)}</strong></a></li>
                            {* <li class="leadGeneration"><a data-toggle="tab" href="#leadGeneration"><strong>{vtranslate('LBL_SOCIAL_CONFIG_LEAD_GENERATION', $MODULE_NAME)}</strong></a></li> *}
                            <li class="permissions"><a data-toggle="tab" href="#permissions"><strong>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS', $MODULE_NAME)}</strong></a></li>
                            <li class="permissions"><a data-toggle="tab" href="#chatDistribution"><strong>{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION', $MODULE_NAME)}</strong></a></li>
                        </ul>
                        <div class="tab-content overflowVisible">
                            <!-- Channels -->
                            <div class="tab-pane active" id="channels">
                                <h6>{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_HINT', $MODULE_NAME)}</strong></h6>

                                <table id="social-channels" class="table-condensed" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_ENABLE_DISABLE', $MODULE_NAME)}</th>
                                            <th>{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_TITLE', $MODULE_NAME)}</th>
                                            <th>{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_LINKS', $MODULE_NAME)}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center alignTop" width="120px">
                                                <input type="checkbox" name="settings[channels][Facebook][enabled]" class="bootstrap-switch hide" {if count($FB_FANPAGE_LIST) eq 0}disabled{/if} {if count($FB_FANPAGE_LIST) > 0 && $CONFIG['channels']['Facebook']['enabled'] eq '1'}checked{/if}>
                                            </td>
                                            <td width="200px" class="alignTop">
                                                <div class="channels-wraper">
                                                    <img src="resources/images/facebook.png" width="50px"/>&nbsp;{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_FB_FANPAGE', $MODULE_NAME)}
                                                </div>
                                            </td>
                                            <td>
                                                <div id="fb-fanpage-list" class="item-list row">
                                                    {foreach from=$FB_FANPAGE_LIST item=FANPAGE}
                                                        <div class="channel-item col-sm-6">
                                                            <div class="avatar-wraper">
                                                                <div class="avatar">
                                                                    <img src="{$FANPAGE['avatar']}"/>
                                                                </div>
                                                            </div>
                                                            <div class="row info" data-row-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FANPAGE))}'>
                                                                <div class="fanpage-name">{$FANPAGE['name']}</div>
                                                                <span class="fanpage-expiry-date label label-{if $FANPAGE['token_issue_status'] eq 'valid'}success{elseif $FANPAGE['token_issue_status'] eq 'close'}warning{else}danger{/if}">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_EXPIRY', $MODULE_NAME)}:&nbsp;{if !empty($FANPAGE['token_expiry_time'])}{$FANPAGE['token_expiry_time']}{else}{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_EXPIRED', $MODULE_NAME)}{/if}</span>
                                                                <div class="followers-count">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_FOLLOWERS', $MODULE_NAME)}: <span>{if !empty($FANPAGE['followers_count'])}{$FANPAGE['followers_count']}{else}0{/if}</span></div>
                                                                <div class="item-actions row">
                                                                    <span><a href="javascript:void(0)" class="disconnect disconnect-fb-fanpage redColor">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_DISCONNECT', $MODULE_NAME)}</a></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/foreach}

                                                    {if !isForbiddenFeature('FacebookIntegration')}
                                                        <div class="add-item-placeholder col-sm-6">
                                                            <a id="add-fb-fanpage" href="javascript:void(0)">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_ADD_FB_FANPAGE', $MODULE_NAME)}</a>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr width="300px">
                                            <td class="text-center alignTop" >
                                                <input type="checkbox" name="settings[channels][Zalo][enabled]" class="bootstrap-switch hide" {if count($ZALO_OA_LIST) eq 0}disabled{/if} {if count($ZALO_OA_LIST) > 0 && $CONFIG['channels']['Zalo']['enabled'] eq '1'}checked{/if}>
                                            </td>
                                            <td class="alignTop">
                                                <div class="channels-wraper">
                                                    <img src="resources/images/zalo.png" width="50px"/>&nbsp;{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_ZALO_OA', $MODULE_NAME)}
                                                </div>
                                            </td>
                                            <td>
                                                <div id="zalo-oa-list" class="item-list row">
                                                    {foreach from=$ZALO_OA_LIST item=OA}
                                                        <div class="channel-item col-sm-6">
                                                            <div class="avatar-wraper">
                                                                <div class="avatar">
                                                                    <img src="{$OA['avatar']}"/>
                                                                </div>
                                                                <div class="item-actions row">
                                                                    <label><input type="checkbox" name="settings[zalo_oa_tokens][{$OA['id']}][is_shop]" {if $OA['is_shop'] eq 1}checked{/if} {if $OA['token_issue_status'] eq 'expired' || $OA['token_issue_status'] eq 'not_connected'}disabled{/if}/><span>{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_IS_SHOP', $MODULE_NAME)}</span></label>
                                                                </div>
                                                            </div>
                                                            <div class="row info" data-row-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($OA))}'>
                                                                <div class="oa-name">{$OA['name']}</div>
                                                                <span class="oa-expiry-date label label-{if $OA['token_issue_status'] eq 'valid'}success{elseif $OA['token_issue_status'] eq 'close'}warning{else}danger{/if}">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_EXPIRY', $MODULE_NAME)}:&nbsp;{if !empty($OA['token_expiry_time'])}{$OA['token_expiry_time']}{else}{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_EXPIRED', $MODULE_NAME)}{/if}</span>
                                                                <div class="followers-count">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_FOLLOWERS', $MODULE_NAME)}: <span>{if !empty($OA['followers_count'])}{$OA['followers_count']}{else}0{/if}</span></div>
                                                                <div class="item-actions row">
                                                                    <span><a href="javascript:void(0)" class="syncZaloFollowerIds">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_GET_FOLLOWER_IDS', $MODULE_NAME)}</a></span>
                                                                    <span><a href="javascript:void(0)" class="disconnect disconnect-zalo-oa redColor">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_DISCONNECT', $MODULE_NAME)}</a></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/foreach}

                                                    {if !isForbiddenFeature('ZaloIntegration')}
                                                        <div class="add-item-placeholder col-sm-6">
                                                            <a id="add-zalo-oa" href="javascript:void(0)">{vtranslate('LBL_SOCIAL_CONFIG_CHANNELS_ADD_ZALO_OA', $MODULE_NAME)}</a>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Lead Generation -->
                            {* <div class="tab-pane" id="leadGeneration">
                                <h6>{vtranslate('LBL_SOCIAL_CONFIG_LEAD_GENERATION_HINT', $MODULE_NAME)}</strong></h6>

                                <div class="contents tabbable">
                                    <ul class="nav nav-tabs marginBottom10px">
                                        <li class="fb-lead-generation active"><a data-toggle="tab" href="#fb-lead-generation"><strong>{vtranslate('LBL_SOCIAL_CONFIG_FB', $MODULE_NAME)}</strong></a></li>
                                        <li class="zalo-lead-generation"><a data-toggle="tab" href="#zalo-lead-generation"><strong>{vtranslate('LBL_SOCIAL_CONFIG_ZALO', $MODULE_NAME)}</strong></a></li>
                                    </ul>
                                    <div class="tab-content overflowVisible">
                                        <div class="tab-pane active" id="fb-lead-generation">
                                            <table class="lead-generation-content-table">
                                                <thead>
                                                    <tr>
                                                        <th>{vtranslate('LBL_SOCIAL_CONFIG_ACTION', $MODULE_NAME)}</th>
                                                        <th>{vtranslate('LBL_SOCIAL_CONFIG_CONVERT_TO', $MODULE_NAME)}</th>
                                                        <th>{vtranslate('LBL_SOCIAL_CONFIG_STATUS', $MODULE_NAME)}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_FB_LIKE_FANPAGE', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][like_fanpage_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['like_fanpage_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['like_fanpage_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['like_fanpage_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][like_fanpage_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['like_fanpage_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_FB_COMMENT_FANPAGE_POST', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][comment_fanpage_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][comment_fanpage_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="content">{vtranslate('LBL_SOCIAL_CONFIG_FB_COMMENT_FANPAGE_POST_WITH_KEYWORD', $MODULE_NAME)}</div>
                                                            <div class="dynamic-input">
                                                                <div class="keywords"></div>
                                                                <div class="add-keyword-container">
                                                                    <span class="add-keyword">{vtranslate('LBL_SOCIAL_CONFIG_ADD_MORE_KEYWORD', $MODULE_NAME)} :</span>
                                                                    <span class="dynamic-input-add-container"><input class="dynamic-input-add" placeholder="{vtranslate('LBL_SOCIAL_CONFIG_FB_ADD_KEYWORD_PLACEHOLDER', $MODULE_NAME)}" /></span>
                                                                    <input type="hidden" name="settings[lead_generation][Facebook][comment_fanpage_post_with_keywords]" class="dynamic-input-data" value='{ZEND_JSON::encode($CONFIG['lead_generation']['Facebook']['comment_fanpage_post_with_keywords'])}' />
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][comment_fanpage_with_keyword_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_with_keyword_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_with_keyword_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_with_keyword_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][comment_fanpage_with_keyword_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['comment_fanpage_with_keyword_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_FB_SEND_MESSAGE_TO_FANPAGE', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][send_message_fanpage_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][send_message_fanpage_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="content">{vtranslate('LBL_SOCIAL_CONFIG_FB_SEND_MESSAGE_TO_FANPAGE_WITH_KEYWORD', $MODULE_NAME)}</div>
                                                            <div class="dynamic-input">
                                                                <div class="keywords"></div>
                                                                <div class="add-keyword-container">
                                                                    <span class="add-keyword">{vtranslate('LBL_SOCIAL_CONFIG_ADD_MORE_KEYWORD', $MODULE_NAME)} :</span>
                                                                    <span class="dynamic-input-add-container"><input class="dynamic-input-add" placeholder="{vtranslate('LBL_SOCIAL_CONFIG_FB_ADD_KEYWORD_PLACEHOLDER', $MODULE_NAME)}" /></span>
                                                                    <input type="hidden" name="settings[lead_generation][Facebook][send_message_to_fanpage_with_keywords]" class="dynamic-input-data" value='{ZEND_JSON::encode($CONFIG['lead_generation']['Facebook']['send_message_to_fanpage_with_keywords'])}' />
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][send_message_fanpage_with_keyword_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_with_keyword_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_with_keyword_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_with_keyword_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][send_message_fanpage_with_keyword_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['send_message_fanpage_with_keyword_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_FB_LIKE_ADVERTISEMENT', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][like_advertisement_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['like_advertisement_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['like_advertisement_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['like_advertisement_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][like_advertisement_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['like_advertisement_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_FB_COMMENT_ADVERTISEMENT', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][comment_advertisement_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][comment_advertisement_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="content">{vtranslate('LBL_SOCIAL_CONFIG_FB_COMMENT_ADVERTISEMENT_WITH_KEYWORD', $MODULE_NAME)}</div>
                                                            <div class="dynamic-input">
                                                                <div class="keywords"></div>
                                                                <div class="add-keyword-container">
                                                                    <span class="add-keyword">{vtranslate('LBL_SOCIAL_CONFIG_ADD_MORE_KEYWORD', $MODULE_NAME)} :</span>
                                                                    <span class="dynamic-input-add-container"><input class="dynamic-input-add" placeholder="{vtranslate('LBL_SOCIAL_CONFIG_FB_ADD_KEYWORD_PLACEHOLDER', $MODULE_NAME)}" /></span>
                                                                    <input type="hidden" name="settings[lead_generation][Facebook][comment_advertisement_with_keywords]" class="dynamic-input-data" value='{ZEND_JSON::encode($CONFIG['lead_generation']['Facebook']['comment_advertisement_with_keywords'])}' />
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select name="settings[lead_generation][Facebook][comment_advertisement_with_keyword_convert]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_with_keyword_convert'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_with_keyword_convert'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_with_keyword_convert'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Facebook][comment_advertisement_with_keyword_convert_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Facebook']['comment_advertisement_with_keyword_convert_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="zalo-lead-generation">
                                            <table class="lead-generation-content-table">
                                                <thead>
                                                    <tr>
                                                        <th>{vtranslate('LBL_SOCIAL_CONFIG_ACTION', $MODULE_NAME)}</th>
                                                        <th>{vtranslate('LBL_SOCIAL_CONFIG_CONVERT_TO', $MODULE_NAME)}</th>
                                                        <th>{vtranslate('LBL_SOCIAL_CONFIG_STATUS', $MODULE_NAME)}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_ZALO_FOLLOW_OA', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Zalo][follow]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Zalo']['follow'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Zalo']['follow'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Zalo']['follow'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Zalo][follow_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Zalo']['follow_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_ZALO_SEND_MESSAGE_QA', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Zalo][user_send_message]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Zalo']['user_send_message'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Zalo']['user_send_message'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Zalo']['user_send_message'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Zalo][user_send_message_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Zalo']['user_send_message_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_ZALO_SEEN_QA_MESSAGE', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Zalo][user_seen_message]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Zalo']['user_seen_message'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Zalo']['user_seen_message'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Zalo']['user_seen_message'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Zalo][user_seen_message_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Zalo']['user_seen_message_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_ZALO_AUTHENTICATED_ZALO_WIFI_ACCESS', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Zalo][user_authentication]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Zalo']['user_authentication'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Zalo']['user_authentication'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Zalo']['user_authentication'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Zalo][user_authentication_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Zalo']['user_authentication_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{vtranslate('LBL_SOCIAL_CONFIG_ZALO_SHARE_INFORMATION_ACCEPTED', $MODULE_NAME)}</td>
                                                        <td>
                                                            <select name="settings[lead_generation][Zalo][user_submit_info]" class="convert-option select2">
                                                                <option value="CPTarget" {if $CONFIG['lead_generation']['Zalo']['user_submit_info'] eq 'CPTarget'}selected{/if}>{vtranslate('SINGLE_CPTarget', 'CPTarget')}</option>
                                                                <option value="Leads" {if $CONFIG['lead_generation']['Zalo']['user_submit_info'] eq 'Leads'}selected{/if}>{vtranslate('SINGLE_Leads', 'Leads')}</option>
                                                                <option value="Contacts" {if $CONFIG['lead_generation']['Zalo']['user_submit_info'] eq 'Contacts'}selected{/if}>{vtranslate('SINGLE_Contacts', 'Contacts')}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input name="settings[lead_generation][Zalo][user_submit_info_enabled]" type="checkbox" class="bootstrap-switch hide" {if $CONFIG['lead_generation']['Zalo']['user_submit_info_enabled'] eq '1'}checked{/if}>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div> *}

                            <!-- Permissions -->
                            <div class="tab-pane" id="permissions">
                                <h6>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_HINT', $MODULE_NAME)}</strong></h6>

                                <div class="contents tabbable">
                                    <ul class="nav nav-tabs marginBottom10px">
                                        <li class="fb-permissions active"><a data-toggle="tab" href="#fb-permissions"><strong>{vtranslate('LBL_SOCIAL_CONFIG_FB', $MODULE_NAME)}</strong></a></li>
                                        <li class="zalo-permissions"><a data-toggle="tab" href="#zalo-permissions"><strong>{vtranslate('LBL_SOCIAL_CONFIG_ZALO', $MODULE_NAME)}</strong></a></li>
                                    </ul>
                                    <div class="tab-content overflowVisible">
                                        <div class="tab-pane active" id="fb-permissions">
                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_CREATE_POST_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][create_post_allowed_roles]" multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['create_post_allowed_roles']))}'
                                                            data-rule-required="true"
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['create_post_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_DELETE_POST_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][delete_post_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['delete_post_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['delete_post_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_COMMENT_REACT_POST_AND_REPLY_MESSAGE_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][comment_react_post_and_reply_message_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['comment_react_post_and_reply_message_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['comment_react_post_and_reply_message_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_PROFILE_POST_MESSAGE_READ_ONLY_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][profile_post_message_read_only_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['profile_post_message_read_only_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['profile_post_message_read_only_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_ACCESS_LEAD_FORM_SYNCHRONIZED_LEADS_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][access_lead_form_synchronized_leads_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['access_lead_form_synchronized_leads_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['access_lead_form_synchronized_leads_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_CONTROL_LEAD_FORM_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][control_lead_form_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['control_lead_form_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['control_lead_form_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_FACEBOOK_DELETE_LEAD_FORM_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Facebook][delete_lead_form_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Facebook']['delete_lead_form_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Facebook']['delete_lead_form_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="zalo-permissions">
                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_ZALO_MESSAGE_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Zalo][message_allowed_roles]" multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Zalo']['message_allowed_roles']))}'
                                                            data-rule-required="true"
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Zalo']['message_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSIONS_ZALO_BROADCAST_ALLOWED_ROLES', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <select name="settings[permissions][Zalo][broadcast_allowed_roles]" 
                                                            multiple="true"
                                                            class="form-control select2" 
                                                            data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($CONFIG['permissions']['Zalo']['broadcast_allowed_roles']))}'
                                                            data-rule-required="true"    
                                                        >
                                                            {foreach from=$ROLE_LIST item=ROLE}
                                                                {assign var=roleid value=$ROLE->get('roleid')}
                                                                {assign var=rolename value=$ROLE->get('rolename')}
                                                                <option value="{$roleid}" {if in_array($roleid, $CONFIG['permissions']['Zalo']['broadcast_allowed_roles'])}selected{/if}>{$rolename}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="control-label fieldLabel col-sm-5">
                                                    <span>{vtranslate('LBL_SOCIAL_CONFIG_PERMISSTION_CHAT_ADMIN', $MODULE_NAME)}</span>
                                                    &nbsp;
                                                    <span class="redColor">*</span>
                                                </label>
                                                <div class="controls fieldValue col-sm-6">
                                                    <div class="input-group" style="margin-bottom: 3px">
                                                        <input type="text"
                                                            name="settings[permissions][chat_admin_users]" 
                                                            class="assigned-users inputElement" 
                                                            data-assignableUsersOnly="true"
                                                            data-rule-required="true"
                                                            {if !empty($CONFIG['permissions']['chat_admin_users'])}
                                                                data-selected-tags='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG['permissions']['chat_admin_users'])))}' 
                                                            {/if}  
                                                        />
                                                    </div>
                                                    <span data-toggle="tooltip" title="{vtranslate('LBL_SOCIAL_CONFIG_PERMISSTION_CHAT_ADMIN_DESCRIPTION', $MODULE_NAME)}"><i class="far fa-question-circle tooltip-helper"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Distribution -->
                            <div class="tab-pane chat-distribution-container" id="chatDistribution">
                                <br />
                                <div class="row form-group">
                                    <div class="col-sm-5">
                                        <span>{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_ASSIGNED_USER', $MODULE_NAME)}</span>
                                    </div>
                                    <div class="col-sm-7">
                                        <span>{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_DISTRIBUTION_TYPE', $MODULE_NAME)}</span>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-5">
                                        <div class="distribution-assigned-users-wrapper">
                                            <input type="text"
                                                class="assigned-users inputElement"
                                                data-assignableUsersOnly="true"
                                                name="settings[chat_distribution][users]"
                                                {if !empty($CONFIG['chat_distribution']['users'])} 
                                                    data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG['chat_distribution']['users']))}'
                                                {/if}
                                            />
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="distribution-type-wrapper">
                                            <label class="distribution-type-item">
                                                <span class="distribution-type-input">
                                                    <input type="radio"
                                                        class="inputElement"
                                                        name="settings[chat_distribution][type]"
                                                        value="equally_for_selected_users"
                                                        data-rule-required="true"
                                                        {if $CONFIG['chat_distribution']['type'] == 'equally_for_selected_users' || empty($CONFIG['chat_distribution']['type'])}checked{/if}
                                                    />
                                                </span>
                                                <span class="distribution-type-describe">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_SELECTED_USERS', $MODULE_NAME)}</span>
                                                <i class="far fa-question-circle tooltip-helper" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_SELECTED_USERS_DESC', $MODULE_NAME)}"></i>
                                            </label>
                                        </div>
                                        <div class="distribution-type-wrapper">
                                            <label class="distribution-type-item">
                                                <span class="distribution-type-input">
                                                    <input type="radio"
                                                        class="inputElement"
                                                        name="settings[chat_distribution][type]"
                                                        value="equally_for_online_users"
                                                        data-rule-required="true"
                                                        {if $CONFIG['chat_distribution']['type'] == 'equally_for_online_users'}checked{/if}
                                                    />
                                                </span>
                                                <span class="distribution-type-describe">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_ONLINE_USERS', $MODULE_NAME)}</span>
                                                <i class="far fa-question-circle tooltip-helper" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_ONLINE_USERS_DESC', $MODULE_NAME)}"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                {* <div class="contents tabbable">
                                    <ul class="nav nav-tabs marginBottom10px">
                                        <li class="fb-chat-distribution active"><a data-toggle="tab" href="#fb-chat-distribution"><strong>{vtranslate('LBL_SOCIAL_CONFIG_FB', $MODULE_NAME)}</strong></a></li>
                                        <li class="zalo-chat-distribution"><a data-toggle="tab" href="#zalo-chat-distribution"><strong>{vtranslate('LBL_SOCIAL_CONFIG_ZALO', $MODULE_NAME)}</strong></a></li>
                                    </ul>
                                    <div class="tab-content overflowVisible">
                                        <div class="tab-pane active" id="fb-chat-distribution">
                                            Under contruction!
                                        </div>
                                        <div class="tab-pane" id="zalo-chat-distribution">
                                            <table class="chat-distribution-content-table" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th class="chat-distribution-zalo-oa">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_ZALO_OA_PAGE', $MODULE_NAME)}</th>
                                                        <th class="chat-distribution-assigned-users">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_ASSIGNED_USER', $MODULE_NAME)}</th>
                                                        <th class="chat-distribution-distribution-type">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_DISTRIBUTION_TYPE', $MODULE_NAME)}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {foreach from=$ZALO_OA_LIST item=OA}
                                                        <tr class="oa-chat-distribution" data-oa-id="{$OA['id']}">
                                                            <td class="fieldLabel"><span>{$OA['name']}</span></td>
                                                            <td class="fieldValue">
                                                                <div class="distribution-assigned-users-wrapper">
                                                                    <input type="text"
                                                                        class="assigned-users inputElement"
                                                                        data-assignableUsersOnly="true"
                                                                        name="settings[chat_distribution][Zalo][{$OA['id']}][users]"
                                                                        {if !empty($CONFIG['chat_distribution']['users'])} 
                                                                            data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($CONFIG['chat_distribution']['users']))}'
                                                                        {/if}
                                                                    />
                                                                </div>
                                                            </td>
                                                            <td class="fieldValue">
                                                                <div class="distribution-type-wrapper">
                                                                    <label class="distribution-type-item">
                                                                        <span class="distribution-type-input">
                                                                            <input type="radio"
                                                                                class="inputElement"
                                                                                name="settings[chat_distribution][Zalo][{$OA['id']}][type]"
                                                                                value="equally_for_selected_users"
                                                                                data-rule-required="true"
                                                                                {if $CONFIG['chat_distribution']['type'] == 'equally_for_selected_users' || empty($CONFIG['chat_distribution']['type'])}checked{/if}
                                                                            />
                                                                        </span>
                                                                        <span class="distribution-type-describe">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_SELECTED_USERS', $MODULE_NAME)}</span>
                                                                        <i class="far fa-question-circle tooltip-helper" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_SELECTED_USERS_DESC', $MODULE_NAME)}"></i>
                                                                    </label>
                                                                </div>
                                                                <div class="distribution-type-wrapper">
                                                                    <label class="distribution-type-item">
                                                                        <span class="distribution-type-input">
                                                                            <input type="radio"
                                                                                class="inputElement"
                                                                                name="settings[chat_distribution][Zalo][{$OA['id']}][type]"
                                                                                value="equally_for_online_users"
                                                                                data-rule-required="true"
                                                                                {if $CONFIG['chat_distribution']['type'] == 'equally_for_online_users'}checked{/if}
                                                                            />
                                                                        </span>
                                                                        <span class="distribution-type-describe">{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_ONLINE_USERS', $MODULE_NAME)}</span>
                                                                        <i class="far fa-question-circle tooltip-helper" aria-hidden="true" data-toggle="tooltip" title="{vtranslate('LBL_SOCIAL_CONFIG_CHAT_DISTRIBUTION_EQUAL_FOR_ONLINE_USERS_DESC', $MODULE_NAME)}"></i>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div> *}
                            </div>
                        </div>
                    </div>
                </div>
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

    <!-- Connect Facebook Fanpage Modal -->
    <div id="connectFBFanpageModal" class="modal-dialog modal-content hide">
        {assign var=HEADER_TITLE value={vtranslate('LBL_SOCIAL_CONFIG_ADD_FB_FANPAGE_MODAL_TITLE', 'CPSocialIntegration')}}
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
    
        <form class="form-horizontal connectFBFanpageForm" method="POST">
            <div class="zaloOAForm-content">
                <h6>{vtranslate('LBL_SOCIAL_CONFIG_ADD_FB_FANPAGE_MODAL_HINT', 'CPSocialIntegration')}</h6>
                <br>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_SOCIAL_CONFIG_FACEBOOK_APP_ID', 'CPSocialIntegration')}</span>
                        &nbsp;
                        <span class="redColor">*</span>
                    </label>
                    <div class="controls fieldValue col-sm-8">
                        <div class="input-group inputElement" style="margin-bottom: 3px">
                            <input type="text" name="fb_app_id" class="form-control" data-rule-required="true"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_SOCIAL_CONFIG_FACEBOOK_APP_SECRET', 'CPSocialIntegration')}</span>
                        &nbsp;
                        <span class="redColor">*</span>
                    </label>
                    <div class="controls fieldValue col-sm-8">
                        <div class="input-group inputElement" style="margin-bottom: 3px">
                            <input type="password" name="fb_app_secret" class="form-control" data-rule-required="true"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <center>
                    <button class="btn btn-success" type="submit" name="addButton">{vtranslate('LBL_ADD', $MODULE_NAME)}</button>
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
                </center>
            </div>
        </form>
    </div>

    <!-- Connect Zalo OA Modal -->
    <div id="connectZaloOAModal" class="modal-dialog modal-content hide">
        {assign var=HEADER_TITLE value={vtranslate('LBL_SOCIAL_CONFIG_ADD_ZALO_OA_MODAL_TITLE', $MODULE_NAME)}}
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
    
        <form class="form-horizontal connectZaloOAForm" method="POST">
            <div class="zaloOAForm-content">
                <h6>{vtranslate('LBL_SOCIAL_CONFIG_ADD_ZALO_OA_MODAL_HINT', $MODULE_NAME)}</h6>
                <br>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_SOCIAL_CONFIG_ZALO_APP_ID', $MODULE_NAME)}</span>
                        &nbsp;
                        <span class="redColor">*</span>
                    </label>
                    <div class="controls fieldValue col-sm-8">
                        <div style="margin-bottom: 3px">
                            <input type="text" name="zalo_app_id" class="form-control" data-rule-required="true"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <center>
                    <button class="btn btn-success" type="submit" name="addButton">{vtranslate('LBL_ADD', $MODULE_NAME)}</button>
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
                </center>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}"/>
    <script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
    <script>const _SECRET_KEY = '{$SECRET_KEY}';</script>
{strip}