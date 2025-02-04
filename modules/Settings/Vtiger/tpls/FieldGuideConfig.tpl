{* Added by Hieu Nguyen on 2021-01-20 *}

{strip}
    <form id="config" name="config" autocomplete="off">
        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_FIELD_GUIDE_CONFIG', $MODULE_NAME)}</h4>
                    <hr />
                    <table class="configDetails" style="width: 100%">
                        <tbody>
                            <tr>
                                <td class="fieldLabel alignTop" style="width: 5%"><span>{vtranslate('LBL_FIELD_GUIDE_CONFIG_SELECT_MODULE', $MODULE_NAME)}&nbsp;<span class="redColor">*</span></span></td>
                                <td class="fieldValue alignTop">
                                    <select name="target_module" class="inputElement select2" data-rule-required="true" data-value="{$TARGET_MODULE}">
                                        {foreach from=$ALL_MODULES key=NAME item=LABEL}
                                            <option value="{$NAME}" {if $NAME == $TARGET_MODULE}selected{/if}>{$LABEL}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldLabel alignTop" colspan="2">
                                    <br />
                                    <div>
                                        <table style="width: 60%">
                                            <thead>
                                                <tr>
                                                    <th width="40px">{vtranslate('LBL_FIELD_GUIDE_CONFIG_LINE_NO', $MODULE_NAME)}</th>
                                                    <th>{vtranslate('LBL_FIELD_GUIDE_CONFIG_FIELD_NAME', $MODULE_NAME)}</th>
                                                    <th width="400px">{vtranslate('LBL_FIELD_GUIDE_CONFIG_HELP_TEXT', $MODULE_NAME)}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {assign var=INDEX value=0}

                                                {foreach from=$FIELD_MODELS item=FIELD_MODEL}
                                                    {assign var=INDEX value=$INDEX + 1}

                                                    <tr>
                                                        <td class="text-center">{$INDEX}</td>
                                                        <td>{vtranslate($FIELD_MODEL->get('label'), $TARGET_MODULE)}</td>
                                                        <td>
                                                            <textarea name="help_text[{$FIELD_MODEL->getName()}]" style="width: 100%">{$FIELD_MODEL->get('helpinfo')}</textarea>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
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