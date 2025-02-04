<form name="settings">
    <div class="editViewBody">
        <div class="editViewContents">
            <div class="fieldBlockContainer">
                <h4 class="fieldBlockHeader">{vtranslate('LBL_CONFIG_SYSTEM_NOTIFICAITON', $MODULE_NAME)}</h4>
                <hr />
                <table class="configDetails" style="width: 100%">
                    <tbody>
                        <tr>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_CONFIG_DUEDAY_COMING', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop">
                                <select class="inputElement select2" name="days_to_remind_coming_activities" value="{$CONFIG->days_to_remind_coming_activities}">
                                    <option></option>
                                    <option value="5" {if $CONFIG->days_to_remind_coming_activities eq '5'}selected{/if}>5 {vtranslate('LBL_REMIND_COMING_DAYS', $MODULE_NAME)}</option>
                                    <option value="10" {if $CONFIG->days_to_remind_coming_activities eq '10'}selected{/if}>10 {vtranslate('LBL_REMIND_COMING_DAYS', $MODULE_NAME)}</option>
                                    <option value="15" {if $CONFIG->days_to_remind_coming_activities eq '15'}selected{/if}>15 {vtranslate('LBL_REMIND_COMING_DAYS', $MODULE_NAME)}</option>
                                    <option value="30" {if $CONFIG->days_to_remind_coming_activities eq '30'}selected{/if}>30 {vtranslate('LBL_REMIND_COMING_DAYS', $MODULE_NAME)}</option>
                                    <option value="45" {if $CONFIG->days_to_remind_coming_activities eq '45'}selected{/if}>45 {vtranslate('LBL_REMIND_COMING_DAYS', $MODULE_NAME)}</option>
                                    <option value="60" {if $CONFIG->days_to_remind_coming_activities eq '60'}selected{/if}>45 {vtranslate('LBL_REMIND_COMING_DAYS', $MODULE_NAME)}</option>
                                </select>
                            </td>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_REFRESH_TASK_NOTIFY_INTERVAL', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop">
                                <select class="inputElement select2" name="minutes_to_notify_activitites_before_start_time" value="{$CONFIG->minutes_to_notify_activitites_before_start_time}">
                                    <option></option>
                                    <option value="5" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '5'}selected{/if}>5 {vtranslate('LBL_REMIND_COMING_MINUTES', $MODULE_NAME)}</option>
                                    <option value="10" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '10'}selected{/if}>10 {vtranslate('LBL_REMIND_COMING_MINUTES', $MODULE_NAME)}</option>
                                    <option value="15" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '15'}selected{/if}>15 {vtranslate('LBL_REMIND_COMING_MINUTES', $MODULE_NAME)}</option>
                                    <option value="30" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '30'}selected{/if}>30 {vtranslate('LBL_REMIND_COMING_MINUTES', $MODULE_NAME)}</option>
                                    <option value="45" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '45'}selected{/if}>45 {vtranslate('LBL_REMIND_COMING_MINUTES', $MODULE_NAME)}</option>
                                    <option value="60" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '60'}selected{/if}>1 {vtranslate('LBL_REMIND_COMING_HOUR', $MODULE_NAME)}</option>
                                    <option value="120" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '120'}selected{/if}>2 {vtranslate('LBL_REMIND_COMING_HOURS', $MODULE_NAME)}</option>
                                    <option value="1440" {if $CONFIG->minutes_to_notify_activitites_before_start_time eq '1440'}selected{/if}>1 {vtranslate('LBL_REMIND_COMING_DAY', $MODULE_NAME)}</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-overlay-footer clearfix">
        <div class="row clear-fix">
            <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
                <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
            </div>
        </div> 
    </div>
</form>