/*
    DetailView.js
    Author: Phuc Lu
    Date: 2019.11.25
    Purpose: to handle logic on the UI
*/

jQuery(function($) {
    
    // Added by Phuc on 2019.11.25 to remove action quick edit when status is Converted
    if ($('[data-name="leadstatus"]').data('value') == 'Converted') {
        $('.fieldValue.leadstatus').find('.action.pull-right').remove();
    }
    // Ended by Phuc

    // Register dependency list
    app.event.on('post.convertLeadModal.load', function (event, data) {
        $('.convertLeadModules').find('.picklistDependency').each(function () {
            var picklistDependencyMapping = JSON.parse($(this).val());
            var sourcePicklists = Object.keys(picklistDependencyMapping);
            
            if (sourcePicklists.length <= 0) {
                return;
            }
            
            var sourcePickListNames = "";
    
            for (var i=0; i < sourcePicklists.length; i++) {
                sourcePickListNames += '[name="' + sourcePicklists[i] + '"],';
            }
            
            sourcePickListNames = sourcePickListNames.replace(/(^,)|(,$)/g, "");
    
            $(this).closest('.convertLeadModules').on('change', sourcePickListNames, function (e) {
                var currentElement = $(e.currentTarget);
                var sourcePicklistname = currentElement.attr('name');
                var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
                var selectedValue = currentElement.val();
                var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
                
                if (typeof targetObjectForSelectedSourceValue == 'undefined') {
                    return;
                }
                
                $.each(targetObjectForSelectedSourceValue, function (targetPickListName, targetPickListValue) {
                    var targetPickList = $('[name="' + targetPickListName + '"]');
                    
                    if (targetPickList.length <= 0) {
                        return;
                    }

                    targetPickList.val(targetPickListValue);
                    targetPickList.trigger('change');
                })
            });
        });
    });
})