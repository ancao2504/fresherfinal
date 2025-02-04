{*
    MessageContentHistoryView.tpl
    Author: Phuc Lu
    Date: 2019.10.01
*}

{strip}
    <div id="div_message_content_block">
    
        {if $MESSAGE_CONTENT_TYPE == 'text'}
            <div class="div_cover_container">
                <div style="margin-top:5px; text-decoration: underline; margin-bottom:3px;">{vtranslate('LBL_MESSAGE_CONTENT', $MODULE)}:</div>
                <div style="padding-left: 10px;">{$TEXT_MESSAGE}</div>
            </div>
        {/if}

        {if $MESSAGE_CONTENT_TYPE == 'buttons' || $MESSAGE_CONTENT_TYPE == 'list'}
            <div class="div_cover_container">
                <div style="margin-top:5px; text-decoration: underline; margin-bottom:3px;">{vtranslate('LBL_COVER', $MODULE)}:</div>
                <div class="div_cover" style="max-width:50px; padding-left: 10px;">
                    <img style="cursor:pointer;max-width: 100%; display: block;;" onclick="window.open('{$CURRENT_COVER['click_url']}','_blank');" src="{if $CURRENT_COVER|@count > 0}{$CURRENT_COVER['image_url']}{else}modules/CPSocialMessageTemplate/resources/DefaultCover.png{/if}"/>
                </div>
                <div class="div_cover_title" style="padding-left: 10px;">{$CURRENT_COVER['title']}</div>
                <div class="div_cover_description" style="padding-left: 10px;">{nl2br($CURRENT_COVER['description'])}</div>
            <div>
        {/if}

        {if $MESSAGE_CONTENT_TYPE == 'buttons'}
            <div style="margin-top:5px; text-decoration: underline; margin-bottom:3px;">{vtranslate('LBL_BUTTONS', $MODULE)}:</div>
            <div class="div_buttons_container div_content">
                {if $CURRENT_BUTTONS|@count > 0}
                    <ul>
                        {foreach from=$CURRENT_BUTTONS key=KEY item=BUTTON}
                            <li><a target="_blank" href="{$BUTTON['data']}">{$BUTTON['title']}</a></li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
        {/if}

        {if $MESSAGE_CONTENT_TYPE == 'list'}        
            <div style="margin-top:5px; text-decoration: underline; margin-bottom:3px;">{vtranslate('LBL_ITEMS', $MODULE)}:</div>
            <div class="div_items_container div_content">
                {if $CURRENT_ITEMS|@count > 0}
                    <ul>
                        {foreach from=$CURRENT_ITEMS key=KEY item=ITEM}
                            <li><a target="_blank" href="{$ITEM['data']}">{$ITEM['title']}</a></li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
        {/if}

    </div>
{/strip}