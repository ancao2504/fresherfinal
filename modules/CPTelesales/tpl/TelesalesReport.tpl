<!-- Added by Vu Mai on 2022-11-28 to display template for telelsales report -->

{strip}
	<div id="report-page" class="row-fluid">
		<input type="hidden" name="record" value="{$RECORD}">
		<div id="header" class="align-item-center">
			<span id="title">{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CAMPAIGN_REPORT', $MODULE)}: 
				<select class="campaigns-filter dropdown-filter ml-2">
					<option value="">{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_SELETE_ONE_CAMPAIGN_TO_VIEW_REPORT', $MODULE)}</option>
					{foreach item=CAMPAIGNS key=KEY from=$TELESALE_CAMPAIGN_LIST}
						<option value="{$CAMPAIGNS.id}" {if $CAMPAIGNS.id == $RECORD}selected{/if}>{$CAMPAIGNS.name}</option>
					{/foreach}
				</select>
			</span>
			<div id="date-filter" class="flex">
				<div class="col-md-6 align-item-center">
					<span class="fieldLabel mr-2">{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_DATE_FILTER_FROM', $MODULE)}</span>
					<div class="input-group inputElement">
						<input type="text" name="date_from" class="form-control dateField" value="{$CAMPAIGN_INFO.start_date}" data-fieldtype="date" data-date-format="{$USER_MODEL->get('date_format')}" data-rule-required="true" />
						<span class="input-group-addon"><i class="far fa-calendar"></i></span>
					</div>
				</div>
				<div class="col-md-6 align-item-center">
					<span class="fieldLabel mr-2">{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_DATE_FILTER_TO', $MODULE)}</span>
					<div class="input-group inputElement">
						<input type="text" name="date_to" class="form-control dateField" value="{(strtotime($CURRENT_DATE) < strtotime($CAMPAIGN_INFO.end_date)) ? $CURRENT_DATE : $CAMPAIGN_INFO.end_date}" data-fieldtype="date" data-date-format="{$USER_MODEL->get('date_format')}" data-rule-required="true" />
						<span class="input-group-addon"><i class="far fa-calendar"></i></span>
					</div>
				</div>
			</div>
			<div id="action">
				<button id="print-report-btn" class="btn btn-default">
					<i class="fa-light fa-print mr-2"></i>
					<span>{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_PRINT_REPORT', $MODULE)}</span>
				</button>
			</div>
		</div>
		<div class="box-body">
			<!-- Report Statistic -->
			<div id="report-statistics-container" class="mt-3"></div>

			<!-- Telesale Summary by Employee -->
			<div id="telesales-summary-container" class="table-statistic"></div>

			<!-- Customer Status by Employee -->
			<div id="customer-status-by-employee-container" class="table-statistic"></div>
		</div>
	</div>
{/strip}