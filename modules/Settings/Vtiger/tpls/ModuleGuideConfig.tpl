{* Added by Hieu Nguyen on 2021-01-18 *}

{strip}
    <form id="config" name="config" autocomplete="off">
        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_MODULE_GUIDE_CONFIG', $MODULE_NAME)}</h4>
                    <hr />
                    <table class="configDetails" style="width: 100%">
                        <tbody>
                            <tr>
                                <td class="fieldLabel alignTop" style="width: 5%"><span>{vtranslate('LBL_MODULE_GUIDE_CONFIG_SELECT_MODULE', $MODULE_NAME)}&nbsp;<span class="redColor">*</span></span></td>
                                <td class="fieldValue alignTop">
                                    <select name="target_module" class="inputElement select2" data-rule-required="true">
                                        <option value="">{vtranslate('LBL_MODULE_GUIDE_CONFIG_SELECT_A_MODULE', $MODULE_NAME)}</option>

                                        {foreach from=$ALL_MODULES key=NAME item=LABEL}
                                            <option value="{$NAME}">{$LABEL}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldLabel alignTop" colspan="2">
                                    <span>{vtranslate('LBL_MODULE_GUIDE_CONFIG_GUIDE_CONTENT', $MODULE_NAME)}&nbsp;<span class="redColor">*</span></span>
                                    <div>
                                        <textarea id="guide_content" name="guide_content" data-rule-required="true" style="display: none"></textarea>
                                    </div>
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
{/strip}