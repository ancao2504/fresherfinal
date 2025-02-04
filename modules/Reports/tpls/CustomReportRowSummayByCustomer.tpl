{strip}
<tr>
    {foreach key=KEY item=VALUE from=$ROW_DATA}
    {if strpos($KEY, 'lbl_action') === false}
    <td {if !$PRINT}nowrap{/if}>{$VALUE}-{$KEY}</td>
    {/if}
    {/foreach}
</tr>
{/strip}