{* Added by Hieu Nguyen on 2020-02-20 to render custom owner field for new Activties in Workflow *}

{strip}
    <input type="text" autocomplete="off" class="inputElement select2" style="width: 100%" data-rule-required="true" data-rule-main-owner="true"
        data-fieldtype="owner" data-fieldname="assigned_user_id" data-name="assigned_user_id" name="assigned_user_id"
        {if $FOR_EVENT}
            data-assignable-users-only="true" data-user-only="true" data-single-selection="true"
        {/if}
        {if $FIELD_VALUE} 
            data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getCurrentOwners($FIELD_VALUE))}'
        {/if}
    />
{/strip}