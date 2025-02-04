{* Added by Vu Mai on 2022-11-28 to display statistic customer status by employee table *}

{strip}
	<div id="customer-status-by-employee" class="box shadowed">
		<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_STATISTICS_OF_CUSTOMER_STATUS_BY_EMPLOYEE', $MODULE)}</div>
		<div class="box-body mt-3 ml-3 mr-3">
			<table class="table">
				<thead>
					<tr>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_EMPLOYEE', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CUSTOMER_TOTAL_COLUMN', $MODULE)}
						</th>
						{foreach key=STATUS item=STATUS_INFO from=$CUSTOMER_STATUS_LIST}
							{assign var="STATUS_LABEL" value=CPTelesales_Logic_Helper::generateCustomerStatusLabelKey($CAMPAIGN_PURPOSE, $STATUS)}

							<th class="text-center">
								{vtranslate($STATUS_LABEL, 'CampaignCustomerStatus')}
							</th>
						{/foreach}
					</tr>
				<thead>
				<tbody>
					{foreach key=ID item=EMPLOYEE from=$CUSTOMER_STATUS_LIST_BY_EMPLOYEE}
						{assign var=USER_NAME value=explode(" (", $EMPLOYEE.name)}

						<tr>
							<td class="text-center">
								{$USER_NAME[0]}
							</td>
							<td class="text-center">
								{$EMPLOYEE.total}
							</td>

							{foreach key=STATUS item=STATUS_INFO from=$CUSTOMER_STATUS_LIST}
								<td class="text-center">
									{if $EMPLOYEE[$STATUS]}{$EMPLOYEE[$STATUS]}{else}0{/if}
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}