/**
 * Name: EventRegistrationHelper.js
 * Author: Phu Vo
 * Date: 2020.07.15
 */

(() => {
    window.EventRegistrationHelper = new class {
        constructor() {
            $(() => {
                this.initManualRegister();
            });
        }

        initManualRegister () {
            const self = this;

            // Workaround
            $('.addButton[module="CPEventRegistration"]').removeAttr('name');
            app.event.on('post.relatedListLoad.click', function() {
                $('.addButton[module="CPEventRegistration"]').removeAttr('name');
            });

            $('.detailViewContainer').on('click', '.addButton[module="CPEventRegistration"]', function (event) {
                self.openManualRegistrationForm($(this));
            });
        }

        openManualRegistrationForm (ui) {
            const self = this;
            let url = ui.data('url');
            let params = app.convertUrlToDataParams(url);

            app.helper.showProgress();

            app.request.post({ data: params }).then((err, res) => {
                app.helper.hideProgress();

                if (err) {
                    app.helper.showAlertBox({ message: err.message });
                    return;
                }
                if (!res) {
                    app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_MANUAL_REGISTER_ERROR_MSG') });
                    return;
                }

                // Show modal
                app.helper.showModal(res, {
                    preShowCb: (modal) => {
                        self.initFormSubmitHandler(modal);
                    }
                });
            });
        }

        initFormSubmitHandler (modal) {
            const form = modal.find('form');

            form.vtValidate({
                submitHandler: function (form) {
                    const params = $(form).serializeFormData();

                    app.helper.showProgress();

                    app.request.post({ data: params }).then((err, res) => {
                        app.helper.hideProgress();

                        if (err) {
                            app.helper.showErrorNotification({ message: err.message }, { delay: 5000 });
                            return;
                        }
                        if (!res) {
                            app.helper.showErrorNotification({ message: app.vtranslate('CPEventRegistration.JS_MANUAL_REGISTER_ERROR_MSG') });
                            return;
                        }

                        app.helper.showSuccessNotification({ message: app.vtranslate('CPEventRegistration.JS_MANUAL_REGISTER_SUCCESS_MSG')});
                        app.helper.hideModal();
                        $('.related-tabs').find('li.moreTabElement.active[data-module="CPEventRegistration"]').trigger('click');
                    });
                }
            });
        }
    }
})();
