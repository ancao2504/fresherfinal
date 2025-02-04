{* Added by Hieu Nguyen on 2019-10-30 to modify the user feed editor for both add and edit forms *}

{strip}
    <div class="modal-dialog modal-md modal-content">
        {assign var=HEADER_TITLE value={vtranslate('LBL_USER_FEED_EDITOR_ADD_NEW_FEED_TITLE', $MODULE)}}

        {if $EDITOR_MODE == 'update'}
            {assign var=HEADER_TITLE value={vtranslate('LBL_USER_FEED_EDITOR_UPDATE_FEED_TITLE', $MODULE)}}
        {/if}

        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <br/>

        <form class="form-horizontal">
            <input type="hidden" id="selected-user-id" value="{$SELECTED_USER_ID}" />
            <input type="hidden" id="selected-color" value="{$SELECTED_COLOR}" />
            <input type="hidden" id="editor-mode" value="{$EDITOR_MODE}" />

            <div class="form-group">
                <label class="control-label fieldLabel col-sm-4">
                    {if $SELECTED_USER_NAME}
                        {vtranslate('LBL_USER_FEED_EDITOR_SELECTED_USER_FEED', $MODULE)}</label>
                    {else}
                        {vtranslate('LBL_USER_FEED_EDITOR_SELECT_USER_FEED', $MODULE)}</label>
                    {/if}
                <div class="controls fieldValue col-sm-6">
                    {if $SELECTED_USER_ID == $CURRENT_USER->id}
                        <span class="label label-info" style="font-size: 100%">{vtranslate('LBL_MINE', $MODULE)}</span>
                    {elseif $SELECTED_USER_NAME}
                        <span class="label label-info" style="font-size: 100%">{$SELECTED_USER_NAME}</span>
                    {else}
                        <input type="text" id="user-selector" class="form-control" data-user-only="true"/>
                    {/if}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-4">{vtranslate('LBL_SELECT_CALENDAR_COLOR', $MODULE)}</label>
                <div class="controls fieldValue col-sm-8">
                    <p id="color-picker"></p>
                </div>
            </div>

            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
        </form>
    </div>
{/strip}