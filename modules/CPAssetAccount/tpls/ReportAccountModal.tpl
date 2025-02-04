{strip}
    <div id = "report_detail_container" class="item_modal modal-dialog modal-content hide">
        {assign var="HEADER_TITLE" value={vtranslate('LBL_REPORT_DETAIL', $MODULE_NAME)}}
        {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
        <form class='form-horizontal formReportDetail' method='POST'>
            <div id="report_filter">
                 <div class="form-group" style = "margin-top: 15px;">
                    <label class="control-label fieldLabel col-sm-2">
                        <span>{vtranslate('LBL_FROM_DATE', $MODULE_NAME)}</span>&nbsp;
                    </label>
                    <div class="control col-sm-3" style="display: inline-table;">
                        <input type="text" name="report_from_date" class="form-control datePicker" size=5 data-field-type="date" data-date-format="{$CURRENT_USER_MODEL->get('date_format')}"/><span class="input-group-addon"><i class="far fa-calendar "></i></span>
                    </div>
                     <label class="control-label fieldLabel col-sm-2">
                        <span>{vtranslate('LBL_TO_DATE', $MODULE_NAME)}</span>&nbsp;
                    </label>
                    <div class="control col-sm-3" style="display: inline-table;">
                        <input type="text" name="report_to_date" class="form-control datePicker" size=5 data-field-type="date" data-date-format="{$CURRENT_USER_MODEL->get('date_format')}"/><span class="input-group-addon"><i class="far fa-calendar "></i></span>
                    </div>
                </div>
            </div>
            <div id="report_action">
                <button class="btn btn-success btn-sm btn-view" onclick="getReportDetails()">{vtranslate('LBL_VIEW', $MODULE_NAME)}</button>
            </div>
            <div id = "report_detail">
                <div>
                    <table id="tbl_transactions">
                        <thead>
                            <tr class = "no-border">
                                <th colspan=2 style = "text-align: right">{vtranslate('LBL_CLOSING_BALANCE', $MODULE_NAME)}:</th>
                                <th style = "text-align: right">
                                    <label  id="lbl_closing_balance">
                                        <span></span>
                                    </label>
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th>{vtranslate('LBL_NO.', $MODULE_NAME)}</th>
                                <th>{vtranslate('LBL_DATE', $MODULE_NAME)}</th>
                                <th>{vtranslate('LBL_AMOUNT', $MODULE_NAME)}</th>
                                <th>{vtranslate('LBL_TRANSACTION_DETAIL', $MODULE_NAME)}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr class = "no-border">
                                <th colspan=2 style = "text-align: right">{vtranslate('LBL_OPENING_BALANCE', $MODULE_NAME)}:</th>
                                <th style = "text-align: right">
                                    <label  id="lbl_opening_balance">
                                        <span></span>
                                    </label>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </form>
    </div>
{/strip}