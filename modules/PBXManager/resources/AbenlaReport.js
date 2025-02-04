/*
    File: AbenlaReport.js
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
                            connector: 'Abenla',
                        },
                        $('form[name="filters"]').serializeFormData()
                    );
                },
            },
            columns: [
                {data: 'stt', name: 'stt'},
                {data: 'key', name: 'key'},
                {data: 'dauSo', name: 'dauSo'},
                {data: 'soGoiDen', name: 'soGoiDen'},
                {data: 'soNhan', name: 'soNhan'},
                {data: 'ngayGoi', name: 'ngayGoi', render: function(data, type,row) {
                    return data ? data.replace('T', ' ') : '';
                }},
                {data: 'tongThoiGianGoi', name: 'tongThoiGianGoi'},
                {
                    data: 'thoiGianThucGoi', 
                    name: 'thoiGianThucGoi',
                    render: function (data, type, row) {
                        data = data || 0;
                        return moment('00:00:00', 'HH:mm:ss').add(data, 'seconds').format('mm:ss');
                    }
                },
                {data: 'trangThai', name: 'trangThai'},
                {data: 'linkFile', name: 'linkFile', render: function(data, type, row) {
                    let source = data ? `<source src="${data}">` : '';
                    return `<audio controls="controls" preload="none">${source}</audio>`;
                }},
            ],
            language: language
        });
    }

    function registerEvents() {
        // Filter form button
        $('button#filter').on('click', function(e) {
            e.preventDefault();
            ReportTable.ajax.reload();
        });

        // Reset form button
        $('button#clear').on('click', function(e) {
            e.preventDefault();
            
            filterForm[0].reset();
            filterForm.find('select').trigger('change');
        });

        // Export Excel
        $('.exportExcel').on('click', function() {
            window.open('data:application/vnd.ms-excel,' + encodeURIComponent($('#listViewTable').parent().html()));
        });
    }

    function registerElement() {
        vtUtils.applyFieldElementsView($('[name="filters"]'));
    }

    registerDataTable();
    registerEvents();
    registerElement();
});