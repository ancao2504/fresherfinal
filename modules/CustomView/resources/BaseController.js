/*
	BaseController.js
	Author: Hieu Nguyen
	Date: 2018-09-24
	Purpose: provide a base controller in javascript for other custom views
*/

Vtiger.Class('CustomView_BaseController_Js', {}, {
    init: function () {
        this.addComponents();
    },
    addComponents: function () {
        this.addIndexComponent();
    },
    addIndexComponent: function () {
        this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
    },
    getFieldValue: function (fieldName, sourceElement) {
        var form = sourceElement.closest('form');
        return form.find('[name="' + fieldName + '"]').val();
    },
    registerEvents: function () {
        this._super();
    }
});