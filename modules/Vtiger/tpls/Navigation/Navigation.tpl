{*--Added By Kelvin Thang on 2019-11-11 -- Template default Navigation Sidebar*}
{strip}
    <div class="navigation-progress-bar-wrapper">
        <div class="status-bar" style="width: 100%;">
            <div class="current-status" style="transition: width 3500ms linear 0s;"></div>
        </div>

        <ul class="progress-bar">
            {assign var="VISITED" value="visited"}
            {assign var="WIDTH" value="{(100/count($NAVIGATION_DATA.picklist))}"}

            {foreach key=CRUMBID item=STEP_TEXT from=$NAVIGATION_DATA.picklist name=breadcrumbLabels}
                {if $CURRENT || !in_array($RECORD->get($NAVIGATION_DATA.field_name), $NAVIGATION_DATA.picklist) }
                    {assign var="VISITED" value=""}
                {/if}

                {if $RECORD->get($NAVIGATION_DATA.field_name) == $STEP_TEXT}
                    {assign var="CURRENT" value="current"}
                {else}
                    {assign var="CURRENT" value=""}
                {/if}

                <li class=" section {$VISITED} {$CURRENT}" title="{vtranslate($STEP_TEXT,$MODULE)}" {* style="width: calc(100%/{$NAVIGATION_DATA_COUNT});"*} style="width: {$WIDTH}%;" id="{str_replace(' ','_',str_replace('.','',$STEP_TEXT))}" >
                    {vtranslate($STEP_TEXT,$MODULE)}
                </li>

            {/foreach}
        </ul>
    </div>
{/strip}

<script type="text/javascript">
    $(document).ready(function () {
        var currentStatus = (100 * ($('.visited ').length-1) )/($('.section').length-1)+'%';
        $('.current-status').css('width', currentStatus);

        var statusBar = 100 - (100 / $('.section').length)+ '%';
        $('.status-bar').css('width', statusBar);
    });
</script>