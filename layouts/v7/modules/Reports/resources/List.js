/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_List_Js("Reports_List_Js",{

	listInstance : false,
	
	addReport : function(url){
		var listInstance = Reports_List_Js.getInstance();
		window.location.href=url+'&folder='+listInstance.getCurrentCvId();
	},

	triggerAddFolder : function(url) {
		app.helper.showProgress();
		app.request.get({url: url}).then(
			function(error, data) {
				app.helper.hideProgress();
				var callback = function(data) {
					var addFolderForm = jQuery('#addFolder');
					addFolderForm.vtValidate({
						submitHandler: function(addFolderForm) {
							var formData = jQuery(addFolderForm).serializeFormData();
							app.request.post({data:formData}).then(function(error,data){
								if(error == null){
									app.helper.hideModal();
									app.helper.showSuccessNotification({message:data.message});
									location.reload(true);
								} else {
									app.helper.showErrorNotification({'message' : error.message});
								}
							});
						},
                        validationMeta : false
					});
				}
				var params = {};
				params.cb = callback
				app.helper.showModal(data, params);
			}
		)
	},

	massDelete : function(url) {
		var listInstance = app.controller();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var searchParams = JSON.stringify(listInstance.getListSearchParams());
			var cvId = listInstance.getCurrentCvId();

			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
			app.helper.showConfirmationBox({'message' : message}).then(
				function(e) {
					var deleteURL = url+'&viewname='+cvId+'&selected_ids='+selectedIds+'&excluded_ids='+excludedIds+'&search_params='+searchParams;
					var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					app.helper.showProgress(deleteMessage);
					app.request.post({url:deleteURL}).then(function(error,data) {
							app.helper.hideProgress();
							if(data){
								app.helper.showSuccessNotification({message:app.vtranslate(data)});
								listInstance.massActionPostOperations(data);
							} else {
								app.helper.showErrorNotification({message: error.message});
								listInstance.massActionPostOperations(error);
							}
						});
				},
				function(error, err){
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},

	massMove : function(url){
		var listInstance = app.controller();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds,
				"viewname" : cvId,
				"search_params" : JSON.stringify(listInstance.getListSearchParams())
			};
			var params = {
				"url":url,
				"data" : postData
			};
			app.helper.showProgress();
			app.request.post(params).then(function(error,data) {
				app.helper.hideProgress();
				var callBackFunction = function(data){
					var reportsListInstance = new Reports_List_Js();
					reportsListInstance.moveReports().then(function(data){
						if(data){
							if(data.success){
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:data.message});
									listInstance.massActionPostOperations(data);
							}else{
								if(data.denied){
									var messageText= data.message+": "+data.denied;
								}else{
									messageText= data.message;
								}
								app.helper.hideModal();
								app.helper.showErrorNotification({message: messageText}); // Updated by Phuc on 2019.10.25
								listInstance.massActionPostOperations(data);
							}
						}
					});
				};
				var params = {};
				params.cb = callBackFunction;
				app.helper.showModal(data,params);
			});
		} else{
			listInstance.noRecordSelectedAlert();
		}

	}

},{


	init : function() {
        this.addComponents();
    },

	folderSubmit : function(){
		var aDeferred = jQuery.Deferred();
		var addFolderForm = jQuery('#addFolder');
		addFolderForm.vtValidate({
			submitHandler:function(form,event){
				event.preventDefault();
				var formData = jQuery(form).serializeFormData();
				app.request.post({data:formData}).then(function(error,data){
					aDeferred.resolve(data);
				});
			},
            validationMeta : false
		});
		return aDeferred.promise();
	},

	moveReports : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#moveReports').on('submit',function(e){
			var formData = jQuery(e.currentTarget).serializeFormData();
			app.helper.showProgress();
			app.request.post({data:formData}).then(function(error,data){
					app.helper.hideProgress();
					aDeferred.resolve(data);
				}
			);
			e.preventDefault();
		});
		return aDeferred.promise();
	},

	updateCustomFilter : function (info){
		var folderId = info.folderId;
		var customFilter =  jQuery("#customFilter");
		var constructedOption = this.constructOptionElement(info);
		var optionId = 'filterOptionId_'+folderId;
		var optionElement = jQuery('#'+optionId);
		if(optionElement.length > 0){
			optionElement.replaceWith(constructedOption);
			customFilter.trigger("liszt:updated");
		} else {
			customFilter.find('#foldersBlock').append(constructedOption).trigger("liszt:updated");
		}
	},

	constructOptionElement : function(info){
		return '<option data-editable="'+info.isEditable+'" data-deletable="'+info.isDeletable+'" data-editurl="'+info.editURL+'" data-deleteurl="'+info.deleteURL+'" class="filterOptionId_'+info.folderId+' filterOptionsLabel" id="filterOptionId_'+info.folderId+'" value="'+info.folderId+'" data-id="'+info.folderId+'">'+info.folderName+'</option>';

	},

	/*
	 * Function to perform the operations after the mass action
	 */
	massActionPostOperations : function(data){
		var thisInstance = this;
		var cvId = this.getCurrentCvId();
        this.clearList();
		if(data){
			var module = app.getModuleName();
			app.request.post({url:'index.php?module='+module+'&view=List&viewname='+cvId}).then(function(error,data) {
				jQuery('#recordsCount').val('');
				jQuery('#totalPageCount').text('');
				app.helper.hideProgress();
				var listViewContainer = thisInstance.getListViewContainer();
				listViewContainer.html(data);
				vtUtils.showSelect2ElementView(listViewContainer.find('select.select2'));
				jQuery('#deSelectAllMsg').trigger('click');
				thisInstance.updatePagination();

				// Added by Phu Vo on 2021.09.30 to fix could not scroll after mass action
				thisInstance.registerFloatingThead();
				// End Phu Vo
			});
		} else {
			app.helper.hideProgress();
			app.helper.showErrorNotification({message:app.vtranslate('JS_LBL_PERMISSION')});
		}
	},

	registerEditOverlayContent: function(){
		jQuery('li.reportEdit a').on('click',function(e){
			var url = jQuery(e.currentTarget).data('url');
			app.request.pjax({url: url}).then(function(error, data) {
				 jQuery('#listViewContent').html(data);
            });
		});
	},
	
	registerEventToShowQuickPreview: function() {
        var self = this;
        var listViewPageDiv = self.getListViewContainer();
        listViewPageDiv.on('click', '.quickView', function(e) {
            var element = listViewPageDiv.find(e.currentTarget);
            var row = element.closest('.listViewEntries');
            var recordId = row.data('id');
            self.showQuickPreviewForId(recordId);
        });
        
    },
	showQuickPreviewForId: function(recordId) {
        var self = this;
        var params = {};
        var moduleName = self.getModuleName();
            params['module'] = moduleName;
        params['record'] = recordId;
        params['view'] = 'ListViewQuickPreview';
        params['navigation'] = 'true';
        
        app.helper.showProgress();
        app.request.get({data: params}).then(function(err, response) {
            app.helper.hideProgress();
            jQuery('#helpPageOverlay').css({"width":"550px","box-shadow":"-8px 0 5px -5px lightgrey",'height':'100vh','background':'white'});
			
			// Modified by Phu Vo on 2019.06.26 to fix preview chart could not fully loaded
			let cb = () => {
				jQuery('.quickPreviewSummary').trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
			}

			app.helper.loadHelpPageOverlay(response, {cb: cb});
			// End fix preview chart could not fully loaded
			
            var params = {
                setHeight: "100%",
                alwaysShowScrollbar: 2,
                autoExpandScrollbar: true,
                setTop: 0,
                scrollInertia: 70,
                mouseWheel: {preventDefault: true}
            };
            app.helper.showVerticalScroll(jQuery('.quickPreview .modal-body'), params);
        });
    },
	
    // Modified by Hieu Nguyen on 2020-11-25 to handle add chart to dashboard
	registerEventForPinChartToDashboard: function () {
        jQuery(document).on('click', '.dashBoardTab', function (e) {
            // Get dropdown menu data
            var tabElement = jQuery(e.currentTarget);
            var originalDropDownMenu = tabElement.closest('.dropdown-menu').data('original-menu');
            var data = originalDropDownMenu.closest('.dropdown').find('.pinToDashboard').data();

            // Prepare widget info
			var dashBoardTabId = tabElement.data('tabId');
            var recordId = data.recordid;
            var primarymodule = data.primemodule;
            var widgetTitle = 'ChartReportWidget_' + primarymodule + '_' + recordId;
            
            // Add to dashboard
            var customParams = { 
                reportId: recordId,
                dashBoardTabId: dashBoardTabId, 
                title: widgetTitle,
            };
            
            CustomReportHelper.addChartToDashboard(customParams, null);
		});
	},
    // End Hieu Nguyen
	
	registerFolderEditEvent:function(){
		jQuery('#module-filters').on('click', '.editFolder', function (e) {	// Refactored by Hieu Nguyen on 2021-09-17
			var url = jQuery(e.currentTarget).data('url');
			var folderId = jQuery(e.currentTarget).closest('.popover-content').data('filterId');	// Added by Hieu Nguyen on 2021-09-17 to get folder id
			
			app.request.get({url:url}).then(function(error,data){
				var callBackFunction = function(data){
					var reportsListInstance = new Reports_List_Js();
					reportsListInstance.folderSubmit().then(function(data){
						if(data){
							if(data.success){
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:data.message});

								// Added by Hieu Nguyen on 2021-09-17 to display new folder name after saving
								let filterEle = jQuery('.filterName[data-filter-id="'+ folderId +'"]');
								filterEle.find('.name').text(data.info.folderName);

								if (filterEle.closest('.listViewFilter').hasClass('active')) {
									filterEle.closest('.listViewFilter').trigger('click');
								}
								// End Hieu Nguyen
							}else{
								if(data.denied){
									var messageText= data.message+": "+data.denied;
								}else{
									messageText= data.message;
								}
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:messageText});
							}
						}
					});
				};
				var params = {};
				params.cb = callBackFunction;
				app.helper.showModal(data,params);
			});
		});
	},
	
	registerFolderDeleteEvent:function(){
		jQuery('#module-filters').on('click', '.deleteFolder', function (e) {	// Refactored by Hieu Nguyen on 2021-09-17
            var element = jQuery(e.target);
			var url = jQuery(e.currentTarget).data('url');
			var folderId = jQuery(e.currentTarget).closest('.popover-content').data('filterId');	// Modified by Hieu Nguyen on 2021-09-17 to get folder id
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			app.helper.showConfirmationBox({'message' : message}).then(function(e) {
				app.request.post({url:url}).then(function(error,data){
					if(data.success){
						app.helper.showSuccessNotification({"message":data.message});

						// Modified by Hieu Nguyen on 2021-09-17 to set default folder as active when the active folder is deleted
						let filterEle = jQuery('.filterName[data-filter-id="'+ folderId +'"]');
						filterEle.closest(".listViewFilter").remove();

						if (filterEle.closest('.listViewFilter').hasClass('active')) {
							jQuery('.filterName[data-filter-id="All"]').closest('.listViewFilter').trigger('click');
						}
						// End Hieu Nguyen
					} else {
						app.helper.showErrorNotification({"message":data.message});
					}
                element.closest('.popover').remove();
                });
			},
			function(error, err){
				//Do nothing
			});
		});
	},
	
	markFolderAsActive : function() {
		var folder = jQuery('[name="folder"]').val();
		jQuery('.filterName[data-filter-id="'+folder+'"]').closest(".listViewFilter").addClass('active');
	},
	
	getCurrentCvId : function() {
		return jQuery('.listViewFilter.active').find('.filterName').data("filter-id");
	},
	
	registerInlineEdit : function(currentTrElement) {
		//do nothing as inline edit not there for reports
	},
    
    registerFolderchange : function(){
        jQuery(".listViewFilter.active").find('.foldericon').removeClass('fa-folder').addClass('fa-folder-open');
        jQuery(".listViewFilter").click(function (e) {
          jQuery(".listViewFilter").find('.foldericon').removeClass('fa-folder-open').addClass('fa-folder');
            var element = jQuery(e.currentTarget);
            var value = element.find('.foldericon');
            if (value.hasClass('fa-folder')) {
                jQuery(value.removeClass('fa-folder').addClass('fa-folder-open'));
            }
        });
    },

	registerFolderScroll : function() {
		// Comment out by Phu Vo to use another method to control scrollbar
		// app.helper.showVerticalScroll(jQuery('.list-menu-content'), {
		// 	setHeight: 450,
		// 	autoExpandScrollbar: true,
		// 	scrollInertia: 200,
		// 	autoHideScrollbar: true
		// });
		// End Phu Vo
	},

    // Added by Hieu Nguyen on 2019-12-11 to fix issue Pin To Dashboard dropdown does not dismiss
    registerPinToDashboardDropdownDismissEvent: function () {
        jQuery(document).click(function (e) {
            if (!jQuery(e.target).hasClass('dropdown-header')) {
                jQuery('.dashBoardTabMenu:visible').remove();
            }
		});
    },
    // End Hieu Nguyen

	registerEvents : function(){
        this._super();

		// Added by Hieu Nguyen on 2021-09-17 to prevent bug multiple event handler on filter buttons
		window.skipDefaultEditFilterEvent = true;
		window.skipDefaultDeleteFilterEvent = true;
		// End Hieu Nguyen

        this.registerEditOverlayContent();
        this.registerEventForPinChartToDashboard();
        this.registerFolderEditEvent();
        this.registerFolderDeleteEvent();
        this.markFolderAsActive();
        this.registerFolderchange();
        this.registerFolderScroll();
        this.registerPinToDashboardDropdownDismissEvent();  // Added by Hieu Nguyen on 2019-12-11 to fix issue Pin To Dashboard dropdown does not dismiss
	}
});
