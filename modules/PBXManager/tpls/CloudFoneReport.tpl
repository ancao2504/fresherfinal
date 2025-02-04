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
                    <tr>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_CALLER_NUMBER', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="call_num" class="inputElement" placeholder="{vtranslate('LBL_CALLER_NUMBER', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_RECEIVER_NUMBER', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="receive_num" class="inputElement" placeholder="{vtranslate('LBL_RECEIVER_NUMBER', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_KEY', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="key" class="inputElement" placeholder="{vtranslate('LBL_KEY', $MODULE)}" value="" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_FILTER_FROM', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="date_start" class="inputElement dateField" placeholder="{vtranslate('LBL_DATE_START', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('LBL_FILTER_TO', $MODULE)}</td>
                        <td class="fieldValue alignMiddle"><input name="date_end" class="inputElement dateField" placeholder="{vtranslate('LBL_DATE_END', $MODULE)}" value="" autocomplete="off"/></td>
                        <td class="fieldLabel alignMiddle">{vtranslate('Direction', $MODULE)}</td>
                        <td class="fieldValue alignMiddle">
                            <select name="type_get" class="inputElement select2" placeholder="{vtranslate('Direction', $MODULE)}" value="" autocomplete="off">
                                <option value="">{vtranslate('Direction', $MODULE)}</option>
                                <option value="1">{vtranslate('LBL_INBOUND', $MODULE)}</option>
                                <option value="2">{vtranslate('LBL_OUTBOUND', $MODULE)}</option>
                                <option value="3">{vtranslate('LBL_LOCAL', $MODULE)}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="fieldValue alignMiddle toRight">
                            <button type="button" id="clear" class="btn">{vtranslate('LBL_CLEAR', $MODULE)}</button>
                            <button type="button" id="filter" class="btn-primary">{vtranslate('LBL_FILTER', $MODULE)}</button>
                        </td>
                    </tr>
                </table>
            </form>
            <table id="listViewTable" class="table table-striped table-bordered" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 3%">{vtranslate('LBL_ORDER_NUMBER', $MODULE)}</th>
                        <th style="width: 12%">{vtranslate('LBL_KEY', $MODULE)}</th>
                        <th style="width: 12%">{vtranslate('LBL_HEAD_NUMBER', $MODULE)}</th>
                        <th style="width: 12%">{vtranslate('LBL_CALLER_NUMBER', $MODULE)}</th>
                        <th style="width: 12%">{vtranslate('LBL_RECEIVER_NUMBER', $MODULE)}</th>
                        <th style="width: 12%">{vtranslate('LBL_CALL_TIME', $MODULE)}</th>
                        <th style="width: 9%">{vtranslate('LBL_CALL_DURATION', $MODULE)}</th>
                        <th style="width: 9%">{vtranslate('LBL_CALL_REAL_DURATION', $MODULE)}</th>
                        <th style="width: 9%">{vtranslate('LBL_CALL_STATUS', $MODULE)}</th>
                        <th style="width: 10%">{vtranslate('LBL_RECORDING_FILE', $MODULE)}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    
    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/DataTables/css/CustomTable.css')}" />
    <link type="text/css" rel="stylesheet" href="{vresource_url('modules/PBXManager/resources/CloudFoneReport.css')}" />

    <script src="{vresource_url('resources/libraries/DataTables/js/dataTables.buttons.min.js')}"></script>
    <script src="{vresource_url('resources/libraries/DataTables/js/buttons.html5.min.js')}"></script>
    <script src="{vresource_url('resources/libraries/DataTables/js/jszip.min.js')}"></script>
    
    <script src="{vresource_url('modules/PBXManager/resources/CloudFoneReport.js')}"></script>
{strip}