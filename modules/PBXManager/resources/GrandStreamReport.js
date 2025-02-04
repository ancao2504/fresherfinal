/*
    File: CloudFoneReport.js
    Author: Phu Vo
    Date: 2019.04.11
*/

jQuery(function() {
    let filterForm = $('[name="filters"]');
    let cache = {
        rawData: [],
        data: [],
        revertStart: 0,
        start: 0,
    }

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
                    let params = $.extend({}, data, 
                        {
                            module: 'PBXManager',
                            action: 'HandleExternalReport',
                            mode: 'getReport',
                            connector: 'GrandStream',
                        },
                        filterForm.serializeFormData()
                    );

                    params.start -= cache.revertStart;
                    cache.start = params.start;

                    return params;
                },
                dataSrc: function(res) {
                    let rawData = res.data || [];
                    let empty = rawData.length === 0;
                    let data = !empty ? rawData : cache.data;
                    
                    cache.rawData = rawData;
                    if (empty) cache.revertStart += res['length'] || 0;

                    return data;
                },
                complete: function(res, status) {
                    let data = res.responseJSON;

                    data = data.data || [];
                    cache.data = data;

                    updateUI(cache.rawData);
                },
            },
            columns: [
                {data: 'uniqueid', name: 'uniqueid'},
                {data: 'src', name: 'src'},
                {data: 'dst', name: 'dst'},
                {data: 'start', name: 'date_start'},
                {data: 'end', name: 'date_end'},
                {
                    data: 'duration', 
                    name: 'duration',
                    render: function (data, type, row) {
                        data = data || 0;
                        return moment('00:00:00', 'HH:mm:ss').add(data, 'seconds').format('HH:mm:ss');
                    }
                },
                {
                    data: 'billsec', 
                    name: 'billsec',
                    render: function (data, type, row) {
                        data = data || 0;
                        return moment('00:00:00', 'HH:mm:ss').add(data, 'seconds').format('HH:mm:ss');
                    }
                },
                {data: 'disposition', name: 'disposition'},
                {data: 'recordfiles', name: 'recordfiles', render: function(data, type, row) {
                    let source = data ? `<source src="index.php?module=PBXManager&action=GetRecording&filename=${data}">` : '';
                    return `<audio controls="controls" preload="none">${source}</audio>`;
                }},
            ],
            language: language
        });
    }

    function registerFilterForm() {
        let params = {
            submitHandler: function() {
                cache.revertStart = 0;
                ReportTable.ajax.reload();
                return false;
            },
        };

        filterForm.vtValidate(params);
    }

    function registerElement() {
        vtUtils.applyFieldElementsView(filterForm);
        
        $('.dt-button.buttons-excel.buttons-html5').addClass('btn btn-default');
        $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="far fa-download" aria-hidden="true"></i> ');
    }

    function registerEvents() {
        // Reset form button
        $('button#clear').on('click', function(e) {
            e.preventDefault();
            
            let form = $('form[name="filters"]');
            form.find('input, select').val('').trigger('change');
        });
    }

    function updateUI(data = []) {
        $('#listViewTable_next').toggleClass('disabled', data.length === 0);
        $('#listViewTable_previous').toggleClass('disabled', cache.start ===0);
    }

    registerDataTable();
    registerFilterForm();
    registerEvents();
    registerElement();
});