/*
    Native controller for EditView (put here to be consistent with Detail.js)
    Author: Hieu Nguyen
    Date: 2020-03-10
*/

Vtiger_Edit_Js('Potentials_Edit_Js', { }, {
    
    // Override this function to filter Contacts according to selected Account in Lookup Popup
    getPopUpParams: function (container) {
        var params = this._super(container);
        var sourceFieldElement = container.find('input[class="sourceField"]');

        if (sourceFieldElement.attr('name') == 'contact_id') {        
            var form = this.getForm();
            var parentIdElement = form.find('[name="related_to"]');
        
            if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
                var closestContainer = parentIdElement.closest('td');
                params['related_parent_id'] = parentIdElement.val();
                params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
            }
        }
     
        return params;
    }
});