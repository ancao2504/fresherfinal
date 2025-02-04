/**
 * Name: GlobalSearchConfig.js
 * Author: Phu Vo
 * Date: 2020.06.24
 */

CustomView_BaseController_Js('Settings_Vtiger_GlobalSearchConfig_Js', {}, {
    registerEvents: function () {
        this._super();
        this.globalSearchConfigInit();
        this.registerFormSubmit();
    },

    getForm: function () {
        return $('form[name="configs"]');
    },

    getFormData: function () {
        const self = this;
        const formData = this.getForm().serializeFormData();
        Object.keys(formData).forEach((name) => {
            const element = self.getForm().find(`:input[name="${name}"]`);
            if (element.is('select') && formData[name] && !(formData[name] instanceof Array)) {
                formData[name] = [formData[name]];
            }
        });

        return formData;
    },

    getEnabledModules: function () {
        const form = this.getForm();
        const selectedModules = form.find('.module-select');
        const enabledModules = [];

        selectedModules.each((index, ui) => {
            let moduleName = $(ui).data('module-name');
            if (!enabledModules.includes()) enabledModules.push(moduleName);
        });

        return enabledModules;
    },

    getModuleListOptions: function () {
        const enabledModules = this.getEnabledModules() || [];
        let moduleListOptions = Object.assign({
            '': app.vtranslate('JS_GLOBAL_SEARCH_SELECT_A_MODULE_TO_ADD'),
        }, _MODULE_LIST);

        enabledModules.forEach((moduleName) => {
            delete moduleListOptions[moduleName];
        });

        moduleListOptions = Object.keys(moduleListOptions).map((moduleName) => {
            return {
                id: moduleName,
                text: moduleListOptions[moduleName],
            };
        });

        return moduleListOptions;
    },

    initModuleSelect: function () {
        const self = this;
        $('input.inputElement.moduleSelect').select2({
            placeholder: app.vtranslate('JS_GLOBAL_SEARCH_SELECT_A_MODULE_TO_ADD'),
            data: self.getModuleListOptions(),
        });
    },

    updateModuleSelect: function() {
        const self = this;
        const moduleSelect = $('input.inputElement.moduleSelect');

        moduleSelect.select2({
            data: self.getModuleListOptions(),
        });

        moduleSelect.val('').trigger('change');
    },

    registerAddModuleButton: function() {
        const self = this;

        $('.addModuleBtn').on('click', function() {
            const selectedModule = $('input.inputElement.moduleSelect').val();

            if (selectedModule) {
                const template = $('.globalSearchModuleTemplates').find(`.module-select[data-module-name="${selectedModule}"]`).clone();
                template.find('select.temp-select2').select2();

                setTimeout(() => {
                    $('.enabledModules').append(template);
                    self.updateModuleSelect();
                }, 0);
            }
            else {
                app.helper.showAlertBox({ message: app.vtranslate('JS_GLOBAL_SEARCH_PLEASE_SELECT_A_MODULE')});
            }
        });
    },

    registerRemoveModuleButton: function (button) {
        const self = this;

        this.getForm().on('click', '.removeModule', function() {
            const target = $(this);
            const row = target.closest('tr.module-select');
            const moduleName = row.find('.module-name').find('label').text();
            const replaceParams = {
                module_name: moduleName,
            };

            app.helper.showConfirmationBox({ message: app.vtranslate('JS_GLOBAL_SEARCH_REMOVE_MODULE_CONFIRMATION_MSG', replaceParams)})
                .then(() => {
                    target.closest('tr.module-select').remove();
                    self.updateModuleSelect();
                });
        });
    },

    globalSearchConfigInit: function () {
        this.initModuleSelect();
        this.registerAddModuleButton();
        this.registerRemoveModuleButton();
    },

    registerFormSubmit: function () {
        const self = this;

        this.getForm().vtValidate({
            submitHandler: () => {
                const formData = self.getFormData();
    
                let params = Object.assign({
                    module: 'Vtiger',
                    parent: 'Settings',
                    action: 'SaveGlobalSearchConfig',
                }, formData);
    
                app.helper.showProgress();
    
                app.request.post({ data: params }).then((err, res) => {
                    app.helper.hideProgress();
    
                    if(err) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_GLOBAL_SEARCH_SAVE_CONFIG_ERROR_MSG')});
                        return;
                    }
    
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_GLOBAL_SEARCH_SAVE_CONFIG_SUCCESS_MSG')});
                    return;
                });
    
                return;
            }
        });
    }
});