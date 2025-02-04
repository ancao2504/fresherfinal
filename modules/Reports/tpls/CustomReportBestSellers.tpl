{*
CustomReportBestseller template
Author: Si Dat
Date: 2024-01-03
Purpose: custom report to show best sellers
*}

{strip}
<script type="text/javascript" src="{vresource_url('modules/Reports/resources/CustomReportBestSellers.js')}"></script>

<div {if $PRINT}style="width:80%; margin:auto" {/if}>
    <h2>{$REPORT_NAME}</h2>

    <form method="post" action="">
        <label for="start_date">Ngày bắt đầu:</label>
        <input type="date" id="start_date" name="start_date" style="margin-left: 5px">
        <label for="end_date" style="margin-left: 10px">Ngày kết thúc:</label>
        <input type="date" id="end_date" name="end_date" style="margin-left: 5px">
        <label for="time_filter" style="margin-left: 10px">Bộ lọc thời gian:</label>
        <select id="time_filter" name="time_filter" style="margin-left: 5px">
            {foreach from=$TIME_FILTERS item=filter}
            <option value="{$filter}">{$filter}</option>
            {/foreach}
        </select>

        <button id="btnReport" class="btn btn-success" type="submit" style="margin-left: 10px">Report</button>
    </form>

    <table cellpadding="5" cellpadding="0"
        class="{if !$PRINT}table table-bordered{else}printReport reportPrintData{/if}">
        <thead>
            {foreach item=WIDTH key=HEADER from=$REPORT_HEADERS}
            <th {if !$PRINT}nowrap{/if} width="{$WIDTH}">{$HEADER}</th>
            {/foreach}
        </thead>
        <tbody>
            {if $REPORT_RESULT == ''}
            <tr>
                <td colspan="{count($REPORT_HEADERS)}" style="text-align: center;">Không có bản ghi nào</td>
            </tr>
            {else}
            {$REPORT_RESULT}
            {/if}
        </tbody>
    </table>
</div>
{/strip}