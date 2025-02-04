/*
    DynamicTable.js
    Author: Hieu Nguyen
    Date: 2018-09-20
    Purpose: Allow to add and remove rows in a table
    Usage: this version is a native jquery plugin. Use cases:
        + $('#dynamicTable').dynamicTable();    // Single selector, no call back function
        + $('.dynamicTable').dynamicTable();    // Multiple selector by class, no call back function
        + $('#dynamicTable1, #dynamicTabl2').dynamicTable();    // Multiple selector by id, no call back function
        + $('#dynamicTable').dynamicTable({delAction: 'hide'});    // Switch to hide row mode. Hidden input name="deleted[]" is required in each row
        + $('#dynamicTable').dynamicTable({preAddCallback: function() {console.log('Adding new row!')}});    // Using call back function
        + $('#dynamicTable').dynamicTable({postAddCallback: function(insertedRow) {console.log(insertedRow)}});    // Using call back function
        + $('#dynamicTable').dynamicTable({preDelCallback: function(selectedRow) {console.log(selectedRow)}});    // Using call back function
        + $('#dynamicTable').dynamicTable({postDelCallback: function() {console.log('Row deleted!')}});    // Using call back function
*/

(function($) {
    $.fn.dynamicTable = function(options) {

        var settings = $.extend({
            delAction: 'delete',    // Default is delete, can be set to hide
            preAddCallback: null,
            postAddCallback: null,
            preDelCallback: null,
            postDelCallback: null,
        }, options);

        if(settings.delAction != 'delete' && settings.delAction != 'hide') {
            settings.delAction = 'delete';
        }

        // Init dynamic table for each table in the selector
        return this.each(function() {
            $(this).find('.btnAddRow').live('click', function () {
                if (typeof settings.preAddCallback == 'function') {
                    var result = settings.preAddCallback();
                    if(result == false) return false;
                }

                var table = $(this).closest('table');
                var template = table.find('tfoot.template');
                var newRow = template.html();
                table.find('tbody').append(newRow);
                var inserted = table.find('tbody').find('tr:last');

                if (typeof settings.postAddCallback == 'function') {
                    settings.postAddCallback(inserted);
                }
            });

            $(this).find('.btnDelRow').live('click', function () {
                if (typeof settings.preDelCallback == 'function') {
                    var result = settings.preDelCallback($(this));
                    if(result == false) return false;
                }

                var row = $(this).closest('tr');

                if(settings.delAction == 'delete') {
                    row.remove();
                }
                else {
                    row.find('input[name="deleted[]"]').val('1');
                    row.hide();
                }

                if (typeof settings.postDelCallback == 'function') {
                    settings.postDelCallback();
                }
            });
        });
    };
}(jQuery));