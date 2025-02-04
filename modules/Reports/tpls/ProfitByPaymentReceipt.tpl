{*
    ProfitByPaymentReceipt.tpl
    Author: Phuc
    Date: 2019.09.19
*}

{literal}
<script>
    jQuery(function($) {
        setRecord = setInterval(function() {
            if ($('#countValue').find('img').length == 0) {
                $('#countValue').html({/literal}{$ROW_COUNT}{literal});
                clearInterval(setRecord);
            }
        }, 100);

        removeSummary = setInterval(function() {
            if ($('.contents-topscroll').length > 0) {
                $('.contents-topscroll').remove();
                clearInterval(removeSummary);
            }
        }, 100);
    })
</script>
{/literal}

{strip}
    <div class="contents-topscroll">
        <div class="topscroll-div">
            <table class=" table-bordered table-condensed marginBottom10px" width="100%">
                <thead>
                    <tr class="blockHeader">
                        <th></th>
                        <th>{vtranslate('LBL_AMOUNT_VND', 'CPReceipt')}</th>
                    </tr>
                </thead>
                <tbody>
                        <th>{vtranslate('LBL_SUM_VALUE', 'Reports')}</th>
                        <th style="text-align:right">{$CALCULATION_FIELD['amount']}</th>
                </tbody>
            </table>
        </div>
    </div>
    <div {if $PRINT}style="width:80%; margin:auto"{/if}>
        <table cellpadding="5" cellpadding="0" class="{if !$PRINT}table table-bordered{else}printReport reportPrintData{/if}">
            <thead>
                <tr class="blockHeader">
                    {foreach item=HEADER_NAME from=$REPORT_HEADERS}
                        <th {if !$PRINT}nowrap{/if}>{$HEADER_NAME}</th>
                    {/foreach}
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{$REPORT_RESULT['receiptAmount']}</td>
                    <td>{$REPORT_RESULT['paymentAmount']}</td>
                    <td>{$REPORT_RESULT['profit']}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
{/strip}