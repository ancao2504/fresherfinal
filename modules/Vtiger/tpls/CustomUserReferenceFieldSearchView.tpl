{* Added by Hieu Nguyen on 2019-10-21 *}

{strip}
    {assign var=FIELD_INFO value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}

    <script type="text/javascript">
        jQuery(function($) {
            CustomOwnerField.initCustomOwnerFields($('input[name="{$FIELD_NAME}"]'));
        });
    </script>

    <div class="select2_search_div">
        <input type="text" name="{$FIELD_NAME}" class="select2 form-control listSearchContributor {$FIELD_NAME}" multiple="true"  
            data-fieldinfo='{$FIELD_INFO|escape}' value="{$SEARCH_INFO['searchValue']}"
            {if $SEARCH_INFO['searchValue']} data-selected-tags='{ZEND_JSON::encode(Vtiger_Owner_UIType::getSelectedOwnersForSearchView($SEARCH_INFO['searchValue']))}' {/if}
        />
    </div>
{/strip}