/*
    File: CloudFoneReport.js
    Author: Phu Vo
    Date: 2019.06.04
*/

jQuery(function() {
    let filterForm = $('[name="filters"]');
    let cache = {
        rawData: [],
        data: [],
        revertStart: 0,
        start: 0,
        reset: false,
        length: 0,
    }

    function registerDataTable() { - cache.revertStart
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
            pageLength: 20,
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
                            connector: 'FreePBX',
                        },
                        filterForm.serializeFormData()
                    );

                    if (!cache.reset) params.start -= cache.revertStart;
                    cache.start = params.start;
                    cache.length = params.length;

                    return params;
                },
                dataSrc: function(res) {
                    let rawData = res.data || [];
                    let empty = rawData.length === 0;
                    let data = rawData;
                    
                    if (empty && !cache.reset) {
                        data = cache.data;
                    }
                    
                    cache.rawData = rawData;
                    if (empty) {
                        cache.revertStart += res['length'] || 0;
                        if (res.offset === cache.length) cache.start = 0;
                    }

                    if (cache.reset) cache.revertStart = 0;

                    return data;
                },
                complete: function(res, status) {
                    let data = res.responseJSON;

                    data = data.data || [];
                    cache.data = data;
                    cache.reset = false;

                    updateUI(cache.rawData);
                },
            },
            columns: [
                {
                    data: 'uuid', 
                    name: 'uuid'
                },
                {
                    data: 'source', 
                    name: 'source'
                },
                {
                    data: 'destination', 
                    name: 'destination'
                },
                {
                    data: 'starttime', 
                    name: 'starttime', 
                    render: function(data, type, row) {
                        if (!data) return '';
                        return MomentHelper.getDisplayDateTime(timePHPtoJS(data));
                    }
                },
                {
                    data: 'endtime', 
                    name: 'endtime', 
                    render: function(data, type, row) {
                        if (!data) return '';
                        return MomentHelper.getDisplayDateTime(timePHPtoJS(data));
                    }
                },
                {
                    data: 'duration', 
                    name: 'duration',
                    render: function (data, type, row) {
                        data = data || 0;
                        return moment('00:00:00', 'HH:mm:ss').add(data, 'seconds').format('HH:mm:ss');
                    }
                },
                {
                    data: 'direction', 
                    name: 'direction'
                },
                {
                    data: 'file', 
                    name: 'file', 
                    render: function(data, type, row) {
                        let source = data ? `<source src="${data}">` : '';
                        return `<audio controls="controls" preload="none">${source}</audio>`;
                    }
                },
            ],
            language: language
        });
    }

    function registerFilterForm() {
        let params = {
            submitHandler: function() {
                cache.reset = true;
                ReportTable.ajax.reload();
                return false;
            },
        };

        filterForm.vtValidate(params);
    }

    function registerElement() {
        vtUtils.applyFieldElementsView(filterForm);
    }

    function registerEvents() {
        // Reset form button
        $('button#clear').on('click', function(e) {
            e.preventDefault();
            
            let form = $('form[name="filters"]');
            form.find('input, select').val('').trigger('change');
        });
    }

    function timePHPtoJS($time) {
        return Number($time) * 1000;
    }

    function updateUI(data = []) {
        $('#listViewTable_next').toggleClass('disabled', data.length === 0);
        $('#listViewTable_previous').toggleClass('disabled', cache.start < 1);
    }

    registerDataTable();
    registerFilterForm();
    registerEvents();
    registerElement();
});