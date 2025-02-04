{strip}
    <div id="div_message_content_block" style="display:none">
        <table class="table table-borderless">
            <tbody>
                <tr>
                    <td class="fieldLabel module alignMiddle">
                        {vtranslate('LBL_MODULE', $MODULE_NAME)}
                    </td>
                    <td class="fieldValue">
                        <select data-fieldname="cpsocialmessagetemplate_module" data-fieldtype="picklist" class="inputElement select2 row" type="picklist" name="cpsocialmessagetemplate_module">
                            <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                            {foreach item=MODULE_LABEL key=MODULE_KEY from=$MODULE_LIST}
                                <option value="{$MODULE_KEY}" {if $MODULE_KEY == $RECORD->get('cpsocialmessagetemplate_module')}selected="selected"{/if}>{$MODULE_LABEL}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="fieldLabel field alignMiddle">
                        {vtranslate('LBL_FIELDS', $MODULE_NAME)}
                    </td>
                    <td class="fieldValue row">
                        <select data-fieldname="fields" data-fieldtype="picklist" class="inputElement select2 row" type="picklist" name="field_list">
                        </select>
                        <select style="display:none" name="all_fields">
                            <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                            {foreach item=VARIABLE_DEF key=VARIABLE_NAME from=$VARIABLES_BY_MODULE}
                                <option style="display:none" value="{$VARIABLE_DEF['field']}" data-parent-module="{$VARIABLE_DEF['module']}">{$VARIABLE_DEF['label']}</option>
                            {/foreach}
                        </select>
                        <span class="button btn_insert" onclick="insertVariable()">{vtranslate('LBL_INSERT', 'CPSocialMessageTemplate')}</span>
                    </td>
                </tr>
                <tr>
                    <td class="fieldLabel message_content_custom alignMiddle">
                        {vtranslate('LBL_MESSAGE_CONTENT', $MODULE_NAME)}&nbsp;<span class="redColor">*</span>
                    </td>
                    <td class="fieldValue">
                        <textarea class="txt_content inputElement textAreaElement " rows=7 name="text_message" id="text_message" data-rule-required="true"  date-rule-text="true" data-rule-maxlength="2000" value="{$TEXT_MESSAGE}">{$TEXT_MESSAGE}</textarea>

                        <textarea class="txt_content inputElement textAreaElement hide" rows=7 name="message_content" id="message_content" value=""></textarea>
                    </td>                    
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td class="fieldLabel cover alignMiddle">
                        {vtranslate('LBL_COVER', $MODULE_NAME)}&nbsp;<span class="redColor">*</span>
                    </td>
                    <td class="fieldValue">
                        <div>
                            <div class="div_cover col-sm-9">
                                <img onclick="openCoverModal()" src="{if isset($CURRENT_COVER['image_url']) && $CURRENT_COVER['image_url'] != ''} {$CURRENT_COVER['image_url']} {else} modules/CPSocialMessageTemplate/resources/DefaultCover.png {/if}"/>
                                <span id="cover_title" class="hide">{$CURRENT_COVER['title']}</span>
                                <span id="cover_image_url" class="hide">{$CURRENT_COVER['image_url']}</span>
                                <span id="cover_description" class="hide">{$CURRENT_COVER['description']}</span>
                                <span id="cover_click_url" class="hide">{$CURRENT_COVER['click_url']}</span>
                            </div>

                            <div class="div_cover_action div_action col-sm-3">
                                <a class="action_link" href="javascript:void(0);" onclick="openCoverModal()">{vtranslate('LBL_EDIT', $MODULE_NAME)}</a>
                            </div>
                            <div style="clear:both"></div>
                            
                            <div class="div_cover_title div_cover col-sm-9">{if isset($CURRENT_COVER['title']) && $CURRENT_COVER['title'] != ''} {$CURRENT_COVER['title']} {else} {vtranslate('LBL_COVER_DEFAULT_TITLE', $MODULE_NAME)} {/if}</div>
                            <div class="div_cover_description div_cover col-sm-9">{if isset($CURRENT_COVER['description']) && $CURRENT_COVER['description'] != ''}{$CURRENT_COVER['description']}{/if}</div>
                        </div>
                    </td>                
                    <td colspan="2"></td>
                </tr>

                <tr>
                    <td class="fieldLabel buttons alignMiddle">
                        {vtranslate('LBL_BUTTONS', $MODULE_NAME)}
                    </td>
                    <td class="fieldValue">
                        <div class="div_buttons_container">
                            <div class='div_button_template hide' style="">
                                <div class="div_button col-sm-9">
                                    <span class="button_title"></span>
                                    <span class="button_type hide"></span>
                                    <span class="button_data hide"></span>
                                </div>
                                <div class="div_button_action div_action col-sm-3">
                                    <a class="action_link" href="javascript:void(0);">{vtranslate('LBL_EDIT', $MODULE_NAME)}</a>
                                    <a class="action_link" href="javascript:void(0);" onclick="removeRow(this)">{vtranslate('LBL_REMOVE', $MODULE_NAME)}</a>
                                </div>
                                <div style="clear:both"></div>
                            </div>
                            {foreach from=$CURRENT_BUTTONS key=KEY item=BUTTON}
                                <div class='div_button_container' style="">
                                    <div class="div_button col-sm-9">
                                        <span class="button_title">{$BUTTON['title']}</span>
                                        <span class="button_type hide">{$BUTTON['type']}</span>
                                        <span class="button_data hide">{$BUTTON['data']}</span>
                                    </div>
                                    <div class="div_button_action div_action col-sm-3">
                                        <a class="action_link" href="javascript:void(0);" onclick="openButtonModal(this)">{vtranslate('LBL_EDIT', $MODULE_NAME)}</a>
                                        <a class="action_link" href="javascript:void(0);" onclick="removeRow(this)">{vtranslate('LBL_REMOVE', $MODULE_NAME)}</a>
                                    </div>
                                    <div style="clear:both"></div>
                                </div>
                            {/foreach}
                        </div>

                        <div><a class="action_link" href="javascript:void(0);" onclick="openButtonModal()">{vtranslate('LBL_ADD_BUTTON', $MODULE_NAME)}</a></div>
                    </td>
                </tr>

                <tr>
                    <td class="fieldLabel items alignMiddle">
                        {vtranslate('LBL_ITEMS', $MODULE_NAME)}
                    </td>
                    <td class="fieldValue">
                        <div class="div_items_container">
                            <div class='div_item_template hide' >
                                <div class="div_item col-sm-9">
                                    <div class="div_item_icon" style="background-image: url('');"></div>
                                    <div class="div_item_content">
                                        <span class="item_title"></span>
                                        <span class="item_type hide"></span>
                                        <span class="item_icon_url hide"></span>
                                        <span class="item_data hide"></span>
                                    </div>
                                    <div style="clear:both"></div>
                                </div>
                                <div class="div_item_action div_action col-sm-3">
                                    <a class="action_link" href="javascript:void(0);">{vtranslate('LBL_EDIT', $MODULE_NAME)}</a>
                                    <a class="action_link" href="javascript:void(0);" onclick="removeRow(this)">{vtranslate('LBL_REMOVE', $MODULE_NAME)}</a>
                                </div>
                                <div style="clear:both"></div>
                            </div>
                            {foreach from=$CURRENT_ITEMS key=KEY item=ITEM}
                                <div class="div_item_container">
                                    <div class="div_item col-sm-9">
                                        <div class="div_item_icon" style="background-image: url('{$ITEM['icon_url']}');"></div>
                                        <div class="div_item_content">
                                            <span class="item_title">{$ITEM['title']}</span>
                                            <span class="item_type hide">{$ITEM['type']}</span>
                                            <span class="item_icon_url hide">{$ITEM['icon_url']}</span>
                                            <span class="item_data hide">{$ITEM['data']}</span>
                                        </div>
                                        <div style="clear:both"></div>
                                    </div>
                                    <div class="div_item_action div_action col-sm-3">
                                        <a class="action_link" href="javascript:void(0);" onclick="openItemModal(this)">{vtranslate('LBL_EDIT', $MODULE_NAME)}</a>
                                        <a class="action_link" href="javascript:void(0);" onclick="removeRow(this)">{vtranslate('LBL_REMOVE', $MODULE_NAME)}</a>
                                    </div>
                                    <div style="clear:both"></div>
                                </div>
                            {/foreach}
                        </div>

                        <div><a class="action_link" href="javascript:void(0);" onclick="openItemModal()">{vtranslate('LBL_ADD_ITEM', $MODULE_NAME)}</a></div>
                    </td>
                </tr>
            </tbody>
        </table>

        {* Declare modal for cover *}   
        <div id="div_cover_modal" class="cover_modal modal-dialog modal-content hide">
            {assign var="HEADER_TITLE" value={vtranslate('LBL_EDIT_COVER', $MODULE_NAME)}}
            {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
            <div class="formDeclareCoverForm">
                <input type="hidden" name="leftSideModule" value="{$MODULE_NAME}"/>

                <div class="form-group" style = "margin-top: 15px;">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_TITLE', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="cover_title" class="form-control" data-rule-required="true" data-rule-maxlength="100"/>
                    </div>
                </div>

                <div class="form-group" style = "margin-top: 15px;">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_DESCRIPTION', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <textarea type="text" name="cover_description" cols=20 rows=3 class="form-control" data-rule-required="true" data-rule-maxlength="500"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_IMAGE_URL', $MODULE_NAME)}</span>&nbsp;(450x300)&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="cover_image_url" maxlength='200' class="form-control" data-rule-required="true" data-rule-url="true" data-hasqtip="26"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_URL_ON_CLICK', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="cover_url" class="form-control" data-rule-required="true" data-rule-url="true" data-hasqtip="26"/>
                    </div>
                </div>

                {assign var="BUTTON_NAME" value={vtranslate('LBL_OK', $MODULE_NAME)}}
                {include file="ModalFooter.tpl"|@vtemplate_path:Vtiger BUTTON_NAME=$BUTTON_NAME}
            </div>
        </div>

        {* Declare modal for add button *}
        <div id="div_button_modal" class="button_modal modal-dialog modal-content hide">
            {assign var="HEADER_TITLE" value={vtranslate('LBL_ADD_BUTTON', $MODULE_NAME)}}
            {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
            <div class="formDeclareButtonForm">
                <input type="hidden" name="leftSideModule" value="{$MODULE_NAME}"/>

                <div class="form-group" style = "margin-top: 15px;">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_BUTTON_TITLE', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="button_title" class="form-control" data-rule-required="true" data-rule-maxlength="100"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_ACTION', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <select class="slc_action" data-rule-required="true">
                            <option value="oa.open.url">{vtranslate('LBL_OPEN_URL', $MODULE_NAME)}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_URL_ON_CLICK', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="button_url" class="form-control" data-rule-required="true" data-rule-url="true" data-hasqtip="26"/>
                    </div>
                </div>

                {assign var="BUTTON_NAME" value={vtranslate('LBL_OK', $MODULE_NAME)}}
                {include file="ModalFooter.tpl"|@vtemplate_path:Vtiger BUTTON_NAME=$BUTTON_NAME}
            </div>
        </div>

        {* Declare modal for item *}   
        <div id="div_item_modal" class="item_modal modal-dialog modal-content hide">
            {assign var="HEADER_TITLE" value={vtranslate('LBL_EDIT_ITEM', $MODULE_NAME)}}
            {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
            <div class="formDeclareItemForm">
                <input type="hidden" name="leftSideModule" value="{$MODULE_NAME}"/>

                <div class="form-group" style = "margin-top: 15px;">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_TITLE', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="item_title" class="form-control" data-rule-required="true" data-rule-maxlength="100"/>
                    </div>
                </div>

                <div class="form-group" style = "margin-top: 15px;">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_ICON_URL', $MODULE_NAME)}</span>&nbsp;(50x50)&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="icon_url" class="form-control" data-rule-required="true" data-rule-url="true" data-hasqtip="26"/>
                    </div>
                </div>

                 <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_ACTION', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <select class="slc_action" data-rule-required="true">
                            <option value="oa.open.url">{vtranslate('LBL_OPEN_URL', $MODULE_NAME)}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-4">
                        <span>{vtranslate('LBL_URL_ON_CLICK', $MODULE_NAME)}</span>&nbsp;<span class="redColor">*</span>
                    </label>
                    <div class="control col-sm-7">
                        <input type="text" name="item_url" class="form-control" data-rule-required="true" data-rule-url="true" data-hasqtip="26"/>
                    </div>
                </div>

                {assign var="BUTTON_NAME" value={vtranslate('LBL_OK', $MODULE_NAME)}}
                {include file="ModalFooter.tpl"|@vtemplate_path:Vtiger BUTTON_NAME=$BUTTON_NAME}
            </div>
        </div>
    </div>
{/strip}