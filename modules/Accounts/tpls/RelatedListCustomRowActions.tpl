{*
    File RelatedListCustomRowActions.tpl
    Author: Hieu Nguyen
    Date: 2022-01-18
    Purpose: to add custom buttons in related list rows
*}

{strip}
    {if $RELATED_MODULE_NAME eq 'Calendar'}
        <input type="hidden" name="related_account" value="{getActivityRelatedCustomerAccountId($RELATED_RECORD->getId())}"
    {/if}
{/strip}