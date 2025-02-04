{strip}
<label>
    <input type="radio" name="accounts_business_type" value="B2B" {if $RECORD && $RECORD->get('accounts_business_type')
    == 'B2B'}checked
    {elseif !$RECORD}{/if} />
    <span>{vtranslate("LBL_ACCOUNTS_BUSINESS_TYPE_B2B", $MODULE)}</span>
</label>

<label>
    <input type="radio" name="accounts_business_type" value="B2C" {if $RECORD && $RECORD->get('accounts_business_type')
    == 'B2C'}checked
    {elseif !$RECORD}checked{/if} />
    <span>{vtranslate("LBL_ACCOUNTS_BUSINESS_TYPE_B2C", $MODULE)}</span>
</label>
{/strip}