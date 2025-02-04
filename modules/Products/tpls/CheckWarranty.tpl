{strip}
<div id="checkWarranty">
    <h4>{vtranslate('LBL_CHECK_WARRANTY_TITLE', 'Products')}</h4>
    <form id="checkWarrantyForm" method="POST" action="">
        <input type="text" name="serial" value="{$smarty.post.serial}"
            placeholder="{vtranslate('LBL_CHECK_WARRANTY_SERIAL', 'Products')}">&nbsp;
        <button id="btnCheck" class="btn btn-primary">{vtranslate('LBL_CHECK_WARRANTY_SUBMIT_BTN', 'Products')}</button>
    </form>
    <div id="result">
        {$RESULT}
    </div>
</div>
{/strip}