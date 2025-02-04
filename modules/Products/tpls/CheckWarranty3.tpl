{strip}
<div id="checkWarranty">
    <h4>{vtranslate('LBL_CHECK_WARRANTY_TITLE', 'Products')}</h4>
    <form id="checkWarrantyForm" method="POST" action="">
        <input type="text" name="serial" value="{$smarty.post.serial}"
            placeholder="{vtranslate('LBL_CHECK_WARRANTY_SERIAL', 'Products')}" />
        &nbsp;
        <button id="btnCheck" class="btn btn-primary">{vtranslate('LBL_CHECK_WARRANTY_SUBMIT_BTN', 'Products')}</button>
    </form>
    <div id="result" style="display: none">
        <table>
            <tr>
                <th width="200">{vtranslate('LBL_WARRANTY_PRODUCT_NAME', 'Products')}:</th>
                <td id="productName"></td>
            </tr>
            <tr>
                <th>{vtranslate('LBL_WARRANTY_SERIAL_NO', 'Products')}:</th>
                <td id="serialNo"></td>
            </tr>
            <tr>
                <th>{vtranslate('LBL_WARRANTY_START_DATE', 'Products')}:</th>
                <td id="warrantyStartDate"></td>
            </tr>
            <tr>
                <th>{vtranslate('LBL_WARRANTY_END_DATE', 'Products')}:</th>
                <td id="warrantyEndDate"></td>
            </tr>
            <tr>
                <th>{vtranslate('LBL_WARRANTY_STATUS', 'Products')}:</th>
                <td><span id="warrantyStatus" class="label"></span></td>
            </tr>
        </table>
    </div>
</div>
{/strip}