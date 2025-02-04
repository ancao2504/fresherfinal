{if $RECORD->getId()}
    {assign var="USER_TYPE" value=$RECORD->get('users_type')}
{else}
    {if $smarty.request.users_type}
        {assign var="USER_TYPE" value=$smarty.request.users_type}
    {else}
        {assign var="USER_TYPE" value='normal_user'}
    {/if}
{/if}

<input type="hidden" name="users_type" value="{$USER_TYPE}"/>