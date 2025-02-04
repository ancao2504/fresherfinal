{* Added by Hieu Nguyen on 2019-11-22 to customize user invitees field *}

{strip}
    <ul style="margin: 0; padding: 0; padding-left: 18px;">
        {assign var="USER_INVITEES" value=Events_Invitation_Helper::getInviteesForDisplay($RECORD->get('id'), 'Users')}
        
        {foreach from=$USER_INVITEES item=INVITEE}
            <li><a target="_blank" href="index.php?module=Users&parent=Settings&view=Detail&record={$INVITEE['id']}"/>{$INVITEE['name']}&nbsp;({$INVITEE['email']})</a>&nbsp;-&nbsp;{vtranslate($INVITEE['status'], $MODULE_NAME)}</li>
        {/foreach}
    </ul>
{/strip}