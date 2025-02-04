/*
*   Kanban.js
*   Author: Phu Vo
*   Date: 2019-02-21
*   Purpose: Kanban App Helper functions
*/

var KanbanHelper = {
    // Shorthand vtiger notify error
    notifyError(msg) {
        app.helper.showErrorNotification({ message: msg });
    },
    // Shorthand vtiger notify success
    notifySuccess(msg) {
        app.helper.showSuccessNotification({ message: msg });
    },
    // Shorthand vtiger ajax request
    request(params, callback, background) {
        let defaultParams = {
            module: app.module(),
            action: "HandleAjax"
        };

        params = Object.assign(defaultParams, params);

        if(!background) app.helper.showProgress();

        app.request.post({ data: params }).then((err, res) => {
            app.helper.hideProgress();
            if(err) this.notifyError(app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR'));
            if(typeof callback === 'function') callback(err, res);
        });
    },
    // Util function to check if that element has a vertical scrollbar
    hasScrollBar(ui) {
        if(!(ui instanceof jQuery)) ui = jQuery(ui); // Only work with jquery var

        setTimeout(function() {
            if(ui.get(0).scrollHeight > ui.height()) {
                ui.addClass('hasScrollBar');
                return true;
            }
    
            ui.removeClass('hasScrollBar');
            return false;
        });
    },
    // Init popover for kanban
    initPopover() {
        // detroy before create new (just in case we init popover before) to prevent memory leaks
        $('span.kb-popover').popover('destroy');

        $('span.kb-popover').popover({
            html: true,
            container: $(this).closest('div.items').get()[0],
            content: $('div#kb-popover-template').html(),
            placement: 'auto',
            boundary: 'viewport'
        });

        $('body').off('click').on('click', function (e) {
            //did not click a popover toggle or popover
            if (e.target.dataset['prevent'] !== 'hide_popover'
                && $(e.target).parents('div.popover.in').length === 0) { 
                $('span.kb-popover').popover('hide');
            }
        });
    },
    // Util function for reset chosen select dom
    resetChosen(dom) {
        dom.removeClass('chzn-done').attr('id', '').next('div.chzn-container').remove();
        dom.chosen();
        
        this.initSortableSelect();
    },
    reOrder(dom, order) {
        const choices = dom.find('li.search-choice');
        order.forEach((value) => {
            const rel = choices.find(`[rel="${value}"]`);
            const choice = rel.closest('.search-choice');
            choice.insertBefore(dom.find('.ui-sortable-handle'));
        });
    },
    // init sortable for chosen select
    initSortableSelect(params) {
        let defaultParams = {
            containment: 'parent'
        }

        params = Object.assign(defaultParams, params);

        $('div.sortable-select').find("ul.chzn-choices").sortable(params);
    },
    // load related ui meta for dynamic quick edit
    loadRelatedUimeta(moduleName) {
        if(typeof _FIELDS_INFO !== "undefined" || (typeof _MODULES_FIELDS_INFO !== "undefined" && _MODULES_FIELDS_INFO[moduleName])) {
            window.related_uimeta = (() => {
                let fieldInfo;
                if(typeof _FIELDS_INFO !== "undefined") {
                    fieldInfo = _FIELDS_INFO;
                }
                else {
                    fieldInfo = _MODULES_FIELDS_INFO[moduleName];
                }

                return {
                    field: {
                        get: function(name, property) {
                            if (name && property === undefined) {
                                return fieldInfo[name];
                            }
                            if (name && property) {
                                return fieldInfo[name][property]
                            }
                        },
                        isMandatory: function(name) {
                            if (fieldInfo[name]) {
                                return fieldInfo[name].mandatory;
                            }
                            return false;
                        },
                        getType: function(name) {
                            if (fieldInfo[name]) {
                                return fieldInfo[name].type
                            }
                            return false;
                        }
                    },
                };
            })();
        }
    },
    // Quick get ui display data start here
    getAssignedLink(id) {
        if(id) return `index.php?module=Users&parent=Settings&view=Detail&record=${id}`;

        return '';
    },
    goToAssignUserProfile(id) {
        if(id) {
            if(Number(_USER_INFO.id) !== Number(id) && !_USER_INFO.isAdmin) {
                this.notifyError(app.vtranslate('JS_ERROR_NOT_HAVE_USER_INFO_PERMISSION'));
                return;
            }

            let link = this.getAssignedLink(id);
            window.open(link, '_blank');
        }
        else {
            this.notifyError(app.vtranslate('JS_ERROR_NOT_FIND_INFO'));
        }
    },
    getItemLink(moduleName, id) {
        if(id) {
            return `index.php?module=${moduleName}&view=Detail&record=${id}`;
        }

        return '';
    },
    formatDate(str) {
        if(str) {
            return moment(str).format(KanbanVars.dateFormat[app.getUserLanguage()]);
        }

        return '';
    },
    // Quick get ui display data end here
    getMultiselectDefault(options) {
        if(options) return options.splice(0, 5);
        return [];
    },
    setDefaultAvatar(ui) {
        ui.src = KanbanVars.defaultAvatar;
    },
    getSelectDefault(container, name) {
        let select = container.find(`select[name="${name}"]`);
        let defaultValue = '';

        select.find('option').each(function() {
            if($(this).val()) {
                defaultValue = $(this).val();
                return false; // Break this each loop
            }
        });

        return defaultValue;
    },
    showModal(params) {
        this.hideModal();

        if(!(params.ui instanceof jQuery)) params.ui = jQuery(params.ui);

        params.ui.find('div.modal-backdrop, a.cancelLink, button.close').off('click').on('click', () => this.hideModal());

        params.ui.off('show.kb.modal').on('show.kb.modal', () => {
            if(params.show && params.show instanceof Function) params.show(params.ui);
        });

        params.ui.off('hide.kb.modal').on('hide.kb.modal', () => {
            if(params.hide && params.hide instanceof Function) params.hide(params.ui);
        });

        params.ui.show().trigger('show.kb.modal');

        return params.ui;
    },
    hideModal() {
        app.helper.hideModal();
        $('.kb-modal:visible').hide().trigger('hide.kb.modal');
    },

    // Added by Phu Vo on 2018.02.26
    // [TODO] Move to vtUtils with dynamic related_ui_meta
    // params: {
    //     data: {
    //         module: [moduleName],
    //         record: [recordId],
    //     },
    //     postShowCb: [function],
    //     postSaveCb: [function],   
    // }
    openQuickEditModal(params) {
        if(! (typeof Vtiger_Detail_Js === 'function')) return false;

        let thisInstance = Vtiger_Detail_Js.getInstanceByModuleName('Vtiger');

        params.data = {...params.data, ...{view: 'Edit', displayMode: 'overlay'}};

        app.helper.showProgress();
        app.request.get({data: params.data}).then(function(err, response) {
            app.helper.hideProgress();
            let overlayParams = {'backdrop': 'static', 'keyboard': false};
            app.helper.loadPageContentOverlay(response, overlayParams).then(function(container) {
                let overlay = jQuery('.overlayEdit');
                let height = jQuery(window).height() - jQuery('.app-fixed-navbar').height() - jQuery('.overlayFooter').height() - 80;

                let scrollParams = {
                    setHeight: height,
                    alwaysShowScrollbar: 2,
                    autoExpandScrollbar: true,
                    setTop: 0,
                    scrollInertia: 70
                }
                app.helper.showVerticalScroll(jQuery('.editViewContents'), scrollParams);

                thisInstance.registerOverlayEditEvents(params.data.module, container);
                
                app.event.on('post.overLayEditView.loaded',function(e, container){
                    jQuery('#EditView').vtValidate({
                        submitHandler : function(form){
                            window.onbeforeunload = null;
                            let e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                            app.event.trigger(e, $(form).serializeFormData()); // Modified by Phu Vo on 2019.07.30 to add data to event
                            if(e.isDefaultPrevented()) {
                                return false;
                            }
                            let formData = new FormData(form);
                            let postParams = {
                                data: formData,
                                contentType: false,
                                processData: false
                            };
                            app.helper.showProgress();
                            app.request.post(postParams).then(function(err,data){
                                app.helper.hideProgress();
                                if (err === null) {
                                    jQuery('.vt-notification').remove();
                                    app.helper.hidePageContentOverlay();
                                    if(typeof params.postSaveCb === 'function') params.postSaveCb(data);
                                } else {
                                    app.event.trigger('post.save.failed', err);
                                }
                        });
                        return false;
                        }
                    });

                    jQuery('#EditView').find('.saveButton').on('click', function(e){
                        window.onbeforeunload = null;
                    });
                });
                
                app.event.trigger('post.overLayEditView.loaded', overlay);
                if(typeof params.postShowCb === 'function') params.postShowCb();
            });
        });
    },
    openQuickPreview(target, id) {
        Vtiger_Index_Js.getInstance().showQuickPreviewForId(id, target);
    },
    // Todo: Review later
    // related lists popup
    openRelatedPopup(relatedModuleName, parentModule, parentId, parentName, relatedType = '') {
        let labelKey = relatedType ? relatedType : relatedModuleName;
        let labelMapping = {
            Call: 'JS_CALLS',
            Task: 'JS_TASKS',
            Meeting: 'JS_MEETINGS',
            Potentials: 'JS_OPPORTUNITIES',
            SalesOrder: 'JS_SALES_ORDERS',
            HelpDesk: 'JS_TICKETS',
        }
        
        let callback = popup => {
            // replace popup title
            popup.addClass('tele-modal preventClick');
            popup.find('h4.pull-left').html(`
                <div class="title">${app.vtranslate(labelMapping[labelKey], KanbanVars.module)}</div>
                <div class="parent-name">${parentName}</div>
            `);
        }

        let searchParams = [[], []]; // 0: AND, 1: OR

        if (relatedType) searchParams[0].push(['activitytype', 'e', relatedType]);

        let params = {
            data: {
                module: parentModule,
                relatedModule: relatedModuleName,
                record: parentId,
                search_params: searchParams,
            },
            preShowCb: callback,
        }

        vtUtils.openRelatedListModal(params)
    },
    disablePopupLinks(ui) {
        if( !(ui instanceof jQuery) )  ui = jQuery(ui);

        ui.find('a').each(function() {
            $(this).replaceWith($(this).text())
        })
    },
    getRemainHeight(container, ui) {
        let self = this;
        if( !(container instanceof jQuery)) container = jQuery(container);
        ui = container.find(ui);

        if(container.length > 1) return;
        if(ui.length === 0) return;

        let getNumber = str => {
            return Number((String(str).match(/\d/g) || []).join(''));
        }

        let used = 0;

        container.children().not(ui).each(function() {
            used += $(this).height() + getNumber($(this).css('margin-top')) + getNumber($(this).css('margin-bottom'));
        });

        return container.height() - used;
    },
    getTextColor(backgroundHexColor) {
        if (!backgroundHexColor) backgroundHexColor = '';
		let contrast = app.helper.getColorContrast(backgroundHexColor);
        let textColor = (contrast === 'dark') ? 'white' : 'black';
        return textColor;
    },
    getSearchString(str = '') {
        str = '' + str;
        str = str.unUnicode();
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    },
}