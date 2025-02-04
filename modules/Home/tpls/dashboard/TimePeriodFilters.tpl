{*
    TimePeriodFilters
    Author: Phu Vo
    Date: 2020.08.24
*}

{strip}
    <div class="filterContainer boxSizingBorderBox">
        <div class="row">
            <div class="col-sm-12">
                <div class="col-lg-4 fieldLabel" style="margin-top: 0.5em">
                    <span>{vtranslate('LBL_DASHBOARD_TIME_PERIOD_FILTERS')}:</span>
                </div>
                <div class="col-lg-8 fieldValue">
                    <div class="inputElement-container">
                        <select name="period" class="filter widgetFilter dislayed-filter select2 reloadOnChange inputElement">
                            <option value="date" {if $PARAMS.period == 'date'}selected{/if}>{vtranslate('LBL_DASHBOARD_TIME_PERIOD_FILTER_TODAY', 'Reports')}</option>
                            <option value="week" {if $PARAMS.period == 'week'}selected{/if}>{vtranslate('LBL_DASHBOARD_TIME_PERIOD_FILTER_THIS_WEEK', 'Reports')}</option>
                            <option value="month" {if $PARAMS.period == 'month'}selected{/if}>{vtranslate('LBL_DASHBOARD_TIME_PERIOD_FILTER_THIS_MONTH', 'Reports')}</option>
                            <option value="quarter" {if $PARAMS.period == 'quarter'}selected{/if}>{vtranslate('LBL_DASHBOARD_TIME_PERIOD_FILTER_THIS_QUARTER', 'Reports')}</option>
                            <option value="year" {if $PARAMS.period == 'year'}selected{/if}>{vtranslate('LBL_DASHBOARD_TIME_PERIOD_FILTER_THIS_YEAR', 'Reports')}</option>
                            {if $CUMULATE}<option value="cumulate" {if $PARAMS.period == 'cumulate'}selected{/if}>{vtranslate('LBL_ALL', 'Reports')}</option>{/if}
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}