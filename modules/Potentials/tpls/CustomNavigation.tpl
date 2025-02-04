
{*
    Navigation.tpl
    Author: Phuc Lu
    Date: 2020.03.18
*}

{strip}
    <div class="navigation-progress-bar-wrapper">
        <div class="status-bar" style="width: 100%;">
            <div class="current-status" style="transition: width 3500ms linear 0s;"></div>
        </div>

        <ul class="progress-bar">
            {assign var="WIDTH" value="{(100/(count($NAVIGATION_DATA.picklist) + 1))}"}

            {if $RECORD->get('potentialresult') == 'Closed Lost'}
            {assign var="CLASS_RESULT" value="closed-lost"}
            {else}            
            {assign var="CLASS_RESULT" value=""}
            {/if}

            {foreach key=CRUMBID item=STEP_TEXT from=$NAVIGATION_DATA.picklist name=breadcrumbLabels}
                {if $CURRENT || !in_array($STEP_TEXT, $NAVIGATION_DATA.visited) }
                    {assign var="VISITED" value=""}
                {else}
                    {assign var="VISITED" value="visited"}
                {/if}
                

                {if $RECORD->get($NAVIGATION_DATA.field_name) == $STEP_TEXT && empty($RECORD->get('potentialresult'))}
                    {assign var="CURRENT" value="current"}
                {else}
                    {assign var="CURRENT" value=""}
                {/if}

                <li class="section {$VISITED} {$CURRENT} {$CLASS_RESULT}" title="{vtranslate($STEP_TEXT,$MODULE)}" {* style="width: calc(100%/{$NAVIGATION_DATA_COUNT});"*} style="width: {$WIDTH}%;" id="{str_replace(' ','_',str_replace('.','',$STEP_TEXT))}" >
                    {vtranslate($STEP_TEXT,$MODULE)}
                </li>

            {/foreach}

            {if $RECORD->get('potentialresult') == 'Closed Won'}
                <li class=" section visited current closed-won" title="{vtranslate('Closed Won', $MODULE)}" style="width: {$WIDTH}%;" id="Closed_Won" >
                    {vtranslate('Closed Won', $MODULE)}
                    {assign var="STATUS_COLOR" value="mediumseagreen"}
                </li>
            {else} {if $RECORD->get('potentialresult') == 'Closed Lost'}
                <li class=" section visited current closed-lost" title="{vtranslate('Closed Lost', $MODULE)}" style="width: {$WIDTH}%;" id="Closed_Lost" >
                    {vtranslate('Closed Lost', $MODULE)}
                    {assign var="STATUS_COLOR" value="#ff4b4b"}
                </li>
            {else}
                <li class="section closed-won" title="{vtranslate('Closed Won', $MODULE)}" style="width: {$WIDTH}%;" id="Closed_Won" >
                    {vtranslate('Closed Won', $MODULE)}
                    {assign var="STATUS_COLOR" value="mediumseagreen"}
                </li>
            {/if}
            {/if}
            
        </ul>
    </div>
{/strip}

{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            var currentStatus = $('.section.visited:last');
            var currentWidth = (100 * $('.section').index(currentStatus)) / ($('.section').length-1) + '%';

            $('.current-status').css('background', '{/literal}{$STATUS_COLOR}{literal}');
            $('.current-status').css('width', currentWidth);

            var statusBar = 100 - (100 / $('.section').length)+ '%';
            $('.status-bar').css('width', statusBar);
        });
    </script>    
{/literal}