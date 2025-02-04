{*
    CustomerHaveBirthdayThisMonthWidget
    Author: Phu Vo
    Date: 2020.08.28
*}

{include file="modules/Home/tpls/dashboard/BaseListCustomDashboardContents.tpl"}

<div style="display: none !important">
    <div class="modal-dialog modal-md modal-content customerHaveBirthDayThisMonthWidgetActions">
        {assign var=HEADER_TITLE value={vtranslate('Action')}}
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="action-wraper">
            <button class="btn btn-default">Gửi SMS</button>
            <button class="btn btn-default">Gửi Zalo</button>
            <button class="btn btn-default">Gửi Tin nhắn Facebook</button>
        </div>
    </div>
</div>