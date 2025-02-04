{*
    Name: GlobalSearchConfig.tpl
    Author: Phu Vo
    Date: 2020.07.15
*}

{strip}
    <script>
        var _MODULE_LIST = {json_encode($MODULE_LIST)};
        var _CONFIGS = {json_encode($CONFIGS)};
    </script>
    <form autocomplete="off" name="configs">
        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_GLOBAL_SEARCH_CONFIG', $MODULE_NAME)}</h4>
                    <hr />
                    <table class="configDetails" style="width: 100%">
                        <tbody>
                            <tr>
                                <td class="fieldLabel alignTop">
                                    <input class="inputElement moduleSelect" /> 
                                </td>
                                <td class="fieldValue alignTop">
                                    <a class="btn btn-primary addModuleBtn"><i class="far fa-plus"></i> Thêm</a>
                                </td>
                                <td class="fieldLabel alignTop"></td>
                                <td class="fieldValue alignTop"></td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="configDetails" style="width: 100%">
                        <thead class="enabled-header">
                            <tr>
                                <th class="fieldLabel module-name">{vtranslate('LBL_GLOBAL_SEARCH_MODULE_NAME', $MODULE_NAME)}</th>
                                <th class="row-actions"></th>
                                <th class="fieldValue module-fields">{vtranslate('LBL_GLOBAL_SEARCH_ENABLED_FIELDS', $MODULE_NAME)}</th>
                            </tr>
                        </thead>
                        <tbody class="enabledModules">
                            {if !empty($CONFIGS)}
                                {foreach from=$CONFIGS['enabled_modules'] key=moduleName item=enabledFields}
                                    <tr class="module-select" data-module-name="{$moduleName}"> 
                                        <td class="fieldLabel alignTop module-name">
                                            <label>{$MODULE_LIST[$moduleName]}</label>
                                        </td>
                                        <td class="row-actions alignTop">
                                            <a class="btn btn-default removeModule"><i class="far fa-close"></i></a>
                                        </td>
                                        <td class="fieldValue alignTop module-fields">
                                            <div class="pos-rel">
                                                <select class="inputElement select2" name="configs[enabled_modules][{$moduleName}]" multiple data-rule-required="true">
                                                    {foreach from=$MODULES_FIELDS[$moduleName] item=field}
                                                        <option value="{$field['id']}" {if in_array($field['id'], $CONFIGS['enabled_modules'][$moduleName])}selected{/if}>{$field['text']}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
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
    <div class="globalSearchModuleTemplates" style="display: none !important">
        <table>
            {foreach from=$MODULE_LIST key=moduleName item=moduleLabel}
                <tr class="module-select" data-module-name="{$moduleName}">
                    <td class="fieldLabel alignTop module-name">
                        <label>{$moduleLabel}</label>
                    </td>
                    <td class="row-actions alignTop">
                        <a class="btn btn-default removeModule"><i class="far fa-close"></i></a>
                    </td>
                    <td class="fieldValue alignTop module-fields">
                        <div class="pos-rel">
                            <select class="inputElement temp-select2" name="configs[enabled_modules][{$moduleName}]" multiple data-rule-required="true">
                                {foreach from=$MODULES_FIELDS[$moduleName] item=field}
                                    <option value="{$field['id']}">{$field['text']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
{/strip}