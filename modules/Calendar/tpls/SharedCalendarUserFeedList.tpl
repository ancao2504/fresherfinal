{* Added by Hieu Nguyen on 2019-10-30 to render a user feed list *}

{strip}
    <div class="sidebar-widget-contents" name="calendarViewTypes">
        <div id="calendarview-feeds">
            <ul class="list-group feedslist">
                {* Mine calendar *}
                {include file="modules/Calendar/tpls/SharedCalendarUserFeedListItem.tpl" CURRENT_USER=$CURRENT_USER USER_FEED_INFO=$CURRENT_USER_FEED}

                {* Others calendar *}
                {foreach item=USER_FEED_INFO from=$SAVED_USER_FEEDS}
                    {include file="modules/Calendar/tpls/SharedCalendarUserFeedListItem.tpl"}
                {/foreach}
            </ul>

            {* Template row *}
            <ul class="hide dummy">
                {include file="modules/Calendar/tpls/SharedCalendarUserFeedListItem.tpl" CURRENT_USER=$CURRENT_USER USER_FEED_INFO=[] IS_TEMPLATE=true}
            </ul>
        </div>
    </div>
{/strip}