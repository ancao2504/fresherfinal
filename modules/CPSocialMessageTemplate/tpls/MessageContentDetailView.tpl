{strip}
    <link type="text/css" rel="stylesheet" href="modules/CPSocialMessageTemplate/resources/DetailView.css">
    <div id="div_message_content_block">
        <table>
            <tbody>
            {if $MESSAGE_CONTENT_TYPE == 'text'}
                <tr>
                    <td class="fieldValue">
                        <div>{nl2br($TEXT_MESSAGE)}</div>
                    </td>
                </tr>
            {/if}

            {if $MESSAGE_CONTENT_TYPE == 'list' || $MESSAGE_CONTENT_TYPE == 'buttons'}
                <tr>
                    <td class="fieldValue">
                        <div class="div_cover_container">
                            <div class="div_cover">
                                <img style="cursor:pointer" onclick="window.open('{$CURRENT_COVER['click_url']}','_blank');" src="{if $CURRENT_COVER|@count > 0}{$CURRENT_COVER['image_url']}{else}modules/CPSocialMessageTemplate/resources/DefaultCover.png{/if}"/>
                            </div>
                            <div class="div_cover_title">{$CURRENT_COVER['title']}</div>
                            <div class="div_cover_description">{nl2br($CURRENT_COVER['description'])}</div>
                        <div>
                    </td>            
                </tr>
            {/if}

            {if $MESSAGE_CONTENT_TYPE == 'buttons'}
                <tr>
                    <td class="fieldValue">
                            {if $CURRENT_BUTTONS|@count > 0}
                                {foreach from=$CURRENT_BUTTONS key=KEY item=BUTTON}
                                    <div class='div_button_container' style="">
                                        <div class="div_button"><a target="_blank" href="{$BUTTON['data']}">{$BUTTON['title']}</a></div>
                                    </div>
                                {/foreach}
                            {/if}
                        </div>
                    </td>
                </tr>
            {/if}

            {if $MESSAGE_CONTENT_TYPE == 'list'}
                <tr>
                    <td class="fieldValue">
                        {if $CURRENT_ITEMS|@count > 0}
                            {foreach from=$CURRENT_ITEMS key=KEY item=ITEM}
                                <div class="div_item_container">
                                    <div class="div_item_icon" style="background-image: url('{$ITEM['icon_url']}');"></div>
                                    <div class="div_item_content"><a target="_blank" href="{$ITEM['data']}">{$ITEM['title']}</a></div>
                                    <div style="clear:both"></div>
                                </div>
                            {/foreach}
                        {/if}
                    </td>
                </tr>
            {/if}
            </tbody>
        </table>
    <div>
{/strip}