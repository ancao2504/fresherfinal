{*
    CallsSummaryWidgetFooter.tpl
    Author: Hieu Nguyen
    Date: 2019-10-01
*}

{strip}
    <div class="filterContainer">
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <strong>{vtranslate('LBL_WIDGET_DIRECTION_FILTER', $MODULE_NAME)}</strong>
            </label>
            <div class="controls fieldValue col-sm-7">
                <select name="direction" class="select2 form-control widgetFilter reloadOnChange">
                    <option value="{PBXManager_Widget_Model::$DEFAULT_DIRECTION}">{vtranslate('All', $MODULE_NAME)}</option>
                    <option value="inbound">{vtranslate('LBL_WIDGET_DIRECTION_FILTER_INBOUND', $MODULE_NAME)}</option>
                    <option value="outbound">{vtranslate('LBL_WIDGET_DIRECTION_FILTER_OUTBOUND', $MODULE_NAME)}</option>
                </select>
            </div>
        </div>
    </div>
    <div class="footerIcons pull-right">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
{/strip}