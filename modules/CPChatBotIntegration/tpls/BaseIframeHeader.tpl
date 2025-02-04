{*
    Author: Phu Vo
    Data: 2020.04.20
    Description: Provide basic header for iframe
*}

{strip}
<!DOCTYPE html>
<html>
    <head>
        <title>{$PAGETITLE}</title>
        <link rel="manifest" href="manifest.json">
        <link rel="shortcut icon" href="layouts/v7/resources/Images/logo_favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/font-awesome/css/font-awesome.min.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/resources/fonts/fontawsome6/css/all.css')}"> {* [UI] Added by Phu Vo on 2021.04.15 *}
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/jquery/select2/select2.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/select2-bootstrap/select2-bootstrap.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/vt-icons/style.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('resources/libraries/BootstrapDatepicker/bootstrap-datetimepicker.min.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('resources/libraries/DataTables/css/dataTables.bootstrap4.min.css')}">
        <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css')}">

        {foreach key=index item=cssModel from=$STYLES}
            <link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
        {/foreach}

        <script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>

        {* Added by Hieu Nguyen on 2019-10-22 to expose config into JS *}
        {assign var='VALIDATION_CONFIG' value=getGlobalVariable('validationConfig') scope='global'}

        <script>
            var _VALIDATION_CONFIG = {json_encode($VALIDATION_CONFIG)};
        </script>
        {* End Hieu Nguyen *}

		<script type="text/javascript">
			var _META = { 'module': "{$MODULE}", view: "{$VIEW}", 'parent': "{$PARENT_MODULE}", 'notifier':"{$NOTIFIER_URL}", 'app':"{$SELECTED_MENU_CATEGORY}" };

            {if $EXTENSION_MODULE}
                var _EXTENSIONMETA = { 'module': "{$EXTENSION_MODULE}", view: "{$EXTENSION_VIEW}"};
            {/if}

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

        <script src="{vresource_url('resources/StringUtils.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/select2/select2.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.class.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.js')}"></script>
        <script type="text/javascript" src="{vresource_url('libraries/jquery/jstorage.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/bootstrap-notify/bootstrap-notify.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mousewheel.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/daterangepicker/moment.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/daterangepicker/jquery.daterangepicker.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Class.js')}"></script>
        <script type='text/javascript' src="{vresource_url('layouts/v7/resources/helper.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/resources/application.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Utils.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/bootbox/bootbox.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Base.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Vtiger.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/Moment/MomentHelper.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/BootstrapDatepicker/bootstrap-datetimepicker.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/jquery.additions.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/DataTables/js/jquery.dataTables.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/DataTables/js/dataTables.bootstrap4.min.js')}"></script>

        {foreach key=index item=jsModel from=$SCRIPTS}
            <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
        {/foreach}

        <style type="text/css">
            .hide {
                display: none;
            }
            @media print {
                .noprint {
                    display:none;
                }
            }
        </style>

        <script>
            var _IFRAME_DATA = {ZEND_JSON::encode($IFRAME_DATA)};
        </script>
    </head>
    <body data-skinpath="{Vtiger_Theme::getBaseThemePath()}"
        data-language="{$LANGUAGE}"
        data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}"
        data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
        data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}"
        data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}"
        data-user-hourformat="{$CURRENT_USER_MODEL->get('hour_format')}"
        data-user-calendar-reminder-interval="{$CURRENT_USER_MODEL->getCurrentUserActivityReminderInSeconds()}"
    >
        <input type="hidden" id="start_day" value="{$CURRENT_USER_MODEL->get('dayoftheweek')}" /> 
        <div id="pjaxContainer" class="hide noprint"></div>
        <div id="messageBar" class="hide"></div>
        <div class="app-menu"></div>
        <div class="app-nav"></div>
{/strip}