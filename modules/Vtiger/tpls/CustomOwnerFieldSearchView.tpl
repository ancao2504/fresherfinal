{* Added by Hieu Nguyen on 2019-06-07 *}

{strip}
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    
    <div class="select2_search_div">
        {assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
        {assign var=ALL_ACTIVEGROUP_LIST value=array()}

        <input type="text" name="{$ASSIGNED_USER_ID}" class="select2 form-control listSearchContributor {$ASSIGNED_USER_ID}" multiple="true"  
            data-fieldinfo='{$FIELD_INFO|escape}' value="{$SEARCH_INFO['searchValue']}"
            {if $SEARCH_INFO['searchValue']} data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getSelectedOwnersForSearchView($SEARCH_INFO['searchValue']))}' {/if}
        />
    </div>
{/strip}