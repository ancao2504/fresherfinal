{*
    Name: AddDashboardModal
    Author: Phu Vo
    Date: 2020.10.12
*}

{strip}
    <div id="add-widget-to-category-modal" class="edit-homepage-modal config-homepage-modal modal-dialog modal-md modal-content modal-template-md">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_DASHBOARD_ADD_WIDGET', $MODULE)}
        <form name="select_widget" class="form">
            <input type="hidden" name="module" value="Home" />
            <input type="hidden" name="action" value="DashboardAjax" />
            <input type="hidden" name="mode" value="selectWidgetCategory" />
            <input type="hidden" name="category_id" value="{$CATEGORY_ID}" />

            <div class="editViewBody">
                <div class="container-fluid modal-body">
                    <div class="row">
                        <div class="col-lg-6 fieldValue">
                            <div class="input-wraper" style="position: relative">
                                <i class="far fa-search" aria-hidden="true" style="position: absolute; top: 9px; left: 9px"></i>
                                <input class="inputElement" name="widget_keyword" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" style="width: 300px; padding-left: 30px"/>
                            </div>
                        </div>
                        <div class="col-lg-6 fieldValue">
                            <div class="input-wrapper" style="float:right">
                                <button class="btn inputElement unselectAll" style="width: auto">{vtranslate('LBL_DASHBOARD_UNSELECT_ALL', $MODULE)}</button><span>&nbsp;</span>
                                <button class="btn inputElement selectAll" style="width: auto">{vtranslate('LBL_DASHBOARD_SELECT_ALL', $MODULE)}</button>
                            </div>
                            <div style="clear: both"></div>
                        </div>
                    </div>
                    <table id="widget-list" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="th name">{vtranslate('LBL_DASHBOARD_WIDGET_NAME', $MODULE)}</th>
                                <th class="th type">{vtranslate('LBL_DASHBOARD_WIDGET_TYPE', $MODULE)}</th>
                                <th class="th type">{vtranslate('LBL_DASHBOARD_PRIMARY_MODULE', $MODULE)}</th>
                                <th class="th actived">{vtranslate('LBL_DASHBOARD_SELECT', $MODULE)}</th>
                                <th class="th payload" style="display: none"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$WIDGETS item=item key=key}
                                <tr>
                                    <td class="td name">{$item.name}</td>
                                    <td class="td type">{$item.type}</td>
                                    <td class="td type">{$item.primary_module}</td>
                                    <td class="td actived">
                                        <div class="actions-wrapper">
                                            <input type="hidden" name="widgets[{$key}][id]" value="{$item.id}" />
                                            <input type="hidden" name="widgets[{$key}][category_type]" value="{$item.category_type}" />
                                            <input type="checkbox" class="inputElement" name="widgets[{$key}][active]" />
                                        </div>
                                    </td>
                                    <td class="td payload" style="display: none">
                                        {unUnicode($item.name)}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button class="btn btn-success" type="submit">{vtranslate('LBL_DASHBOARD_ADD', $MODULE)}</button>
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </center>
            </div>
        </form>
    </div>
{/strip}