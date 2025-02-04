{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form class="form-horizontal" name="faq">
                {assign var=HEADER_TITLE value="FAQs"}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <input type="hidden" name="record" value="{$RECORD->get('id')}" />
                <div class="modal-body">
                    <table class="table no-border">
                        <tr>
                            <td class="fieldLabel col-lg-3"><label>{vtranslate('LBL_CALL_POPUP_FAQ_QUESTION', 'PBXManager')}</label></td>
                            <td class="fieldValue col-lg-9">{$RECORD->get('question')}</td>
                        </tr>
                        <tr>
                            <td class="fieldLabel col-lg-3"><label>{vtranslate('LBL_CALL_POPUP_FAQ_CATEGORY', 'PBXManager')}</label></td>
                            <td class="fieldValue col-lg-9">{$RECORD->get('faqcategories')}</td>
                        </tr>
                        <tr>
                            <td class="fieldLabel col-lg-3"><label>{vtranslate('LBL_CALL_POPUP_FAQ_ANSWER', 'PBXManager')}</label></td>
                            <td class="fieldValue col-lg-9">
                                <div class="fqa-answer">
                                    {$RECORD->get('faq_answer')}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldLabel col-lg-3"><button type="button" name="send_email" class="btn btn-default">{vtranslate('LBL_CALL_POPUP_FAQ_SEND_EMAIL', 'PBXManager')}</button></td>
                            <td class="fieldValue col-lg-9">
                                <div class="displayOnEmail email-container table fieldBlockContainer" style="display: none">
                                    <div class="row form-group">
                                        <div class="fieldLabel col-lg-2"><label>{vtranslate('LBL_CALL_POPUP_FAQ_SEND_EMAIL_TO', 'PBXManager')} <span class="redColor">*</span></label></div>
                                        <div class="fieldValue col-lg-4"><input type="text" name="emails" data-rule-required="true" class="inputElement" value="{$DATA['customer_email']}"/></div>
                                        <div class="col-lg-6"><button type="submit" name="send" class="btn btn-success">{vtranslate('LBL_CALL_POPUP_FAQ_SEND_EMAIL_BUTTON', 'PBXManager')}</button></div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="fieldLabel col-lg-2"><label>{vtranslate('LBL_CALL_POPUP_FAQ_SEND_EMAIL_CC', 'PBXManager')}</label></div>
                                        <div class="fieleValue col-lg-4"><input type="text" name="ccs" class="inputElement" /></div>
                                        <div class="col-lg-6"></div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="fieldLabel col-lg-2"></div>
                                        <div class="fieldValue col-lg-10"><p class="email-tip">{vtranslate('LBL_FAQ_EMAIL_SEPARATE_TIP', 'PBXManager')}</p></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                {if !empty($DATA['use_footer'])}
                    <div class="modal-footer">
                        <center><a href="javascript:void(0)" class="cancelLink" type="reset">{vtranslate('LBL_BACK', 'Vtiger')}</a></center>
                    </div>
                {/if}
            </form>
        </div>
    </div>
{/strip}