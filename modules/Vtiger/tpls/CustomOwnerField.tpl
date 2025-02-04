{* Added by Hieu Nguyen on 2019-05-24 *}

{strip}
    <input type="hidden" name="current_owner_id" value="{$FIELD_VALUE}"/>
    <input type="hidden" name="current_assignees_hash" value="{Vtiger_Owner_UIType::getOwnerIdsHash($FIELD_VALUE)}"/>
    <input type="text" autocomplete="off" class="inputElement select2" style="width: 100%" data-rule-required="true" data-rule-main-owner="true"
        data-fieldtype="owner" data-fieldname="{$FIELD_NAME}" data-name="{$FIELD_NAME}" name="{$FIELD_NAME}" data-assignable-users-only="true"
        {if $FIELD_VALUE} 
            {if is_array($FIELD_VALUE)} 
                data-selected-tags='{ZEND_JSON::encode($FIELD_VALUE)}'
            {else} 
                data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($FIELD_VALUE))}' 
            {/if}
        {/if}
    />
{/strip}