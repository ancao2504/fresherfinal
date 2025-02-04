<form autocomplete="off" name="settings">
    <div class="editViewBody">
        <div class="editViewContents">
            <div class="fieldBlockContainer">
                <h4 class="fieldBlockHeader">{vtranslate('LBL_CONFIG_NOTIFICAITON', $MODULE_NAME)}</h4>
                <hr />
                <table class="configDetails" style="width: 100%">
                    <tbody>
                        <tr>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_RECEIVE_NOTIFICATION', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop"><input type="checkbox" class="inputElement" name="receive_notifications" {if $CONFIG->receive_notifications eq 1}checked{/if}/></td>
                            <td class="alignTop"></td>
                            <td class="fieldValue alignTop"></td>
                        </tr>
                        <tr class="showOnAccept" {if $CONFIG->receive_notifications neq 1}style="display: none"{/if}>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_METHOD', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop">
                                <select multiple name="receive_notifications_method" class="inputElement select2">
                                    <option value="popup" {if 'popup'|in_array:$CONFIG->receive_notifications_method}selected{/if}>{vtranslate('LBL_NOTIFY_BY_POPUP', $MODULE_NAME)}</option>
                                    <option value="app" {if 'app'|in_array:$CONFIG->receive_notifications_method}selected{/if}>{vtranslate('LBL_NOTIFY_BY_APP', $MODULE_NAME)}</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="showOnAccept" {if $CONFIG->receive_notifications neq 1}style="display: none"{/if}>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_ASSIGN_TASK', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop"><input type="checkbox" class="inputElement" name="receive_assignment_notifications" {if $CONFIG->receive_assignment_notifications eq 1}checked{/if}/></td>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_UPDATE_PROFILE', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop"><input type="checkbox" class="inputElement" name="receive_record_update_notifications" {if $CONFIG->receive_record_update_notifications eq 1}checked{/if}/></td>
                        </tr>
                        <tr class="showOnAccept" {if $CONFIG->receive_notifications neq 1}style="display: none"{/if}>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_UPDATE_FOLLOWING_RECORD', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop"><input type="checkbox" class="inputElement" name="receive_following_record_update_notifications" {if $CONFIG->receive_following_record_update_notifications eq 1}checked{/if}/></td>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_OVERDUE_TASK', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop"><input type="checkbox" class="inputElement" name="show_activity_reminders" {if $CONFIG->show_activity_reminders eq 1}checked{/if}/></td>
                        </tr>
                        <tr class="showOnAccept" {if $CONFIG->receive_notifications neq 1}style="display: none"{/if}>
                            <td class="fieldLabel alignTop">{vtranslate('LBL_NOTIFY_CUSTOMER_BIRTHDAY', $MODULE_NAME)}:</td>
                            <td class="fieldValue alignTop"><input type="checkbox" class="inputElement" name="show_customer_birthday_reminders" {if $CONFIG->show_customer_birthday_reminders eq 1}checked{/if}/></td>
                            <td class="fieldLabel alignTop"></td>
                            <td class="fieldValue alignTop"></td>
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