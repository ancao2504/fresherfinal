/*
    File: AICameraIntegrationConfig.js
    Author: Phu Vo
    Date: 2021.04.02
    Purpose: System notification ui handler
*/

// Copied from https://stackoverflow.com/questions/4068373/center-a-popup-window-on-screen and modified by Hieu Nguyen on 2019-07-10
function popupCenter(url, title, width, height) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

    var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var systemZoom = screenWidth / window.screen.availWidth;
    var left = (screenWidth - width) / 2 / systemZoom + dualScreenLeft;
    var top = (screenHeight - height) / 2 / systemZoom + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + width / systemZoom + ', height=' + height / systemZoom + ', top=' + top + ', left=' + left);
    
    newWindow.callBack = () => window.location.reload();

    // Puts focus on the newWindow
    if (window.focus) newWindow.focus();

    return newWindow;
}

CustomView_BaseController_Js('Settings_Vtiger_AICameraIntegrationConfig_Js', {}, {
    registerEvents: function () {
        this._super();
        this.handleSearch();
        this.handleConnect();
        this.handleDetail();
        this.handleEdit();
        this.handleDeletePlace();
        this.handleAddCamera();
        this.handleDisconnect();
    },

    getForm: function () {
        return $('form[name="configs"]');
    },

    handleSearch: function () {
        let form = this.getForm();

        form.find('.search-input').on('input', function (event) {
            let keyword = $(event.target).val().toLowerCase();
            form.find('.vendor-select-container .vendor').hide();

            form.find('.vendor-select-container .vendor').each(function () {
                if ($(this).data('displayName').toLowerCase().search(keyword) > -1) {   // Refactored by Hieu Nguyen on 2021-06-08
                    $(this).show();
                }
            });
        });
    },

    handleConnect: function () {
        let self = this;
        let form = this.getForm();
        
        form.find('.connect-btn').on('click', function (event) {
            let vendor = $(this).closest('.vendor');
            let vendorData = vendor.data();

            event.preventDefault();
            self.showEditCameraVendorModal('new', vendorData);
        });
    },

    handleDetail: function () {
        this.getForm().find('.vendors .vendor[data-connected="1"]').on('click', function (event) {
            if ($(event.target).is('button, a')) return;
            
            event.preventDefault();

            let data = $(this).data();
            let url = 'index.php?module=Vtiger&parent=Settings&view=AICameraIntegrationConfig';
            url += '&targetView=ShowDetail';
            url += '&provider=' + data.name;

            window.location = url;
        });
    },

    handleEdit: function () {
        let self = this;
        
        this.getForm().find('.edit-connect').on('click', function (event) {
            let data = $(this).data();
            event.preventDefault();
            self.showEditCameraVendorModal('edit', data);
        });
    },

    handleDeletePlace: function () {
        $('.delete-place').on('click', function (event) {
            event.preventDefault();

            let replaceParams = { place_name: $(this).data('name') };
            let confirmationMessage = app.vtranslate('JS_AI_CAMERA_REMOVE_PLACE_CONFIRM_MSG', replaceParams);

            app.helper.showConfirmationBox({ message: confirmationMessage }).then(() => {
                let data = {
                    module: 'Vtiger',
                    parent: 'Settings',
                    action: 'SaveAICameraIntegrationConfig',
                    mode: 'removePlace',
                    place_id: $(this).data('id'),
                };
        
                app.helper.showProgress();
        
                app.request.post({ data }).then((err, res) => {
                    app.helper.hideProgress();
        
                    if (err) {
                        return app.helper.showErrorNotification({ message: err.message });
                    }
        
                    if (!res) {
                        return app.helper.showErrorNotification({ message: app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR') });
                    }

                    app.helper.showSuccessNotification({message: app.vtranslate('JS_AI_CAMERA_REMOVE_PLACE_SUCCESS_MSG')});
    
                    window.location.reload();
                });
            });
        });
    },

    handleAddCamera: function () {
        this.getForm().find('.add-camera').on('click', function (event) {
            event.preventDefault();

            let siteUrl = window.location.origin + window.location.pathname.replace('index.php', '');
            let url = siteUrl +'index.php?module=Vtiger&parent=Settings&view=ConnectHanet&targetView=PlaceList';

            popupCenter(url, 'Connect AI Camera', 600, 780);
        });
    },

    handleDisconnect: function () {
        var form = this.getForm();

        form.on('click', '.disconnect-btn', function (event) {
            event.preventDefault();
            
            let providerDiv = $(this).closest('.vendor');
            let providerName = providerDiv[0] != null ? providerDiv.data('displayName') : form.find('[name="provider_display_name"]').val();
            let replaceParams = { provider_name: providerName };
            let confirmationMessage = app.vtranslate('JS_AI_CAMERA_DISCONNECT_VENDOR_CONFIRMATION_MSG', replaceParams);

            app.helper.showConfirmationBox({ message: confirmationMessage }).then(() => {
                let data = {
                    module: 'Vtiger',
                    parent: 'Settings',
                    action: 'SaveAICameraIntegrationConfig',
                    mode: 'disconnectVendor',
                };
        
                app.helper.showProgress();
        
                app.request.post({ data }).then((err, res) => {
                    app.helper.hideProgress();
        
                    if (err) {
                        return app.helper.showErrorNotification({ message: err.message });
                    }
        
                    if (!res) {
                        return app.helper.showErrorNotification({ message: app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR') });
                    }

                    app.helper.showSuccessNotification({ message: app.vtranslate('JS_AI_CAMERA_DISCONNECT_VENDOR_SUCCESS_MSG') });

                    window.location = 'index.php?module=Vtiger&parent=Settings&view=AICameraIntegrationConfig';
                });
            });

            return false;
        });
    },

    getPassWordText: function (pass = '') {
        return new Array(pass.length + 1).join('*');
    },

    showEditCameraVendorModal: function (mode, vendorData) {
        let modal = $('.editAiCameraModal').clone().attr('id', 'editAiCamera');

        app.helper.showModal(modal, {
            preShowCb: function (modal) {
                const form = modal.find('form[name="edit_ai_camera"]');

                form.find('[name="config[active_provider]"]').val(vendorData.name);

                form.find('table.fieldBlockContainer').hide();
                form.find('table[provider="'+ vendorData.name +'"]').show();

                if (mode == 'edit') {
                    form.find('button.new').remove();

                    if (vendorData.name == 'HanetAICamera') {
                        form.find('[name="config[app_id]"]').val(vendorData.app_id);
                        form.find('[name="config[secret_key]"]').val(vendorData.secret_key);
                    }

                    // Added by Hieu Nguyen on 2021-06-08 to support CMC Cloud Camera
                    if (vendorData.name == 'CMCCloudCamera') {
                        form.find('[name="config[credentials][domain]"]').val(vendorData.domain);
                        form.find('[name="config[credentials][access_token]"]').val(vendorData.access_token);
                    }
                    // End Hieu Nguyen

                    form.find('[name="new"]').val(0);
                }
                else {
                    form.find('[name="new"]').val(1);
                    form.find('button.edit').remove();
                }

                form.vtValidate({
                    submitHandler: form => {
                        let data = $(form).serializeFormData();

                        app.helper.showProgress();

                        app.request.post({ data }).then((err, res) => {
                            app.helper.hideProgress();

                            if (err) {
                                return app.helper.showErrorNotification({ message: err.message });
                            }

                            if (!res) {
                                return app.helper.showErrorNotification({ message: app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR') });
                            }

                            // Modified by Hieu Nguyen on 2021-06-08 to support CMC Cloud Camera
                            if (mode == 'edit' || vendorData.name == 'CMCCloudCamera') {
                                app.helper.showSuccessNotification({ message: app.vtranslate('JS_AI_CAMERA_SAVE_CONFIG_SUCCESS_MSG') });
                                location.reload();
                            }
                            // End Hieu Nguyen
                            else {
                                let siteUrl = window.location.origin + window.location.pathname.replace('index.php', '');
                                let redirectUrl = siteUrl +'index.php?module=Vtiger&parent=Settings&view=ConnectHanet';
                                let loginUrl = 'https://oauth.hanet.com/oauth2/authorize?response_type=code&scope=full&client_id=' + res.app_id;
                                loginUrl += '&redirect_uri='+ encodeURIComponent(redirectUrl);
                            
                                popupCenter(loginUrl, 'Connect AI Camera', 600, 780);
                            }

                            app.helper.hideModal();
                        });
                    }
                });
            }
        });
    },
});