/*
    SocialIntegrationConfig.js
    Author: Hieu Nguyen
    Date: 2019-07-03
    Purpose: handle saving social config
*/

CustomView_BaseController_Js('Settings_Vtiger_SocialIntegrationConfig_Js', {}, {
    registerEvents: function () {
        this._super();
        this.initCustomOwnerFields();
        this.registerEventFormInit();
    },
    registerEventFormInit: function () {
		var form = jQuery('form[name="settings"]');

        form.find('.bootstrap-switch').bootstrapSwitch();

        // Modified by Phu Vo on 2019.07.16 => init select2 using class
        this.getForm().find('select.select2').select2();
        // End Phu Vo

        // Handle click event for add facebook fanpage button
        form.find('#add-fb-fanpage').on('click', function() {
            var connectFBFanpageModal = $('#connectFBFanpageModal').clone(true, true);

            var callBackFunction = function(data) {
                data.find('#connectFBFanpageModal').removeClass('hide');
                var form = data.find('.connectFBFanpageForm');

                // Init modal form
                var controller = Vtiger_Edit_Js.getInstance();
                controller.registerBasicEvents(form);
                vtUtils.applyFieldElementsView(form);

                // Form validation
                var params = {
                    submitHandler: function (form) {
                        var form = jQuery(form);

                        var siteUrl = window.location.origin + window.location.pathname.replace('index.php', '');
                        var callbackUrl = siteUrl + 'webhook.php?name=FacebookConnector&action=OauthCallback';
                        
                        form.find('.cancelLink').trigger('click');  // Dismiss modal
                        app.helper.showProgress();

                        jQuery.ajax({ 
                            url: 'webhook.php?name=FacebookConnector&action=GetOauthUrl',
                            type: 'post',
                            data: {
                                app_id: form.find('[name="fb_app_id"]').val(),
                                app_secret: form.find('[name="fb_app_secret"]').val(),
                                callback_url: callbackUrl,
                            },
                            success: (res, status) => {
                                app.helper.hideProgress();
                                
                                // Handle error
                                if (status != 'success') {
                                    app.helper.showErrorNotification({message: err.message});
                                    return;
                                }
                                
                                var loginUrl = res;
                                var popup = popupCenter(loginUrl, 'ConnectFBFanpage', 800, 780);    // Open connect url in new popup
                            }
                        });

                        return;
                    }
                };

                form.vtValidate(params);
            };

            var modalParams = {
                cb: callBackFunction
            };

            app.helper.showModal(connectFBFanpageModal, modalParams);

            return false;
        });

		// Handle click event for add zalo oa button
        form.find('#add-zalo-oa').on('click', function() {
            var connectZaloOAModal = $('#connectZaloOAModal').clone(true, true);

            var callBackFunction = function(data) {
                data.find('#connectZaloOAModal').removeClass('hide');
                var form = data.find('.connectZaloOAForm');
                var zaloAppIdInput = form.find('[name="zalo_app_id"]');

                // Init modal form
                var controller = Vtiger_Edit_Js.getInstance();
                controller.registerBasicEvents(form);
                vtUtils.applyFieldElementsView(form);

                // Form validation
                var params = {
                    submitHandler: function (form) {
                        var form = jQuery(form);

                        var siteUrl = window.location.origin + window.location.pathname.replace('index.php', '');
                        var redirectUrl = siteUrl +'webhook.php?name=ZaloConnector&state=OauthCallback&secret_key=' + _SECRET_KEY;
                        var zaloAppId = zaloAppIdInput.val();
                        var zaloConnectUrl = 'https://oauth.zaloapp.com/v3/oa/permission?app_id='+ zaloAppId;
                        zaloConnectUrl += '&redirect_uri='+ encodeURIComponent(redirectUrl);
                        
                        form.find('.cancelLink').trigger('click');  // Dismiss modal
                        var popup = popupCenter(zaloConnectUrl, 'ConnectZaloOA', 800, 780);    // Open connect url in new popup
                        
                    }
                };

                form.vtValidate(params);
            };

            var modalParams = {
                cb: callBackFunction
            };

            app.helper.showModal(connectZaloOAModal, modalParams);

            return false;
        });

        // Added by Phu Vo on 2019.07.12 to handle form submit
        this.getForm().vtValidate({
            submitHandler: (form) => {
                app.helper.showProgress();

                let data = {
                    module: 'Vtiger',
                    parent: 'Settings',
                    action: 'SaveSocialIntegrationConfig',
                    mode: 'saveSettings'
                };

                data = Object.assign(data, this.getFormSerialize(form));

                // Need to peform form data procession here

                app.request.post({data}).then((err, res) => {
                    app.helper.hideProgress();
                    
                    // handle error
                    if (err) {
                        app.helper.showErrorNotification({message: err.message});
                        return;
                    }
                    
                    // handle saving error
                    if (res !== true && !res.result) {
                        app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_SAVE_SETTINGS_ERROR_MSG')});
                        return;
                    }
                    
                    // Process res here
                    app.helper.showSuccessNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_SAVE_SETTINGS_SUCCESS_MSG')});
                });

                return;
            }
        });
        // End form submit

        // Added by Phu Vo on 2019.07.12 to handle disconnect Zalo OA
        this.getForm().on('click', 'a.disconnect.disconnect-zalo-oa', e => {
            let target = $(e.target);
            let id = this.getRowInfo(target, 'id');

            app.helper.showConfirmationBox({
                message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_ZALO_DISCONNECT_OA_CONFIRM_MSG'),
            }).then(() => {
                app.helper.showProgress();

                return app.request.post({
                    data: {
                        module: 'Vtiger',
                        parent: 'Settings',
                        action: 'SaveSocialIntegrationConfig',
                        mode: 'disconnectZaloOA',
                        id: id,
                    }
                });
            }).then((err, res) => {
                app.helper.hideProgress();

                // handle error
                if (err) {
                    app.helper.showErrorNotification({message: err.message});
                    return;
                }

                // handle saving error
                if (res !== true && !res.result) {
                    bootbox.alert({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_ZALO_DISCONNECT_OA_ERROR_MSG')});
                    return;
                }

                // Process res here

		        let form = jQuery('form[name="settings"]');
                target.closest('.channel-item').remove();
                form.find(`.oa-chat-distribution[data-oa-id="${id}"]`).remove();
                
                bootbox.alert({
                    message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_ZALO_DISCONNECT_OA_SUCCESS_MSG'),
                    callback: () => {
                        app.event.trigger('post.zaloOa.disconnect', target);
                    }
                });
            });
        });
        // End handle disconnect Zalo OA

        // Added by Phu Vo on 2019.07.16 to handle zalo oa disconnected event
        app.event.on('post.zaloOa.disconnect', (event, data) => {
            let count = $('#zalo-oa-list').find('.channel-item').length;

            if (count == 0) {
                app.helper.showProgress();

                app.request.post({
                    data: {
                        module: 'Vtiger',
                        parent: 'Settings',
                        action: 'SaveSocialIntegrationConfig',
                        mode: 'toggleZaloEnabled',
                        value: null,
                    }
                }).then((err, res) => {
                    app.helper.hideProgress();

                    // handle error
                    if (err) {
                        app.helper.showErrorNotification({message: err.message});
                        return;
                    }
                    
                    // handle saving error
                    if (res !== true && !res.result) {
                        app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_SAVE_SETTINGS_ERROR_MSG')});
                        return;
                    }
                    
                    location.reload();
                });
            }
        });
        // End zalo oa connected event

        this.getForm().on('click', 'a.syncZaloFollowerIds', e => {
            let id = this.getRowInfo(e.target, 'id');
            let status = this.getRowInfo(e.target, 'token_issue_status');

            // Check if OA is valid to sync or not
            if (status == 'expired' || status == 'not_connected') {
                app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_ZALO_OA_EXPIRED_ERROR_MSG')});
                return;
            }
            
            app.helper.showProgress();

            app.request.post({
                data: {
                    module: 'CPSocialIntegration',
                    action: 'SyncAjax',
                    mode: 'triggerSyncZaloOAFollowersIds',
                    oa_id: id,
                }
            }).then((err, res) => {
                app.helper.hideProgress();

                // handle error
                if (err) {
                    app.helper.showErrorNotification({message: err.message});
                    return;
                }
                
                if (res !== true && !res.result) {
                    app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_SYNC_ZALO_FOLLOWER_IDS_ERROR_MSG')});
                    return;
                }

                // Process res here
                app.helper.showSuccessNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_SYNC_ZALO_FOLLOWER_IDS_SUCCESS_MSG')});
            });
        });

        // Added by Phu Vo on 2020.02.11 to handle facebook disconnect fanpage
        this.getForm().on('click', 'a.disconnect.disconnect-fb-fanpage', (event) => {
            const target = $(event.target);
            const id = this.getRowInfo(target, 'id');

            app.helper.showConfirmationBox({
                message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_FACEBOOK_DISCONNECT_FANPAGE_CONFIRM'),
            }).then(() => {
                app.helper.showProgress();

                return app.request.post({
                    data: {
                        module: 'Vtiger',
                        parent: 'Settings',
                        action: 'SaveSocialIntegrationConfig',
                        mode: 'disconnectFBFanpage',
                        id
                    }
                });
            }).then((error, response) => {
                app.helper.hideProgress();

                // handle error
                if (error) {
                    return app.helper.showErrorNotification({message: error.message});
                }

                // handle saving error
                if (response !== true && !response.result) {
                    return bootbox.alert({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_FACEBOOK_DISCONNECT_FANPAGE_ERROR_MSG')});
                }

                // Process res here
                target.closest('.channel-item').remove();
                
                bootbox.alert({
                    message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_FACEBOOK_DISCONNECT_FANPAGE_SUCCESS_MSG'),
                    callback: () => {
                        app.event.trigger('post.fbFanpage.disconnect', target);
                    }
                });
            });
        });

        // Added by Phu Vo on 2020.02.11 to handle facebook fanpage disconnected event
        app.event.on('post.fbFanpage.disconnect', (event, data) => {
            const count = $('#fb-fanpage-list').find('.channel-item').length;

            if (count === 0) {
                app.helper.showProgress();

                app.request.post({
                    data: {
                        module: 'Vtiger',
                        parent: 'Settings',
                        action: 'SaveSocialIntegrationConfig',
                        mode: 'toggleFacebookEnabled',
                        value: null,
                    }
                }).then((error, response) => {
                    app.helper.hideProgress();

                    // handle error
                    if (error) {
                        return app.helper.showErrorNotification({message: error.message});
                    }
                    
                    // handle saving error
                    if (response !== true && !response.result) {
                        return app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_SAVE_SETTINGS_ERROR_MSG')});
                    }
                    
                    location.reload();
                });
            }
        });
        
        this.registerDynamicInput(this.getForm());
    },

    /**
     * Init custom owner field in form
     * @author Phu Vo (2021.01.12)
     */
    initCustomOwnerFields: function () {
        const form = this.getForm();
        const ownerFields = form.find(':input.assigned-users');
        CustomOwnerField.initCustomOwnerFields(ownerFields);
    },

    /**
     * Method to get main form
     * @author Phu Vo (2019.07.12)
     */
    getForm: function () {
        if (!this.form) this.form = $('form[name="settings"]');
        return this.form;
    },

    /**
     * Method to get Zalo OA row info
     * @param {*} dom 
     * @param {String} infoName
     * @author Phu Vo (2019.07.12) 
     */
    getRowInfo: function (dom, infoName = '') {
        if (!(dom instanceof jQuery)) dom = $(dom);
        if(!dom.is('.row.info')) dom = dom.closest('.row.info');
        if(!dom.is('.row.info')) dom = dom.find('.row.info');

        let data = dom.data('row-info');

        if (infoName && data) return data[infoName];
        return data;
    },

    /**
     * Method to process form data for submission
     * @param {*} form 
     * @author Phu Vo (2019.07.12) 
     */
    getFormSerialize: function (form) {
        if (!(form instanceof jQuery)) form = $(form);
        let data = form.serializeFormData();

        for (let name in data) {
            let selector = `[name="${name}"]`;

            // Process for dynamic-input-data
            if (this.getForm().find(selector).hasClass('dynamic-input-data')) {
                data[name] = JSON.parse(data[name]);
            }

            // Process checkbox case to save 1 value for on
            if (
                this.getForm().find(selector).attr('type') == 'checkbox'
                && data[name] == 'on'
            ) {
                data[name] = '1';
            }

            // Walk around in case select multiple save with only one value

            if (this.getForm().find(selector).is('select')) {
                data[name] = this.getForm().find(selector).val();
            }
        }

        return data;
    },

    registerDynamicInput: function (form) {
        form.find('.dynamic-input').each((index, target) => {
            const container = $(target);
            const dynamicInputData = container.find('.dynamic-input-data');
            const dynamicInputAdd = container.find('.dynamic-input-add');

            // Local utils
            const getArrayValueFromElement = (element) => {
                if (!(element instanceof jQuery)) element = jQuery(element);
                let value = element.val();

                if (!value || value == 'null') return [];

                value = JSON.parse(value);
                if (!(value instanceof Array)) {
                    value = value.split(',');
                }
                value = value.filter((single) => single != null && single != 'null');

                return value;
            }
            const setArrayValueToElement = (element, value) => {
                if (!(element instanceof jQuery)) element = jQuery(element);

                if (!(value instanceof Array)) {
                    value = value.split(',');
                }
                value = JSON.stringify(value);

                element.val(value);

                return element;
            }

            // Regiter handling render keywords logic
            dynamicInputData.on('change', (event) => {
                let keywords = getArrayValueFromElement(event.target);
                let keyWordsDom = $([]);

                keywords.forEach((keyword) => {
                    if (!keyword) return;

                    let removeTitle = app.vtranslate('CPSocialIntegration.JS_REMOVE_KEYWORD');
                    let domString = `
                        <span class="keyword" data-keyword="${keyword}" title="${keyword}">
                            ${keyword} <span class="remove" title="${removeTitle}">x</span>
                        </span>
                    `.trim();

                    keyWordsDom = keyWordsDom.add($(domString));
                });

                // Assign new dom using replace
                container.find('.keywords').html(keyWordsDom);

                // Init event handler for new dom elements
                container.find('.keyword .remove').on('click', (event) => {
                    let keywords = getArrayValueFromElement(dynamicInputData);
                    let toRemove = $(event.target).closest('.keyword').data('keyword');

                    keywords = keywords.filter((keyword) => '' + keyword !== '' + toRemove);

                    // Assign new keyword and trigger render ui logic
                    setArrayValueToElement(dynamicInputData, keywords).trigger('change');
                });
            }).trigger('change');

            // Event on add more keyword input
            dynamicInputAdd.on('keydown', (event) => {
                // Handle input adding more keyword by press enter event
                if (event.keyCode === 13) {
                    let keywords = getArrayValueFromElement(dynamicInputData);
                    let newKeyword = dynamicInputAdd.val().trim();

                    // Return when new keyword already exists
                    if (keywords.includes(newKeyword)) {
                        // Clear input
                        dynamicInputAdd.val('');

                        // Prevent auto submit
                        event.preventDefault();

                        return;
                    }

                    // Assign new keyword and trigger render ui logic
                    keywords.push(newKeyword);
                    setArrayValueToElement(dynamicInputData, keywords).trigger('change');

                    // Clear input
                    dynamicInputAdd.val('');

                    // Prevent auto submit
                    event.preventDefault();
                }
            });
        });
    }
});

function handleConnectFBFanpageResult(popup, success) {
    popup.close();

    setTimeout(() => {
        if(success) {
            bootbox.alert({
                message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_CONNECT_FB_FANPAGE_SUCCESS_MSG'),
                callback: () => {
                    let count = $('#fb-fanpage-list').find('.channel-item').length;

                    if (count == 0) {
                        app.helper.showProgress();

                        app.request.post({
                            data: {
                                module: 'Vtiger',
                                parent: 'Settings',
                                action: 'SaveSocialIntegrationConfig',
                                mode: 'toggleFacebookEnabled',
                                value: 1,
                            }
                        }).then((err, res) => {
                            app.helper.hideProgress();

                            // Handle error
                            if (err) {
                                app.helper.showErrorNotification({message: err.message});
                                return;
                            }

                            // Handle saving error
                            if (res !== true && !res.result) {
                                app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_SAVE_SETTINGS_ERROR_MSG')});
                                return;
                            }
                            
                            location.reload();
                        });
                    }
                    else {
                        location.reload();
                    }
                }
            });
        }
        else {
            bootbox.alert(app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_CONNECT_FB_FANPAGE_ERROR_MSG'));
        }
    }, 100);
}

function handleConnectZaloOAResult(popup, success) {
    popup.close();

    setTimeout(() => {
        if(success) {
            bootbox.alert({
                message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_CONNECT_ZALO_OA_SUCCESS_MSG'),
                callback: () => {
                    let count = $('#zalo-oa-list').find('.channel-item').length;

                    if (count == 0) {
                        app.helper.showProgress();

                        app.request.post({
                            data: {
                                module: 'Vtiger',
                                parent: 'Settings',
                                action: 'SaveSocialIntegrationConfig',
                                mode: 'toggleZaloEnabled',
                                value: 1,
                            }
                        }).then((err, res) => {
                            app.helper.hideProgress();

                            // handle error
                            if (err) {
                                app.helper.showErrorNotification({message: err.message});
                                return;
                            }

                            // handle saving error
                            if (res !== true && !res.result) {
                                app.helper.showErrorNotification({message: app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_SAVE_SETTINGS_ERROR_MSG')});
                                return;
                            }
                            
                            location.reload();
                        });
                    }
                    else {
                        location.reload();
                    }
                }
            });
        }
        else {
            bootbox.alert(app.vtranslate('CPSocialIntegration.JS_SOCIAL_CONFIG_CONNECT_ZALO_OA_ERROR_MSG'));
        }
    }, 100);
}

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

    // Puts focus on the newWindow
    if (window.focus) newWindow.focus();

    return newWindow;
}