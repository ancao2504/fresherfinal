{* 
    Author: Phu Vo
    Date: 2019.20.06
    Description: Provide a template for custom owner field as dashboard view
*}

{strip}
    {if !isset($USER_ONLY)} {assign var=USER_ONLY value=false} {/if}
    {if !isset($OPTIONAL_FILTER)} {assign var=OPTIONAL_FILTER value=false} {/if}
    {if !isset($FIELD_NAME)} {assign var=FIELD_NAME value="assigned_user_id"} {/if}
    {if !isset($VALIDATE)} {assign var=VALIDATE value=false} {/if}

    <div class="user-list">
        {if $OPTIONAL_FILTER} {* Incase filter have optional filter (Dashboard Widget) *}
            <div class="user-list_optional">
                <label>{vtranslate('LBL_MINE', 'Vtiger')} <input type="checkbox" name="optional_filter_mine" rel="optional_filter" value="mine" checked/></label>
                <label>{vtranslate('LBL_ALL', 'Vtiger')} <input type="checkbox" name="optional_filter_all" rel="optional_filter" value="all"/></label>
            </div>
        {/if}
        <div class="user-list_input">
            <input type="text" 
                name="{$FIELD_NAME}" {if isset($FIELD_ID)}id="{$FIELD_ID}"{/if}
                class="select2 {if $OPTIONAL_FILTER}widgetFilter reloadOnChange{/if}" multiple="false" data-user-only="{$USER_ONLY}" 
                {if $VALIDATE} data-rule-required="true" data-rule-main-owner="true" {/if} data-assignable-users-only="true"
            />
        </div>
    </div>
{/strip}