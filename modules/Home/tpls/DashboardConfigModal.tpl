{*
    Name: getDashboardConfigModal.tpl
    Author: Phu Vo
    Date: 2020.10.122
*}

{strip}
    <div id="config-homepage-modal" class="config-homepage-modal modal-dialog modal-lg modal-content modal-template-lg">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_DASHBOARD_DASHBOARD_CONFIG', $MODULE)}
        <div class="editViewBody">
            <div class="contents tabbable">
                <ul class="nav nav-tabs">
                    <li class="homepage-management active"><a data-toggle="tab" href="#homepage-management">{vtranslate('LBL_DASHBOARD_DASHBOARD_MANAGEMENT', $MODULE)}</a></li>
                    <li class="dashlet-category-management"><a data-toggle="tab" href="#dashlet-category-management">{vtranslate('LBL_DASHBOARD_WIDGET_CATEGORY_MANAGEMENT', $MODULE)}</a></li>
                </ul>
                <div class="tab-content">
                    <!-- homepage-management -->
                    <div class="tab-pane active" id="homepage-management">
                        <div class="container-fluid modal-body">
                            <div class="row">
                                <div class="col-lg-6 fieldValue">
                                    <div class="input-wraper" style="position: relative">
                                        <i class="far fa-search" aria-hidden="true" style="position: absolute; top: 9px; left: 9px"></i>
                                        <input class="inputElement" name="category_keyword" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" style="width: 300px; padding-left: 30px"/>
                                    </div>
                                </div>
                                <div class="col-lg-6 fieldValue">
                                    <button class="btn btn-default addHomepage" type="button" style="float: right">
                                        <i class="far fa-plus" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_DASHBOARD_ADD_DASHBOARD', $MODULE)}
                                    </button>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <table id="homepage-list" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="th name" style="width: 25%">{vtranslate('LBL_DASHBOARD_DASHBOARD_NAME', $MODULE)}</th>
                                                <th class="th status" style="width: 86px">{vtranslate('LBL_DASHBOARD_DASHBOARD_STATUS', $MODULE)}</th>
                                                <th class="th roles" style="width: 25%">{vtranslate('LBL_DASHBOARD_DASHBOARD_ROLES', $MODULE)}</th>
                                                <th class="th permission">{vtranslate('LBL_DASHBOARD_DASHBOARD_PERMISSION', $MODULE)}</th>
                                                <th class="th actions" style="width: 163px">{vtranslate('LBL_DASHBOARD_ACTIONS', $MODULE)}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- dashlet-category-management -->
                    <div class="tab-pane" id="dashlet-category-management">
                        <div class="container-fluid modal-body form-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>
                                        {vtranslate('LBL_DASHBOARD_WIDGET_CATEGORY', $MODULE)}&nbsp;&nbsp;
                                    </label>
                                    <div class="dashlet-filter-input-wrapper" style="width: 300px">
                                        <select class="inputElement select2" name="widget_category">
                                            <option value="">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
                                            {foreach from=$WIDGET_CATEGORIES item=item}
                                                <option value="{$item.id}">{$item.name}</option>
                                            {/foreach}
                                        </select>
                                        <button class="btn inputElement deleteCategory"><i class="far fa-trash-alt" aria-hidden="true"></i></buton>
                                        <button class="btn inputElement editCategory"><i class="far fa-pen" aria-hidden="true"></i></i></buton>
                                        <button class="btn inputElement createCategory"><i class="far fa-plus" aria-hidden="true"></i></buton>
                                    </div>
                                </div>
                            </div>
                            <div class="dashlet-filter-margin-top"></div>
                            <div class="row">
                                <div class="col-lg-6 fieldValue input-wrapper">
                                    <div class="dashlet-filter-input-wrapper" style="position: relative">
                                        <i class="far fa-search" aria-hidden="true" style="position: absolute; top: 9px; left: 9px"></i>
                                        <input class="inputElement" name="widget_keyword" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" style="width: 300px; padding-left: 30px"/>
                                    </div>
                                </div>
                                <div class="col-lg-6 fieldValue input-wrapper">
                                    <div class="button-wrapper" style="float: right">
                                        <button class="btn inputElement removeAllWidget" style="width: auto"><i class="far fa-trash-alt" aria-hidden="true"></i> {vtranslate('LBL_DASHBOARD_REMOVE_ALL_WIDGET_FROM_CATEGORY', $MODULE)}</button><span>&nbsp;</span>
                                        <button class="btn inputElement selectWidget" style="width: auto"><i class="far fa-plus" aria-hidden="true"></i> {vtranslate('LBL_DASHBOARD_ADD_WIDGET', $MODULE)}</button>
                                    </div>
                                    <div style="clear: both"></div>
                                </div>
                            </div>
                            <div class="row" id="dashlet-managerment">
                                <div class="col-lg-12">
                                    <table id="dashlet-list" class="table table-striped table-bordered" style="width: 100%">
                                        <thead>
                                            <th class="th name">{vtranslate('LBL_DASHBOARD_WIDGET_NAME', $MODULE)}</th>
                                            <th class="th category">{vtranslate('LBL_DASHBOARD_WIDGET_TYPE', $MODULE)}</th>
                                            <th class="th category">{vtranslate('LBL_DASHBOARD_PRIMARY_MODULE', $MODULE)}</th>
                                            <th class="th actions">{vtranslate('LBL_DASHBOARD_ACTIONS', $MODULE)}</th>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}