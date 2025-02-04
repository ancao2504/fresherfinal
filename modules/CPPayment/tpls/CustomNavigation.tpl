
{*
    Navigation.tpl
    Author: Phuc Lu
    Date: 2019.10.01
*}

{strip}
    {if $RECORD->get('cppayment_status') != 'cancelled'}
        <div class="navigation-progress-bar-wrapper">
            <div class="status-bar" style="width: 85%;">
                <div class="current-status" style="transition: width 3500ms linear 0s;"></div>
            </div>

            <ul class="progress-bar">
                {assign var="VISITED" value="visited"}
                {assign var="WIDTH" value="{(100/count($NAVIGATION_DATA.picklist))}"}

                {foreach key=CRUMBID item=STEP_TEXT from=$NAVIGATION_DATA.picklist name=breadcrumbLabels}
                    {if $CURRENT || !in_array($RECORD->get($NAVIGATION_DATA.field_name), $NAVIGATION_DATA.picklist) }
                        {assign var="VISITED" value=""}
                    {/if}
                                    
                    {assign var='ALTERNAL_LABEL' value=''}

                    {if $STEP_TEXT == 'leader_status' && $VISITED != ''}
                        {assign var='ALTERNAL_LABEL' value=vtranslate('LBL_LEADER', $MODULE)}
                        {assign var='STATUS' value=$RECORD->get('cppayment_leader_status')}
                        {assign var='STATUS_LABEL' value=vtranslate($RECORD->get('cppayment_leader_status'), $MODULE)|lower}
                        {assign var='ALTERNAL_LABEL' value="$ALTERNAL_LABEL <span class ='navigate-status-$STATUS'>$STATUS_LABEL<span>"}
                    {/if}

                    {if $STEP_TEXT == 'manager_status' && $VISITED != ''}
                        {assign var='ALTERNAL_LABEL' value=vtranslate('LBL_MANAGER', $MODULE)}
                        {assign var='STATUS' value=$RECORD->get('cppayment_manager_status')}
                        {assign var='STATUS_LABEL' value=vtranslate($RECORD->get('cppayment_manager_status'), $MODULE)|lower}
                        {assign var='ALTERNAL_LABEL' value="$ALTERNAL_LABEL <span class ='navigate-status-$STATUS'>$STATUS_LABEL<span>"}
                    {/if}

                    {if $RECORD->get($NAVIGATION_DATA.field_name) == $STEP_TEXT}
                        {assign var="CURRENT" value="current"}
                    {else}
                        {assign var="CURRENT" value=""}
                    {/if}

                    <li class=" section {$VISITED} {$CURRENT}" title="{vtranslate($STEP_TEXT,$MODULE)}" {* style="width: calc(100%/{$NAVIGATION_DATA_COUNT});"*} style="width: {$WIDTH}%;" id="{str_replace(' ', '_', str_replace('.', '', $STEP_TEXT))}" >
                        {if $ALTERNAL_LABEL != ''}
                            {$ALTERNAL_LABEL}
                        {else}
                            {vtranslate($STEP_TEXT,$MODULE)}
                        {/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
{/strip}