{* Added by Hieu Nguyen on 2020-09-07 *}

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />

        <link rel="icon" href="data:;base64,iVBORw0KGgo=">

        {* START-- Modified by Phu Vo on 2020.09.14 to fix missing and wrong lib version some css file cause unpredicable ui issues *}
        <link rel="stylesheet" href="{vresource_url('libraries/jquery/select2/select2.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/bootstrap.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/docs.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/todc-bootstrap.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/font-awesome/css/font-awesome.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/resources/fonts/fontawsome6/css/all.css')}" /> {* [UI] Added by Phu Vo on 2021.04.15 *}
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/select2/select2.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/select2-bootstrap/select2-bootstrap.css')}" />
        <link rel="stylesheet" href="{vresource_url('libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker3.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/vt-icons/style.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/animate/animate.min.css')}">
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.css')}">
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/daterangepicker/daterangepicker.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/timepicker/jquery.timepicker.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/skins/marketing/style.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/resources/custom.css')}" />

        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('libraries/jquery/jquery-visibility.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/todc/js/bootstrap.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/select2/select2.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.class.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Class.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/dashboards/Widget.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/GoogleChart/loader.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/HighCharts_8.1.0/code/highcharts.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/FreezeTable/freeze-table.min.js')}"></script>
        {* END-- Modified by Phu Vo on 2020.09.14 to fix missing and wrong lib version some css file cause unpredicable ui issues *}

        {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}

        <script type="text/javascript">
			const _META = { 'module': 'Reports', 'view': 'ChartDetail' };

            var _USERMETA;

            {if $CURRENT_USER_MODEL}
                {assign var='USER_IMAGE' value=$CURRENT_USER_MODEL->getImageDetails()}

                _USERMETA =  { 'id' : "{$CURRENT_USER_MODEL->get('id')}", 'menustatus' : "{$CURRENT_USER_MODEL->get('leftpanelhide')}", 
                    'currency' : "{$USER_CURRENCY_SYMBOL}", 'currencySymbolPlacement' : "{$CURRENT_USER_MODEL->get('currency_symbol_placement')}",
                    'currencyGroupingPattern' : "{$CURRENT_USER_MODEL->get('currency_grouping_pattern')}", 'truncateTrailingZeros' : "{$CURRENT_USER_MODEL->get('truncate_trailing_zeros')}"
                };

                _CURRENT_USER_META = { 
                    'id': '{$CURRENT_USER_MODEL->get('id')}',
                    'name': '{getFullNameFromArray('Users', $CURRENT_USER_MODEL->getData())}',
                    'avatar' : '{if $USER_IMAGE[0]}{$USER_IMAGE[0]['path']}_{$USER_IMAGE[0]['name']}{/if}',
                    'ext_number' : '{$CURRENT_USER_MODEL->get('phone_crm_extension')}',
                    'email' : '{$CURRENT_USER_MODEL->get('email1')}',
                };
            {/if}
		</script>

        <script type="text/javascript" src="{vresource_url('libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/bootstrap-datepicker.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/timepicker/jquery.timepicker.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Utils.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/resources/helper.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/resources/application.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/momentjs/moment.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/daterangepicker/moment.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/Moment/MomentHelper.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/StringUtils.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/CustomUiMeta.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/validation.js')}"></script>
    </head>

    <body data-skinpath="{Vtiger_Theme::getBaseThemePath()}" data-language="{$LANGUAGE}"
        data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}"
        data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
        data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}"
        data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}"
        data-user-hourformat="{$CURRENT_USER_MODEL->get('hour_format')}"
        data-user-calendar-reminder-interval="{$CURRENT_USER_MODEL->getCurrentUserActivityReminderInSeconds()}"
        style="visibility: hidden" {* Modified by Phu Vo on 2020.09.15 to make UI only show when ready*}
    >
        <input type="hidden" id="start_day" value="{$CURRENT_USER_MODEL->get('dayoftheweek')}" />
        <div id="js_strings" class="hide noprint">{Zend_Json::encode($JS_LANGUAGE_STRINGS)}</div>

        {* Begin: Custom scripts *}
        <link rel="stylesheet" href="{vresource_url('modules/Reports/resources/CustomReport.css')}" />
        <link rel="stylesheet" href="{vresource_url('modules/Reports/resources/EmbeddedReportChart.css')}" />
        <script type="text/javascript" src="{vresource_url('modules/Reports/resources/CustomReportHelper.js')}"></script>
        
        {if $REPORT_DETAIL_JS_FILE}
            <script type="text/javascript" src="{vresource_url($REPORT_DETAIL_JS_FILE)}"></script>
        {/if}
        {* End: Custom scripts *}

        <div id="custom-report-detail">
            <h1 class="text-center marginTop0px">{$REPORT_TITLE}</h1> {* Modified by Phu Vo on 2020.09.14 to fix margin top cause ui position issue *}

            {if $CHART}
                {if $REPORT_FILTER}
                    <div id="filter">
                        <form id="form-filter" name="filter" action="" method="GET" class="filter-container recordEditView">
                            <input type="hidden" name="name" value="EmbeddedReportChart"/>
                            <input type="hidden" name="record" value="{$smarty.get.record}"/>
                            <input type="hidden" name="token" value="{$smarty.get.token}"/>

                            {$REPORT_FILTER}
                        </form>
                    </div>
                {/if}

                <div id="chart" class="text-center">
                    <input type="hidden" name="chart_data" value='{json_encode($CHART_DATA)}' />
                    {$CHART}
                </div>
            {else}
                <div id="chart" class="text-center">
                    {vtranslate('LBL_EMBEDDED_REPORT_NO_CHART_MSG', 'Reports')}
                </div>
            {/if}
        </div>

        {* BEGIN-- Added by Phu Vo on 2020.09.15 to make UI only show when ready*}
        <script>
            $(function() {
                setTimeout(function() {
                    $('body').css('visibility', 'visisble');
                }, 0);
            });
        </script>
        {* END-- Added by Phu Vo on 2020.09.15 to make UI only show when ready*}

    </body>
</html>