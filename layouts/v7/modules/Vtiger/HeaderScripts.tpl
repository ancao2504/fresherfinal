{strip}
    {* Added by Hieu Nguyen on 2019-10-22 to expose config into JS *}
    {assign var='VALIDATION_CONFIG' value=getGlobalVariable('validationConfig') scope='global'}

    <script>
        var _VALIDATION_CONFIG = {json_encode($VALIDATION_CONFIG)};
    </script>
    {* End Hieu Nguyen *}

    {* Added by Hieu Nguyen on 2020-02-24 to expose user calendar settings into JS *}
    {assign var='CALENDAR_USER_SETTINGS' value=Calendar_Settings_Model::getUserSettings() scope='global'}

    <script>
        var _CALENDAR_USER_SETTINGS = {json_encode($CALENDAR_USER_SETTINGS)};
    </script>
    {* End Hieu Nguyen *}

    {* Added by Phu Vo on 2019-04-03 *}
    {* moment helper *}
    <script type="text/javascript" src="{vresource_url('resources/libraries/Moment/MomentHelper.js')}"></script>
    {* End Phu Vo *}

    {* Added by Phu Vo on 2020.01.15 to init perfect scrollbar *}
    <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css')}">
    <script src="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js')}"></script>
    {* End Phu Vo *}

    {* Added by Phu Vo on 2021.05.21 support tippy tooltip *}
    <script src="resources/libraries/Poper/2.9.2/popper.min.js"></script>
    <script src="resources/libraries/Tippy/6.3.1/tippy-bundle.umd.js"></script>
    {* End Phu Vo *}

    {* Added by Phu Vo on 2021.01.13 to load vue js globally *}
    {if $CURRENT_USER_MODEL}
        {if isDeveloperMode()}
            <script src="{vresource_url('resources/libraries/Vue/development.vue.js')}"></script>
        {else}
            <script src="{vresource_url('resources/libraries/Vue/product.vue.js')}"></script>
        {/if}
        <script src="{vresource_url('resources/libraries/Vue/bootstrap-vue.js')}"></script>
        <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/Vue/bootstrap-vue.css')}" />
    {/if}
    {* End Phu Vo *}
    
    <script src="{vresource_url('resources/Numeric.js')}"></script>
    <script src="{vresource_url('resources/libraries/jQuery/CursorPosition.js')}"></script>
    <script src="{vresource_url('resources/libraries/jQuery/jquery.cookie.js')}"></script>
    <script src="{vresource_url('resources/libraries/jQuery/jquery.serialize-object.min.js')}"></script>
    <script src="{vresource_url('resources/StringUtils.js')}"></script>
    <script src="{vresource_url('resources/CustomPopover.js')}"></script> {* Added by Phu Vo on 2019.06.25 *}

    {* Added by Phu Vo on 2019.08.26 to add swipebox libraries *}
    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/SwipeBox/swipebox.css')}">
    <script src="{vresource_url('resources/libraries/SwipeBox/jquery.swipebox.min.js')}"></script>
    <script src="{vresource_url('resources/libraries/SwipeBox/swipe.init.js')}"></script>
    {* End swipebox libraries *}

    {* Added by Phu Vo on 2020.01.06 *}
    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/DataTables/css/jquery.dataTables.min.css')}" />
    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/DataTables/css/dataTables.bootstrap4.min.css')}" />
    <script src="{vresource_url('resources/libraries/DataTables/js/jquery.dataTables.min.js')}"></script>
    <script src="{vresource_url('resources/libraries/DataTables/js/dataTables.bootstrap4.min.js')}"></script>
    {* End Phu Vo *}

    {* Added by Phu Vo on 2020.12.04 to disable Datatable column error warning *}
    <script>jQuery(function() { $.fn.DataTable.ext.errMode = 'none'; });</script>
    {* End Phu Vo *}

    {* Added by Phuc on 2020.05.20 *}
    <script src="{vresource_url('resources/libraries/FreezeTable/freeze-table.min.js')}"></script>
    {* Ended by Phuc *}

    {* Added by Phuc on 2020.06.04 *}
    <script src="{vresource_url('resources/libraries/HighCharts_8.1.0/code/highcharts.js')}"></script>
    {* Ended by Phuc *}

    {* Added by Hieu Nguyen on 2021-06-10 to show export menu on chart rendered by HighCharts *}
    <script src="{vresource_url('resources/libraries/HighCharts_8.1.0/code/modules/exporting.js')}"></script>

    <script>
        Highcharts.setOptions({
            lang: {
                thousandsSep: '{$CURRENT_USER_MODEL->get('currency_grouping_separator')}',
                printChart: '{vtranslate('LBL_HIGHCHARTS_PRINT_CHART', 'Vtiger')}',
                viewFullscreen: '{vtranslate('LBL_HIGHCHARTS_VIEW_FULL_SCREEN', 'Vtiger')}',
                exitFullscreen: '{vtranslate('LBL_HIGHCHARTS_EXIT_FULL_SCREEN', 'Vtiger')}',
                downloadJPEG: '{vtranslate('LBL_HIGHCHARTS_DOWNLOAD_JPEG_IMAGE', 'Vtiger')}',
                downloadPDF: '{vtranslate('LBL_HIGHCHARTS_DOWNLOAD_PDF_FILE', 'Vtiger')}',
                downloadPNG: '{vtranslate('LBL_HIGHCHARTS_DOWNLOAD_PNG_IMAGE', 'Vtiger')}',
                downloadSVG: '{vtranslate('LBL_HIGHCHARTS_DOWNLOAD_SVG_IMAGE', 'Vtiger')}',
                
            },
            colors: ['#008ecf', '#678be0', '#aa82df', '#e274cc', '#F654C9', '#ff6aa8', '#ff707a', '#ff8749', '#ffa600'],
        });
    </script>
    {* End Hieu Nguyen *}

    <script src="{vresource_url('resources/CustomOwnerField.js')}"></script> {* Moved by Phu Vo on 2019.09.19*}

    {* Added by Hieu Nguyen on 2018-08-30 *}
    {assign var='GOOGLE_CONFIG' value=getGlobalVariable('googleConfig') scope='global'}
    <script>var googleMapsAndPlacesApiKey = '{$GOOGLE_CONFIG.maps.maps_and_places_api_key}';</script>
    <script>var _SHOULD_INIT_ADDRESS_AUTO_COMPLETE = false;</script>

    {if !isForbiddenFeature('GoogleMapsIntegration') && !empty($GOOGLE_CONFIG.maps.maps_and_places_api_key)}
        <script src="https://maps.googleapis.com/maps/api/js?key={$GOOGLE_CONFIG.maps.maps_and_places_api_key}&libraries=places"></script>
        <script>_SHOULD_INIT_ADDRESS_AUTO_COMPLETE = true;</script>
    {/if}

    <script src="{vresource_url('resources/GoogleMaps.js')}"></script>
    {* End Hieu Nguyen *}

    {* Added by Hieu Nguyen on 2018-10-03 *}
    <script>
        var _CURRENT_USER_META;

        {if $CURRENT_USER_MODEL}
            {assign var='USER_IMAGE' value=$CURRENT_USER_MODEL->getImageDetails()}

            _CURRENT_USER_META = { 
                'id': '{$CURRENT_USER_MODEL->get('id')}',
                'username': '{$CURRENT_USER_MODEL->get('user_name')}',
                'name': '{getFullNameFromArray('Users', $CURRENT_USER_MODEL->getData())}',
                'avatar' : '{if $USER_IMAGE[0]}{$USER_IMAGE[0]['path']}_{$USER_IMAGE[0]['name']}{/if}',
                'ext_number' : '{$CURRENT_USER_MODEL->get('phone_crm_extension')}',
            };
        {/if}
    </script>

    <script src="{vresource_url('resources/NotificationHelper.js')}"></script>

    {assign var='CALL_CENTER_CONFIG' value=getGlobalVariable('callCenterConfig') scope='global'}
    {* End Hieu Nguyen *}

    {* Added by Hieu Nguyen on 2019-07-16 *}
    {assign var="FB_ENABLED" value=CPSocialIntegration_Config_Helper::isFbEnabled()}
    {assign var="ZALO_ENABLED" value=CPSocialIntegration_Config_Helper::isZaloEnabled()}

    {if $FB_ENABLED eq true || $ZALO_ENABLED eq true}
        <script src="{vresource_url('resources/SocialHandler.js')}" async defer></script>
    {/if}
    {* End Hieu Nguyen *}

    {* Added by Hieu Nguyen on 2018-10-26 *}
    {assign var='VOICE_COMMAND_CONFIG' value=getGlobalVariable('voiceCommandConfig')}
    {assign var='VOICE_COMMAND_PROXY_SERVER_PROTOCOL' value="{if $VOICE_COMMAND_CONFIG.proxy_server_ssl}https{else}http{/if}"}
    {assign var='VOICE_COMMAND_PROXY_SERVER_URL' value="{$VOICE_COMMAND_PROXY_SERVER_PROTOCOL}://{$VOICE_COMMAND_CONFIG.proxy_server_name}:{$VOICE_COMMAND_CONFIG.proxy_server_port}"}

    {if $VOICE_COMMAND_CONFIG.enable eq true}
        <script>var _VOICE_COMMAND_PROXY_SERVER_URL = '{$VOICE_COMMAND_PROXY_SERVER_URL}';</script>

        <script src="{vresource_url('resources/libraries/SocketIO/socket.io.js')}"></script>
        <script src="{vresource_url('resources/VoiceCommand.js')}" async defer></script>
    {/if}
    {* End Hieu Nguyen *}

    {* Added by Hieu Nguyen on 2019-12-31 to load Google Chart *}
    <script type="text/javascript" src="{vresource_url("resources/libraries/GoogleChart/loader.js")}"></script>
    {* End Hieu Nguyen *}

    {* [ModuleGuide] Added by Hieu Nguyen on 2021-01-18 *}
    <script src="{vresource_url('modules/Vtiger/resources/ModuleGuidePopup.js')}" async defer></script>
    {* End Hieu Nguyen *}

    {* [Mention] Added by Hieu Nguyen on 2021-01-18 *}
    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/Tribute/tribute.css')}" />
    <script src="{vresource_url('resources/libraries/Tribute/tribute.min.js')}"></script>
    <script src="{vresource_url('resources/MentionHandler.js')}"></script>
    {* End Hieu Nguyen *}

	{* Added by Vu Mai on 2022-08-06 to get new post count from website api *}
	<script src="{vresource_url('resources/OnlineCRMBlogPosts.js')}"></script>
	{* End Vu Mai *}

    {* [CustomTag] Added by Vu Mai on 2022-09-07 *}
    <script src="{vresource_url('modules/Vtiger/resources/CustomTag.js')}"></script>
	{* End Vu Mai *}

    {* [CustomComment] Added by Vu Mai on 2022-09-12 *}
	<script src="{vresource_url('modules/Vtiger/resources/CustomComment.js')}"></script>
	{* End Vu Mai *}
    
    {* [QuickEdit] Added by Vu Mai on 2022-09-27 *}
	<script src="{vresource_url('modules/Vtiger/resources/QuickEdit.js')}"></script>
	{* End Vu Mai *}

	{* Added by Vu Mai on 2022-10-14 to expose inventory module array into JS *}
	{assign var='INVENTORY_MODULES' value=getGlobalVariable('inventoryModules') scope='global'}

	<script>
		var _INVENTORY_MODULES = {json_encode($INVENTORY_MODULES)};
	</script>
	{* End Vu Mai *}

    {* [SurveyPopup] Added by Hieu Nguyen on 2022-11-14 *}
    <link rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/SurveyPopup.css')}"></link>
	<script src="{vresource_url('modules/Vtiger/resources/SurveyPopup.js')}"></script>
    {* End Hieu Nguyen *}

    {* [jQueryPrintLibrary] Added by Vu Mai on 2022-11-30 *}
    <script src="{vresource_url('resources/libraries/jQuery/jQuery.print.min.js')}"></script>
    {* End Vu Mai *}

    {* Added by Vu Mai on 2023-02-13 to expose business managers config array into JS *}
	{assign var='BUSINESS_MANAGERS_CONFIG' value=getGlobalVariable('businessManagersConfig') scope='global'}

	<script>
		var _BUSINESS_MANAGERS_CONFIG = {json_encode($BUSINESS_MANAGERS_CONFIG)};
	</script>
	{* End Vu Mai *}
{/strip}