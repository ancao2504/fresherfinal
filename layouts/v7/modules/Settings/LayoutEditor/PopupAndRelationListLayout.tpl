{* Added by Hieu Nguyen on 2019-10-09 *}
{strip}
    {if in_array($SELECTED_MODULE_NAME, ['PriceBooks'])}
        <br />{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_UNSUPPORTED_MODULE_HINT', $QUALIFIED_MODULE)}
    {else}
        <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/modules/Settings/LayoutEditor/resources/PopupAndRelationListEditor.css')}"></link>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Settings/LayoutEditor/resources/PopupAndRelationListEditor.js')}"></script>
        <script type="text/javascript">
            var _MODULE_FIELDS = {ZEND_JSON::encode($SELECT2_MODULE_FIELDS)};
        </script>

        <form id="popupAndRelationListLayoutForm" class="form-horizontal">
            <div id="actions" class="row" style="padding: 10px 20px">
                <button type="button" class="btn btn-primary btnSaveLayout">
                    <i class="far fa-save"></i>&nbsp; {vtranslate('LBL_SAVE_LAYOUT', $QUALIFIED_MODULE)}
                </button>
            </div>

            <!-- Popup Layout -->
            <div class="row">
                <h4>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_POPUP_LAYOUT', $QUALIFIED_MODULE)}</h4>

                <div class="row">
                    <div class="col-sm-2 text-right">
                        <strong>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_DISPLAY_FIELDS', $QUALIFIED_MODULE)}</strong>
                    </div>
                    <div class="col-sm-9">
                        <input type="text" name="popup_fields" style="width: 100%" data-rule-required="true" data-rule-minlength="5" {if $SELECT2_SELECTED_POPUP_FIELDS} data-selected-tags='{ZEND_JSON::encode($SELECT2_SELECTED_POPUP_FIELDS)}' {/if}>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2 text-right">
                        <strong>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_SORT_FIELD', $QUALIFIED_MODULE)}</strong>
                    </div>
                    <div class="col-sm-9">
                        <select type="text" name="popup_sort_field" class="inputElement select2 sort-field" style="width: 200px">
                            <option value="">{vtranslate('LBL_NONE', 'Vtiger')}</option>

                            {foreach item=ITEM from=$SELECT2_MODULE_FIELDS}
                                <option value="{$ITEM.id}" {if $SELECTED_POPUP_SORTING['sort_field'] == $ITEM.id}selected{/if}>{$ITEM.text}</option>
                            {/foreach}
                        </select>
                        &nbsp;&nbsp;
                        <select type="text" name="popup_sort_order" class="inputElement select2 sort-order" style="width: 150px; {if !$SELECTED_POPUP_SORTING['sort_field']}display: none{/if}">
                            <option value="ASC" {if $SELECTED_POPUP_SORTING['sort_order'] == 'ASC'}selected{/if}>{vtranslate('LBL_ASCENDING', 'Vtiger')}</option>
                            <option value="DESC" {if $SELECTED_POPUP_SORTING['sort_order'] == 'DESC'}selected{/if}>{vtranslate('LBL_DESCENDING', 'Vtiger')}</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr />

            <!-- Relation List Layout -->
            <div class="row">
                <h4>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_RELATION_LIST_LAYOUT', $QUALIFIED_MODULE)}</h4>

                <div class="row">
                    <div class="col-sm-2 text-right">
                        <strong>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_DISPLAY_FIELDS', $QUALIFIED_MODULE)}</strong>
                    </div>
                    <div class="col-sm-9">
                        <input type="text" name="relation_list_fields" style="width: 100%" data-rule-required="true" data-rule-minlength="5" {if $SELECT2_SELECTED_RELATION_LIST_FIELDS} data-selected-tags='{ZEND_JSON::encode($SELECT2_SELECTED_RELATION_LIST_FIELDS)}' {/if}>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2 text-right">
                        <strong>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT_SORT_FIELD', $QUALIFIED_MODULE)}</strong>
                    </div>
                    <div class="col-sm-9">
                        <select type="text" name="relation_list_sort_field" class="inputElement select2 sort-field" style="width: 200px">
                            <option value="">{vtranslate('LBL_NONE', 'Vtiger')}</option>

                            {foreach item=ITEM from=$SELECT2_MODULE_FIELDS}
                                <option value="{$ITEM.id}" {if $SELECTED_RELATION_LIST_SORTING['sort_field'] == $ITEM.id}selected{/if}>{$ITEM.text}</option>
                            {/foreach}
                        </select>
                        &nbsp;&nbsp;
                        <select type="text" name="relation_list_sort_order" class="inputElement select2 sort-order" style="width: 150px; {if !$SELECTED_RELATION_LIST_SORTING['sort_field']}display: none{/if}">
                            <option value="ASC" {if $SELECTED_RELATION_LIST_SORTING['sort_order'] == 'ASC'}selected{/if}>{vtranslate('LBL_ASCENDING', 'Vtiger')}</option>
                            <option value="DESC" {if $SELECTED_RELATION_LIST_SORTING['sort_order'] == 'DESC'}selected{/if}>{vtranslate('LBL_DESCENDING', 'Vtiger')}</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    {/if}
{/strip}
{* End Hieu Nguyen *}