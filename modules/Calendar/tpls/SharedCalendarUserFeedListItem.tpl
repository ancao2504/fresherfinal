{* Added by Hieu Nguyen on 2019-10-30 to render a single user feed item *}

{strip}
    {assign var=USER_ID value=$USER_FEED_INFO['id']}
    {assign var=USER_NAME value=$USER_FEED_INFO['name']}

    {if $USER_ID == $CURRENT_USER->id}
        {assign var=USER_NAME value={vtranslate('LBL_MINE', $MODULE)}}
    {/if}

    <li class="activitytype-indicator calendar-feed-indicator {if $IS_TEMPLATE}feed-indicator-template{/if}" style="background-color: {$USER_FEED_INFO['color']}">
        <input type="checkbox" {if $USER_FEED_INFO['visible'] == '1'}checked{/if} class="toggleCalendarFeed cursorPointer" data-calendar-sourcekey="Events_{$USER_ID}" 
            data-calendar-feed="Events" data-calendar-feed-color="{$USER_FEED_INFO['color']}" data-calendar-fieldlabel="{$USER_NAME}" 
            data-calendar-userid="{$USER_ID}" data-calendar-group="false" data-calendar-feed-textcolor="white" 
        />
        <span class="userName textOverflowEllipsis" data-toggle="tooltip" title="{$USER_NAME}">{$USER_NAME}</span>
        <span class="activitytype-actions pull-right">
            <button class="btn btn-link dropdown-toggle" data-toggle="dropdown">
                <i class="far fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu" role="menu">
                <li>
                    <a href="javascript:void(0)" class="editCalendarFeedColor cursorPointer">
                        <i class="far fa-pen"></i> {vtranslate('LBL_EDIT_FEED')}
                    </a>
                </li>
                {if $USER_ID != $CURRENT_USER->id}
                    <li>
                        <a href="javascript:void(0)" class="redColor deleteCalendarFeed cursorPointer">
                            <i class="far fa-trash-alt"></i> {vtranslate('LBL_DELETE_FEED')}
                        </a>
                    </li>
                {/if}
            </ul>
        </span>
    </li>
{/strip}