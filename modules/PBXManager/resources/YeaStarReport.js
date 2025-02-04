/*
    File: CloudFoneReport.js
    Author: Phu Vo
    Date: 2019.04.11
*/

jQuery(function() {
    let filterForm = $('[name="filters"]');

    function registerDataTable() {
        // process language to replace prev and next button to icon
        let language = {
            emptyTable: app.vtranslate('JS_DATATABLES_NO_DATA_AVAILABLE'),
            info: app.vtranslate('JS_DATATABLES_FOOTER_INFO'),
            infoEmpty: app.vtranslate('JS_DATATABLES_FOOTER_INFO_NO_ENTRY'),
            lengthMenu: app.vtranslate('JS_DATATABLES_LENGTH_MENU'),
            loadingRecords: app.vtranslate('JS_DATATABLES_LOADING_RECORD'),
            processing: app.vtranslate('JS_DATATABLES_PROCESSING'),
            search: app.vtranslate('JS_DATATABLES_SEARCH'),
            zeroRecords: app.vtranslate('JS_DATATABLES_NO_RECORD'),
            sInfoFiltered: app.vtranslate('JS_DATATABLES_INFO_FILTERED'),
            paginate: {
                first: app.vtranslate('JS_DATATABLES_FIRST'),
                last: app.vtranslate('JS_DATATABLES_LAST'),
                next: '<i class="far fa-chevron-right"></i>',
                previous: '<i class="far fa-chevron-left"></i>',
            }
        }

        window.ReportTable = $('#listViewTable').DataTable({
            ordering: false,
            searching: false,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-right'B>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: $('.reportTitle .title').html(),
                    extension: '.xlsx',
                    text: app.vtranslate('PBXManager.JS_EXTERNAL_REPORT_EXPORT_EXCEL'),
                }
            ],
            ajax: {
                url: 'index.php',
                type: 'POST',
                dataType: 'JSON',
                data: function(data) {
                    return $.extend({}, data, 
                        {
                            module: 'PBXManager',
                            action: 'HandleExternalReport',
                            mode: 'getReport',
                            connector: 'YeaStar',
                        },
                        $('form[name="filters"]').serializeFormData()
                    );
                },
            },
            columns: [
                {data: 'callid', name: 'callid'},
                {data: 'type', name: 'type'},
                {data: 'callfrom', name: 'callfrom'},
                {data: 'callto', name: 'callto'},
                {data: 'timestart', name: 'timestart'},
                {
                    data: 'callduraction', 
                    name: 'callduraction',
                    render: function (data, type, row) {
                        data = data || 0;
                        return moment('00:00:00', 'HH:mm:ss').add(data, 'seconds').format('HH:mm:ss');
                    }
                },
                {
                    data: 'talkduraction', 
                    name: 'talkduraction',
                    render: function (data, type, row) {
                        data = data || 0;
                        return moment('00:00:00', 'HH:mm:ss').add(data, 'seconds').format('HH:mm:ss');
                    }
                },
                {data: 'status', name: 'status'},
                {data: 'recording', name: 'recording', render: function(data, type, row) {
                    let source = data ? `<source src="index.php?module=PBXManager&action=GetRecording&filename=${data}">` : '';
                    return `<audio controls="controls" preload="none">${source}</audio>`;
                }},
            ],
            language: language
        });
    }

    function registerEvents() {
        // Filter form button
        // $('button#filter').on('click', function(e) {
        //     e.preventDefault();
        //     ReportTable.ajax.reload();
        // });

        $('form[name="filters"]').vtValidate({
            submitHandler: form => {
                ReportTable.ajax.reload();
            }
        });

        // Reset form button
        $('button#clear').on('click', function(e) {
            e.preventDefault();
            
            filterForm[0].reset();
            filterForm.find('select, input, textarea').trigger('change');
            filterForm.find('.dateField').datepicker('update');
        });
    }

    function registerElement() {
        vtUtils.applyFieldElementsView($('[name="filters"]'));
    }

    registerDataTable();
    registerEvents();
    registerElement();
});