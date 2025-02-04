{*
    MessageContentTextView.tpl
    Author: Phuc Lu
    Date: 2019.10.04
*}

{strip}
    <div id="div_message_content_block">
    
        {if $MESSAGE_CONTENT_TYPE == 'text'}
            <div>{vtranslate('LBL_MESSAGE_CONTENT', $MODULE)}: {if $TEXT_MESSAGE|count_characters > 30}{$TEXT_MESSAGE|mb_substr:0:30}...{else}{$TEXT_MESSAGE}{/if}</div>
        {/if}

        {if $MESSAGE_CONTENT_TYPE == 'buttons' || $MESSAGE_CONTENT_TYPE == 'list'}
            <div>{vtranslate('LBL_TITLE', $MODULE)}: {if $CURRENT_COVER['title']|count_characters > 30}{$CURRENT_COVER['title']|mb_substr:0:30}...{else}{$CURRENT_COVER['title']}{/if}</div>
        {/if}

        {if $MESSAGE_CONTENT_TYPE == 'buttons'}
            <div>{vtranslate('LBL_BUTTONS', $MODULE)}:&nbsp;
                {if $CURRENT_BUTTONS|@count > 0}
                    {for $key = 0 to $CURRENT_BUTTONS|@count - 1 max = 3}
                        {$CURRENT_BUTTONS[$key]['title']}{if $key < 2},&nbsp;{/if}
                    {/for}                    
                    {if $CURRENT_BUTTONS|@count > 2},&nbsp;...{/if}
                {/if}
            </div>
        {/if}

        {if $MESSAGE_CONTENT_TYPE == 'list'}        
            <div>{vtranslate('LBL_ITEMS', $MODULE)}:&nbsp;
                {if $CURRENT_ITEMS|@count > 0}  
                    {for $key = 0 to $CURRENT_ITEMS|@count - 1 max = 3}
                        {$CURRENT_ITEMS[$key]['title']}{if $key < 2},&nbsp;{/if}
                    {/for}
                    {if $CURRENT_ITEMS|@count > 3},&nbsp;...{/if}
                {/if}
            </div>
        {/if}

    </div>
{/strip}