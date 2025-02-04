{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    {* Modified by Hieu Nguyen on 2022-01-24 to fix report summary layout *}
    {if !empty($CALCULATION_FIELDS)}
        <div id="reportSummary">
            
            <table class="table-bordered table-condensed marginBottom10px" width="100%">
                <thead>
                    <tr class="blockHeader">
                        <th>{vtranslate('LBL_FIELD_NAMES', $MODULE)}</th>
                        <th>{vtranslate('LBL_SUM', $MODULE)}</th>
                        <th>{vtranslate('LBL_AVG', $MODULE)}</th>
                        <th>{vtranslate('LBL_MIN', $MODULE)}</th>
                        <th>{vtranslate('LBL_MAX', $MODULE)}</th>
                    </tr>
                </thead>
                {assign var=ESCAPE_CHAR value=array('_SUM','_AVG','_MIN','_MAX')}

                {foreach from=$CALCULATION_FIELDS item=CALCULATION_FIELD key=index}
                    <tr>
                        {assign var=CALCULATION_FIELD_KEYS value=array_keys($CALCULATION_FIELD)}
                        {assign var=CALCULATION_FIELD_KEYS value=$CALCULATION_FIELD_KEYS|replace:$ESCAPE_CHAR:''}
                        {assign var=FIELD_IMPLODE value=explode('_',$CALCULATION_FIELD_KEYS['0'])}
                        {assign var=MODULE_NAME value=$FIELD_IMPLODE['0']}
                        
                        {* Modified by Vu Mai on 2022-12-27 to fix bug label display error on report *}
                        {assign var=FIELD_LABEL value=" "|implode:array_slice($FIELD_IMPLODE, 1)}
                        {assign var=FIELD_LABEL value=str_replace("'", '', $FIELD_LABEL)}
                        {assign var=TRANSLATED_FIELD_LABEL value=vtranslate(trim($FIELD_LABEL), $MODULE_NAME)}
                        
                        {if strcmp($FIELD_LABEL, $TRANSLATED_FIELD_LABEL) == 0}
                            {assign var=FIELD_LABEL value=str_replace(' ', '_', $FIELD_LABEL)}
                            {assign var=TRANSLATED_FIELD_LABEL value=vtranslate(trim($FIELD_LABEL), $MODULE_NAME)}
                        {/if}

                        <td>{vtranslate($MODULE_NAME,$MODULE_NAME)} {$TRANSLATED_FIELD_LABEL}</td>
                        {* End Vu Mai *}

                        {foreach from=$CALCULATION_FIELD item=CALCULATION_VALUE}
                            <td width="15%">{$CALCULATION_VALUE}</td>
                        {/foreach}
                    </tr>
                {/foreach}
            </table>
            
            {if $REPORT_MODEL->isInventoryModuleSelected()}
                <div class="alert alert-info">
                    {assign var=BASE_CURRENCY_INFO value=Vtiger_Util_Helper::getUserCurrencyInfo()}
                    <i class="far fa-info-circle"></i>&nbsp;&nbsp;
                    {vtranslate('LBL_CALCULATION_CONVERSION_MESSAGE', $MODULE)} - {$BASE_CURRENCY_INFO['currency_name']} ({$BASE_CURRENCY_INFO['currency_code']})
                </div>
            {/if}
        </div>
    {/if}
    {* End Hieu Nguyen *}

    {* Modified by Hieu Nguyen on 2022-01-24 to fix report detail layout *}
    <div id="reportDetails">
        {* Added by Hieu Nguyen on 2021-07-15 to support Add To / Remove From Target List *}
        {if in_array($PRIMARY_MODULE, ['CPTarget', 'Leads', 'Contacts']) && $DATA}
            {include file="modules/Reports/tpls/TargetListHelperActions.tpl"}
        {/if}
        {* End Hieu Nguyen *}

        <div class="fancy-table-wrapper">
            <input type="hidden" id="updatedCount" value="{$NEW_COUNT}" />

            {if $DATA neq ''}
                {assign var=HEADERS value=$DATA[0]}

                <table class="table table-bordered fancy-table">
                    <thead>
                        <tr class="blockHeader">
                            {foreach from=$HEADERS item=HEADER key=NAME}
                                <th nowrap>{$NAME}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {assign var=REPORTRUN value=$REPORT_RUN_INSTANCE}
                        {assign var=GROUPBYFIELDS value=array_keys($REPORTRUN->getGroupingList($RECORD_ID))}
                        {assign var=GROUPBYFIELDSCOUNT value=count($GROUPBYFIELDS)}

                        {if $GROUPBYFIELDSCOUNT > 0}
                            {assign var=FIELDNAMES value=array()}

                            {for $i=0 to $GROUPBYFIELDSCOUNT-1}
                                {assign var=FIELD value=explode(':',$GROUPBYFIELDS[$i])}
                                {assign var=FIELD_EXPLODE value=explode('_',$FIELD[2])}
                                {for $j=1 to count($FIELD_EXPLODE)-1}
                                    {$FIELDNAMES.$i = $FIELDNAMES.$i|cat:$FIELD_EXPLODE[$j]|cat:" "}
                                {/for}
                            {/for}

                            {if $GROUPBYFIELDSCOUNT eq 1}
                                {assign var=FIRST_FIELD value=vtranslate(trim($FIELDNAMES[0]), $MODULE)}
                            {else if $GROUPBYFIELDSCOUNT eq 2}    
                                {assign var=FIRST_FIELD value=vtranslate(trim($FIELDNAMES[0]),$MODULE)}
                                {assign var=SECOND_FIELD value=vtranslate(trim($FIELDNAMES[1]),$MODULE)}
                            {else if $GROUPBYFIELDSCOUNT eq 3}    
                                {assign var=FIRST_FIELD value=vtranslate(trim($FIELDNAMES[0]),$MODULE)}
                                {assign var=SECOND_FIELD value=vtranslate(trim($FIELDNAMES[1]),$MODULE)}
                                {assign var=THIRD_FIELD value=vtranslate(trim($FIELDNAMES[2]),$MODULE)}
                            {/if}    

                            {assign var=FIRST_VALUE value=" "}
                            {assign var=SECOND_VALUE value=" "}
                            {assign var=THIRD_VALUE value=" "}

                            {foreach from=$DATA item=VALUES}
                                <tr>
                                    {foreach from=$VALUES item=ROW key=NAME}    {* Modified by Hieu Nguyen on 2022-01-20 to render report according to new field data format *}
                                        {assign var=VALUE value=$ROW.value}     {* Modified by Hieu Nguyen on 2022-01-20 to render report according to new field data format *}

                                        {if ($NAME eq $FIRST_FIELD || $NAME|strstr:{$FIRST_FIELD}) && ($FIRST_VALUE eq $VALUE || $FIRST_VALUE eq " ")}
                                            {if $FIRST_VALUE eq " " || $VALUE eq "-"}
                                                <td class="summary">{$VALUE}</td>
                                            {else}    
                                                <td class="summary">{" "}</td>
                                            {/if}   
                                            {if $VALUE neq " " }
                                                {$FIRST_VALUE = $VALUE}
                                            {/if}   
                                        {else if ( $NAME eq $SECOND_FIELD || $NAME|strstr:$SECOND_FIELD) && ($SECOND_VALUE eq $VALUE || $SECOND_VALUE eq " ")}
                                            {if $SECOND_VALUE eq " " || $VALUE eq "-"}
                                                <td class="summary">{$VALUE}</td>
                                            {else}    
                                                <td class="summary">{" "}</td>
                                            {/if}   
                                            {if $VALUE neq " " }
                                                {$SECOND_VALUE = $VALUE}
                                            {/if}   
                                        {else if ($NAME eq $THIRD_FIELD || $NAME|strstr:$THIRD_FIELD) && ($THIRD_VALUE eq $VALUE || $THIRD_VALUE eq " ")}
                                            {if $THIRD_VALUE eq " " || $VALUE eq "-"}
                                                <td class="summary">{$VALUE}</td>
                                            {else}    
                                                <td class="summary">{" "}</td>
                                            {/if}   
                                            {if $VALUE neq " " }
                                                {$THIRD_VALUE = $VALUE}
                                            {/if}
                                        {else}
                                            <td class="value" data-field-type="{$ROW.type}">{$VALUE}</td>   {* Modified by Hieu Nguyen on 2022-01-20 expose field data type for CSS *}
                                            {if $NAME eq $FIRST_FIELD || $NAME|strstr:$FIRST_FIELD}
                                                {$FIRST_VALUE = $VALUE}
                                            {else if $NAME eq $SECOND_FIELD || $NAME|strstr:$SECOND_FIELD}
                                                {$SECOND_VALUE = $VALUE}
                                            {else if $NAME eq $THIRD_FIELD || $NAME|strstr:$THIRD_FIELD}
                                                {$THIRD_VALUE = $VALUE}
                                            {/if}    
                                        {/if}   
                                    {/foreach}
                                </tr>
                            {/foreach}
                        {else}    
                            {foreach from=$DATA item=VALUES}
                                {* Modified by Hieu Nguyen on 2021-12-22 to render report according to new field data format *}
                                <tr>
                                    {foreach from=$VALUES item=ROW key=NAME}
                                        <td class="value" data-field-type="{$ROW.type}">{$ROW.value}</td>
                                    {/foreach}
                                </tr>
                                {* End Hieu Nguyen *}
                            {/foreach}
                        {/if}
                    </tbody>
                </table>

                {if $LIMIT_EXCEEDED}
                    <center>{vtranslate('LBL_LIMIT_EXCEEDED',$MODULE)} <span class="pull-right"><a href="#top" >{vtranslate('LBL_TOP',$MODULE)}</a></span></center>
                {/if}
            {else}
                <div style="text-align: center; border: 1px solid #DDD; padding: 20px; font-size: 15px;">{vtranslate('LBL_NO_DATA_AVAILABLE',$MODULE)}</div>
            {/if}
        </div>
    </div>
    {* End Hieu Nguyen *}
{/strip}

