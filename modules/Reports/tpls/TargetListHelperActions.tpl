{* Added by Hieu Nguyen on 2021-07-16 *}

{strip}
    <div id="target-list-helper" class="text-right">
        <button type="button" id="btn-add-to-target-list" class="btn btn-primary" data-mode="addToTargetList">{vtranslate('LBL_TARGET_LIST_HELPER_ADD_TO_TARGET_LIST', 'Reports')}</button>&nbsp;
        <button type="button" id="btn-remove-from-target-list" class="btn btn-danger" data-mode="removeFromTargetList">{vtranslate('LBL_TARGET_LIST_HELPER_REMOVE_FROM_TARGET_LIST', 'Reports')}</button>
    </div>

    <div id="target-list-helper-modal" class="modal-dialog modal-content model-lg hide">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=''}
    
        <form id="target-list-helper-form" class="form-horizontal" method="POST">
            <input type="hidden" name="module" value="Reports" />
            <input type="hidden" name="action" value="TargetListHelperAjax" />
            <input type="hidden" name="mode" value="" />
            <input type="hidden" name="report_id" value="{$RECORD_ID}" />
            <input type="hidden" name="advanced_filter" value="" />

            <div class="padding10">
                <div class="hint"></div>
                <br/>
                <select name="target_list_id" class="form-control max-width">
                    <option value="">-</option>
                </select>
                <br>
            </div>

            <div class="modal-footer">
                <center>
                    <button type="submit" name="submit" class="btn">{vtranslate('LBL_CONFIRM', 'Vtiger')}</button>
                    <a href="#" class="cancelLink" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                </center>
            </div>
        </form>
    </div>

    <script src="{vresource_url('modules/Reports/resources/TargetListHelper.js')}"></script>
{/strip}