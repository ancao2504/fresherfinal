{strip}
<div id="checkWarranty" class="container my-4 p-4 border rounded shadow-sm">
    <h4 class="text-primary mb-3">{vtranslate('LBL_CHECK_WARRANTY_TITLE', 'Products')}</h4>
    <form id="checkWarrantyForm" method="POST" action="" class="mb-4">
        <div class="form-group mb-3">
            <input type="text" name="serial" value="{$smarty.post.serial}" class="form-control"
                placeholder="{vtranslate('LBL_CHECK_WARRANTY_SERIAL', 'Products')}" />
        </div>
        <div class="d-flex gap-2">
            <button id="btnCheck" class="btn btn-primary">
                {vtranslate('LBL_CHECK_WARRANTY_SUBMIT_BTN', 'Products')}
            </button>
            <button id="btnDeclare" class="btn btn-success">
                {vtranslate('LBL_DECLARE_PRODUCT_BTN', 'Products')}
            </button>
            <button id="btnDelete" class="btn btn-danger">Xóa</button>
            <button id="btnUpdate" class="btn btn-secondary">Update</button>
        </div>
    </form>
    <div id="result" class="mt-4" style="display: none;">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th scope="row" class="text-end" width="200">
                        {vtranslate('LBL_WARRANTY_PRODUCT_NAME', 'Products')}:
                    </th>
                    <td id="productName"></td>
                </tr>
                <tr>
                    <th scope="row" class="text-end">
                        {vtranslate('LBL_WARRANTY_SERIAL_NO', 'Products')}:
                    </th>
                    <td id="serialNo"></td>
                </tr>
                <tr>
                    <th scope="row" class="text-end">
                        {vtranslate('LBL_WARRANTY_START_DATE', 'Products')}:
                    </th>
                    <td id="warrantyStartDate"></td>
                </tr>
                <tr>
                    <th scope="row" class="text-end">
                        {vtranslate('LBL_WARRANTY_END_DATE', 'Products')}:
                    </th>
                    <td id="warrantyEndDate"></td>
                </tr>
                <tr>
                    <th scope="row" class="text-end">
                        {vtranslate('LBL_WARRANTY_STATUS', 'Products')}:
                    </th>
                    <td><span id="warrantyStatus" class="label"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="declareProductModal" class="modal-dialog modal-content hide">
    {assign var=HEADER_TITLE value={vtranslate('LBL_DECLARE_PRODUCT_MODAL_TITLE', 'Products')}}
    {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <form class="form-horizontal declareProductForm" method="POST">
        <input type="hidden" name="leftSideModule" value="{$SELECTED_MODULE_NAME}" />

        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>{vtranslate('LBL_PRODUCT_NAME', 'Products')}</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="product_name" class="form-control" data-rule-required="true" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>{vtranslate('LBL_SERIAL_NO', 'Products')}</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="serial_no" class="form-control" data-rule-required="true" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>{vtranslate('LBL_WARRANTY_START_DATE', 'Products')}</span>
                <span class="redColor">*</span>
            </label>
            <div class="input-group inputElement" style="margin-bottom: 3px">
                <input type="text" name="warranty_start_date" class="form-control datePicker" data-fieldtype="date"
                    datadate-format="{$USER_MODEL->get('date_format')}" data-rule-required="true" />
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>


        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>{vtranslate('LBL_WARRANTY_END_DATE', 'Products')}</span>
                <span class="redColor">*</span>
            </label>

            <div class="input-group inputElement" style="margin-bottom: 3px">
                <input type="text" name="warranty_end_date" class="form-control datePicker" data-fieldtype="date"
                    datadate-format="{$USER_MODEL->get('date_format')}" data-rule-required="true" />
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>{vtranslate('LBL_WEBSITE', 'Products')}</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="website" class="form-control" data-rule-required="true" />
            </div>
        </div>

        {include file='ModalFooter.tpl'|@vtemplate_path:'CRM'}
    </form>
</div>

{/strip}