{*
    File RecordingPopup.tpl
    Author: Hieu Nguyen
    Date: 2019-12-30
    Purpose: to render recording popup
*}

{strip}
    <div>
        <div class="form-group">
            <label class="fieldLabel col-sm-3">
                <span>{vtranslate('LBL_RECORDING_POPUP_PLAY_RECORDING', 'PBXManager')}</span>
            </label>
            <div class="controls fieldValue col-sm-9">
                <audio controls="controls">
                    <source src="index.php?module=PBXManager&action=GetRecording&record={$PBX_CALL_MODEL->getId()}" type="audio/mp3">
                </audio>
            </div>
        </div>

        <div class="form-group">
            <label class="fieldLabel col-sm-3">
                <span>{vtranslate('LBL_RECORDING_POPUP_CALL_CONTENT', 'PBXManager')}</span>
            </label>
            <div class="controls fieldValue col-sm-9">
                {$CALL_LOG_MODEL->get('description')}
            </div>
        </div>
    </div>
{/strip}