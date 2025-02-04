{strip}
{if $INVOICES_DETAILS != ''}
    <div id = "div-invoices">
        <input type="hidden" name="invoices_detail" value='{$JS_INVOICES_DETAILS}'/>
        <div id = "div-invoices-details">
            <table celspacing = 0 celpadding = 0 id = "tbl-invoices">
                <thead>
                    <tr>
                        <th width=10%>{vtranslate('LBL_INVOICE_CODE', $MODULE_NAME)}</th>
                        <th width=20%>{vtranslate('LBL_SUBJECT', $MODULE_NAME)}</th>
                        <th width=14%>{vtranslate('LBL_TOTAL_AMOUNT', $MODULE_NAME)}</th>
                        <th width=14%>{vtranslate('LBL_RECEIVED_AMOUNT', $MODULE_NAME)}</th>
                        <th width=14%>{vtranslate('LBL_BALANCE', $MODULE_NAME)}</th>
                        <th width=15%>{vtranslate('LBL_PAID', $MODULE_NAME)}</th>     
                        {if $INVOICES_STATUS == 'completed'}
                            <th width=15%>{vtranslate('LBL_REAL_PAID', $MODULE_NAME)}</th>   
                        {/if}
                    </tr>
                </thead>
                <tbody>
                {foreach from=$INVOICES_DETAILS key=key item=INVOICE}
                    <tr>
                        <td><span class="invoice-code"><a target="_blank" href="index.php?module=Invoice&view=Detail&record={$INVOICE.invoice_id}">{$INVOICE.invoice_no}</span><input type="hidden" name="invoice-id[]" value="{$INVOICE.invoice_id}"/></td>
                        <td><span class="invoice-subject">{$INVOICE.subject}</span></td>
                        <td class="right"><span class="invoice-total">{CurrencyField::convertToUserFormat($INVOICE.invoice_total)}</span></td>
                        <td class="right"><span class="invoice-received">{CurrencyField::convertToUserFormat($INVOICE.invoice_received)}</span></td>
                        <td class="right"><span class="invoice-balance">{CurrencyField::convertToUserFormat($INVOICE.invoice_balance)}</span></td>
                        <td class="right"><span class="invoice-paid">{CurrencyField::convertToUserFormat($INVOICE.invoice_paid)}</span></td>                        
                        {if $INVOICES_STATUS == 'completed'}
                            <td class="right"><span class="invoice-real_paid">{CurrencyField::convertToUserFormat($INVOICE.invoice_real_paid)}</span></td>   
                        {/if}
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    <div>
{/if}
{/strip}