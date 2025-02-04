{*
    PopupNavigation.tpl
    Author: Phuc Lu
    Date: 2020.06.02
*}

{strip}
    <div class="col-md-3">
        {if $SOURCE_MODULE == 'Reports'}<button class="btn btn-default" onclick="window.open('index.php?module=CPTargetList&view=Edit')"><strong>{vtranslate('LBL_ADD_NEW', $MODULE)}</strong></button>&nbsp;{/if}

        {if $MULTI_SELECT}
            {if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-default" disabled="disabled"><strong>{vtranslate('LBL_CHOOSE', $MODULE)}</strong></button>{/if}
        {else}
            &nbsp;
        {/if}
        
        </div>
    <div class="col-md-9">
        {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
        {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
    </div>
{/strip}