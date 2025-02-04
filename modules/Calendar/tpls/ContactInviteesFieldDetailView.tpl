{* Added by Hieu Nguyen on 2019-11-22 to customize contact invitees field *}

{strip}
    <ul style="margin: 0; padding: 0; padding-left: 18px;">
        {assign var="CONTACT_INVITEES" value=Events_Invitation_Helper::getInviteesForDisplay($RECORD->get('id'), 'Contacts')}
        
        {foreach from=$CONTACT_INVITEES item=INVITEE}
            <li>
                <a target="_blank" href="index.php?module=Contacts&view=Detail&record={$INVITEE['id']}"/>{$INVITEE['name']}</a>&nbsp;-&nbsp;
                {vtranslate($INVITEE['status'], $MODULE_NAME)}&nbsp;{if $INVITEE['status'] == 'Failed'}({vtranslate($INVITEE['failed_reason'], $MODULE_NAME)}){/if}
            </li>
        {/foreach}
    </ul>
{/strip}