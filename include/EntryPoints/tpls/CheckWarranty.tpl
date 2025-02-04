{strip}
<div id="checkWarranty">

    <head>


        {* START-- Modified by Phu Vo on 2020.09.14 to fix missing and wrong lib version some css file cause
        unpredicable ui issues *}
        <link rel="stylesheet" href="{vresource_url('libraries/jquery/select2/select2.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/bootstrap.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/docs.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/todc-bootstrap.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/font-awesome/css/font-awesome.min.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/resources/fonts/fontawsome6/css/all.css')}" /> {* [UI]
        Added by Phu Vo on 2021.04.15 *}
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/select2/select2.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/select2-bootstrap/select2-bootstrap.css')}" />
        <link rel="stylesheet"
            href="{vresource_url('libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker3.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/vt-icons/style.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/animate/animate.min.css')}">
        <link rel="stylesheet"
            href="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.css')}">
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/daterangepicker/daterangepicker.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/timepicker/jquery.timepicker.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/skins/marketing/style.css')}" />
        <link rel="stylesheet" href="{vresource_url('layouts/v7/resources/custom.css')}" />

        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('libraries/jquery/jquery-visibility.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/todc/js/bootstrap.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/select2/select2.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.class.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Class.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('layouts/v7/modules/Vtiger/resources/dashboards/Widget.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/GoogleChart/loader.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('resources/libraries/HighCharts_8.1.0/code/highcharts.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('resources/libraries/FreezeTable/freeze-table.min.js')}"></script>
        {* END-- Modified by Phu Vo on 2020.09.14 to fix missing and wrong lib version some css file cause unpredicable
        ui issues *}

        <script type="text/javascript"
            src="{vresource_url('libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/bootstrap-datepicker.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('layouts/v7/lib/jquery/timepicker/jquery.timepicker.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/Utils.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/resources/helper.js')}"></script>
        <!-- <script type="text/javascript" src="{vresource_url('layouts/v7/resources/application.js')}"></script> -->
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/momentjs/moment.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('layouts/v7/lib/jquery/daterangepicker/moment.min.js')}"></script>
        <!-- <script type="text/javascript" src="{vresource_url('resources/libraries/Moment/MomentHelper.js')}"></script> -->
        <script type="text/javascript" src="{vresource_url('resources/StringUtils.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/CustomUiMeta.js')}"></script>
        <script type="text/javascript"
            src="{vresource_url('layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js')}"></script>
        <!-- <script type="text/javascript"
            src="{vresource_url('layouts/v7/modules/Vtiger/resources/validation.js')}"></script> -->
    </head>

    <body>
        {* Begin: Custom scripts *}
        <link rel="stylesheet" href="{vresource_url('include/EntryPoints/resources/CheckWarranty.css')}" />
        <script type="text/javascript" src="{vresource_url('include/EntryPoints/resources/CheckWarranty.js')}"></script>
        <div id="checkWarranty">
            <h4>{vtranslate('LBL_CHECK_WARRANTY_TITLE', 'Products')}</h4>
            <form id="checkWarrantyForm" method="POST" action="">
                <input type="text" name="serial" value="{$smarty.post.serial}"
                    placeholder="{vtranslate('LBL_CHECK_WARRANTY_SERIAL', 'Products')}">&nbsp;
                <button id="btnCheck" class="btn btn-primary">{vtranslate('LBL_CHECK_WARRANTY_SUBMIT_BTN',
                    'Products')}</button>
            </form>
            <div id="result">
                {$RESULT}
            </div>
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



</body>


{/strip}