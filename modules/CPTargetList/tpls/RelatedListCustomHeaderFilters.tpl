{strip}
    {if in_array($RELATED_MODULE_NAME, ['Leads', 'Contacts', 'CPTarget'])}
        <div class='col-lg-4 col-md-4 col-sm-4'>
            <span class="customFilterMainSpan">
                <select id="customFilter" class="form-control select2" 
                    data-placeholder="{vtranslate('LBL_ADD_RELATIONS_FROM_FILTER_PLACEHOLDER', $MODULE)}"
                    onchange="triggerAddRelationsFromFilter(this, this.value);"
                >
                    <option></option>

                    {foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
                        <optgroup label="{if $GROUP_LABEL eq 'Mine'} &nbsp; {else} {vtranslate($GROUP_LABEL, $RELATED_MODULE_NAME)} {/if}">
                            {foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
                                <option value="{$CUSTOM_VIEW->get('cvid')}">
                                    {if $CUSTOM_VIEW->get('viewname') eq 'All'}
                                        {vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)} {vtranslate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}
                                    {else}
                                        {vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)}
                                    {/if}

                                    {if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}
                                </option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </span>
        </div>
    {/if}
{/strip}