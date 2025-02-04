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
            lengthChange: false,
            pageLength: 25,
            processing: true,
            serverSide: true,
            deferLoading: false,
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
                            connector: 'VoIP24H',
                        },
                        filterForm.serializeFormData()
                    );
                },
                complete: function(res, status) {
                    let data = res.responseJSON;
                    $('#start').val(''); // reset start

                    // Set Value
                    $('#next').val(data.next);
                    $('#prev').val(data.prev);

                    updateUI();
                    registerEvents();
                } 
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'callid', name: 'callid'},
                {data: 'did', name: 'did'},
                {data: 'src', name: 'src'},
                {data: 'dst', name: 'dst'},
                {data: 'type', name: 'type'},
                {data: 'calldate', name: 'calldate'},
                {data: 'billsec', name: 'billsec'},
                {data: 'note', name: 'note'},
                {data: 'status', name: 'status'},
                {data: 'recording', name: 'recording', render: function(data, type, row) {
                    let source = data ? `<source src="${data}">` : '';
                    return `<audio controls="controls" preload="none">${source}</audio>`;
                }},
            ],
            language: language
        });
    }

    function registerFilterForm() {
        let params = {
            submitHandler: function() {
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
        // Next and Previous button
        $('a.page-link').on('click', function(e) {
            let button = $(e.target).closest('li');
            let id = button.attr('id');

            if(button.hasClass('disabled')) return;

            if(id === 'listViewTable_next') {
                $('#start').val($('#next').val());
            }

            if(id === 'listViewTable_previous') {
                $('#start').val($('#prev').val());
            }
        });

        // Reset form button
        $('button#clear').on('click', function(e) {
            e.preventDefault();
            
            let form = $('form[name="filters"]');
            form.find('input, select')
                .not('input[name="date_start"]')
                .not('input[name="date_end"]')
                .val('')
                .trigger('change');
        });
    }

    function updateUI() {
        if ($('#next').val()) {
            $('li#listViewTable_next').removeClass('disabled').addClass('active');
        }
        else {
            $('li#listViewTable_next').removeClass('active').addClass('disabled');
        }

        if ($('#prev').val()) {
            $('li#listViewTable_previous').removeClass('disabled').addClass('active');
        }
        else {
            $('li#listViewTable_previous').removeClass('active').addClass('disabled');
        }
    }

    registerDataTable();
    registerFilterForm();
    registerEvents();
    registerElement();
    updateUI();
});