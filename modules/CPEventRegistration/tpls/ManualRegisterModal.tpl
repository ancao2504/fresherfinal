{*
    File: ManualRegisterModal.tpl
    Author: Phu Vo
    Date: 2020.07.20
*}

{strip}
    <div class="modal-dialog modal-md manual-transfer">
        <div class="modal-content binding-form faq-search">
            <form class="form-horizontal" name="manual_register">
                {assign var=HEADER_TITLE value="{vtranslate('LBL_MANUAL_REGISTER', $MODULE)}"}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <div class="modal-body">
                    <input type="hidden" name="module" value="CPEventRegistration" />
                    <input type="hidden" name="action" value="RegistrationAjax" />
                    <input type="hidden" name="mode" value="manualRegister" />
                    <input type="hidden" name="parent_module" value="{$PARENT_MODULE}" />
                    <input type="hidden" name="parent_id" value="{$PARENT_ID}" />

                    {$replaceParams = ['%customer_type' => vtranslate($SINGLE_PARENT_MODULE, $PARENT_MODULE), '%customer_name' => $PARENT_NAME]}
                    <h4 style="padding: 0px 8px">{vtranslate('LBL_MANUAL_REGISTER_DESCRIPTION', $MODULE, $replaceParams)}</h4>

                    <div class="quickCreateContent" style="margin-top: 6px">
                        <table class="table no-border">
                            <tr>
                                <td class="fieldLabel alignTop col-sm-4">
                                    <label>{vtranslate('LBL_MANUAL_REGISTER_SELECT_EVENT', $MODULE)}</label>
                                </td>
                                <td class="fieldValue alignTop col-sm-8">
                                    <select name="event_id" class="inputElement select2" data-rule-required="true" style="width: 100%; max-width: 362px">
                                        <option value="">{vtranslate('LBL_MANUAL_REGISTER_SELECT_EVENT', $MODULE)}</option>
                                        {foreach from=$RUNNING_EVENTS item=item}
                                            <option value="{$item['id']}">{$item['text']}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer ">
                    <center>
                        <button class="btn btn-success" type="submit" name="manualRegister"><strong>{vtranslate('LBL_MANUAL_REGISTER_SUBMIT_BTN', $MODULE)}</strong></button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </center>
                </div>
            </form>
        </div>
    </div>
{/strip}