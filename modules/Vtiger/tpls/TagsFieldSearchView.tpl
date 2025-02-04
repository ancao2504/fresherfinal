{* Added by Hieu Nguyen on 2021-01-26 *}

{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
	{assign var=TAG_LIST value=Vtiger_Tag_Model::getAllUserAccessibleTags()}
	{assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}
    {assign var=SEARCH_VALUES value=explode(',', $SEARCH_INFO['searchValue'])}

    <div class="select2_search_div">
        <input type="text" class="listSearchContributor inputElement select2_input_element"/>
        
        <select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" multiple data-fieldinfo='{$FIELD_INFO|escape}' style="display: none">
            {foreach item=TAG from=$TAG_LIST}
                <option value="{$TAG.id}" {if in_array($TAG.id, $SEARCH_VALUES)}selected{/if}>{$TAG.tag}</option>
            {/foreach}
        </select>
    </div>
{/strip}