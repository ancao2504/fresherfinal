
{*
    ProgressBar.tpl
    Author: Phuc Lu
    Date: 2020.03.18
    Refactored by Hieu Nguyen on 2021-08-03
    Modified by Phu Vo on 2021.08.04 to create sizing container
*}

{strip}
    {assign var="PROGRESS_DATA" value=Potentials_Data_Model::getDataForProgressBar($RECORD)}
    {assign var="STATUS_COLOR" value="mediumseagreen"}
    {assign var="WIDTH" value=110}

    <div class="progress-bar-container">
        <div class="progress-bar-wrapper">
            <div class="progress-line" style="width: {$WIDTH * count($PROGRESS_DATA.all_nodes)}px">
                <div class="current-progress"></div>
            </div>

            <ul class="progress-bar">

                {if $RECORD->get('potentialresult') == 'Closed Lost'}
                    {assign var="CLASS_RESULT" value="closed-lost"}
                {else}            
                    {assign var="CLASS_RESULT" value=""}
                {/if}

                {* Intermediate Nodes *}
                {foreach key=NODE_KEY item=NODE_VALUE from=$PROGRESS_DATA.all_nodes}
                    {assign var="VISITED_NODE_INFO" value=$PROGRESS_DATA.visited_nodes[$NODE_VALUE]}

                    {if $CURRENT || $VISITED_NODE_INFO == null}
                        {assign var="VISITED" value=""}
                        {assign var="TOOLTIP" value=vtranslate($NODE_VALUE, 'Potentials')}
                    {else}
                        {assign var="VISITED" value="visited"}
                        {assign var="TOOLTIP" value=$VISITED_NODE_INFO.tooltip}
                    {/if}
                    
                    {if $RECORD->get($PROGRESS_DATA.field_name) == $NODE_VALUE && empty($RECORD->get('potentialresult'))}
                        {assign var="CURRENT" value="current"}
                    {else}
                        {assign var="CURRENT" value=""}
                    {/if}

                    <li class="node {$VISITED} {$CURRENT} {$CLASS_RESULT}" data-toggle="tooltip" title="{$TOOLTIP}" style="width: {$WIDTH}px;" id="{str_replace(' ', '_', str_replace('.', '', $NODE_VALUE))}">
                        {vtranslate($NODE_VALUE, 'Potentials')}
                    </li>
                {/foreach}

                {* Final Node *}
                {if $RECORD->get('potentialresult') == 'Closed Won' || $RECORD->get('sales_stage') == 'Closed Won'}
                    {assign var="WON_NODE_INFO" value=$PROGRESS_DATA.visited_nodes['Closed Won']}

                    {if $WON_NODE_INFO == null}
                        {assign var="WON_NODE_TOOLTIP" value=$PROGRESS_DATA.won_result_tooltip}
                    {else}
                        {assign var="WON_NODE_TOOLTIP" value=$WON_NODE_INFO.tooltip}
                    {/if}

                    <li class="node visited current closed-won" data-toggle="tooltip" title="{$WON_NODE_TOOLTIP}" style="width: {$WIDTH}px;" id="Closed_Won">
                        {vtranslate('Closed Won', 'Potentials')}
                        
                    </li>
                {else if $RECORD->get('potentialresult') == 'Closed Lost' || $RECORD->get('sales_stage') == 'Closed Lost'}
                    {assign var="STATUS_COLOR" value="#ff4b4b"}
                    {assign var="LOST_NODE_INFO" value=$PROGRESS_DATA.visited_nodes['Closed Lost']}

                    {if $LOST_NODE_INFO == null}
                        {assign var="LOST_NODE_TOOLTIP" value=$PROGRESS_DATA.lost_result_tooltip}
                    {else}
                        {assign var="LOST_NODE_TOOLTIP" value=$LOST_NODE_INFO.tooltip}
                    {/if}

                    <li class="node visited current closed-lost" data-toggle="tooltip" title="{$LOST_NODE_TOOLTIP}" style="width: {$WIDTH}px;" id="Closed_Lost">
                        {vtranslate('Closed Lost', 'Potentials')}
                    </li>
                {else}
                    <li class="node closed-won" data-toggle="tooltip" title="{vtranslate('Closed Won', 'Potentials')}" style="width: {$WIDTH}px;" id="Closed_Won">
                        {vtranslate('Closed Won', 'Potentials')}
                    </li>
                {/if}
            </ul>
        </div>
    </div>
{/strip}

{literal}
    <script type="text/javascript">
        $(function () {
            var currentStatus = $('.node.visited:last');
            var currentWidth = (100 * $('.node').index(currentStatus)) / ($('.node').length - 1) + '%';

            $('.current-progress').css('background', '{/literal}{$STATUS_COLOR}{literal}');
            $('.current-progress').animate({ 'width': currentWidth }, 2000, 'linear');
        });
    </script>    
{/literal}