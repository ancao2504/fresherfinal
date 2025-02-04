{*
    BaseListCustomDashboardContents
    Author: Phu Vo
    Date: 2020.08.24
*}

{strip}
    <table class="table widgetTable dataTable customWidgetTable table-highlighted {$WIDGET_NAME}" style="width: 100% !important">
        <thead>
            {foreach from=$WIDGET_META['widget_headers'] item=COLUMN_DATA}
                <th class="widget-header {$COLUMN_DATA['name']} {if !empty($COLUMN_DATA['type'])}{$COLUMN_DATA['type']}{/if}"><strong>{$COLUMN_DATA['label']}</strong></th>
            {/foreach}
        </thead>
        <tbody></tbody>
    </table>

    {if $CONTENT == false}
        <script type="text/javascript">
            jQuery(function($) {
                window.BaseListCustomDashboard.init($('.customWidget.{$WIDGET_NAME}'));
            });
        </script>
    {/if}
{/strip}
