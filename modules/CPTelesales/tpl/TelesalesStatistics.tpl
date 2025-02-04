{* Created By Vu Mai on 2022-10-24 to render telesales stactistics view *}

{strip}
	<div id="call-statistics" class="box shadowed">
		<div class="box-body">
			<div id="statistics-items" class="flex">
				<div class="statistics-item-wrapper">
					{if $OUTBOUND_CALL_TOTAL.main_value < $OUTBOUND_CALL_TOTAL.compare_value}
						{assign var=OUTBOUND_CALL_TOTAL_STATUS value='decrease'}
						{assign var=OUTBOUND_CALL_TOTAL_STATUS_ICON value='fa-caret-down'}
					{else}
						{assign var=OUTBOUND_CALL_TOTAL_STATUS value='increase'}
						{assign var=OUTBOUND_CALL_TOTAL_STATUS_ICON value='fa-caret-up'}
					{/if}

					<div id="outbound-call-total" class="statistics-item {$OUTBOUND_CALL_TOTAL_STATUS}">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_OUTBOUND_CALL_TOTAL', $MODULE_NAME)}</div>
						<div class="current-value">{if $OUTBOUND_CALL_TOTAL.main_value}{$OUTBOUND_CALL_TOTAL.main_value}{else}0{/if}</div>
						<div class="compare-value">
							<i class="fa-solid {$OUTBOUND_CALL_TOTAL_STATUS_ICON} mr-1"></i>
							<span>{if $OUTBOUND_CALL_TOTAL.compare_value}{$OUTBOUND_CALL_TOTAL.compare_value}{else}0{/if}</span>
						</div>
					</div>
				</div>


				<div class="statistics-item-wrapper">
					{if $OUTBOUND_CALL_MINUTES_TOTAL.main_value < $OUTBOUND_CALL_MINUTES_TOTAL.compare_value}
						{assign var=OUTBOUND_CALL_MINUTES_TOTAL_STATUS value='decrease'}
						{assign var=OUTBOUND_CALL_MINUTES_TOTAL_STATUS_ICON value='fa-caret-down'}
					{else}
						{assign var=OUTBOUND_CALL_MINUTES_TOTAL_STATUS value='increase'}
						{assign var=OUTBOUND_CALL_MINUTES_TOTAL_STATUS_ICON value='fa-caret-up'}
					{/if}

					<div id="outbound-call-minutes-total" class="statistics-item {$OUTBOUND_CALL_MINUTES_TOTAL_STATUS}">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_OUTBOUND_CALL_MINUTES_TOTAL', $MODULE_NAME)}</div>
						<div class="current-value">{if $OUTBOUND_CALL_MINUTES_TOTAL.main_value}{$OUTBOUND_CALL_MINUTES_TOTAL.main_value}{else}0{/if}</div>
						<div class="compare-value">
							<i class="fa-solid {$OUTBOUND_CALL_MINUTES_TOTAL_STATUS_ICON} mr-1"></i>
							<span>{if $OUTBOUND_CALL_MINUTES_TOTAL.compare_value}{$OUTBOUND_CALL_MINUTES_TOTAL.compare_value}{else}0{/if}</span>
						</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					{if $SALE_TOTAL.main_value < $SALE_TOTAL.compare_value}
						{assign var=SALE_TOTAL_STATUS value='decrease'}
						{assign var=SALE_TOTAL_STATUS_ICON value='fa-caret-down'}
					{else}
						{assign var=SALE_TOTAL_STATUS value='increase'}
						{assign var=SALE_TOTAL_STATUS_ICON value='fa-caret-up'}
					{/if}

					<div id="sale-total" class="statistics-item {$SALE_TOTAL_STATUS}">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_SALE_TOTAL', $MODULE_NAME)}</div>
						<div class="current-value">
							{if $SALE_TOTAL.main_value}
								{Vtiger_Currency_UIType::transformDisplayValue($SALE_TOTAL.main_value, null, false)}
							{else}
								0
							{/if}
						</div>
						<div class="compare-value">
							<i class="fa-solid {$SALE_TOTAL_STATUS_ICON} mr-1"></i>
							<span>
								{if $SALE_TOTAL.compare_value}
									{Vtiger_Currency_UIType::transformDisplayValue($SALE_TOTAL.compare_value, null, false)}
								{else}
									0
								{/if}
							</span>
						</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					{if $ORDERS_TOTAL.main_value < $ORDERS_TOTAL.compare_value}
						{assign var=ORDERS_TOTAL_STATUS value='decrease'}
						{assign var=ORDERS_TOTAL_STATUS_ICON value='fa-caret-down'}
					{else}
						{assign var=ORDERS_TOTAL_STATUS value='increase'}
						{assign var=ORDERS_TOTAL_STATUS_ICON value='fa-caret-up'}
					{/if}

					<div id="orders-total" class="statistics-item {$ORDERS_TOTAL_STATUS}">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDERS_TOTAL', $MODULE_NAME)}</div>
						<div class="current-value">{if $ORDERS_TOTAL.main_value}{$ORDERS_TOTAL.main_value}{else}0{/if}</div>
						<div class="compare-value">
							<i class="fa-solid {$ORDERS_TOTAL_STATUS_ICON} mr-1"></i>
							<span>{if $ORDERS_TOTAL.compare_value}{$ORDERS_TOTAL.compare_value}{else}0{/if}</span>
						</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					{if $ORDERS_CONFIRMED_RATE.main_value < $ORDERS_CONFIRMED_RATE.compare_value}
						{assign var=ORDERS_TOTAL_STATUS value='decrease'}
						{assign var=ORDERS_TOTAL_STATUS_ICON value='fa-caret-down'}
					{else}
						{assign var=ORDERS_TOTAL_STATUS value='increase'}
						{assign var=ORDERS_TOTAL_STATUS_ICON value='fa-caret-up'}
					{/if}

					<div id="order-confirmed-rate" class="statistics-item {$ORDERS_TOTAL_STATUS}">
						<div class="name">
							{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDER_CONFIRMED_RATE', $MODULE_NAME)}
							<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDER_CONFIRMED_RATE_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
						</div>
						<div class="current-value">{if $ORDERS_CONFIRMED_RATE.main_value}{$ORDERS_CONFIRMED_RATE.main_value}%{else}0%{/if}</div>
						<div class="compare-value">
							<i class="fa-solid {$ORDERS_TOTAL_STATUS_ICON} mr-1"></i>
							<span>{if $ORDERS_CONFIRMED_RATE.compare_value}{$ORDERS_CONFIRMED_RATE.compare_value}%{else}0%{/if}</span>
						</div>
					</div>
				</div>
			</div>
			<div id="statistics-filter-wrapper">
				<i class="fa-solid fa-filter"></i>
				<select class="statistics-filter dropdown-filter">
					<option value="today" {if $TIME == 'today'}selected{/if}>{vtranslate('LBL_TODAY')}</option>
					<option value="yesterday" {if $TIME == 'yesterday'}selected{/if}>{vtranslate('LBL_YESTERDAY')}</option>
					<option value="3_day" {if $TIME == '3_day'}selected{/if}>{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_FILTER_THE_PAST_3_DAYS', $MODULE_NAME)}</option>
					<option value="7_day" {if $TIME == '7_day'}selected{/if}>{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_FILTER_THE_PAST_7_DAYS', $MODULE_NAME)}</option>
					<option value="14_day" {if $TIME == '14_day'}selected{/if}>{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_FILTER_THE_PAST_14_DAYS', $MODULE_NAME)}</option>
					<option value="this_week" {if $TIME == 'this_week'}selected{/if}>{vtranslate('LBL_CURRENT_WEEK')}</option>
					<option value="last_week" {if $TIME == 'last_week'}selected{/if}>{vtranslate('LBL_LAST_WEEK')}</option>
					<option value="this_month" {if $TIME == 'this_month'}selected{/if}>{vtranslate('LBL_CURRENT_MONTH')}</option>
					<option value="last_month" {if $TIME == 'last_month'}selected{/if}>{vtranslate('LBL_LAST_MONTH')}</option>
					<option value="this_quarter" {if $TIME == 'this_quarter'}selected{/if}>{vtranslate('LBL_CURRENT_FQ')}</option>
					<option value="last_quarter" {if $TIME == 'last_quarter'}selected{/if}>{vtranslate('LBL_PREVIOUS_FQ')}</option>
					<option value="this_year" {if $TIME == 'this_year'}selected{/if}>{vtranslate('LBL_CURRENT_FY')}</option>
					<option value="last_year" {if $TIME == 'last_year'}selected{/if}>{vtranslate('LBL_PREVIOUS_FY')}</option>
				</select>
			</div>
		</div>
	</div>
{/strip}