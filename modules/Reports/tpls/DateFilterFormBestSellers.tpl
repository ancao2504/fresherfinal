{*
CustomReportBestseller template
Author: Si Dat
Date: 2024-01-03
Purpose: custom report to show best sellers
*}

{strip}
<!-- <script type="text/javascript" src="{vresource_url('modules/Reports/resources/CustomReportBestSellers.js')}"></script> -->

<form method="post" action="">
    <label for="start_date">Ngày bắt đầu:</label>
    <input type="date" id="start_date" name="start_date" style="margin-left: 5px">
    <label for="end_date" style="margin-left: 10px">Ngày kết thúc:</label>
    <input type="date" id="end_date" name="end_date" style="margin-left: 5px">
    <button id="btnReport" class="btn btn-success" type="submit" style="margin-left: 10px">Report</button>
</form>
{/strip}