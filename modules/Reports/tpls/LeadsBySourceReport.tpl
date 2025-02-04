{strip}
    <div id="custom-report-detail">
        <div id="filter">FILTER FORM HERE</div>
        <div id="chart">
            {$CHART}

            {* Clone dropdown button Pin To Dashboard. Sau khi click chọn thì gọi hàm Reports_ChartDetail_Js.savePinToDashBoard({ dashBoardTabId: 'id', params: {} }); *}
            {* Trong đó params là object chứa các params default cho widget filter. VD: { chart_title: 'Custom title cho mỗi bộ lọc', start_date: '', end_date: '' } *}
            {include file="modules/Reports/tpls/CustomReportAddChartToDashboard.tpl"}
        </div>
        <div id="result">{$REPORT_RESULT}</div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/LeadsBySourceReportDetail.js")}"></script>
{/strip}