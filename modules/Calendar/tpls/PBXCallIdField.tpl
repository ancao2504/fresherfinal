{strip}
    {if !$PBX_LOG_DATA || $RECORD->get("activitytype") != "Call"}
        <style>
            .fieldLabel.pbx_call_id, .fieldValue.pbx_call_id {
                display: none;
            }
        </style>
    {else}
        {if $PBX_LOG_DATA.callstatus eq "completed"}
            <audio id="PBXAudio" controls="controls"><source src="index.php?module=PBXManager&action=GetRecording&record={$PBX_LOG_DATA.pbxmanagerid}" type="audio/mp3"></audio>
            <a id="PBXLink" target="_blank" href="index.php?module=PBXManager&view=Detail&mode=showDetailViewByMode&requestMode=full&record={$PBX_LOG_DATA.pbxmanagerid}"><i class="far fa-external-link" aria-hidden="true"></i></a>
        {else}
            <span class="value">
                <a id="PBXLink" target="_blank" href="index.php?module=PBXManager&view=Detail&mode=showDetailViewByMode&requestMode=full&record={$PBX_LOG_DATA.pbxmanagerid}">{$PBX_LOG_DATA.customernumber}</a>
            </span>
        {/if}
    {/if}
{/strip}