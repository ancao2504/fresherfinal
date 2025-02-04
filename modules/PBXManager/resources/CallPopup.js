/**
 * Module: Call Center UI Controller version 2.0
 * Caution: Optimized for vtiger framework
 * and may consumes a huge effort if anyone tries to apply it to sugarcrm
 * Version: 2.1
 * Author: Phu Vo
 * Date: 2019.12.19
 * Update: 2020.02.17
 */

(function () {
    // Module variable
    const MODULE = 'PBXManager';

    /**
     * Utils method, data to handle datatable
     */
    const DataTableUtils = {
        languages: {
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
                next: app.vtranslate('JS_DATATABLES_PAGINATE_NEXT_PAGE'),
                previous: app.vtranslate('JS_DATATABLES_PAGINATE_PREVIOUS_PAGE')
            }
        }
    }

    /**
     * Include util method to handle tab on UI
     */
    const CallTabs = new class {
        init (element, params = {}) {
            element.each((index, target) => {
                $(target).find('.call-tab').each((index, tab) => {
                    $(tab).click(() => {
                        const key = $(tab).data('tab');
                        const group = $(tab).closest('.call-tabs').data('tabs');
                        const tabGroup = $(target).find(`.call-tabs[data-tabs="${group}"]`);
                        const contentGroup = $(target).find(`.call-tab-content[data-tabs="${group}"]`);

                        // Processing tab start
                        tabGroup
                            .find('.call-tab')
                            .not(`[data-tab="${key}"]`)
                            .toggleClass('active', false);

                        tabGroup
                            .find(`.call-tab[data-tab="${key}"]`)
                            .toggleClass('active', true);
                        // Processing tab end

                        // Processing content start
                        contentGroup
                            .find('.call-tab-pane')
                            .not(`[data-tab="${key}"]`)
                            .toggleClass('active', false);

                        contentGroup
                            .find(`.call-tab-pane[data-tab="${key}"]`)
                            .toggleClass('active', true);
                        // Processing content end

                        // Process side logic start
                        const data = {
                            element: $(target),
                            group: tabGroup,
                            tab: tabGroup.find(`.call-tab[data-tab="${key}"]`),
                            tabPane: contentGroup.find(`.call-tab-pane[data-tab="${key}"]`),
                            active: key,
                        };

                        if (typeof params.postUpdate === 'function') params.postUpdate(data);
                        // Process side logic end
                    });
                });

                // Trigger active
                $(target).find('.call-tab.active').trigger('click');
            });
        }
    }

    const AjaxSelect2 = new class {
        /**
         * Init Ajax select 2 method call to this module ajax action
         * @param {*} element `params.placeholder` `params.mode`
         * @param {*} params
         */
        init(element, params) {
            element.each((index, target) => {
                $(target).select2({
                    placeholder: params.placeholder,
                    minimumInputLength: _VALIDATION_CONFIG.autocomplete_min_length,
                    closeOnSelect: false,
                    tags: [],
                    tokenSeparators: [','],
                    ajax: {
                        type: 'POST',
                        url: 'index.php',
                        dataType: 'json',
                        data: function (term, page) {
                            term = term.trim();

                            // Skip ajax request when user enter only spaces
                            if (term.length == 0) {
                                $(target).select2('close');
                                $(target).select2('open');
                                return null;
                            }

                            const data = {
                                module: MODULE,
                                action: 'CallPopupAjax',
                                mode: params.mode,
                                targetModule: params.targetModule,
                                keyword: term
                            };

                            return data;
                        },
                        results: function (data) {
                            return { results: data.result };
                        },
                        transport: function (params) {
                            return jQuery.ajax(params);
                        }
                    }
                });
            });
        }
    }

    /**
     * Some Basic helper
     */
    const Utils = new class {
        /**
         * Remove HTML entity from string
         * @param {String} text
         */
        HTMLParse (text = '') {
            let parser = new DOMParser();
            let dom = parser.parseFromString(text, 'text/html');

            return dom.body.textContent;
        }

        /**
         * Update value if element is form element and update inner html if not
         * @param {jQuery} element
         * @param {String} value
         */
        updateValue (element, value) {
            // First, we will support update title within element if needs
            if (element.data('ui-title')) {
                element.attr('title', this.HTMLParse(value));
            }

            // Then update value base on what element type is
            if (this.isFormElement(element)) {
                return element.val(value);
            }

            // Process with case image
            if (element.is('img')) return element.attr('src', value);

            // Or default just update it inner html
            return element.html(value);
        }

        /**
         * Return true if element is a form element
         * @param {jQuery} element
         */
        isFormElement (element) {
            if (element.is('input')) return true;
            if (element.is('select')) return true;
            if (element.is('textarea')) return true;

            return false;
        }

        isResError (err, res) {
            // Process timeout session
            if (this.isLoginPage($(res)) || res === 'Invalid request') {
                window.onbeforeunload = null;
                window.location.reload();
                return true;
            }

            return err || !res;
        }

        /**
         * Simple pad helper
         * @param {*} n
         * @param {*} width
         * @param {*} z
         */
        pad (n, width, z) {
            z = z || '0';
            n = n + '';
            return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
        }

        /**
         * Vtiger Apply Field Element View Wrapper
         * @param {jQuery} element
         */
        applyFieldElementsView (element) {
            // Our template contain some form element class, so even it hidden from user
            // it still apply field element view and have some unpredictable bahavior
            // => We will remove these element before apply our own element when call popup was cloned
            element.find('.select2-container').remove();
            vtUtils.applyFieldElementsView(element);

            // Init Dom Controller
            Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(element.find('form'));
        }

        /**
         * Handle call ajax view request
         * @param {Object} params
         */
        ajaxView (params) {
            const defaultParams = {
                module: MODULE,
                view: 'CallPopupAjax',
                mode: 'relatedListView',
            }

            params = Object.assign(defaultParams, params);

            return app.request.post({ data: params });
        }

        /**
         * Return true if response is a login page (session timeout)
         * @param {jQuery} container
         */
        isLoginPage (container) {
            return Boolean(container.find('#loginFormDiv')[0]);
        }

        /**
         * Util method to update elements data and data attribute
         * @param {jQuery} element
         * @param {String} key
         * @param {String} value
         */
        updateData (element, key, value) {
            element.data(key, value);
            element.attr(`data-${key}`, value);
        }

        /**
         * Display an error notification
         * @param {Object} err
         */
        errorMessage (err) {
            const message = app.vtranslate('PBXManager.JS_CALL_POPUP_AJAX_ACTION_ERROR');
            return app.helper.showErrorNotification({ message: err ? (err.message ||  message) : message});
        }
    }

    /**
     * Call Popup UI Handler
     * DO NOT EDIT THIS CLASS UNLESS YOU ARE CREATOR OR MAINTAINER
     * CUSTOMIZE CODE PLACE IS AT THE END OF THE FILE
     */
    class BaseCallPopupHandler {
        /**
         * Invoke when CallCenter singleton created
         */
        constructor () {
            this.$list = {};
        }

        /**
         * This method is going to change some request data value with javascript pass by reference
         * @param {*} request
         * @return {void}
         */
        parseRequest (request) {
            // All request direction is upper case
            request.direction && (request.direction = request.direction.toUpperCase());

            // All request state is upper case
            request.state && (request.state = request.state.toUpperCase());
        }

        /**
         * Method to create or update call popup
         * @param {Object} request
         * @return {String} Call Id
         */
        newState (request) {
            // Clone to new Object before do anything
            request = Object.assign({}, request);
            const isNew = !this.$list[request.call_id];

            // Transfer to usable value
            this.parseRequest(request);

            // May perform side effect here
            this.preProcessRequest(request, isNew);

			// Added by Vu Mai on 2022-10-19 to handle data changed event
			if (request.state == 'DATA_CHANGED') {
				this.handleEventDataChanged(request);
			}
			// End Vu Mai

			// Added by Vu Mai on 2022-11-03 to enable auto insert tab when module is CPTelesale and view is Telesales in ADDITION_INFO state
			if (request.state == 'ADDITION_INFO') {
				request.auto_insert_tab = false;

				if (request.addition_info.target_view == 'Telesales') {
					request.auto_insert_tab = true;
				}
			}
			// End Vu Mai

			// Added by Vu Mai on 2022-11-09 to handle state COMPLETED with event after save call log
			if (request.state == 'COMPLETED' && request.event == 'AFTER_SAVE_CALL_LOG') {
				this.$list[request.call_id].reloadCustomerListInTelesalesCampaign(request);
			}

            // Validate request
            if (!this.validateRequest(request, isNew)) return;

            // Remove processing popup when we have a new popup with regular state
            if (isNew && request.call_id && this.$list['PROCESSING']) {
                this.$list['PROCESSING'].update({ state: 'COMPLETED' });
            }

            // Create or update call popup
            if (isNew) {
                if (request.state !== 'COMPLETED') {
                    this.$list[request.call_id] = new Popup(request);
                }
            }
            else if (request.state === 'COMPLETED') {
                this.removeCallPopup(request.call_id);
            }
            else { // Update
                this.$list[request.call_id].update(request);
            }

            // Return result
            return request.call_id;
        }

		// Added by Vu Mai on 2022-10-19 to impletment function in popup instance have customer_id matching with request
		handleEventDataChanged (data) {
			let self = this;

			$.each(self.$list, function( index, value ) {
				if (value.props.customer_id == data.customer_id) {
					self.$list[value.props.call_id].updateChangedData(data);
				}
			});
		}
		// End Vu Mai

        /**
         * Method invoke every call request
         * @param {*} request
         * @param {Boolean} isNew
         */
        preProcessRequest (request, isNew = true) {
            // Default state ringing for new popup
            if (!request.state && isNew) request.state = 'RINGING';

            // Prevent update duration with empty value
            if (!request.duration) delete request.duration;

            // popup without call_id will tranform in to a special form of popup:
            // processing popup with difference behavior
            if (request.call_id === 'PROCESSING' && !request.state && isNew) request.state = 'PROCESSING';

            // Popup have trafer call functionality on or off
            if (window._CALL_CENTER_CAN_TRANSFER_CALL == true && typeof request.transfer_call == 'undefined') {
                request.transfer_call = true;
            }

            // Process webphone behavior
            if (typeof CallCenterClient.webPhone !== 'undefined') {
                if (typeof CallCenterClient.webPhone.answerCall === 'function') request.webphone_answer_supported = true;
                if (typeof CallCenterClient.webPhone.muteIncomingCall === 'function') request.webphone_mute_supported = true;
                
                if (request.direction == 'INBOUND' && typeof CallCenterClient.webPhone.rejectCall === 'function') {
                    request.webphone_reject_supported = true;
                }
                
                if (typeof CallCenterClient.webPhone.hangupCall === 'function') {
                    request.webphone_hangup_supported = true;
                }
            }
        }

        /**
         * Method handle request validation, return `false` and call creating will stop
         * @param {*} request
         * @param {Boolean} isNew
         */
        validateRequest (request, isNew) {
            // NO ID = NO MORE PAIN
            if (!request.call_id) return;

            // NO direction = GET OUT
            if (isNew && !request.direction) return;

            // [START] Validate each direction creation
            if (!this.$list[request.call_id]) {
                if (typeof this.validateInboundRequest === 'function' && request.direction === 'INBOUND') {
                    if (!this.validateInboundRequest(request, isNew)) return;
                }
                if (typeof this.validateOutboundRequest === 'function' && request.direction === 'OUTBOUND') {
                    if (!this.validateOutboundRequest(request, isNew)) return;
                }
            }
            // [END] Validate each direction creation

            return true;
        }

        /**
         * This method will send completed signal to call center bridge and remove local call
         * @param {*} callId
         */
        notifyCompletedCall (callId) {
            window.CallCenterClient && window.CallCenterClient.notifyCompletedCall(callId);
            this.removeCallPopup(callId);
        }

        /**
         * This method will remove call with input callId
         * @param {*} callId
         */
        removeCallPopup (callId) {
            if (callId === 'all') {
                this.$list.forEach((call, id) => {
                    call.destruct();
                    delete this.$list[id];
                });
            }
            else {
                this.$list[callId] && this.$list[callId].destruct();
                delete this.$list[callId];
            }
        }

        /**
         * This method return true if call popup system has
         * @param {*} callId
         */
        has (callId) {
            return this.$list.hasOwnProperty(callId);
        }
    }

    /**
     * Bass class to all Popup
     * DO NOT MODIFY IT IF YOU WANT YOUR LIFE EASIER
     */
    class BasePopup {
        /**
         * Invoke every time new object created
         * @param {Object} props
         */
        constructor (props) {
            this.$el = this.getTemplate(props);

            // Save time out here to clear when destruct
            this.$timeouts = [];

            // Props may we have
            this.preProps = {}

            // Current props
            this.props = {}

			// Added by Vu Mai on 2022-10-19 for Current tag
			this.customTag = null;

			// Added by Vu Mai on 2022-10-19 for Current comment
			this.customComment = null;

            // Invoke process ui logic
            this.update(props);
            this.initEvents();
            this.initEventsByWebPhone();
            this.render();
        }

        /**
         * Method that update data and ui
         * @param {Object} props
         * @return {void}
         */
        update (props) {
            // parse input
            this.parseProps(props);

            // Update into props
            this.props = Object.assign(this.props, props);

            // Compare change with preProps, end if no changed at all
            if (JSON.stringify(this.props) === JSON.stringify(this.preProps)) return;

            // Generate diff using shallow compare technique
            const diff = {};
            for (const key in props) {
                if (props[key] != this.preProps[key]) diff[key] = props[key];
            }

            // Update ui with diff
            // Modified by Vu Mai on 2022-09-15 to only update state = RINGING when state is ADDITION_INFO
			for (const key in diff) {
				if (key == 'state' && props[key] == 'ADDITION_INFO') {
					diff[key] = 'RINGING';
				}

				this.updateUi(key, diff[key]);
			}
			// End Vu Mai

			// Added by Vu Mai on 2022-11-03 to implement auto insert tab when popup open first time and mode auto insert tab is enable
			if (this.props.state == 'ANSWERED' && this.props.auto_insert_tab == true) {
				this.registerEventAutoInsertTab();

				// Prevent popup call auto insert tab in many time
				this.props.auto_insert_tab = false;
			}
			// End Vu Mai

			// Added by Vu Mai on 2022-11-08 to include campaign id hidden input to call log form
			if (this.props.state == 'ADDITION_INFO' && this.props.addition_info.target_module == 'CPTelesales' &&  this.$el.find('form[name="call_log"] input[name="campaign_id"]').length == '0') {
				let campaignId = this.props.addition_info.target_record_id;
				this.$el.find('form[name="call_log"]').prepend(`<input type="hidden" name="campaign_id" value="${campaignId}">`);
			}

            if (this.props.state == 'ADDITION_INFO' && this.props.addition_info.target_module != 'CPTelesales') {
                let targetRecordId = this.props.addition_info.target_record_id;
                let targetModule = this.props.addition_info.target_module;

                this.$el.find('form[name="call_log"]').prepend(`<input type="hidden" name="target_record_id" value="${targetRecordId}">`);
				this.$el.find('form[name="call_log"]').prepend(`<input type="hidden" name="target_module" value="${targetModule}">`);
            }
			// End Vu Mai
        }

		// Added by Vu Mai on 2022-10-19 to update data changed to popup call
		updateChangedData (data) {
            let self = this;

			if (data['data_type'] == 'CUSTOMER_INFO') {
				this.$el.find('.info-name a').text(data.extra_data.info_name);
				this.$el.find('.info-name').attr('title', data.extra_data.info_name);
				this.$el.find('.customer-number').text(data.extra_data.customer_number);

				this.$el.find('.assign-name a').text(data.extra_data.assigned_user_name).attr('href', this.getUserorGroupDetailUrl(data.extra_data.assigned_user_type, data.extra_data.assigned_user_id));
				this.$el.find('.assign-name').attr('title', data.extra_data.assigned_user_name);

				if (data.extra_data.assigned_user_ext == null || data.extra_data.assigned_user_ext == 'phone_crm_extension') {
					this.$el.find('.ext-num').text('');
				}
				else {
					this.$el.find('.ext-num').text(data.extra_data.assigned_user_ext);
				}

				if (this.props.customer_type == 'Contacts') {
					this.$el.find('.info-company').attr('title', data.extra_data.account_name);
					this.$el.find('.info-company a').text(data.extra_data.account_name).attr('href', this.getAccountDetailUrl(data.extra_data.account_id));;
				}
				
			}

			if (data['data_type'] == 'LINKED_TAG') {
				this.customTag.loadTagList(this.$el.find('.custom-tag'));
			}

			if (data['data_type'] == 'RELATED_COMMENTS') {
				this.customComment.renderParentComments();
			}

            if (data['data_type'] == 'CUSTOMER_CONVERTED') {
				this.props.customer_id = data.extra_data.customer_id;
				this.props.customer_type = data.extra_data.customer_type;

				// Update customer type and id in call log form
				this.$el.find('form[name="call_log"] input[name="customer_id"]').val(this.props.customer_id).trigger('change');
				this.$el.find('form[name="call_log"] input[name="customer_type"]').val(this.props.customer_type).trigger('change');

                // Reassign customer_id, customer_type to CustomTag element in popup call
                this.$el.find('.custom-tag').attr('data-customer-id', this.props.customer_id);
                this.$el.find('.custom-tag').attr('data-customer-type', this.props.customer_type);

                // For custom tag
                this.customTag.customerId = this.props.customer_id;
                this.customTag.customerType = this.props.customer_type;

                // Update customer detail url
                let detailUrl = `index.php?module=Contacts&view=Detail&record=${this.props.customer_id}`;
                this.$el.find('.info-name a').attr('href', detailUrl);

                // Reload customer info form according to new record type
                let params = {
                    module: this.props.customer_type,
                    view: 'QuickEditAjax', 
                    mode: 'edit',
                    record: this.props.customer_id
                };
    
                app.request.post({ data: params })
                .then(function (err, data) {
                    if (err) {
                        app.helper.showErrorNotification({ message: err.message });
                        return;
                    }

                    // Replace form in customer info tab content
                    var container = jQuery('<div id="customer-info" class="tab-pane">'+ data +'</div>');
                    container.find('.modal-header').remove();
                    container.find('form').attr('name', 'customer-info');
                    self.$el.find('.right-side .tab-content #customer-info').remove();
                    self.$el.find('.right-side .tab-content').append(container);
    
                    // Init quick edit form
                    let form = self.$el.find(`form[name="customer-info"]`);
                    let quickedit = new BaseQuickEdit();
                    quickedit.registerEvent(form);
    
                    let submitCallBack = () => {
                        form.find('button[name="saveButton"]').attr('disabled', true);  // Disabled button submit to prevent submit multiple time
                    }
    
                    let saveCallBack = (err, res) => {
                        form.find('button[name="saveButton"]').removeAttr('disabled');  // Enable button submit after save process finished
                        if (err) return;
                    };
    
                    quickedit.registerSubmitEvent(submitCallBack, saveCallBack);
    
                    // Handle tab cancel event
                    self.cancelPopupInfo(form, self.$el);
                });
			}
		}

		// Added by Vu Mai on 2022-11-09 to reload customer list and status amount in Telesales Campaign View
		reloadCustomerListInTelesalesCampaign (data) {
			if (this.$el.closest('body').find('#telesales-page #customer-status-container').length != '0') {
				// Minus current status amount value
				let targetCurrentStatus = this.$el.closest('body').find(`#telesales-page #customer-status-container .customer-status[data-status="${data.current_status}"]`);
				let currentStatusAmount = Number.parseInt(targetCurrentStatus.find('.amount').attr('data-amount')) - 1;
				targetCurrentStatus.find('.amount').attr('data-amount', currentStatusAmount).html(`(${currentStatusAmount})`);

				// Plus updated status amount value
				let targetUpdatedStatus = this.$el.closest('body').find(`#telesales-page #customer-status-container .customer-status[data-status="${data.updated_status}"]`);
				let updatedStatusAmount = Number.parseInt(targetUpdatedStatus.find('.amount').attr('data-amount')) + 1;
				targetUpdatedStatus.find('.amount').attr('data-amount', updatedStatusAmount).html(`(${updatedStatusAmount})`);
			}

			if (this.$el.closest('body').find('#telesales-page #list-content').length != '0') {
				let position = this.$el.closest('body').find('#telesales-page #list-content #table-content').scrollTop();
				this.$el.closest('body').find('#telesales-page #list-content .listview-table .list-search').trigger('click');
			}

            // Update statistic
			if (this.$el.closest('body').find('#telesales-page #call-statistics').length != '0') {
				this.$el.closest('body').find('#telesales-page #call-statistics select.statistics-filter').trigger('change');
			}
		}

		// Added by Vu Mai on 2022-10-19 to get user or group detail url
		getUserorGroupDetailUrl (type, id) {
			if (id && type === 'Groups') {
				return `index.php?module=Groups&parent=Settings&view=Detail&record=${id}`;
			}

			if (id) {
				return `index.php?module=Users&parent=Settings&view=Detail&record=${id}`;
			}
		}

		// Added by Vu Mai on 2022-10-19 to get account detail url
		getAccountDetailUrl (id) {
			if (id) {
				return `index.php?module=Accounts&view=Detail&record=${id}`;
			}
		}

        /**
         * New props become preProps
         * @param {Object} newProps
         */
        updatePreProps (newProps) {
            this.preProps = Object.assign(this.preProps, newProps);
        }

        /**
         * Reset all resources, reset status and remove this call
         */
        destruct () {
            this.closeSyncCustomerInfoPopup();
            this.closeTransferCallModal();
            this.$el.remove();
            this.clearTimers(true);
            window.onbeforeunload = function () {};
            delete this;
        }

        /**
         * Trigger call render logic
         * @return {void}
         */
        render () {
            // Prepare event handler for call tabs
            const tabHandler = this.callTabEventHandler.bind(this);

            // Active call tabs
            CallTabs.init(this.$el, { postUpdate: tabHandler });

            // Apply Vtiger Form Field Element
            Utils.applyFieldElementsView(this.$el);

            this.registerCallBackLaterTimeFields();

            // Init call log basic validate rules
            this.$el.find('form[name="call_log"]').vtValidate();

            // Render UI
            $('#callCenterContainer .call-popups').append(this.$el);

            // Init Main form Ajax Select2
            this.initMainFormAjaxSelect2();

            // Prevent from refresh web
            window.onbeforeunload = function () {
                return false;
            };

            // Active UI ready for use (will trigger an animation though)
            setTimeout(() => this.$el.toggleClass('active', true), 1);
        }

        /**
         * Use to transform input data
         * @param {Object} props
         */
        parseProps (props) {
            // Transfer to upper case
            props.stage && (props.stage = props.stage.toUpperCase());
            props.size && (props.size = props.size.toUpperCase());
        }

        /**
         * Return call template from html dom
         * @param {Object} props
         * @return {jQuery}
         */
        getTemplate (props) {
            return $('#callTemplate').clone().attr('id', `call-${props.call_id}`);
        }

        /**
         * Local method to update Ui, may use locally to force update some props binding
         * @return {void}
         */
        updateUi (key, value) {
            let rawValue = value;
            let update = true;
            value = this.parseValue(key, value);

            if (typeof this.propHooks[key] === 'function') {
                update = this.propHooks[key].bind(this)(value, rawValue);
            }
            if (this.$el.find(`[data-ui="${key}"]`)[0]) {
                update && this.updateElement(this.$el.find(`[data-ui="${key}"]`), value, rawValue);
            }

			// Added by Vu Mai by Vu Mai on 2022-10-05 to set data-target-module for current popup call
			if (key == 'addition_info' && value.target_module != undefined) {
				this.$el.attr('data-target-module', value.target_module);

                // set rule required for call result field in call log form if target module is CPTelesales
                if (value.target_module == 'CPTelesales') {
                    let callResultLabel = this.$el.find('form.callLog .call_result_label');
                    let callResultSelect = this.$el.find('form.callLog select[name="events_call_result"]');

                    if (callResultLabel.find('.redColor').length == 0) {
                        callResultLabel.append('<span class="redColor">*</span>');
                    }

                    if (callResultSelect.attr('data-rule-required') != true) {
                        callResultSelect.attr('data-rule-required', true);
                    }
                }
			}
			// End Vu Mai

            // Added by Vu Mai by Vu Mai on 2022-10-18 to
			if (key == 'customer_info') {
				this.updateUi.bind(this)('customer_name', value.full_name);
                // Update customer phone
                this.updateUi.bind(this)('customer_number', value.customer_number);
			}
			// End Vu Mai

            this.updatePreProps({[key]: rawValue});
        }

        /**
         * Method use to handle prop display value
         * @param {*} key Prop key
         * @param {*} value Prop value
         * @return {*} Anything
         */
        parseValue (key, value) {
            if (typeof this.propValueConverters[key] === 'function') {
                return this.propValueConverters[key].bind(this)(value);
            }

            return value;
        }

        /**
         * Trigger basic Update element mechanism
         * @param {jQuery} element jQuery element
         * @param {*} value
         * @param {*} rawValue
         */
        updateElement (element, value, rawValue) {
            element.each((index, target) => {
                target = $(target);

                if (target.data('parser')) {
                    const parser = target.data('parser');

                    if (typeof this.parsers[parser] === 'function') {
                        value = this.parsers[parser].bind(this)(value, rawValue);
                    }
                }

                // Update value
                Utils.updateValue(target, value);
            });
        }

        /**
         * Local function to init event handler
         */
        initEvents () {
            // Added by Hieu Nguyen on 2022-10-05
            let currentPageModule = app.getModuleName();
            let currentPageView = app.getViewName();
            let currentPageRecord = (currentPageView == 'Edit' || currentPageView == 'Detail') ? app.getRecordId() : '';
            // End Hieu Nguyen

            // Call basic commands
            this.$el.find('button[name="close"]').on('click', () => {
                app.helper.showConfirmationBox({
                    message: app.vtranslate('PBXManager.JS_CALL_POPUP_CLOSE_CALL_POPUP_CONFIRM'),
                }).then(() => {
                    this.update({ state: 'COMPLETED'});
                });
            });

            this.$el.find('button[name="minimize"]').on('click', () => {
                this.update({ size: 'SMALL' });
            });

            this.$el.find('button[name="maximize"]').on('click', () => {
                this.update({ size: 'LARGE' });
            });

            this.$el.find('button[name="normalmize"]').on('click', () => {
                this.update({ size: 'MEDIUM' });
            });

            this.$el.find('button[name="restore"]').on('click', () => {
                this.update({ size: this.props.restoreSize });
            });

            // Init a timeout to check, if couldn't receive new status then active urgent close button
            this.$timeouts.push(setTimeout(() => {
                this.$el.find('button[name="close"]').toggleClass('active', true);
            }, 30000));

            // Allow close button allway active on "Processing" popup
            if (this.props.call_id === 'PROCESSING') this.$el.find('button[name="close"]').toggleClass('active', true);

            // Init a timeout to automatically close "Processing" popup after 15s
            if (this.props.call_id === 'PROCESSING') this.$timeouts.push(setTimeout(() => {
                const replaceParams = { customer_name: this.props.customer_name, customer_number: this.props.customer_number };
                let msgKey = 'PBXManager.JS_CALL_POPUP_PROCESSING_OUTBOUND_FAILED_ERROR_MSG';

                if (!this.props.customer_name) msgKey = 'PBXManager.JS_CALL_POPUP_PROCESSING_OUTBOUND_TO_NUMBER_FAILED_ERROR_MSG';
                app.helper.showErrorNotification({ message: app.vtranslate(msgKey, replaceParams) }, { delay: 5000 });

                this.update({ state: 'COMPLETED'});
            }, 30000));

            // Save Call Log Actions
            this.$el.find('button[name="save_call_log"]').on('click', () => {
                this.saveCallLog();
            });

            this.$el.find('button[name="save_call_log_with_ticket"]').on('click', () => {
                this.saveCallLog(true);
            });

            // Handle Quick Create Button
            this.$el.find('.quickCreateBtn').on('click', event => {
                const module = $(event.currentTarget).data('module');
                const inventoryModules = JSON.parse($('#inventoryModules').val());

                if (inventoryModules.includes(module)) {
                    return this.openInventoryEditView(module);
                }

                if (this.props.customer_type === 'Leads' && module === 'Potentials') {
                    return this.openQuickCreatePotentialForLeadPopup();
                }

                return this.openQuickCreatePopup($(event.currentTarget));
            });
            // End Handle Quick Create Button

            // Toggle Call Back Time Other element
            this.$el.find('[name="call_back_time_other"]').on('change', event => {
                const value = $(event.currentTarget).is(':checked');
                this.$el.find('.activeBaseOnTimeOther').find('input').attr('disabled', !value);
                this.$el.find('.disableBaseOnTimeOther').find('input, select').attr('disabled', value);
            }).trigger('change');

            // Trigger Call Timmer

            // START Handle on change event
            this.$el.find('[data-onchange]').each((index, target) => {
                $(target).on('change', event => {
                    const key = $(event.currentTarget).data('onchange');
                    const value = $(event.currentTarget).val();
                    const data = { [key]: value };

                    this.update(data);
                });
            });
            // END Handle on change event

            // START Handle Create Customer Popup
            this.$el.find('.createCustomer').on('click', event => {
                this.openSyncCustomerInfoPopup();
            });
            // End Phu Vo

            // [START] Init event handler for faq tab
            this.initFaqTabEventHandlers();
            // [END] Init event handler for faq tab

            // Register click event for open faq full search modal
            this.$el.find('.openFaqFullSearchPopup').on('click', () => {
                this.openFaqFullSearchModal();
            });

            // Register handle show hide event call purpose other
            this.$el.find('[name="events_call_purpose"]').on('change', (e) => {
                this.$el.find('.toggleOnPurposeOther').toggle($(e.target).val() === 'call_purpose_other');
            }).trigger('change');

            // Register handle show hide event inbound call purpose other
            this.$el.find('[name="events_inbound_call_purpose"]').on('change', (e) => {
                this.$el.find('.toggleOnInboundPurposeOther').toggle($(e.target).val() === 'inbound_call_purpose_other');
            }).trigger('change');

            this.initCallTitleBaseOnCallPurpose();

            this.initFreeCallWarningTooltip();

            this.$el.find('.showTransferCallModal').on('click', () => {
                this.openTransferCallModal();
            });

            // Manual trigger hotline empty info
            if (!this.props.hotline) this.update({ hotline: '' });

            // Register event handler for account logic
            if (this.props.customer_type == 'Accounts') {
                this.$el.find('.relatedContactId :input[name="contact_id"]').on(Vtiger_Edit_Js.postReferenceSelectionEvent, (e) => {
                    let value = $(e.target).val();
                    if (value) this.loadContactInformation(value)
                });

                this.$el.find('.relatedContactId .clearReferenceSelection').on(Vtiger_Edit_Js.referenceDeSelectionEvent, (e) => {
                    this.updateCustomerInformation(false);
                });
            }

            // Added by Hieu Nguyen on 2022-10-04 to do work-arround to support open send message popup outside DetailView
            this.$el.find('.btn-send-msg a').on('click', (e) => {
                let targetButton = $(e.target);
                let actionUrl = targetButton.data('actionUrl') || '';    // Need to init this value or it will fail when access this variable later

                // Fill module name in action url if needed
                if (actionUrl && actionUrl.indexOf('_MODULE_') >= 0) {
                    actionUrl = actionUrl.replace('_MODULE_', this.props.customer_type);
                }

                // Util function to set record id to current page
                let setRecordId = function (recordId) {
                    $('#recordId').val(recordId);

                    app.controller().getRecordId = () => {
                        return recordId;
                    };
                }

                // Assign customer id to hidden input #recordId and function getRecordId() so that the function trigger send message will take the right customer id
                setRecordId(this.props.customer_id);

                // For SMS and OTT
                if (actionUrl.indexOf('mode=showSendSMSOTTModal') > 0) {
                    let channel = targetButton.data('channel');
                    Vtiger_Detail_Js.triggerSendSMSOTT(actionUrl, channel, e.target);

                    // Reset to current page record id when send message modal is closed
                    app.event.one('post.sendMessageModal.load', function (e, container) {
                        container.closest('.modal').on('hidden.bs.modal', function () {
                            setRecordId(currentPageRecord);
                        });
                    });
                }
                
                // For EMAIL
                if (actionUrl.indexOf('mode=showComposeEmailForm') > 0) {
                    Vtiger_Detail_Js.triggerSendEmail(actionUrl, 'Emails');

                    // Reset to current page record id when send Email modal is closed
                    app.event.one('post.sendEmailModal.load', function (e, container) {
                        container.closest('.modal').on('hidden.bs.modal', function () {
                            setRecordId(currentPageRecord);
                        });
                    });
                }

                // For SOCIAL message
                if (targetButton.data('type') == 'Social') {
                    // Quick hack to tel send social message popup that current view is 'Detail'
                    app.getViewName = () => {
                        return 'Detail';
                    };

                    let channel = targetButton.data('channel');
                    SocialHandler.composeSocialMessage(channel);

                    // Reset to current page view name & record id when send social message modal is closed
                    app.event.one('post.sendMessageModal.load', function (e, container) {
                        container.closest('.modal').on('hidden.bs.modal', function () {
                            setRecordId(currentPageRecord);

                            app.getViewName = () => {
                                return currentPageView;
                            };
                        });
                    });
                }
            });
            // End Hieu Nguyen

			// Added by Vu Mai on 2022-09-07 to include custom tag 
			// Assign customer_id, customer_type to CustomTag element in popup call
			this.$el.find('.custom-tag').attr('data-customer-id', this.props.customer_id);
			this.$el.find('.custom-tag').attr('data-customer-type', this.props.customer_type);

			// Init custom tag
			this.customTag = new CustomTag();
			this.customTag.init(this.$el.find('.custom-tag'));
			// End Vu Mai

			// Added by Vu Mai on 2022-09-16 to register edit-customer-info click event
			let customerId = this.props.customer_id;
			let customerType = this.props.customer_type;

			// Register event for button edit customer info
			this.$el.find('.edit-customer-info').on('click', () => {
				this.insertQuickEditTab(this.$el, customerId, customerType, true);
			});

			// Register event for button edit target info
			this.$el.find('.edit-target-info').on('click', (e) => {
				let type = $(e.target).attr('data-target');

				if (type != 'SalesOrder') {
					this.insertQuickEditTab(this.$el, customerId, type);
				}
				else {
					this.insertInventoryTab(this.$el, customerId, customerType);
				}
			});

            // Added by Vu Mai on 2022-12-29 to set rule required for call result field in call log form if target module is CPTelesales
            let targetModule = this.$el.attr('data-target-module');
            
            if (targetModule == 'CPTelesales') {
                let callResultLabel = this.$el.find('form.callLog .call_result_label');
                let callResultSelect = this.$el.find('form.callLog select[name="events_call_result"]');

                if (callResultLabel.find('.redColor').length == 0) {
                    callResultLabel.append('<span class="redColor">*</span>');
                }

                if (callResultSelect.attr('data-rule-required') != true) {
                    callResultLabel.attr('data-rule-required', true);
                }
            }
            // End Vu Mai
        }

        initEventsByWebPhone() {
            if (!this.props.handled_by_webphone) return;

            this.$el.find('.answerBtn').on('click', () => {
                this.update({ answer_btn_clicked: true });
                if (this.props.state === 'RINGING' && CallCenterClient.webPhone && CallCenterClient.webPhone.answerCall) CallCenterClient.webPhone.answerCall();
            });

            this.$el.find('.endCallBtn').on('click', () => {
                if ((this.props.state == 'RINGING' || this.props.state == 'ANSWERED') && CallCenterClient.webPhone) {
                    if (this.props.direction === 'INBOUND' && this.props.state === 'RINGING') {
                        CallCenterClient.webPhone.rejectCall && CallCenterClient.webPhone.rejectCall();
                    }
                    else if (this.props.direction === 'OUTBOUND' && this.props.state === 'RINGING' && this.props.transferred == true) {
                        CallCenterClient.webPhone.rejectCall && CallCenterClient.webPhone.rejectCall();
                    }
                    else {
                        CallCenterClient.webPhone.hangupCall && CallCenterClient.webPhone.hangupCall();
                    }
                }
            });

            this.$el.find('.muteIncommingCall').on('click', () => {
                if (CallCenterClient && CallCenterClient.triggerMuteCall) CallCenterClient.triggerMuteCall(this.props.call_id);
                this.$el.find('.muteIncommingCall').attr('disabled', true);
            });

            // Replace urgent call behavior
            this.$el.find('button[name="close"]').off('click').on('click', () => {
                app.helper.showConfirmationBox({
                    message: app.vtranslate('PBXManager.JS_CALL_POPUP_CLOSE_CALL_POPUP_CONFIRM'),
                }).then(() => {
                    if (this.props.state === 'RINGING' || this.props.state === 'ANSWERED') CallCenterClient.webPhone.hangupCall();
                    this.update({ state: 'COMPLETED'});
                });
            });
        }

        // CALL POPUP EVENT HANDLER START FROM HERE

        initFreeCallWarningTooltip () {
            if (!this.props.from_free_call_btn) return;

            this.$el.find('.warning-free-call[data-toggle="tooltip"]').tooltip({
                placement: function () {
                    const stage = $(this.$element).closest('.call-popup').data('stage');
                    return (stage == 'OPEN') ? 'right' : 'top';
                },
            });
        }

        saveCallLog (withTicket = false) {
            const form = this.$el.find('form[name="call_log"]');
            const formData = form.serializeFormData();

            // Validate form
            if (!form.valid()) return;

            // Force form data module direct to PBXManager
            formData.module = MODULE;

            app.helper.showProgress();
            this.$el.find('.saveLogBtn').attr('disabled', true);

            app.request.post({ data: formData }).then((err, res) => {
                this.$el.find('.saveLogBtn').attr('disabled', false);
                app.helper.hideProgress();

                if (Utils.isResError(err, res)) {
                    return Utils.errorMessage(err);
                }

                app.helper.showSuccessNotification({ message: app.vtranslate('PBXManager.JS_CALL_POPUP_SAVE_CALL_LOG_SUCCESS') });

                // Mark Save with ticket or not
                if (withTicket) {
                    const params = {
                        data: {
                            contact_id: this.props.customer_id,
                        },
                        preShowCb: popup => {
                            popup.find('#goToFullForm').remove();
                        },
                        postShowCb: popup => {
                            const relateTo = popup.find('[name="related_to_display"]');

                            // Disable input and hide search button
                            // relateTo.val(this.props.customerName).attr('readonly', true).addClass('form-control');
                            relateTo.closest('.fieldValue').find('.clearReferenceSelection').remove();
                            relateTo.closest('.fieldValue').find('.relatedPopup').remove();
                        }
                    };

                    if (this.props.account_id) params.data.parent_id = this.props.account_id;
                    params.data.ticketstatus = 'Open';

                    vtUtils.openQuickCreateModal('HelpDesk', params);
                }

                return this.update({ state: 'COMPLETED' });
            });
        }

        /**
         * Create or clear a timer for call popup
         * @param {Boolean} status Invoke with `true` to toggle on timer
         */
        toggleTimer(status) {
            if (status && !this.$timer) {
                this.$timer = setInterval(() => {
                    const duration = this.props.duration ? this.props.duration + 1 : 1;
                    this.update({duration});
                }, 999);

                if (!this.props.start_time) this.update({ start_time: Date.parse(new Date()) });
            }
            else {
                this.clearTimers(true);
            }
        }

        /**
         * Clear call Popup timer interval, pass `true` to confirm action
         * @param {*} confirm
         */
        clearTimers(confirm = false) {
            if (!confirm) return;

            this.$timeouts.forEach((timerId) => clearTimeout(timerId));
            clearInterval(this.$timer);
            delete this.$timer;
        }

        /**
         * Event to handle call popup mains tab behavior
         * @param {*} data
         */
        callTabEventHandler(data) {
            // Update call data so that we'll all know which tab is active
            Utils.updateData(data.element, 'call-tab-active', data.active);

            // Handle ajax view loading
            if (data.tab.data('trigger') === 'ajax-view') {
                // Remove all old content
                data.tabPane.find('.call-tab-content').html('');

                // Display loading icon
                Utils.updateData(data.tabPane, 'status', 'LOADING');

                const params = {
                    tab: data.tab.data('tab'),
                    customer_id: this.props.customer_id,
                    customer_type: this.props.customer_type,
                }

                Utils.ajaxView(params).then((err, res) => {
                    if (Utils.isResError(err, res)) {
                        // Display error block
                        Utils.updateData(data.tabPane, 'status', 'ERROR');

                        // Show error message
                        return Utils.errorMessage(err);
                    }

                    // reset status
                    Utils.updateData(data.tabPane, 'status', 'SUCCESS');
                    data.tabPane.find('.call-tab-content').html(res);
                    this.initTabEventHandlers(data);

                    // Update counter
                    const count = data.tabPane.find('input.total-count').val();
                    const countKey = data.tab.data('tab').replace(/-/g, '_') + '_count';
                    this.update({ [countKey]: count });

					// Added by Vu Mai on 2022-09-12 to init custom comment if tab is comment list and update comment count
					if (data.active == 'comment-list') {
						let customCommentDiv = data.tabPane.find('.custom-comment');

						this.customComment = new CustomComment();
						this.customComment.init(customCommentDiv, this.props.customer_id);
					}
					// End Vu Mai
                });
            }
        }

        initTabEventHandlers(data) {
            data.tabPane.find('.relatedListFull').on('click', event => {
                const targetModule = data.tabPane.data('module');
                const activityType = data.tabPane.data('activity-type');
                const titleMapping = {
                    'call-list': app.vtranslate('PBXManager.JS_CALL_POPUP_CALL'),
                    'salesorder-list': app.vtranslate('PBXManager.JS_CALL_POPUP_SALES_ORDER'),
                    'ticket-list': app.vtranslate('PBXManager.JS_CALL_POPUP_TICKET'),
                    'faq-list': app.vtranslate('PBXManager.JS_CALL_POPUP_FAQS'),
                }

                const params = {
                    data: {
                        module: this.props.customer_type,
                        record: this.props.customer_id,
                        relatedModule: targetModule,
                    },
                    title: titleMapping[data.active],
                }

                // Prepare query search params for module Calendar
                if (targetModule === 'Calendar') {
                    params.data.search_params = [[['activitytype', 'e', activityType]]];
                }

                vtUtils.openRelatedListModal(params);
            });
        }

        openQuickCreatePopup(target) {
            const module = target.data('module');

            const params = {
                parentModule: this.props.customer_type,
                parentId: this.props.customer_id,
                data: {},
                preShowCb: popup => {
                    popup.find('#goToFullForm').remove();
                },
                postShowCb: popup => {
                    const relateTo = popup.find('[name="related_to_display"]');

                    // Disable input and hide search button
                    // relateTo.val(this.props.customerName).attr('readonly', true).addClass('form-control');
                    relateTo.closest('.fieldValue').find('.clearReferenceSelection').remove();
                    relateTo.closest('.fieldValue').find('.relatedPopup').remove();
                },
                postSaveCb: (data, err) => {
                    let container = target.closest('.fieldValue');
                    let fieldElement = container.find(':input[name="contact_id"]');
                    let fieldDisplayElement = container.find(':input[name="contact_id_display"]');
                    var popupReferenceModuleElement = container.find(':input[name="popupReferenceModule"]').length ? container.find(':input[name="popupReferenceModule"]') : container.find(':input.popupReferenceModule');
                    var popupReferenceModule = popupReferenceModuleElement.val();
                    let selectedName = data._recordLabel;
                    let id = data._recordId;

                    if (id && selectedName) {
                        if (!fieldDisplayElement.length) {
                            fieldElement.attr('value',id);
                            fieldElement.data('value', id);
                            fieldElement.val(selectedName);
                        }
                        else {
                            fieldElement.val(id);
                            fieldElement.data('value', id);
                            fieldDisplayElement.val(selectedName);

                            if (selectedName) {
                                fieldDisplayElement.attr('readonly', 'readonly');
                            }
                            else {
                                fieldDisplayElement.removeAttr('readonly');
                            }
                        }
            
                        if (selectedName) {
                            fieldElement.parent().find('.clearReferenceSelection').removeClass('hide');
                            fieldElement.parent().find('.referencefield-wrapper').addClass('selected');
                        }
                        else {
                            fieldElement.parent().find('.clearReferenceSelection').addClass('hide');
                            fieldElement.parent().find('.referencefield-wrapper').removeClass('selected');
                        }
                        
                        fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});
                    }
        
                    container.find('input[class="sourceField"]').trigger(Vtiger_Edit_Js.postReferenceQuickCreateSave, {'data' : data});
                }
            };

            if (module === 'Events') {
                params.data.activitytype = target.data('activity');
                params.data.visibility = 'Public';
            }
            
            if (module === 'Potentials' && this.props.customer_type) {
                params.data.related_to = this.props.account_id;
                params.data.contact_id = this.props.customer_id;
            }
            
            if (module === 'Potentials' && this.props.customer_type == 'Accounts') {
                params.data.related_to = this.props.customer_id;
            }

            if (module === 'HelpDesk') params.data.ticketstatus = 'Open';

            if (this.props.customer_type == 'Accounts' && module == 'Contacts') {
                params.data.account_id = this.props.customer_id;
            }

            app.helper.showProgress();

            vtUtils.openQuickCreateModal(module, params);
        }

        openQuickCreatePotentialForLeadPopup() {
            const params = {
                data: {},
                preShowCb: popup => {
                    popup.find('#goToFullForm').remove();

                    const appendFields = {
                        'mode' : 'savePotentialForLeads',
                        'lead_id': this.props.customer_id,
                    };

                    // Force change form element to handle submission
                    popup.find('[name="module"]:input:hidden').val('PBXManager');
                    popup.find('[name="action"]:input:hidden').val('CallPopupAjax');

                    for (let field in appendFields) {
                        if (!popup.find(`[name="${field}"]`)[0]) {
                            const inputElement = `<input type="hidden" name="${field}" value="${appendFields[field]}" />`;
                            popup.find('form').append(inputElement);
                        }
                        else {
                            popup.find(`[name="${field}"]`).val(appendFields[field]);
                        }
                    }
                },
                postSaveCb: data => {
                    // Update data for account + contact converted to use later
                    if (data.related_to && data.related_to.value) {
                        this.props.account_converted_id = data.related_to.value;
                        this.props.account_converted_name = HTMLParse(data.related_to.displayValue);
                    }
                }
            };

            if (this.props.account_converted_id) {
                params.data.related_to = this.props.account_converted_id;
            }

            app.helper.showProgress();

            vtUtils.openQuickCreateModal('Potentials', params);
        }

        openSyncCustomerInfoPopup() {
            // Prevent duplicate popup
            const idString = this.props.call_id.replace(/\./g, '');
            if ($(`#sync-customer-info-${idString}`)[0]) return;

            // Load modal template
            const modal = $('#syncCustomerInfo').clone().attr('id', `sync-customer-info-${idString}`);

            // Added 2019.05.20 to fix sometime saveButton is disabled
            modal.find('button[type="submit"]').attr('disabled', false);

            // Handle close behavior
            modal.find('.fa.fa-close, .cancelLink').on('click', () => {
                this.closeSyncCustomerInfoPopup();
            });

            // Init DOM Elements
            CallTabs.init(modal);
            Utils.applyFieldElementsView(modal);

            // Init number
            modal.find('[name="mobile"]').val(this.props.customer_number);
            modal.find('[name="customer_number"]').val(this.props.customer_number);
            modal.find('[name="pbx_call_id"]').val(this.props.call_id);

            const open = modal => {
                this.initCreateCustomerFormEvents(modal);
                this.initSearchCustomerFormEvents(modal);
            }

            const params = { cb: open }

            app.helper.showModal(modal, params);
        }

        closeSyncCustomerInfoPopup() {
            const idString = this.props.call_id.replace(/\./g, '');
            if (!$(`#sync-customer-info-${idString}`)[0]) return;
            app.helper.hideModal();

            // Prevent from refresh web
            setTimeout(function () {
                window.onbeforeunload = function () {
                    return false;
                }
            }, 1000);
        }

        initCreateCustomerFormEvents(modal) {
            const form = modal.find('form[name="quick_create"]');

            // Init Events on customer type
            form.find('[name="customer_type"]').on('change', event => {
                const status = $(event.currentTarget).val() === 'Contacts' && $(event.currentTarget).is(':checked');
                const toggleBaseOnContact = form.find('.toggleBaseOnContact');
                const toggleBaseOnContactInputs = toggleBaseOnContact.find('input, select, textarea');

                toggleBaseOnContact.toggleClass('active', status);
                toggleBaseOnContactInputs.attr('disabled', !status);
            }).trigger('change');

            // Form validation
            const validateParams = {
                submitHandler: form => {
                    const params = $(form).serializeFormData();

                    // Force params module to this module
                    params.module = MODULE;

                    app.helper.showProgress();

                    app.request.post({data: params}).then((err, res) => {
                        app.helper.hideProgress();

                        if (Utils.isResError(err, res)) {
                            return Utils.errorMessage(err);
                        }

                        this.update(res);

                        app.helper.hideModal();
                    });
                }
            }

            form.vtValidate(validateParams);
        }

        initSearchCustomerFormEvents(modal) {
            const form = modal.find('form[name="search_customer"]');

            const datatable = modal.find('.customerSearchResult').DataTable({
                ordering: false,
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'index.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: function (data) {
                        return $.extend({}, data,
                            form.serializeFormData()
                        );
                    },
                },
                columns: [
                    { data: 'customer_type', name: 'customer_type' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'assigned_user_name', name: 'assigned_user_name' },
                    { data: 'account_name', name: 'account_name' },
                    { data: 'customer_number', name: 'customer_number' },
                    { data: 'action', name: 'action' },
                ],
                language: DataTableUtils.languages,
            });

            // Handle form submit and update datatable
            form.vtValidate({
                submitHandler: form => {
                    let valid = false;
                    const inputs = $(form).find('table.fieldBlockContainer').find(':input');

                    inputs.each((index, target) => {
                        if ($(target).val()) valid = true;
                    });

                    // Validate
                    if (!valid) {
                        const errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_SEARCH_CUSTOMER_EMPTY_INPUT_ERROR_MESSAGE');
                        return app.helper.showErrorNotification({ message: errorMessage });
                    }

                    // Reload data table to fetch new data
                    datatable.ajax.reload();

                    // Prevent form submition
                    return false;
                }
            });

            // Handle each time data table update
            datatable.on('draw.dt', (a, b) => {
                // Register select customer event
                modal.find('.syncCustomerInfo').on('click', event => {
                    const target = $(event.currentTarget);
                    const customerInfo = target.data('info');
                    const customerType = app.vtranslate(`PBXManager.JS_CALL_POPUP_SINGLE_${customerInfo.customer_type}`);
                    const messageParams = { customer_type: customerType, customer_name: customerInfo.customer_name };
                    const confirmMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_SYNC_CUSTOMER_INFO_CONFIRM', messageParams);

                    bootbox.confirm({
                        message: confirmMessage,
                        callback: (result) => {
                            if (!result) return;

                            // Prevent customer number is empty cause popup fail identifying
                            if (!customerInfo.customer_number) {
                                customerInfo.customer_number = this.props.customer_number;
                            }

                            this.update(customerInfo);

                            app.request.post({
                                data: Object.assign({
                                    module: MODULE,
                                    action: 'CallPopupAjax',
                                    mode: 'sendPopupCustomerInfo',
                                    pbx_call_id: this.props.call_id,
                                }, customerInfo)
                            });

                            app.helper.hideModal();
                        }
                    });
                });
            });
        }

        initFaqTabEventHandlers() {
            const tab = this.$el.find('.call-tab-pane.faq-tab');
            const form = tab.find('form.filter-form');
            const result = tab.find('.faq-result-display');

            // Force search button click event trigger form submit event
            form.find('.searchButton').on('click', () => {
                form.trigger('submit');
            });

            // Declare form submit event and call to ajax view with filter params (keyword for now)
            form.vtValidate({
                submitHandler: () => {
                    // Handle logic start from here
                    const formData = form.serializeFormData();
                    const params = {
                        module: MODULE,
                        view: 'CallPopupAjax',
                        mode: 'searchFaq'
                    }

                    // Prevent submit empty query and cause a confuse face
                    if (!formData.keyword) {
                        result.find('.faq-tab-result-content').html('');
                        return;
                    }

                    Utils.updateData(result, 'status', 'LOADING');

                    app.request.post({ data: Object.assign(params, formData) }).then((err, res) => {
                        // Handle Error
                        if (Utils.isResError(err, res)) {
                            // Update status data attribute
                            Utils.updateData(result, 'status', 'ERROR');

                            // Display error message
                            return Utils.errorMessage(err);
                        }

                        // Append res to DOM and add more event handlers
                        Utils.updateData(result, 'status', 'SUCCESS');
                        result.find('.faq-tab-result-content').html(res);

                        // Init events
                        this.initFaqContentEvents(result.find('.faq-tab-result-content'));

                        // Handle show faq full modal event
                        this.registerOpenFaqFullSearchModal(result.find('.faq-tab-result-content'));
                    });

                    // Prevent form submition
                    return false;
                }
            });
        }

        initFaqContentEvents(container) {
            container.find('.openFaqModel').off('click').on('click', event => {
                const target = $(event.currentTarget);
                const params = {
                    module: MODULE,
                    view: 'CallPopupAjax',
                    mode: 'faqPopup',
                    record: target.data('id'),
                    customer_id: this.props.customer_id,
                    customer_type: this.props.customer_type,
                    customer_email: this.props.customer_email,
                }

                app.helper.showProgress();

                app.request.post({ data: params }).then((err, res) => {
                    app.helper.hideProgress();

                    if (Utils.isResError(err, res)) {
                        return Utils.errorMessage();
                    }

                    app.helper.showModal($(res), {
                        preShowCb: modal => {
                            this.registerSendEmailFaqEvent(modal);
                        }
                    });
                });
            });
        }

        registerOpenFaqContentModelEvent(container, handler = '') {
            container.find('.openFaqModel').off('click').on('click', event => {
                const target = $(event.currentTarget);
                const params = {
                    module: MODULE,
                    view: 'CallPopupAjax',
                    mode: 'faqPopup',
                    record: target.data('id'),
                    customer_id: this.props.customer_id,
                    customer_type: this.props.customer_type,
                    customer_email: this.props.customer_email,
                }

                const data = target.data();

                app.helper.showProgress();

                app.request.post({ data: Object.assign(params, data) }).then((err, res) => {
                    app.helper.hideProgress();

                    if (Utils.isResError(err, res)) {
                        return Utils.errorMessage();
                    }

                    if (typeof handler === 'function') handler(res);
                });
            });
        }

        registerSendEmailFaqEvent(container) {
            container.find('button[name="send_email"]').on('click', () => {
                container.find('.displayOnEmail').toggle();
            });

            app.helper.showVerticalScroll(container.find('.modal-body'), {setMaxHeight: '400px'});

            container.find('form[name="faq"]').vtValidate({
                submitHandler: form => {
                    const formData = $(form).serializeFormData();
                    const params = {
                        module: MODULE,
                        action: 'CallPopupAjax',
                        mode: 'sendFaqEmail',
                    }

                    app.helper.showProgress();

                    app.request.post({ data: Object.assign(params, formData) }).then((err, res) => {
                        app.helper.hideProgress();

                        if (Utils.isResError(err, res)) {
                            return Utils.errorMessage();
                        }

                        app.helper.showSuccessNotification({ message: app.vtranslate('PBXManager.JS_CALL_POPUP_SEND_FAQ_EMAIL_SUCCESS') });
                        app.helper.hideModal();
                    });

                    // Prevent form default submition
                    return false;
                }
            });
        }

        openInventoryEditView(module) {
            const customerId = this.props.customer_id;
            const customerType = this.props.customer_type;
            let url = `index.php?view=Edit&returnmode=showRelatedList&returnview=Detail&app=SALES`;
            let customParams = `&module=${module}&returnrecord=${customerId}&returnmodule=${customerType}`;

            // Process with account
            if (this.props.account_id) customParams += `&account_id=${this.props.account_id}`;

            // Process with customerType
            if (customerType === 'Contacts') {
                customParams += `&contact_id=${customerId}`;
            }
            else if (!this.props.account_id) {
                customParams += `&account_id=${_PERSONAL_CUSTOMER_ID}`;
            }

            // Open new window with processed url
            window.open(url + customParams, '_blank');
        }

        registerOpenFaqFullSearchModal(container) {
            container.find('.openSearchFaqModel').on('click', () => {
                this.openFaqFullSearchModal();
            });
        }

        openFaqFullSearchModal() {
            const params = {
                module: MODULE,
                view: 'CallPopupAjax',
                mode: 'fullFaqSearchPopup',
            }

            app.request.post({ data: params }).then((err, res) => {
                if (Utils.isResError(err, res)) return Utils.errorMessage();

                app.helper.showModal(res, {
                    preShowCb: modal => {
                        const self = this;

                        // Init keyword value from call popup and trigger form submition
                        if (this.props.faq_keyword) modal.find('input[name="keyword"]').val(this.props.faq_keyword);

                        const datatable = modal.find('#faqListViewTable').DataTable({
                            ordering: false,
                            searching: false,
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: 'index.php',
                                type: 'POST',
                                dataType: 'JSON',
                                data: function (data) {
                                    return $.extend({}, data,
                                        {
                                            module: MODULE,
                                            action: 'CallPopupAjax',
                                            mode: 'searchFaqByKeyword',
                                            customer_id: self.props.customer_id,
                                            customer_type: self.props.customer_type,
                                        },
                                        modal.find('form[name="faq_search"]').serializeFormData()
                                    );
                                },
                            },
                            columns: [
                                { data: 'number', name: 'number' },
                                { data: 'question', name: 'question' },
                            ],
                            language: DataTableUtils.languages,
                        });

                        // Handle form submit and update datatable
                        modal.find('form[name="faq_search"]').vtValidate({
                            submitHandler: form => {
                                const params = $(form).serializeFormData()

                                // Stop submit form if keyword empty
                                if (!params.keyword) return;

                                // Update data table
                                datatable.ajax.reload();

                                // Prevent form submition
                                return false;
                            }
                        });

                        // Init search button click event
                        modal.find('.searchButton').on('click', () => {
                            modal.find('form[name="faq_search"]').trigger('submit');
                        });

                        // Handle each time data table update
                        datatable.on('draw.dt', () => {
                            this.registerOpenFaqContentModelEvent(modal.find('form[name="faq_search"]'), res => {
                                const content = $(res).find('form[name="faq"]');
                                const faqDetail = modal.find('.modal-content.faq-detail');
                                faqDetail.html(content);
                                this.registerSendEmailFaqEvent(faqDetail);
                                Utils.updateData(modal.find('.call-popup-search-faq-full'), 'mode', 'DETAIL');

                                // Handle back to search mode
                                faqDetail.find('.cancelLink').on('click', () => {
                                    Utils.updateData(modal.find('.call-popup-search-faq-full'), 'mode', 'SEARCH');
                                });
                            });
                        });
                    }
                });
            });
        }

        openFaqFullSearchModal() {
            const params = {
                module: MODULE,
                view: 'CallPopupAjax',
                mode: 'fullFaqSearchPopup',
            }

            app.request.post({ data: params }).then((err, res) => {
                if (Utils.isResError(err, res)) return Utils.errorMessage();

                app.helper.showModal(res, {
                    preShowCb: modal => {
                        const self = this;

                        // Init keyword value from call popup and trigger form submition
                        if (this.props.faq_keyword) modal.find('input[name="keyword"]').val(this.props.faq_keyword);

                        const datatable = modal.find('#faqListViewTable').DataTable({
                            ordering: false,
                            searching: false,
                            scrollY: '240px',
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: 'index.php',
                                type: 'POST',
                                dataType: 'JSON',
                                data: function (data) {
                                    return $.extend({}, data,
                                        {
                                            module: MODULE,
                                            action: 'CallPopupAjax',
                                            mode: 'searchFaqByKeyword',
                                            customer_id: self.props.customer_id,
                                            customer_type: self.props.customer_type,
                                        },
                                        modal.find('form[name="faq_search"]').serializeFormData()
                                    );
                                },
                            },
                            columns: [
                                { data: 'number', name: 'number' },
                                { data: 'question', name: 'question' },
                            ],
                            language: DataTableUtils.languages,
                        });

                        // Handle form submit and update datatable
                        modal.find('form[name="faq_search"]').vtValidate({
                            submitHandler: form => {
                                const params = $(form).serializeFormData()

                                // Stop submit form if keyword empty
                                if (!params.keyword) return;

                                // Update data table
                                datatable.ajax.reload();

                                // Prevent form submition
                                return false;
                            }
                        });

                        // Init search button click event
                        modal.find('.searchButton').on('click', () => {
                            modal.find('form[name="faq_search"]').trigger('submit');
                        });

                        // Handle each time data table update
                        datatable.on('draw.dt', () => {
                            this.registerOpenFaqContentModelEvent(modal.find('form[name="faq_search"]'), res => {
                                const content = $(res).find('form[name="faq"]');
                                const faqDetail = modal.find('.modal-content.faq-detail');
                                faqDetail.html(content);
                                this.registerSendEmailFaqEvent(faqDetail);
                                Utils.updateData(modal.find('.call-popup-search-faq-full'), 'mode', 'DETAIL');

                                // Handle back to search mode
                                faqDetail.find('.cancelLink').on('click', () => {
                                    Utils.updateData(modal.find('.call-popup-search-faq-full'), 'mode', 'SEARCH');
                                });
                            });
                        });
                    }
                });
            });
        }

        invokeUpdateCounters() {
            app.helper.showProgress();

            app.request.post({
                data: {
                    module: MODULE,
                    action: 'CallPopupAjax',
                    mode: 'getInitCallPopupData',
                    customer_id: this.props.customer_id,
                    customer_type: this.props.customer_type,
                }
            }).then((err, res) => {
                app.helper.hideProgress();

                // Handle error
                if (Utils.isResError(err, res)) return Utils.errorMessage();

                // Handle response
                this.update({
                    call_list_count: res.counts.call || 0,
                    salesorder_list_count: res.counts.salesorder || 0,
                    ticket_list_count: res.counts.ticket || 0,
					comment_list_count: res.counts.comment || 0, // Added by Vu Mai on 2022-09-12 to update count for tab comment
                });

                if (this.props.customer_type != 'Accounts') {
                    this.updateCustomerInformation(res.customer);
                }
            });
        }

        loadContactInformation(contactId) {
            app.helper.showProgress();

            app.request.post({
                data: {
                    module: MODULE,
                    action: 'CallPopupAjax',
                    mode: 'getCustomerInfo',
                    customer_id: contactId,
                    customer_type: 'Contacts',
                }
            }).then((err, res) => {
                app.helper.hideProgress();

                // Handle error
                if (Utils.isResError(err, res)) return Utils.errorMessage();

                this.updateCustomerInformation(res);
            });
        }

        updateCustomerInformation(customerData) {
            const mainForm = this.$el.find('form[name="call_log"]');

            if (!customerData) {
                // Update form value
                mainForm.find('[name="salutationtype"]').val('').trigger('change');
                mainForm.find('[name="firstname"]').val('').trigger('change');
                mainForm.find('[name="lastname"]').val('').trigger('change');
                mainForm.find('[name="mobile_phone"]').val(this.props.customer_number).trigger('change');
                mainForm.find('[name="email"]').val('').trigger('change');
                mainForm.find('[name="account_id"]').val('').trigger('change');
                mainForm.find('[name="account_id_display"]').val('').trigger('change');
                mainForm.find('[name="company"]').val('').trigger('change');
    
                // Hide deselect account button
                mainForm.find('.clearReferenceSelection').addClass('hide');
    
                // Update product and service select2
                mainForm.find('[name="product_ids"]').select2('data', '').trigger('change');
                mainForm.find('[name="service_ids"]').select2('data', '').trigger('change');
            }
            else {
                // Update form value
                mainForm.find('[name="salutationtype"]').val(customerData.salutationtype).trigger('change');
                mainForm.find('[name="firstname"]').val(customerData.firstname).trigger('change');
                mainForm.find('[name="lastname"]').val(customerData.lastname).trigger('change');
                mainForm.find('[name="mobile_phone"]').val(customerData.mobile).trigger('change');
                mainForm.find('[name="email"]').val(customerData.email).trigger('change');
                mainForm.find('[name="account_id"]').val(customerData.account_id).trigger('change');
                mainForm.find('[name="account_id_display"]').val(customerData.account_id_display).trigger('change');
                mainForm.find('[name="company"]').val(customerData.company).trigger('change');
    
                // Show deselect account button if it has a value
                if (mainForm.find('[name="account_id_display"]').val()) {
                    mainForm.find('.clearReferenceSelection').removeClass('hide');
                }
    
                // Update product and service select2
                mainForm.find('[name="product_ids"]').select2('data', customerData.product_ids).trigger('change');
                mainForm.find('[name="service_ids"]').select2('data', customerData.services_ids).trigger('change');
            }
            
        }

        initMainFormAjaxSelect2() {
            if (this.$el.find('[name="product_ids"].select2-offscreen')[0]) return;
            AjaxSelect2.init(this.$el.find('[name="product_ids"]'), {
                placeholder: app.vtranslate('PBXManager.JS_CALL_POPUP_SELECT_PRODUCT_PLACEHOLDER'),
                mode: 'loadSelect2AjaxList',
                targetModule: 'Products',
            });
            AjaxSelect2.init(this.$el.find('[name="service_ids"]'), {
                placeholder: app.vtranslate('PBXManager.JS_CALL_POPUP_SELECT_SERVICE_PLACEHOLDER'),
                mode: 'loadSelect2AjaxList',
                targetModule: 'Services',
            });
        }

        /**
         * This method will toggle disabled attribute base on mode
         * to specific element class
         * @param {*} mode
         * @param {*} status
         * @param {*} revert
         */
        toggleFormControl(mode, status = true, revert = true) {
            // Prevent default element hide from undefined customer
            mode = mode || 'Default';

            const keySearch = `.for${mode}`;
            const targets = this.$el.find(keySearch);
            const modes = ['Contacts', 'Leads', 'Default'];

            // Apply all input
            targets.each((index, element) => {
                const target = $(element);

                if (Utils.isFormElement(target)) {
                    target.attr('disabled', !status);
                }
                else {
                    target.find(':input').attr('disabled', !status);
                }
            });

            // Condition to keep going
            if (!revert) return;

            // Revert effect for opposite modes input
            modes.filter((single) => single !== mode && single !== 'Default').forEach((opposite) => {
                this.toggleFormControl(opposite, !status, false);
            });
        }

        retrievePopupInfo() {
            const params = {
                module: 'Vtiger',
                action: 'GetData',
                source_module: 'Calendar',
                record: this.props.call_log_id,
            }

            app.helper.showProgress();

            app.request.post({ data: params }).then((err, res) => {
                app.helper.hideProgress();

                // Handle Error
                if (err || !res || !res.success || !res.data) {
                    const errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_RETRIEVE_CALL_POPUP_INFO_ERROR');
                    return app.helper.showErrorNotification({ message: errorMessage });
                }

                const callLogData = res.data;

                this.update({
                    subject: callLogData['label'],
                    description: callLogData['description'],
                });
            });
        }

        registerCallBackLaterTimeFields() {
            const now = new Date();
            const isPM = now.getHours() >= 12;

            const defaultTime = isPM ? '08:00' : '02:00';
            const defaultMoment = isPM ? 'next_morning' : 'this_afternoon';

            const times = [];

            // Generate select time options
            for (let i = 1; i < 13; i++) {
                const value = `${Utils.pad(i, 2)}:00`;
                times.push({
                    id: value,
                    text: value,
                });
            }

            const moments = [
                {
                    id: 'this_afternoon',
                    text: app.vtranslate('PBXManager.JS_CALL_POPUP_THIS_AFTERNOON'),
                },
                {
                    id: 'next_morning',
                    text: app.vtranslate('PBXManager.JS_CALL_POPUP_NEXT_MORNING'),
                },
                {
                    id: 'next_afternoon',
                    text: app.vtranslate('PBXManager.JS_CALL_POPUP_NEXT_AFTERNOON'),
                }
            ];

            if (isPM) {
                moments.filter((moment) => {
                    return moment.id !== 'this_afternoon';
                });
            }

            this.$el.find('[name="select_time"]')
                .select2({ data: times })
                .val(defaultTime)
                .trigger('change');

            this.$el.find('[name="select_moment"]')
                .select2({ data: moments })
                .val(defaultMoment)
                .trigger('change');

            // Set default value for start date and start time field
            this.$el.find('[name="date_start"]').val(MomentHelper.getDisplayDate());
            this.$el.find('[name="time_start"]').val(MomentHelper.getDisplayTime());
        }

        initCallTitleBaseOnCallPurpose() {
            this.handleCallTitleWithEvents = this.handleCallTitleWithEvents.bind(this);
            this.$el.find('[name="events_call_purpose"]').on('change', this.handleCallTitleWithEvents);
            this.$el.find('[name="events_call_purpose_other"]').on('change', this.handleCallTitleWithEvents);
            this.$el.find('[name="events_inbound_call_purpose"]').on('change', this.handleCallTitleWithEvents);
            this.$el.find('[name="events_inbound_call_purpose_other"]').on('change', this.handleCallTitleWithEvents);
        }

        handleCallTitleWithEvents(event) {
            // For the first thing ever, we will ignore when it is a existed call (with call_log_id)
            if (this.props.call_log_id) return;

            const target = $(event.currentTarget);

            // For now, prevent logic trigger with empty value
            if (!target.val()) return this.update({ subject: '' });;

            // Base on field value we will have difference logic handle
            // Basically use will choosing options from normal purpose field or the other field
            // So if use choose value 'Other' we will just skip processing
            const otherValues = [
                'call_purpose_other',
                'inbound_call_purpose_other',
            ];

            if (otherValues.includes(target.val())) return this.update({ subject: '' });;

            // A little trick, we added custom attribute  data-other-purpose="true" to fields that use input value
            // directly to set call to popup title
            if (target.data('other-purpose')) {
                this.update({ subject: target.val()});
            }
            else {
                // It comes from select element, so we have to "translate" it to use display value
                const displayValue = this.$el.find(`[value="${target.val()}"]`).html();
                this.update({ subject: displayValue });
            }
        }

        openTransferCallModal () {
            // Prevent duplicate popup
            const idString = this.props.call_id.replace(/\./g, '');
            if ($(`#transfer-call-${idString}`)[0]) return;

            // Load modal template
            const modal = $('#transferCall').clone().attr('id', `transfer-call-${idString}`);

            // Init DOM Elements
            Utils.applyFieldElementsView(modal);
            this.initTransferCallModalEvents(modal);

            app.helper.showModal(modal);
        }

        closeTransferCallModal() {
            const idString = this.props.call_id.replace(/\./g, '');
            if (!$(`#transfer-call-${idString}`)[0]) return;
            app.helper.hideModal();

            // Prevent from refresh web
            setTimeout(function () {
                window.onbeforeunload = function () {
                    return false;
                }
            }, 1000);
        }

        initTransferCallModalEvents(modal) {
            const self = this;

            const datatable = modal.find('.transfer-call-table').DataTable({
                ordering: false,
                searching: false,
                processing: true,
                serverSide: true,
                language: DataTableUtils.languages,
                ajax: {
                    url: 'index.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: function (data) {
                        const filterFormData = modal.find('form[name="transfer_call"]').serializeFormData();
                        return $.extend({}, data, {
                            module: 'PBXManager',
                            action: 'CallPopupAjax',
                            mode: 'getTransferableList',
                            filter: filterFormData,
                        });
                    },
                },
                columns: [
                    { data: 'display_name', name: 'display_name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' },
                    { data: 'ext', name: 'ext' },
                    {
                        data: 'action',
                        name: 'action',
                        render: (data, type, row) => {
                            const actionButton = $('<button></button>');
                            actionButton.attr('class', 'btn btn-default transferCallBtn');
                            actionButton.attr('type', 'button');
                            actionButton.html('<img src="modules/PBXManager/resources/images/transfer-call.png" width="16">');
                            actionButton.attr('data-ext', row.ext);
                            actionButton.attr('data-display_name', row.display_name);

                            // Add tooltip for actionButton
                            const replaceParams = { 'ext': row.ext, 'display_name': row.display_name };
                            actionButton.attr('data-toggle', 'tooltip');
                            actionButton.attr('title', app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_DESCRIPTION', replaceParams));

                            return actionButton.prop('outerHTML');
                        }
                    },
                ],
                initComplete: function () {
                    const table = this;
                    let callAjaxTimeout = null;

                    // Apply the search
                    table.find('thead').find('th').find('input').each((index, ui) => {
                        $(ui).on('keyup change clear', function() {
                            clearInterval(callAjaxTimeout);
                            callAjaxTimeout = setTimeout(() => {
                                table.api().ajax.reload();
                            }, 500);
                        });
                    });

                    table.find('.clearFilters').on('click', function() {
                        table.find(':input.column-search').val('');
                        setTimeout(() => table.api().ajax.reload(), 0);
                    });
                }
            });

            // Handle event each time data table update
            datatable.on('draw.dt', (a, b) => {
                modal.find('[data-toggle="tooltip"]').tooltip();

                modal.find('.transferCallBtn').on('click', function (event) {
                    event.preventDefault();

                    const replaceParams = {
                        'ext': $(this).data('ext'),
                        'display_name': $(this).data('display_name'),
                    };
                    const confirmMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_CONFIRM', replaceParams);

                    app.helper.showConfirmationBox({ message: confirmMessage }).then(() => {
                        self.transferCall($(this).data('ext'), $(this).data('display_name'));
                    });
                });
            });
        }

        transferCall (destExt, destName) {
            const self = this;

            const requestParams = {
                module: 'PBXManager',
                action: 'CallPopupAjax',
                mode: 'transferCall',
                call_id: this.props.call_id,
                dest_agent_ext: destExt,
                dest_agent_name: destName,
            };

            self.update({
                'transferred_to_name': destName,
                'transferred_to_ext': destExt,
            });

            app.helper.showProgress();

            app.request.post({ data: requestParams }).then((err, res) => {
                app.helper.hideProgress();

                if (err || !res || (res && !res.success)) {
                    let errorMessage = '';
                    const replaceParams = {
                        'display_name': destName,
                        'ext': destExt,
                    };

                    if (res.message == 'NO_ACTIVE_PROVIDER') errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_NO_ACTIVE_PROVIDER_ERROR_MSG', replaceParams);
                    else if (res.message == 'CANNOT_CHECK_AGENT_STATUS') errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_CANNOT_CHECK_AGENT_STATUS_ERROR_MSG', replaceParams);
                    else if (res.message == 'AGENT_IS_BUSY') errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_AGENT_IS_BUSY_ERROR_MSG', replaceParams);
                    else if (res.message == 'AGENT_IN_WRAPUP_TIME') errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_AGENT_IN_WRAPUP_TIME_ERROR_MSG', replaceParams);
                    else if (res.message == 'AGENT_IS_NOT_ONLINE') errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_AGENT_IS_NOT_ONLINE_ERROR_MSG', replaceParams);
                    else errorMessage = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_ERROR_MSG', replaceParams);

                    app.helper.showErrorNotification({ message: errorMessage }, { delay: 5000 });

                    return;
                }

                self.update({ state: 'TRANSFERRED' });

                app.helper.showSuccessNotification({ message: app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFER_CALL_SUCCESS_MSG') });

                self.closeTransferCallModal();
            });
        }

		// Added by Vu Mai on 2022-09-16 to insert quick edit tab
		insertQuickEditTab (element, recordId, recordType, isCustomerInfoTab = false, insertCallBack = null) {
			let self = this;
			let tabHref = '';
			let tabLabel = '';

			if (isCustomerInfoTab) {
				tabHref = 'customer-info';
				tabLabel = app.vtranslate('PBXManager.JS_CUSTOMER_INFO');
			}
			else {
				recordId = this.props.addition_info.target_record_id;
				tabHref = recordType + '-info';
				tabLabel = app.vtranslate('PBXManager.JS_'+ recordType.toUpperCase() +'_INFO');
			}

			// If target record quick edit tab exist then we should mark it as active
			if (element.find('.right-side .tab-content #' + tabHref).length > 0) {
				element.find(`.right-side .tab-header .nav-tabs li a[href="#${tabHref}"]`).click();
				return;
			}

			// Call ajax to load quick edit form
			app.helper.showProgress();
			let params = {
				module: recordType,
				view: 'QuickEditAjax', 
				mode: 'edit',
				record: recordId
			};

			app.request.post({ data: params })
			.then(function (err, data) {
				app.helper.hideProgress();

				if (typeof insertCallBack == 'function') {
                    insertCallBack();
                }

				if (err) {
					app.helper.showErrorNotification({ message: err.message });
					return;
				}

				// Show right side
				if (!element.find('.right-side').is(':visible')) {
					element.find('.right-side').show();
				}

				// Add tab to header
				let headerTab = '<li class="' + tabHref +'"><a data-toggle="tab" href="#' + tabHref + '" aria-expanded="true">' + tabLabel + '</a></li>';
				element.find('.right-side .tab-header .nav-tabs').append(headerTab);

				// Add form to tab content
				var container = jQuery('<div id="' + tabHref + '" class="tab-pane">'+ data +'</div>');
				container.find('.modal-header').remove();
				container.find('form').attr('name', tabHref);
				element.find('.right-side .tab-content').append(container);
				element.find(`.right-side .tab-header .nav-tabs li a[href="#${tabHref}"]`).click();

				// Init quick edit form
				let form = element.find(`form[name=${tabHref}]`);
				let quickedit = new BaseQuickEdit();
				quickedit.registerEvent(form);

				let submitCallBack = () => {
					form.find('button[name="saveButton"]').attr('disabled', true);  // Disabled button submit to prevent submit multiple time
				}

				let saveCallBack = (err, res) => {
					form.find('button[name="saveButton"]').removeAttr('disabled');  // Enable button submit after save process finished
					if (err) return;
				};

				quickedit.registerSubmitEvent(submitCallBack, saveCallBack);

				// Handle tab cancel event
				self.cancelPopupInfo(form, element);
			});
		}

		// Added by Vu Mai on 2022-09-27 to insert inventory tab
		insertInventoryTab (element, record, recordType, mode = null, insertCallBack = null) {
			let self = this;
			let inventoryModule = self.props.addition_info.target_module;
			let tabLabel = app.vtranslate(`PBXManager.JS_${inventoryModule.toUpperCase()}_INFO`);

            // Change module name to sales order when target module is CPTelesales
			if (inventoryModule == 'CPTelesales') {
				inventoryModule = 'SalesOrder';
				tabLabel = app.vtranslate(`PBXManager.JS_CALL_POPUP_SALES_ORDER`);
			}

			// If inventory tab exist then we should mark it as active
			if (element.find(`.right-side .tab-content #${inventoryModule}-info`).length > 0) {
				element.find(`.right-side .tab-header .nav-tabs li a[href="#${inventoryModule}-info"]`).click();
				return;
			}
			
			// Call ajax to load inventory form
			app.helper.showProgress();
			let params = {
				module: inventoryModule,
				view: 'QuickEditAjax', 
				customer_id: record,
				customer_type: recordType,
				record: self.props.addition_info.target_record_id,
				mode: mode,
			};

			app.request.post({ data: params }).then(function (err, data) {
				app.helper.hideProgress();

				if (err) {
					app.helper.showErrorNotification({ message: err.message });
					return;
				}

				// Show right side
				if (!element.find('.right-side').is(':visible')) {
					element.find('.right-side').show();
				}

				// Add tab to header
				let headerTab = `<li class="${inventoryModule}-info"><a data-toggle="tab" href="#${inventoryModule}-info" aria-expanded="true">${tabLabel}</a></li>`;
				element.find('.right-side .tab-header .nav-tabs').append(headerTab);

				// Add form to tab content
				var container = jQuery(`<div id="${inventoryModule}-info" class="tab-pane">${data}</div>`);
				container.find('.modal-header').remove();
				container.find('form').attr('name', `${inventoryModule}-info`);
				element.find('.right-side .tab-content').append(container);
				element.find(`.right-side .tab-header .nav-tabs li a[href="#${inventoryModule}-info"]`).click();

                if (typeof insertCallBack == 'function') {
                    insertCallBack();
                }

				// Init quick edit form
				let form = element.find(`form[name=${inventoryModule}-info]`);
				let quickedit = new InventoryQuickEdit();
				quickedit.registerEvent(form);

				let submitCallBack = () => {
					form.find('button[name="saveButton"]').attr('disabled', true);  // Disabled button submit to prevent submit multiple time
				}

				let saveCallBack = (err, res) => {
					form.find('button[name="saveButton"]').removeAttr('disabled');  // Enable button submit after save process finished
					if (err) return;
				};

				quickedit.registerSubmitEvent(submitCallBack, saveCallBack);

				// Handle tab cancel event
				self.cancelPopupInfo(form, element);
			});
		}

        // Added by Vu Mai on 2022-11-04 to insert campaign call script tab
		insertCampaignCallScriptTab (element, campaignId, insertCallBack = null) {
			let tabLabel = app.vtranslate(`PBXManager.JS_CALL_SCRIPT`);

			// If inventory tab exist then we should mark it as active
			if (element.find('.right-side .tab-content #call-script-info').length > 0) {
				element.find('.right-side .tab-header .nav-tabs li a[href="#call-script-info"]').click();
				return;
			}

			// Call ajax to load inventory form
			app.helper.showProgress();
			let params = {
				module: 'CPTelesales',
				view: 'Telesales',
				mode: 'getCallScript',
				record: campaignId
			};

			app.request.post({ data: params })
			.then(function (err, data) {
				app.helper.hideProgress();

                if (typeof insertCallBack == 'function') {
                    insertCallBack();
                }

				if (err) {
					app.helper.showErrorNotification({ message: err.message });
					return;
				}

				// Show right side
				if (!element.find('.right-side').is(':visible')) {
					element.find('.right-side').show();
				}

				// Add tab to header
				let headerTab = `<li class="call-script-info"><a data-toggle="tab" href="#call-script-info" aria-expanded="true">${tabLabel}</a></li>`;
				element.find('.right-side .tab-header .nav-tabs').append(headerTab);

				// Add form to tab content
				let container = jQuery(`<div id="call-script-info" class="tab-pane">${data}</div>`);
				element.find('.right-side .tab-content').append(container);
				element.find('.right-side .tab-header .nav-tabs li a[href="#call-script-info"]').click();
			});
		}

		// Added by Vu Mai on 2022-09-27 to handle event cancel tab info in right side
		cancelPopupInfo (form, element) {
			form.on('click', '.cancelLink', function(e) {
				let tabId = jQuery(e.currentTarget).closest('.tab-pane').attr('id');

				jQuery(e.currentTarget).closest('.tab-pane').remove();
				element.find(`.right-side .tab-header .nav-tabs li.${tabId}`).remove();
				element.find(`.right-side .tab-header .nav-tabs li:first a`).click();

				if (element.find('.right-side .tab-header .nav-tabs li').length == 0) {
					element.find('.right-side').hide();
				}
			});
		}

		// Added By Vu Mai on 2022-11-03 to handle event auto insert tab when module is CPTelesales
		registerEventAutoInsertTab () {
			let self = this;
			if (self.props.addition_info.target_view != 'Telesales') return;

            if (self.props.addition_info.target_module != 'CPTelesales') {
                self.$el.find('.edit-customer-info').click();
                let targetModule = self.props.addition_info.target_module;

                if (targetModule == 'HelpDesk' || targetModule == 'SalesOrder' || targetModule == 'Potentials') {
                    self.$el.find(`.edit-target-info[data-target="${targetModule}"]`).click();
                }

                return;
            }

			let params = {
				module: 'CPTelesales',
				action: 'TelesalesAjax',
				mode: 'getCampaignInfo',
				record: self.props.addition_info.target_record_id,
			};

			app.request.post({ data: params })
			.then(function (err, data) {
				if (err) {
					app.helper.showErrorNotification({ 'message': err.message });
					return;
				}

				// Insert default tab
				let insertCallBack = () => {
					// Insert tab according campaign purpose
					let insertPurposeCallBack = () => {
                        let afterInsertCallBack = () => {
                            self.$el.find('.right-side .tab-header .nav-tabs li a[href="#call-script-info"]').click();
                        }

						if (data.purpose == 'selling') {
							self.insertInventoryTab(self.$el, self.props.customer_id, self.props.customer_type, 'CreateFromTelesalesCampaign', afterInsertCallBack);

                            self.$el.find('select[name="events_call_purpose"]').val('call_purpose_marketing').attr('disabled', true).trigger('change');
						}
                        else {
                            self.$el.find('.right-side .tab-header .nav-tabs li a[href="#call-script-info"]').click();
                        }
					}	

					self.insertQuickEditTab(self.$el, self.props.customer_id, self.props.customer_type, true, insertPurposeCallBack);  // Insert customer info after call script inserted
				}

				self.insertCampaignCallScriptTab(self.$el, self.props.addition_info.target_record_id, insertCallBack);
			});
		}
    }

    /**
     * Static object contains methods to convert prop to display value
     */
    BasePopup.prototype.propValueConverters = {
        customer_name: function (value) {
            if (!this.props.customer_number) return 'N/A';

            if (value && this.props.customer_id && this.props.customer_type) {
                return `<a target="_blank" href="index.php?module=${this.props.customer_type}&view=Detail&record=${this.props.customer_id}">${value}</a>`;
            }

            if (this.props.identified === 'identifying') {
                return app.vtranslate('PBXManager.JS_CALL_POPUP_IDENTIFYING');
            }

            return value || app.vtranslate('PBXManager.JS_CALL_POPUP_UNDEFINED');
        },
        account_name: function (value) {
            if (this.props.account_id && this.props.account_id > 0) {
                return `<a target="_blank" href="index.php?module=Accounts&view=Detail&record=${this.props.account_id}">${value}</a>`;
            }

            return value;
        },
        direction: function (value) {
            // Processing popup case
            if (this.props.call_id === 'PROCESSING' && value === 'OUTBOUND') {
                return app.vtranslate('PBXManager.JS_CALL_POPUP_PROCESSING_OUTBOUND_ERROR_MSG');
            }

            const mapping = {
                INBOUND: app.vtranslate('PBXManager.JS_CALL_POPUP_INBOUND'),
                OUTBOUND: app.vtranslate('PBXManager.JS_CALL_POPUP_OUTBOUND'),
            }

            return mapping[value];
        },
        assigned_user_name: function (value) {
            // In case record assign to group
            if (this.props.assigned_user_id && this.props.assigned_user_type === 'Groups') {
                return `<a target="_blank" href="index.php?module=Groups&parent=Settings&view=Detail&record=${this.props.assigned_user_id}">${value}</a>`;
            }

            if (this.props.assigned_user_id) {
                return `<a target="_blank" href="index.php?module=Users&parent=Settings&view=Detail&record=${this.props.assigned_user_id}">${value}</a>`;
            }

            return app.vtranslate('PBXManager.JS_CALL_POPUP_UNDEFINED');
        },
        customer_avatar: function (value) {
            if (!value) {
                return 'resources/images/default-user-avatar.png';
            }

            return value;
        }
    }

    /**
     * Static object contain method to convert prop to specific display value
     */
    BasePopup.prototype.parsers = {
        raw: function (value, rawValue) {
            return rawValue;
        },
        callStateMapping: function (value, rawValue) {
            const mapping = {
                RINGING: app.vtranslate('PBXManager.JS_CALL_POPUP_RINGING'),
                ANSWERED: app.vtranslate('PBXManager.JS_CALL_POPUP_ANSWERED'),
                HANGUP: app.vtranslate('PBXManager.JS_CALL_POPUP_HANGUP'),
                TRANSFERRED: app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFERRED'),
                REJECTED: app.vtranslate('PBXManager.JS_CALL_POPUP_REJECTED'),
            };

            return mapping[rawValue];
        },
        callDurationHours: function (value, rawValue) {
            return Utils.pad(Math.floor(rawValue / 3600), 2);
        },
        callDurationMinutes: function (value, rawValue) {
            return Utils.pad(Math.floor(rawValue / 60) % 60, 2);
        },
        callDurationSeconds: function (value, rawValue) {
            return Utils.pad(rawValue % 60, 2);
        },
        callTitleParser: function (value, rawValue) {
            if (!this.props.subject) {
                const state = this.parsers.callStateMapping.bind(this)(this.props.state, this.props.state);

                if (this.props.customer_id && this.props.customer_name) {
                    const customerName = this.propValueConverters.customer_name.bind(this)(this.props.customer_name);
                    return `[${state}] ${customerName}`;
                }
                else {
                    return `[${state}] ${app.vtranslate('PBXManager.JS_CALL_POPUP_UNDEFINED')}`;
                }
            }

            return value;
        },
        counterParser: function (value, rawValue) {
            if (!rawValue || rawValue == 0) return '';
            return rawValue;
        }
    }

    /**
     * Contain logic method will trigger during prop update. Return `true` to continue render prop cycle
     */
    BasePopup.prototype.propHooks = {
        /**
         * propHooks for call_id
         * @param {*} value
         * @param {*} rawValue
         */
        call_id: function (value, rawValue) {
            // Init call title for the first time
            this.updateUi.bind(this)('subject', this.props.subject);

            // Invoke default value if it not pass to call for the first time
            if (!this.props.assigned_user_name) this.updateUi.bind(this)('assigned_user_name', '');
            if (!this.props.customer_name) this.updateUi.bind(this)('customer_name', '');
            if (!this.props.customer_avatar) this.updateUi.bind(this)('customer_avatar', '');

            // Invoke default logic if some data missing for the first time
            if (!this.props.customer_type) this.$el.find('.showDefault').show();

            return true;
        },

        /**
         * propHooks for state
         * @param {*} value
         * @param {*} rawValue
         */
        state: function (value, rawValue) {
            // Prevent some stupid socket request send empty state cause funny ui result
            if (!rawValue) return;

            // 'TRANSFERED' is already the last signal, ignore hangup from now
            if (this.preProps.state == 'TRANSFERRED' && rawValue == 'HANGUP') return;

            // End that pain
            if (rawValue === 'COMPLETED') {
                return Handler.notifyCompletedCall(this.props.call_id);
            }

            // Process softphone take ringing call before webphone does
            if (rawValue === 'ANSWERED' && this.props.direction === 'INBOUND' && !this.props.answer_btn_clicked) {
                this.update({ handled_by_webphone: false });
            }

            // Update call popup info first
            if (this.props.call_log_id) {
                this.retrievePopupInfo.bind(this)();
            }

            // Handle Sync Customer Info Popup Behavior base on state
            if (rawValue === 'ANSWERED' && this.props.identified === false && this.preProps.state === 'RINGING') {
                this.openSyncCustomerInfoPopup();
            }
            if (rawValue === 'HANGUP' && this.props.identified === false && this.preProps.state === 'RINGING') {
                this.openSyncCustomerInfoPopup();
            }
            if (rawValue === 'HANGUP') {
                this.closeTransferCallModal();
            }

            // Handle state title within transfered call
            if (rawValue === 'TRANSFERRED') {
                const replaceParams = {
                    'transferred_to_name': this.props.transferred_to_name,
                    'transferred_to_ext': this.props.transferred_to_ext,
                };

                const tooltipTitle = app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFERRED_CALL_TITLE', replaceParams);

                this.$el.find('.call-state').tooltip({ title: tooltipTitle });
                this.$el.find('.call-state').toggleClass('tooltip-active', true);
            }
            else {
                this.$el.find('.call-state').tooltip('destroy');
                this.$el.find('.call-state').toggleClass('tooltip-active', false);
            }

            // Invoke update title when state update
            this.updateUi('subject', this.props.subject);

            // Update Call Popup Status data
            Utils.updateData(this.$el, 'state', rawValue);

            // Handle timmer if this call hangup without answered
            if (rawValue === 'HANGUP' && this.preProps.state !== 'ANSWERED' && !this.$timer) {
                this.toggleTimer(true);
            }

            // Toggle timmer
            this.toggleTimer(rawValue === 'ANSWERED');

            // Handle connection status
            this.$el.find('.connection-status > i.fa-circle').toggleClass('active', rawValue === 'ANSWERED');

            // [Start] Update size props base on call status
            let size = this.props.size, preSize = this.preProps.size;

            switch (rawValue) {
                case 'RINGING': {
                    size = 'NORMAL';
                    break;
                }
                case 'ANSWERED': {
                    if (preSize === 'NORMAL' || !preSize) size = 'MEDIUM';
                    break;
                }
                case 'HANGUP': {
                    if (preSize === 'NORMAL' || !preSize) size = 'MEDIUM';
                    break;
                }
                case 'TRANSFERRED': {
                    if (preSize === 'NORMAL' || !preSize) size = 'MEDIUM';
                    break;
                }
                case 'REJECTED': {
                    if (preSize === 'NORMAL' || !preSize) size = 'MEDIUM';
                    break;
                }
            }
            // [End] Update size props base on call status

            // [START] Switch stage between close and open
            let stage;

            switch (rawValue) {
                case 'PROCESSING': {
                    stage = 'CLOSED';
                    break;
                }
                case 'RINGING': {
                    stage = 'CLOSED';
                    break;
                }
                case 'TRANSFERRED': {
                    stage = 'OPEN';
                    break;
                }
                case 'REJECTED': {
                    stage = 'OPEN';
                    break;
                }
                default : {
                    stage = 'OPEN';
                }
            }
            // [END] Switch stage between close and open

            this.update({ size, stage });

            // Handle save call log behavior
            const saveLogBtn = this.$el.find('.saveLogBtn');
            const isSaveLogBtnDisabled = (rawValue !== 'HANGUP') && (rawValue !== 'TRANSFERRED') && (rawValue !== 'REJECTED');
            saveLogBtn.attr('disabled', isSaveLogBtnDisabled);
            saveLogBtn.toggleClass('disabled', isSaveLogBtnDisabled);

            return true;
        },

        /**
         * propHooks for size
         * @param {*} value
         * @param {*} rawValue
         */
        size: function (value, rawValue) {
            // Ignore if user call small size with status ringing
            if (rawValue === 'SMALL' && this.props.state === 'RINGING') return;

            // Save Old size in case popup update to small size to restore later
            if (rawValue === 'SMALL') this.update({ 'restoreSize': this.preProps.size });

            // Handle form responsive
            const mainForm = this.$el.find('form[name="call_log"]');

            if (rawValue === 'MEDIUM') {
                mainForm.find('.inlineOnLarge').removeClass('active');
            }
            else if (rawValue === 'LARGE') {
                mainForm.find('.inlineOnLarge').addClass('active');
            }

            // Update Call Popup size
            Utils.updateData(this.$el, 'size', rawValue);
        },

        /**
         * propHooks for stage
         * @param {*} value
         * @param {*} rawValue
         */
        stage: function (value, rawValue) {
            // Update Call Popup stage
            Utils.updateData(this.$el, 'stage', rawValue);
        },

        /**
         * propHooks for direction
         * @param {*} value
         * @param {*} rawValue
         */
        direction: function (value, rawValue) {
            // Update Call Popup direction data
            Utils.updateData(this.$el, 'direction', rawValue);
            return true;
        },

        /**
         * propHooks for customer_id
         * @param {*} value
         * @param {*} rawValue
         */
        customer_id: function (value, rawValue) {
            this.updateUi.bind(this)('subject', this.props.subject);
            return true;
        },

        /**
         * propHooks for customer_number
         * @param {*} value
         * @param {*} rawValue
         */
        customer_number: function (value, rawValue) {
            this.updateUi.bind(this)('customer_name', this.props.customer_name);
            return true;
        },

        /**
         * propHooks for assigned_user_id
         * @param {*} value
         * @param {*} rawValue
         */
        assigned_user_id: function (value, rawValue) {
            this.updateUi.bind(this)('assigned_user_name', this.props.assigned_user_name);
            return true;
        },

        /**
         * propHooks for account_id
         * @param {*} value
         * @param {*} rawValue
         */
        account_id: function (value, rawValue) {
            this.updateUi.bind(this)('account_name', this.props.account_name);
            return true;
        },

        /**
         * propHooks for customer_name
         * @param {*} value
         * @param {*} rawValue
         */
        customer_name: function (value, rawValue) {
            let identified = false;

            if (!this.props.customer_number) {
                identified = 'undefined';
            }
            else if (this.props.customer_id && this.props.customer_type) {
                identified = true;
            }

            this.update({ identified });

            return true;
        },

        /**
         * propHooks for identified
         * @param {*} value
         * @param {*} rawValue
         */
        identified: function (value, rawValue) {
            if (rawValue === false && this.props.state !== 'RINGING' && this.props.state !== 'PROCESSING') {
                this.openSyncCustomerInfoPopup();
            }
            else if (rawValue === true) {
                this.closeSyncCustomerInfoPopup();
            }

            // Call request and get count update
            if (rawValue === true && this.props.call_id != 'PROCESSING') this.invokeUpdateCounters();

            Utils.updateData(this.$el, 'identified', rawValue);
        },

        /**
         * propHooks for events_call_result
         * @param {*} value
         * @param {*} rawValue
         */
        events_call_result: function (value, rawValue) {
            Utils.updateData(this.$el, 'call-result', rawValue)
        },

        /**
         * propHooks for customer_type
         * @param {*} value
         * @param {*} rawValue
         */
        customer_type: function (value, rawValue) {
            // Update call on dom data
            Utils.updateData(this.$el, 'customer-type', rawValue);

            // Toggle input usage with customer type
            this.toggleFormControl(rawValue);

            // Toggle customer avatar with customer type (for Accounts)
            if (rawValue == 'Accounts') {
                this.$el.find('.customer-avatar img').hide();
                this.$el.find('.customer-avatar .account-ava').css('display', 'flex');
                this.$el.find('.relatedContactId').data('related_parent_module', 'Accounts');
                this.$el.find('.relatedContactId').data('related_parent_id', this.props.customer_id);
            }

            return true;
        },

        /**
         * propHooks for duration
         * @param {*} value
         * @param {*} rawValue
         */
        duration: function (value, rawValue) {
            if (this.props.start_time) this.update({ end_time: this.props.start_time + rawValue * 1000 });
            return true;
        },

        /**
         * propHooks for from_free_call_btn
         */
        from_free_call_btn: function (value, rawValue) {
            Utils.updateData(this.$el, 'from-free-call-btn', rawValue);
        },

        /**
         * propHooks for handled_by_webphone
         */
        handled_by_webphone: function (value, rawValue) {
            Utils.updateData(this.$el, 'handled-by-webphone', rawValue);
        },

        /**
         * propHooks for transfer_call
         */
        transfer_call: function (value, rawValue) {
            Utils.updateData(this.$el, 'transfer-call', rawValue);
        },

        /**
         * propHooks for webphone_answer_supported
         */
        webphone_answer_supported: function (value, rawValue) {
            Utils.updateData(this.$el, 'webphone-answer-supported', rawValue);
        },

        /**
         * propHooks for webphone_reject_supported
         */
        webphone_reject_supported: function (value, rawValue) {
            Utils.updateData(this.$el, 'webphone-reject-supported', rawValue);
        },

        /**
         * propHooks for webphone_hangup_supported
         */
        webphone_hangup_supported: function (value, rawValue) {
            Utils.updateData(this.$el, 'webphone-hangup-supported', rawValue);
        },

        /**
         * propHooks for webphone_mute_supported
         */
        webphone_mute_supported: function (value, rawValue) {
            Utils.updateData(this.$el, 'webphone-mute-supported', rawValue);
        },

        /**
         * propHooks for transferred
         */
        transferred: function (value, rawValue) {
            const replaceParams = {
                'transferred_from_name': this.props.transferred_from.name,
                'transferred_from_ext': this.props.transferred_from.ext,
            };

            app.helper.showAlertNotification({ message: app.vtranslate('PBXManager.JS_CALL_POPUP_TRANSFERRED_CALL_WARNING', replaceParams) }, { delay: 5000 });

            Utils.updateData(this.$el, 'transferred', rawValue);
        },

        /**
         * propHooks for transferred
         */
        hotline: function (value, rawValue) {
            this.$el.find('.hotline-container').toggle(!rawValue ? false : true);

            if (rawValue) {
                let tooltipTitle = app.vtranslate('PBXManager.JS_CALL_POPUP_HOTLINE') + ": " + rawValue;
                this.$el.find('.hotline-tooltip').attr('data-tippy-content', tooltipTitle);
                tippy(this.$el.find('.hotline-tooltip')[0]);
            }

            return true;
        },
    }

    // [START] YOU CAN CUSTOM YOUR OWN CLASS FROM HERE

    // TO CUSTOM EXISTED METHOD, invoke super function with method params before do anything
    // See constructor or initEvents as an example

    class CallPopupHandler extends BaseCallPopupHandler {
        constructor() {
            super();
        }
    }

    class Popup extends BasePopup {
        constructor(props) {
            super(props);
        }

        initEvents() {
            super.initEvents();
        }
    }

    // [END] YOU CAN CUSTOM YOUR OWN CLASS FROM HERE

    // CREATE CALL POPUP HANDLER INSTANCE
    // KEEP THESE CODE FIXED AT BOTTOM OF CALL POPUP MODULE
    // TO MAKE SURE NOTHING GOING TO HAPPEN WITHOUT HANDLER IS READY
    const Handler = new CallPopupHandler();

    // Public only necessary  methods to environment via CallPopup
    window.CallPopup = {
        newState: function (props) {
            if (props.state && props.state.toUpperCase() == 'HANDLED') return;
            return Handler.newState(props);
        },

        has: function (callId) {
            return Handler.has(callId);
        },
    }
})();

jQuery(function ($) {
    // Added by Hieu nguyen on 2022-10-04 to do work-arround to support open popup send SMS and Zalo ZNS outside DetailView
    let detailInstance = new Vtiger_Detail_Js();
    detailInstance.registerSendSmsSubmitEvent();
    // End Hieu Nguyen
});