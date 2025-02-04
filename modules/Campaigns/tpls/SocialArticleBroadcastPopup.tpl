{*
    File SocialArticleBroadcastPopup.tpl
    Author: Hieu Nguyen
    Date: 2019-08-06
    Purpose: to render social article broadcast popup
*}

{strip}
    <div id="socialArticleBroadcastModal" class="modal-dialog modal-content hide">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_TITLE', $MODULE)}
    
        <form id="socialArticleBroadcastForm" class="form-horizontal" method="POST">
            <input type="hidden" name="channel" value=""/>
            <input type="hidden" name="campaign_id" value=""/>
            <input type="hidden" name="article_id" value=""/>

            <div class="padding10">
                <div class="form-group">
                    <label class="col-sm-3">
                        <span>{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_SELECTED_ARTICLE', $MODULE)}</span>
                    </label>
                    <div class="controls fieldValue col-sm-9">
                        <span class="label label-info articleName"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3">
                        <span>{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_SENDER', $MODULE)}</span>
                    </label>
                    <div class="controls fieldValue col-sm-9">
                        <input type="hidden" name="sender_id" class="form-control" data-rule-required="true"/>
                        <span class="label label-danger senderName"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3">
                        <span>{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_TARGET_LISTS', $MODULE)}</span>
                    </label>
                    <div class="controls fieldValue col-sm-9 targetListsContainer">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3">
                        <span>{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_SEND_PLAN', $MODULE)}</span>
                    </label>
                    <div class="controls fieldValue col-sm-9">
                        <label><input type="radio" name="send_plan" value="immediately" checked/>&nbsp;{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_PLAN_SEND_NOW', $MODULE)}</label>
                        &nbsp;&nbsp;&nbsp;
                        <label><input type="radio" name="send_plan" value="schedule"/>&nbsp;{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_PLAN_SCHEDULE', $MODULE)}</label>
                    </div>
                </div>

                <div class="form-group schedule hide">
                    <label class="col-sm-3">
                        <span>{vtranslate('LBL_SOCIAL_INTEGRATION_ARTICLE_BROADCAST_MODAL_PLAN_SCHEDULE_HINT', $MODULE)}</span>
                    </label>
                    <div class="controls fieldValue col-sm-9">
                        <div class="row">
                            <div class="input-group inputElement scheduleDate">
                                <input type="text" name="schedule_date" class="form-control datePicker" data-fieldtype="date" 
                                    data-date-format="{$USER_MODEL->get('date_format')}" data-rule-required="true"/>
                                <span class="input-group-addon"><i class="far fa-calendar"></i></span>
                            </div>
                            <div class="input-group inputElement time scheduleTime" >
                                <input type="text" name="schedule_time" class="timepicker-default form-control" data-format="12" data-rule- required="true"/>
                                <span class="input-group-addon"> <i class="far fa-clock"></i></span>
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
{/strip}