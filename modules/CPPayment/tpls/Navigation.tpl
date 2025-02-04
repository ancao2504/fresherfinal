
{*
    Navigation.tpl
    Author: Phuc Lu
    Date: 2019.10.01
*}

{literal}
    <style>
        .navigation-progress-bar-wrapper {
            min-width: 320px;
            max-width: 1024px;
            margin: 15px auto 85px auto;
        }

        .navigation-progress-bar-wrapper ul.progress-bar {
            width: 100%;
            margin: 0;
            padding: 0;
            font-size: 0;
            list-style: none;
            background-color: #FFF;
        }

        .navigation-progress-bar-wrapper li.section {
            display: inline-block;
            padding-top: 45px;
            font-size: 13px;
            font-weight: bold;
            line-height: 16px;
            color: #5a5a5a;
            vertical-align: top;
            position: relative;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .navigation-progress-bar-wrapper li.section:before {
            content: '';
            position: absolute;
            top: 2px;
            left: calc(50% - 15px);
            z-index: 1;
            width: 30px;
            height: 30px;
            color: white;
            border: 2px solid white;
            border-radius: 17px;
            line-height: 30px;
            background: gray;
        }
        .navigation-progress-bar-wrapper .status-bar {
            height: 3px;
            background: gray;
            position: relative;
            top: 21px;
            margin: 0 auto;
        }
        .navigation-progress-bar-wrapper .current-status {
            height: 3px;
            width: 0;
            border-radius: 1px;
            background: mediumseagreen;
        }

        .navigation-progress-bar-wrapper li.section.visited:before {
            content: '\2714';
            /*animation: mediumseagreen .5s linear;*/
            animation-fill-mode: forwards;
            background: mediumseagreen;
        }

        .navigation-progress-bar-wrapper li.section.visited.current:before {
            box-shadow: 0 0 0 2px mediumseagreen;
        }

        .navigate-status-approved{
            color: mediumseagreen;
        }
        
        .navigate-status-rejected{
            color: red;
        }
    </style>
{/literal}

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

                    <li class=" section {$VISITED} {$CURRENT}"
                        title="{vtranslate($STEP_TEXT,$MODULE)}"
                    {* style="width: calc(100%/{$NAVIGATION_DATA_COUNT});"*}
                        style="width: {$WIDTH}%;"
                        id="{str_replace(' ','_',str_replace('.','',$STEP_TEXT))}" >
                        
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

<script type="text/javascript">
    $(document).ready(function () {
        var statusBar = (100 * ($('.visited ').length-1) )/($('.section').length-1)+'%';
        $('.current-status').css('width', statusBar);
    });
</script>