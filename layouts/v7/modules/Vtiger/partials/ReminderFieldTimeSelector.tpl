{* Added by Hieu Nguyen on 2020-02-20 to seperate this template for common usage *}

{strip}
    <div style="float:left">
        <div style="float:left">
            <select class="select2" name="{$KEY}remdays">
                {for $DAYS = 0 to 31}
                    <option value="{$DAYS}" {if $DAYS eq $DAY}selected{/if}>{$DAYS}</option>
                {/for}
            </select>
        </div>
        <div style="float:left; margin-top:5px">
            &nbsp;{vtranslate('LBL_DAYS', $MODULE)}&nbsp;&nbsp;
        </div>
        <div class="clearfix"></div>
    </div>
    <div style="float:left">
        <div style="float:left">
            <select class="select2" name="{$KEY}remhrs">
                {for $HOURS = 0 to 23}
                    <option value="{$HOURS}" {if $HOURS eq $HOUR}selected{/if}>{$HOURS}</option>
                {/for}
            </select>
        </div>
        <div style="float:left; margin-top:5px">
            &nbsp;{vtranslate('LBL_HOURS', $MODULE)}&nbsp;&nbsp;
        </div>
        <div class="clearfix"></div>
    </div>
    <div style="float:left">
        <div style="float:left">
            <select class="select2" name="{$KEY}remmin">
            {for $MINUTES = 0 to 59}
                <option value="{$MINUTES}" {if $MINUTES eq $MINUTE}selected{/if}>{$MINUTES}</option>
            {/for}
            </select>
        </div>
        <div style="float:left; margin-top:5px">
            &nbsp;{vtranslate('LBL_MINUTES', $MODULE)}&nbsp;&nbsp;
        </div>
        <div class="clearfix"></div>
    </div>
{/strip}