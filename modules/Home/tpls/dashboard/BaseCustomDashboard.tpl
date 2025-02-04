{*
    customerUnfollowInPeriodWidget
    Author: Phu Vo
    Date: 2020.08.24
*}
{strip}
    {* {foreach key=index item=cssModel from=$STYLES}
        <link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
    {/foreach} *}

    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}

    <script type="text/javascript">
        if (typeof window._CUSTOM_WIDGET_META == 'undefined') {
            var _CUSTOM_WIDGET_META = {};
        }

        _CUSTOM_WIDGET_META.{$WIDGET_NAME} = {ZEND_JSON::encode($WIDGET_META)};

        Vtiger_Custom_Widget_Js('{$WIDGET_JS_MODEL_NAME}', {}, {});
    </script>

    <div class="dashboardWidgetHeader">
        {include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
    <div class="dashboardWidgetContent customWidget {$WIDGET_NAME}" data-widget-name="{$WIDGET_NAME}" data-widget-meta='{ZEND_JSON::encode($WIDGET_META)}'>
        {if !empty($CONTENT_TPL)}{include file="$CONTENT_TPL"}{/if}
    </div>
    <div class="widgeticons dashBoardWidgetFooter">
        {if !empty($FILTER_TPL)}
            {assign var=SETTING_EXIST value=true}
            {include file="$FILTER_TPL"}
        {else}
            {assign var=SETTING_EXIST value=false}
        {/if}
        <div class="footerIcons pull-right">
            {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=$SETTING_EXIST}
        </div>
    </div>

    <script type="text/javascript">
        jQuery(function($) {
            if (typeof window['{$WIDGET_NAME}'] != 'undefined' && typeof window['{$WIDGET_NAME}']['init'] == 'function') window['{$WIDGET_NAME}']['init']($('.customWidget.{$WIDGET_NAME}'));
        });
    </script>
{/strip}