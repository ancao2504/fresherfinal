{strip}
    {* Added By Kelvin Thang -- Fixed issue #1685 -- Label error on editview overlay screen*}
    {assign var=MODULE_NAME value="CPReceipt"}
    <div id = "div-invoices">
        <input type="hidden" name="invoices_detail" value='{$JS_INVOICES_DETAILS}'/>
        <div id = "div-invoices-command">
            <button class="btn btn-success btn-sm btn-view" onclick="selectInvoices()">{vtranslate('LBL_CHOOSE_INVOICE', $MODULE_NAME)}</button>
            &nbsp;<button class="btn btn-success btn-sm btn-view" onclick="distributePaid()">{vtranslate('LBL_DISTRIBUTE', $MODULE_NAME)}</button>
        </div>
        <div id = "div-invoices-details">
            <table celspacing = 0 celpadding = 0 id = "tbl-invoices">
                <thead>
                    <tr>
                        <th width=10%>{vtranslate('LBL_INVOICE_CODE', $MODULE_NAME)}</th>
                        <th width=20%>{vtranslate('LBL_SUBJECT', $MODULE_NAME)}</th>
                        <th width=14%>{vtranslate('LBL_TOTAL_AMOUNT', $MODULE_NAME)}</th>
                        <th width=14%>{vtranslate('LBL_RECEIVED_AMOUNT', $MODULE_NAME)}</th>
                        <th width=14%>{vtranslate('LBL_BALANCE', $MODULE_NAME)}</th>
                        <th width=20%>{vtranslate('LBL_PAID', $MODULE_NAME)}</th>                        
                        <th width=8%>{vtranslate('LBL_ACTION', $MODULE_NAME)}</th>                        
                    </tr>
                </thead>
                <tbody>
                {if !isset($IS_DUPLICATE) || $IS_DUPLICATE == 0}
                    {foreach from=$INVOICES_DETAILS key=key item=INVOICE}
                        <tr>
                            <td><span class="invoice-code"><a target="_blank" href="index.php?module=Invoice&view=Detail&record={$INVOICE.invoice_id}">{$INVOICE.invoice_no}</span><input type="hidden" name="invoice-id[]" value="{$INVOICE.invoice_id}"/></td>
                            <td><span class="invoice-subject">{$INVOICE.subject}</span></td>
                            <td class="right"><span class="invoice-total">{CurrencyField::convertToUserFormat($INVOICE.invoice_total)}</span><input type="hidden" name="invoice-total[]" value="{CurrencyField::convertToUserFormat($INVOICE.invoice_total)}"/></td>
                            <td class="right"><span class="invoice-received">{CurrencyField::convertToUserFormat($INVOICE.invoice_received)}</span><input type="hidden" name="invoice-received[]" value="{CurrencyField::convertToUserFormat($INVOICE.invoice_received)}"/></td>
                            <td class="right"><span class="invoice-balance">{CurrencyField::convertToUserFormat($INVOICE.invoice_balance)}</span><input type="hidden" name="invoice-balance[]" value="{CurrencyField::convertToUserFormat($INVOICE.invoice_balance)}"/></td>
                            <td><input type="text" name="invoice-paid[]" onkeyup="formatNumberWithMaxValue(this, 'float')" class="inputElement" style="width:100%" value="{CurrencyField::convertToUserFormat($INVOICE.invoice_paid)}"/></td>                        
                            <td class="center"><span class="remove" onclick="removeRow(this)"><i class="far fa-times-circle"></i></span></td> 
                        </tr>
                    {/foreach}
                {/if}
                </tbody>
                <tfoot>
                    <tr class="hide">
                        <td><span class="invoice-code"><a target="_blank" href="index.php?module=Invoice&view=Detail&record="></a></span><input type="hidden" name="invoice-id[]"/></td>
                        <td><span class="invoice-subject"></span></td>
                        <td class="right"><span class="invoice-total"></span><input type="hidden" name="invoice-total[]"/></td>
                        <td class="right"><span class="invoice-received"></span><input type="hidden" name="invoice-received[]"/></td>
                        <td class="right"><span class="invoice-balance"></span><input type="hidden" name="invoice-balance[]"/></td>
                        <td><input type="text" name="invoice-paid[]" onkeyup="formatNumberWithMaxValue(this, 'float')" class="inputElement" style="width:100%"/></td>                        
                        <td class="center"><span class="remove" onclick="removeRow(this)"><i class="far fa-times-circle"></i></span></td> 
                    </tr>
                </tfoot>
            </table>
        </div>
    <div>
{/strip}