{*
    File SMSAndOTTMessagePopup.tpl
    Author: Hieu Nguyen
    Date: 2020-11-13
    Purpose: to render SMS and OTT message popup
*}

{strip}
    <div id="smsAndOTTMessageModal" class="modal-dialog modal-content hide">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=""}
    
        <form id="smsAndOTTMessagePopup" class="form-horizontal" method="POST">
            <input type="hidden" name="module" value="Campaigns" />
            <input type="hidden" name="action" value="CampaignAjax" />
            <input type="hidden" name="mode" value="sendSMSAndOTTMessage" />
            <input type="hidden" name="channel" value="" />
            <input type="hidden" name="campaign_id" value="" />

            <div style="padding: 10px 30px">
                <div class="row form-group">
                    <div>
                        <span>{vtranslate('LBL_SMS_OTT_MODAL_SELECT_PHONE_FIELDS', $MODULE)}</span>
                    </div>
                    <div class="controls fieldValue">
                        <select name="phone_fields" class="form-control" style="width: 100%" multiple data-rule-required="true">
                            <option value="mobile">{vtranslate('Mobile')}</option>
                            <option value="phone">{vtranslate('Phone')}</option>
                        </select>
                    </div>
                </div>

                <div class="row form-group">
                    <div>
                        <span>{vtranslate('LBL_SMS_OTT_MODAL_SELECT_TEMPLATE', $MODULE)}</span>
                    </div>
                    <div class="controls fieldValue">
                        <select name="template" class="form-control" style="width: 100%" data-rule-required="true">
                            <option value="">{vtranslate('LBL_SMS_OTT_MODAL_SELECT_A_MESSAGE_TEMPLATE', $MODULE)}</option>
                        </select>
                    </div>
                    <div class="controls fieldValue">
                        <textarea name="message" class="form-control" data-rule-required="true" placeholder="{vtranslate('LBL_SMS_OTT_MODAL_MESSAGE', $MODULE)}" readonly style="resize: vertical;"></textarea>
                    </div>
                </div>

                <div class="row form-group">
                    <div>
                        <span>{vtranslate('LBL_SMS_OTT_MODAL_SELECT_TARGET_LISTS', $MODULE)}</span>
                    </div>
                    <div class="controls fieldValue targetListsContainer padding10">
                    </div>
                </div>

                <div class="row form-group send_plan">
                    <div>
                        <span>{vtranslate('LBL_SMS_OTT_MODAL_SELECT_SEND_TIME', $MODULE)}</span>
                    </div>
                    <div class="controls fieldValue padding10">
                        <label><input type="radio" name="send_plan" value="immediately" checked/>&nbsp;{vtranslate('LBL_SMS_OTT_MODAL_PLAN_IMMEDIATELY', $MODULE)}</label>
                        &nbsp;&nbsp;&nbsp;
                        <label><input type="radio" name="send_plan" value="schedule"/>&nbsp;{vtranslate('LBL_SMS_OTT_MODAL_PLAN_SCHEDULE', $MODULE)}</label>
                    </div>
                </div>

                <div class="row form-group schedule hide" style="padding-left: 10px">
                    <div>
                        <span>{vtranslate('LBL_SMS_OTT_MODAL_PLAN_SCHEDULE_HINT', $MODULE)}</span>
                    </div>
                    <div class="controls fieldValue" style="padding-left: 20px">
                        <div class="row">
                            <div class="col-lg-2 input-group scheduleDate" style="margin-bottom: 5px">
                                <input type="text" name="schedule_date" class="form-control datePicker" data-fieldtype="date" 
                                    data-date-format="{$USER_MODEL->get('date_format')}" data-rule-required="true" autocomplete="off" />
                                <span class="input-group-addon"><i class="far fa-calendar"></i></span>
                            </div>
                            <div class="col-lg-2 input-group time scheduleTime">
                                <input type="text" name="schedule_time" class="timepicker-default form-control" 
                                    data-format="12" data-rule-required="true"  autocomplete="off" />
                                <span class="input-group-addon"> <i class="far fa-clock"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row email hide">
                    <div>
                        <span>{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_FOR_TELCO', $MODULE)}</span>
                    </div>
                    <div class="controls fieldValue" style="padding-left: 20px; padding-top: 10px">
                        <div class="form-group">
                            <label class="col-sm-2 fieldLabel alignMiddle">
                                <span>{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_TO', $MODULE)}&nbsp;<span class="redColor">*</span></span>
                            </label>
                            <div class="controls fieldValue col-sm-9">
                                <select name="email_to" class="form-control" placeholder="{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_SELECT_TELCO_CONTACT', $MODULE)}" data-rule-required="true" multiple></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 fieldLabel alignMiddle">
                                <span>{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_TEMPLATE', $MODULE)}</span>
                            </label>
                            <div class="controls fieldValue col-sm-9">
                                <select name="email_template" class="form-control">
                                    <option value="">{vtranslate('LBL_SMS_OTT_MODAL_SELECT_AN_EMAIL_TEMPLATE', $MODULE)}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 fieldLabel alignMiddle">
                                <span>{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_SUBJECT', $MODULE)}&nbsp;<span class="redColor">*</span></span>
                            </label>
                            <div class="controls fieldValue col-sm-9">
                                <input type="text" name="email_subject" class="form-control" placeholder="{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_SUBJECT', $MODULE)}" data-rule-required="true" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 fieldLabel alignMiddle">
                                <span>{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_CONTENT', $MODULE)}&nbsp;<span class="redColor">*</span></span>
                            </label>
                            <div class="controls fieldValue col-sm-9">
                                <textarea type="text" name="email_content" name="email_content" class="form-control" placeholder="{vtranslate('LBL_SMS_OTT_MODAL_WRITE_EMAIL_CONTENT', $MODULE)}" style="height: 100px" data-rule-required="true"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <center>
                    <button class="btn btn-success" type="submit" name="btnSend">{vtranslate('LBL_SEND', 'Vtiger')}</button>
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                </center>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="{vresource_url("modules/Campaigns/resources/SMSAndOTTMessagePopup.js")}"></script>
{/strip}