{*
    ListViewCustomHeader.tpl
    Author: Phuc Lu
    Date: 2019.11.05
*}

{strip}
    <div id="divConfirmCompleteModal" class="modal-dialog modal-content hide" style="min-width: 200px;width: 25%;">
        {assign var="HEADER_TITLE" value={vtranslate('LBL_CONFIRM_PAY', $MODULE)}}
        {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
        
        <div class="formDeclareConfirmCompleteForm">
            <input type="hidden" name="record" value=""/>
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="ConfirmAjax"/>
            <input type="hidden" name="mode" value="makeReceiptCompleted"/>

            <div class="form-group" style = "margin-top: 15px;">
                <label class="control-label fieldLabel col-sm-5">
                    <span>{vtranslate('LBL_AMOUNT_VND', $MODULE)}</span></span>
                </label>
                <div class="control col-sm-7" style="padding-top:6px;font-weight:100">
                    <span  class="amountVnd"></span>
                </div>
            </div>

            <div class="form-group" style = "margin-top: 15px;">
                <label class="control-label fieldLabel col-sm-5">
                    <span>{vtranslate('LBL_RECEIVED_AMOUNT', $MODULE)}</span>&nbsp;<span class="redColor">*</span>
                </label>
                <div class="control col-sm-7">
                    <input type="text" name="receivedAmountVND" style="width: 80%;" class="form-control inputElement currencyField receivedAmountVND" onkeyup="formatNumber(this, 'int'); validateAndCalculateRemaining(this);" data-rule-required="true"/>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label fieldLabel col-sm-5">
                    <span>{vtranslate('LBL_REMAINING_AMOUNT', $MODULE)}</span>
                </label>
                <div class="control col-sm-7" style="padding-top:6px;font-weight:100">
                    <span class="remainingAmountVND">0</span>
                </div>
            </div>

            {include file="ModalFooter.tpl"|@vtemplate_path:Vtiger}
        </div>
    </div>
{/strip}
