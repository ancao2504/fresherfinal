{*
    ListViewCustomRowActions.tpl
    Author: Phuc Lu
    Date: 2019.11.05
*}
{strip}
    {if $LISTVIEW_ENTRY->getRaw('cpreceipt_status') != 'completed' && $LISTVIEW_ENTRY->getRaw('cpreceipt_status') != 'cancelled'}
            <span>
                <a class="far icon far fa-sack-dollar" data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:confirmComplete('{$LISTVIEW_ENTRY->getId()}')" name="completeLink" title="{vtranslate('LBL_CONFIRM_PAY', $MODULE)}" style="font-size: 16px; margin-top: 3px;"></a>
            </span>
    {/if}
{/strip}
