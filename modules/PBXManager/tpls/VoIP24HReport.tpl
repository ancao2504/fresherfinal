{*
    Author: Phu Vo
    Date: 2019.04.12
    Purpose: Cloudfone report template
*}

{strip}
    <div class="reportContainer">
        <div class="reportTitle rel">
            <label class="title">{$REPORT_TITLE}</label>
        </div>
        <div class="reportContents">
            <form name="filters" class="filter">
                <table style="width: 100%">
                    <tr style="display: none">
                        <td class="fieldValue alignMiddle">
                            <input id="start" name="start" />
                            <input id="next" name="next" />
                            <input id="prev" name="prev" />
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_FILTER_FROM', $MODULE)} <span class="redColor">*</span></td>
                        <td class="fieldValue alignMiddle"><input name="date_start" class="inputElement dateField" data-rule-required="true" data-rule-date="true" placeholder="{vtranslate('LBL_DATE_START', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_FILTER_TO', $MODULE)} <span class="redColor">*</span></td>
                        <td class="fieldValue alignMiddle"><input name="date_end" class="inputElement dateField" data-rule-required="true" data-rule-date="true" placeholder="{vtranslate('LBL_DATE_END', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_SEARCH', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="search" class="inputElement" placeholder="{vtranslate('LBL_SEARCH_PLACEHOLDER', $MODULE)}" value="" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_CALLER_NUMBER', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="source" class="inputElement" placeholder="{vtranslate('LBL_CALLER_NUMBER', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_RECEIVER_NUMBER', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="destination" class="inputElement" placeholder="{vtranslate('LBL_RECEIVER_NUMBER', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_CALL_STATUS', $MODULE)}</td>
                        <td class="fieldValue alignMiddle">
                            <select name="status" class="inputElement select2" placeholder="{vtranslate('LBL_CALL_STATUS', $MODULE)}" value="" autocomplete="off">
                                <option value="">{vtranslate('LBL_CALL_STATUS', $MODULE)}</option>
                                <option value="MISSED">{vtranslate('LBL_CALL_STATUS_MISSED', $MODULE)}</option>
                                <option value="ANSWERED">{vtranslate('LBL_CALL_STATUS_ANSWERED', $MODULE)}</option>
                                <option value="NO ANSWER">{vtranslate('LBL_CALL_STATUS_NO_ANSWER', $MODULE)}</option>
                                <option value="FAILED">{vtranslate('LBL_CALL_STATUS_FAILED', $MODULE)}</option>
                                <option value="BUSY">{vtranslate('LBL_CALL_STATUS_BUSY', $MODULE)}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_KEY', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="callid" class="inputElement" placeholder="{vtranslate('LBL_KEY', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('Direction', $MODULE)}</td>
                        <td class="fieldValue alignMiddle">
                            <select name="type" class="inputElement select2" placeholder="{vtranslate('Direction', $MODULE)}" value="" autocomplete="off">
                                <option value="">{vtranslate('Direction', $MODULE)}</option>
                                <option value="inbound">{vtranslate('LBL_INBOUND', $MODULE)}</option>
                                <option value="outbound">{vtranslate('LBL_OUTBOUND', $MODULE)}</option>
                                <option value="local">{vtranslate('LBL_LOCAL', $MODULE)}</option>
                            </select>
                        </td>
                        <td class="fieldLabel alignMiddle"></td>
                        <td class="fieldValue alignMiddle toRight">
                            <button type="button" id="clear" class="btn">{vtranslate('LBL_CLEAR', $MODULE)}</button>
                            <button type="submit" id="filter" class="btn-primary">{vtranslate('LBL_FILTER', $MODULE)}</button>
                        </td>
                    </tr>
                </table>
            </form>
            <table id="listViewTable" class="table table-striped table-bordered" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 3%">{vtranslate('LBL_ORDER_NUMBER', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_KEY', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_HEAD_NUMBER', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_CALLER_NUMBER', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_RECEIVER_NUMBER', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_CALL_DIRECTION', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_CALL_TIME', $MODULE)}</th>
                        <th style="width: 9%">{vtranslate('LBL_CALL_DURATION', $MODULE)}</th>
                        <th style="width: 9%">{vtranslate('LBL_CALL_NOTE', $MODULE)}</th>
                        <th style="width: 9%">{vtranslate('LBL_CALL_STATUS', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_RECORDING_FILE', $MODULE)}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/DataTables/css/CustomTable.css')}" />
    <link type="text/css" rel="stylesheet" href="{vresource_url('modules/PBXManager/resources/VoIP24HReport.css')}" />

    <script src="{vresource_url('resources/libraries/DataTables/js/dataTables.buttons.min.js')}"></script>
    <script src="{vresource_url('resources/libraries/DataTables/js/buttons.html5.min.js')}"></script>
    <script src="{vresource_url('resources/libraries/DataTables/js/jszip.min.js')}"></script>

    <script src="{vresource_url('modules/PBXManager/resources/VoIP24HReport.js')}"></script>
{strip}