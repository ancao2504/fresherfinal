{* Added by Hieu Nguyen on 2019-11-22 to customize user invitees field *}

{strip}
    <input type="text" autocomplete="off" class="inputElement select2" style="width: 100%"
        data-fieldtype="text" data-fieldname="user_invitees" data-name="user_invitees" name="user_invitees"
        {if $FIELD_MODEL->get('fieldvalue')} data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($FIELD_MODEL->get('fieldvalue')))}' {/if}
    />
{/strip}