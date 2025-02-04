/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Vtiger_DashBoard_Js",{

	gridster : false,

	//static property which will store the instance of dashboard
	currentInstance : false,
	dashboardTabsLimit : 10,

	addWidget : function(element, url) {
		var element = jQuery(element);
		var linkId = element.data('linkid');
		var name = element.data('name');

		// After adding widget, we should remove that widget from Add Widget drop down menu from active tab
		var activeTabId = Vtiger_DashBoard_Js.currentInstance.getActiveTabId();
		jQuery('a[data-name="'+name+'"]',"#tab_"+activeTabId).parent().hide();
		var widgetContainer = jQuery('<li class="new dashboardWidget loadcompleted" id="'+ linkId +'" data-name="'+name+'" data-mode="open"></li>');
		widgetContainer.data('url', url);
		var width = element.data('width');
		var height = element.data('height');
		Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
		Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);

		// Added by Hieu Nguyen on 2021-08-27 to disable widget resize
		if (url.indexOf('fixed_size=true') > 0) {
			widgetContainer.find('.gs-resize-handle').remove();
		}
		// End Hieu Nguyen

		// Added by Phu Vo on 2020.10.30 to allow reload window on modal close
		if (window.DashboardConfig) window.DashboardConfig.reloadOnAddDashboardWidgetModalClose = true;
		// End Phu Vo

		// Added by Phu Vo on 2020.11.11 to show success Notification
		app.helper.showSuccessNotification({ message: app.vtranslate('Home.JS_DASHBOARD_ADD_WIDGET_SUCCESS_MSG') });
		// End Phu Vo
	},

	addMiniListWidget: function(element, url) {
		// 1. Show popup window for selection (module, filter, fields)
		// 2. Compute the dynamic mini-list widget url
		// 3. Add widget with URL to the page.

		element = jQuery(element);

		app.request.post({"url":"index.php?module=Home&view=MiniListWizard&step=step1"}).then(function(err,res){
			var callback = function(data){
				var wizardContainer = jQuery(data);
				var form = jQuery('form', wizardContainer);

				var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
				var filteridSelectDOM = jQuery('select[name="filterid"]', wizardContainer);
				var fieldsSelectDOM = jQuery('select[name="fields"]', wizardContainer);

				var moduleNameSelect2 = vtUtils.showSelect2ElementView(moduleNameSelectDOM, {
					placeholder: app.vtranslate('JS_SELECT_MODULE')
				});
				var filteridSelect2 = vtUtils.showSelect2ElementView(filteridSelectDOM,{
					placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
				});
				var fieldsSelect2 = vtUtils.showSelect2ElementView(fieldsSelectDOM, {
					placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
					closeOnSelect: true,
					maximumSelectionSize: (_VALIDATION_CONFIG.minilist_widget_max_columns)? _VALIDATION_CONFIG.minilist_widget_max_columns : 4, // Modified to 4 by Hieu Nguyen on 2019-08-22. Maximum should be no more than 4 columns as each column width is 2.4 flex
				});
				var footer = jQuery('.modal-footer', wizardContainer);

				filteridSelectDOM.closest('tr').hide();
				fieldsSelectDOM.closest('tr').hide();
				footer.hide();

				moduleNameSelect2.change(function(){
					if (!moduleNameSelect2.val()) return;

					var moduleNameSelect2Params = {
						module: 'Home',
						view: 'MiniListWizard',
						step: 'step2',
						selectedModule: moduleNameSelect2.val()
					};

					app.request.post({"data":moduleNameSelect2Params}).then(function(err,res) {
						filteridSelectDOM.empty().html(res).trigger('change');
						filteridSelect2.closest('tr').show();
						fieldsSelect2.closest('tr').hide();
						footer.hide();
					})
				});
				filteridSelect2.change(function(){
					if (!filteridSelect2.val()) return;

					var selectedModule = moduleNameSelect2.val();
					var filteridSelect2Params = {
						module: 'Home',
						view: 'MiniListWizard',
						step: 'step3',
						selectedModule: selectedModule,
						filterid: filteridSelect2.val()
					};

					app.request.post({"data":filteridSelect2Params}).then(function(err,res){
						fieldsSelectDOM.empty().html(res).trigger('change');
						var translatedModuleNames = JSON.parse(jQuery("#minilistWizardContainer").find("#translatedModuleNames").val());
						var fieldsLabelText = app.vtranslate('JS_EDIT_FIELDS', translatedModuleNames[selectedModule], translatedModuleNames[selectedModule]);
						fieldsSelect2.closest('tr').find('.fieldLabel label').text(fieldsLabelText);
						fieldsSelect2.closest('tr').show();
					});
				});
				fieldsSelect2.change(function() {
					if (!fieldsSelect2.val()) {
						footer.hide();
					} else {
						footer.show();
					}
				});

				form.submit(function(e){
					e.preventDefault();
					//To disable savebutton after one submit to prevent multiple submits
					jQuery("[name='saveButton']").attr('disabled','disabled');
					var selectedModule = moduleNameSelect2.val();
					var selectedFilterId= filteridSelect2.val();
					var selectedFields = fieldsSelect2.val();
					if (typeof selectedFields != 'object') selectedFields = [selectedFields];

					// TODO mandatory field validation

					finializeAdd(selectedModule, selectedFilterId, selectedFields);
				});
			}
			app.helper.showPopup(res, { 'cb': callback }); // Modified by Phu Vo on 2020.10.16
		});

		function finializeAdd(moduleName, filterid, fields) {
			var data = {
				module: moduleName
			}
			if (typeof fields != 'object') fields = [fields];
			data['fields'] = fields;

			url += '&filterid='+filterid+'&data=' + JSON.stringify(data);
			var linkId = element.data('linkid');
			var name = element.data('name');
			var widgetContainer = jQuery('<li class="new dashboardWidget loadcompleted" id="'+ linkId +"-" + filterid +'" data-name="'+name+'" data-mode="open"></li>');
			widgetContainer.data('url', url);
			var width = element.data('width');
			var height = element.data('height');
			Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
			Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);

			// Added by Phu Vo on 2020.10.30
			app.helper.hidePopup();
			if (window.DashboardConfig) window.DashboardConfig.reloadOnAddDashboardWidgetModalClose = true;
			// End Phu Vo

			// Added by Phu Vo on 2020.11.11 to show success Notification
			app.helper.showSuccessNotification({ message: app.vtranslate('Home.JS_DASHBOARD_ADD_WIDGET_SUCCESS_MSG') });
			// End Phu Vo
		}
	},

	addNoteBookWidget : function(element, url) {
		// 1. Show popup window for selection (module, filter, fields)
		// 2. Compute the dynamic mini-list widget url
		// 3. Add widget with URL to the page.

		element = jQuery(element);
		var activeTabId = Vtiger_DashBoard_Js.currentInstance.getActiveTabId();	// Added by Hieu Nguyen on 2022-07-04

		app.request.get({"url":"index.php?module=Home&view=AddNotePad"}).then(function(err,res){
			var callback = function(data){
				var wizardContainer = jQuery(data);
				var form = jQuery('form', wizardContainer);
				var params = {
					submitHandler : function(form){
						//To prevent multiple click on save
						var form = jQuery(form);
						jQuery("[name='saveButton']").attr('disabled','disabled');
						var notePadName = form.find('[name="notePadName"]').val();
						var notePadContent = form.find('[name="notePadContent"]').val();
						var linkId = element.data('linkid');
						var noteBookParams = {
							'module' : app.getModuleName(),
							'action' : 'NoteBook',
							'mode' : 'NoteBookCreate',
							'notePadName' : notePadName,
							'notePadContent' : notePadContent,
							'linkId' : linkId,
							'tab': activeTabId,	// Refactored by Hieu Nguyen on 2022-07-04
						}
						app.request.post({"data":noteBookParams}).then(function(err,data) {
							if(data){
								var widgetId = data.widgetId;

								// Modified by Phu Vo on 2020.10.30
								app.helper.hidePopup();
								if (window.DashboardConfig) window.DashboardConfig.reloadOnAddDashboardWidgetModalClose = true;
								// End Phu Vo

								url += '&widgetid='+widgetId;

								var name = element.data('name');
								var widgetContainer = jQuery('<li class="new dashboardWidget loadcompleted" id="'+ linkId +"-" + widgetId +'" data-name="'+name+'" data-mode="open"></li>');
								widgetContainer.data('url', url);
								var width = element.data('width');
								var height = element.data('height');
								Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
								Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
							}
						});
						return false;
					}
				}
				form.vtValidate(params);
			}
			app.helper.showPopup(res, { 'cb': callback }); // Modified by Phu Vo on 2020.10.16
		});

	}

},{


	container : false,
	instancesCache : {},

	init : function() {
		Vtiger_DashBoard_Js.currentInstance = this;
		this.addComponents();
	},

	addComponents : function (){
		this.addComponent('Vtiger_Index_Js');
	},

	getDashboardContainer : function(){
		return jQuery(".dashBoardContainer");
	},

	getContainer : function(tabid) {
		if(typeof tabid == 'undefined'){
			tabid = this.getActiveTabId();
		}
		return jQuery(".gridster_"+tabid).find('ul');
	},

	getWidgetInstance : function(widgetContainer) {
			var id = widgetContainer.attr('id');
			if(!(id in this.instancesCache)) {
					var widgetName = widgetContainer.data('name');
					if(widgetName === "ChartReportWidget"){
						widgetName+= "_"+id;
					}
					this.instancesCache[id] = Vtiger_Widget_Js.getInstance(widgetContainer, widgetName);
			}
	else{
		this.instancesCache[id].init(widgetContainer);
	}
			return this.instancesCache[id];
	},

	// Added by Hieu Nguyen on 2022-07-04
	getActiveTab: function () {
		let container = this.getDashboardContainer();
		return container.find('.tab-pane.active');
	},

	// Refactored by Hieu Nguyen on 2022-07-04
	getActiveTabId: function () {
		let activeTab = this.getActiveTab();
		return activeTab.data('tabid');
	},

	// Refactored by Hieu Nguyen on 2022-07-04
	getActiveTabName: function () { 
		let activeTab = this.getActiveTab();
		return activeTab.data('tabname');
	},

	getgridColumns: function(){
		return 3; // Added by Phuc on 2020.07.15 to always return size 3

		var _device_width = $(window).innerWidth();
		var gridWidth = _device_width;

		if (_device_width < 480) {
			gridWidth = 1;
		} else if (_device_width >= 480 && _device_width < 768) {
			gridWidth = 1;
		} else if (_device_width >= 768 && _device_width < 992) {
			gridWidth = 2;
		} else if (_device_width >= 992 && _device_width < 1440) {
			gridWidth = 3;
		} else {
			gridWidth = 4;
		}
		return gridWidth;
	},

	saveWidgetSize: function (widget) {
		var dashboardTabId = this.getActiveTabId();	// Refactored by Hieu Nguyen on 2022-07-04
		var widgetSize = {
			'sizex': widget.attr('data-sizex'),
			'sizey': widget.attr('data-sizey')
		};
		if (widgetSize.sizex && widgetSize.sizey) {
			var params = {
				'module': 'Vtiger',
				'action': 'SaveWidgetSize',
				'id': widget.attr('id'),
				'size': widgetSize,
				'tabid': dashboardTabId
			};
			app.request.post({"data": params}).then(function (err, data) {
			});
		}
	},

	getWaitingForResizeCompleteMsg: function () {
		return '<div class="wait_resizing_msg"><p class="text-info">'+app.vtranslate('JS_WIDGET_RESIZING_WAIT_MSG')+'</p></div>';
	},

	/** Modified by Phu Vo on 2021.05.21 */
	registerGridster : function() {
		let thisInstance = this;
		let widgetMargin = 10;
		let activeTabId = this.getActiveTabId();
		let activeGridster = jQuery(".gridster_"+activeTabId);
		let items = activeGridster.find('ul li');
		items.detach();

		// Constructing the grid based on window width
		let cols = this.getgridColumns();
		$(".mainContainer").css('min-width', "500px");
		
		let colWidth = (Math.floor((this.getContainer().width())/cols) - (2*widgetMargin));


		Vtiger_DashBoard_Js.gridster = this.getContainer().gridster({
			widget_margins: [widgetMargin, widgetMargin],
			widget_base_dimensions: [colWidth, 320],
			min_cols: 1,
			max_cols: 4,
			min_rows: 20,
			resize : {
				enabled : window._CAN_EDIT_DASHBOARD || false, // Modified by Phu Vo on 2020.10.30
				start: function (e, ui, widget) {
					let widgetContent = widget.find('.dashboardWidgetContent');
					widgetContent.before(thisInstance.getWaitingForResizeCompleteMsg());
					widgetContent.addClass('hide');
				},
				stop : function(e, ui, widget) {
					let widgetContent = widget.find('.dashboardWidgetContent');
					widgetContent.prev('.wait_resizing_msg').remove();
					widgetContent.removeClass('hide');

					let widgetName = widget.data('name');
					 /**
					 * we are setting default height in DashBoardWidgetContents.tpl
					 * need to overwrite based on resized widget height
					 */ 
					let widgetChartContainer = widget.find(".widgetChartContainer");
					if(widgetChartContainer.length > 0){
						widgetChartContainer.css("height",widget.height() - 60);
					}
					widgetChartContainer.html('');
					Vtiger_Widget_Js.getInstance(widget, widgetName);
					widget.trigger(Vtiger_Widget_Js.widgetPostResizeEvent);
					thisInstance.saveWidgetSize(widget);
				}
			},
			draggable: {
				enabled: window._CAN_EDIT_DASHBOARD || false,	// Added by Hieu Nguyen on 2022-05-12 to control drag dashlet
				'stop': function(event, ui) {
					 thisInstance.savePositions(activeGridster.find('.dashboardWidget'));
				}
			}
		}).data('gridster');


		items.sort(function(a,b){
			let widgetA = jQuery(a);
			let widgetB = jQuery(b);
			let rowA = parseInt(widgetA.attr('data-row'));
			let rowB = parseInt(widgetB.attr('data-row'));
			let colA = parseInt(widgetA.attr('data-col'));
			let colB = parseInt(widgetB.attr('data-col'));

			if(rowA === rowB && colA === colB) {
				return 0;
			}

			if(rowA > rowB || (rowA === rowB && colA > colB)) {
				return 1;
			}
			return -1;
		});
		jQuery.each(items , function (i, e) {
			var item = $(this);
			var columns = parseInt(item.attr("data-sizex")) > cols ? cols : parseInt(item.attr("data-sizex"));
			var rows = parseInt(item.attr("data-sizey"));
			if(item.attr("data-position")=="false"){
				Vtiger_DashBoard_Js.gridster.add_widget(item, columns, rows);
			} else {
				Vtiger_DashBoard_Js.gridster.add_widget(item, columns, rows);
			}
		});
		//used when after gridster is loaded
		thisInstance.savePositions(activeGridster.find('.dashboardWidget'));
	},

	savePositions: function(widgets) {
		var widgetRowColPositions = {}
		for (var index=0, len = widgets.length; index < len; ++index) {
			var widget = jQuery(widgets[index]);
			widgetRowColPositions[widget.attr('id')] = JSON.stringify({
					row: widget.attr('data-row'), col: widget.attr('data-col')
			});
		}
		var params = {
			module: 'Vtiger', 
			action: 'SaveWidgetPositions', 
			positionsmap: widgetRowColPositions
		};
		app.request.post({"data":params}).then(function(err,data){
		});
	},

	// Refactored by Hieu Nguyen on 2022-07-04
	getDashboardWidgets: function () {
		let getActiveTab = this.getActiveTab();
		return getActiveTab.find('.dashboardWidget');
	},

	 loadWidgets : function() {
		var thisInstance = this;
		var widgetList = thisInstance.getDashboardWidgets();
		widgetList.each(function(index,widgetContainerELement){
			if(thisInstance.isScrolledIntoView(widgetContainerELement)){
				thisInstance.loadWidget(jQuery(widgetContainerELement));
				jQuery(widgetContainerELement).addClass('loadcompleted');
			}
		});
	},

	isScrolledIntoView : function (elem) {
		var viewportWidth = jQuery(window).width(),
		viewportHeight = jQuery(window).height(),

		documentScrollTop = jQuery(document).scrollTop(),
		documentScrollLeft = jQuery(document).scrollLeft(),

		minTop = documentScrollTop,
		maxTop = documentScrollTop + viewportHeight,
		minLeft = documentScrollLeft,
		maxLeft = documentScrollLeft + viewportWidth,

		$targetElement = jQuery(elem),
		elementOffset = $targetElement.offset();
		if (
			(elementOffset.top > minTop && elementOffset.top < maxTop) &&
			(elementOffset.left > minLeft &&elementOffset.left < maxLeft)
			){
				return true;
			 } 
		else {
				return false;
			 }
	},

	loadWidget : function(widgetContainer) {
		var thisInstance = this;
		var urlParams = widgetContainer.data('url');
		var mode = widgetContainer.data('mode');

		var activeTabId = this.getActiveTabId();
		urlParams += "&tab="+activeTabId;

		// Added by Hieu Nguyen on 2021-08-27 to disable widget resize
		if (urlParams.indexOf('fixed_size=true') > 0) {
			widgetContainer.find('.gs-resize-handle').remove();
		}
		// End Hieu Nguyen

        // Modified by Hieu Nguyen on 2020-02-04 to fix speed up loading at first page load (Feature #61)
        widgetContainer.append('<div class="loading text-center" style="position: absolute; top: 40%; left: 44%"><img width="50" src="layouts/v7/skins/images/loading.gif" /></div>');
        // End Hieu Nguyen

		if(mode == 'open') {
			app.request.post({"url":urlParams}).then(function(err,data){
				widgetContainer.prepend(data);
				vtUtils.applyFieldElementsView(widgetContainer);

				var widgetChartContainer = widgetContainer.find(".widgetChartContainer");
				if (widgetChartContainer.length > 0) {
					widgetChartContainer.css("height", widgetContainer.height() - 60);
				}

				thisInstance.getWidgetInstance(widgetContainer);
				try {
					widgetContainer.trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
				} catch (error) {
					widgetContainer.find('[name="chartcontent"]').html('<div>'+app.vtranslate('JS_NO_DATA_AVAILABLE')+'</div>').css({'text-align': 'center', 'position': 'relative', 'top': '100px'});
				}

                // Modified by Hieu Nguyen on 2020-02-04 to fix speed up loading at first page load (Feature #61)
                widgetContainer.find('.loading').hide();
                // End Hieu Nguyen
			});
		} else {
		}
	},

	registerRefreshWidget : function() {
		var thisInstance = this;
		this.getContainer().on('click', 'a[name="drefresh"]', function(e) {
			var element = $(e.currentTarget);
			var parent = element.closest('li');
			var widgetInstnace = thisInstance.getWidgetInstance(parent);
			widgetInstnace.refreshWidget();
			return;
		});
	},

	removeWidget : function() {
		this.getContainer().on('click', 'li a[name="dclose"]', function(e) {
			var element = $(e.currentTarget);
			var listItem = jQuery(element).parents('li');
			var width = listItem.attr('data-sizex');
			var height = listItem.attr('data-sizey');

			var url = element.data('url');
			var parent = element.closest('.dashBoardWidgetFooter').parent();
			var widgetName = parent.data('name');
			var widgetTitle = parent.find('.dashboardTitle').text();
			var activeTabId = element.closest(".tab-pane").data("tabid");

			// Modified by Phu Vo on 2020.10.30
			var message = app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_WIDGET', { widget_title: widgetTitle });

			app.helper.showConfirmationBox({
				message,
				htmlSupportEnable : false,
				buttons: DashboardConfig.confirmButtonsDanger,
			}).then(function(e) {
				app.helper.showProgress();

				app.request.post({ url }).then(
					function(err,response) {
						if (err == null) {
							var nonReversableWidgets = ['MiniList', 'Notebook', 'ChartReportWidget'];

							parent.fadeOut('slow', function() {
								Vtiger_DashBoard_Js.gridster.remove_widget(parent);
								parent.remove();
							});

							if (jQuery.inArray(widgetName, nonReversableWidgets) == -1) {
								var divider = jQuery('.widgetsList .divider','#tab_' + activeTabId);
								var data = '<li><a onclick="Vtiger_DashBoard_Js.addWidget(this, \'' + response.url + '\')" href="javascript:void(0);"';

								data += 'data-width=' + width + ' data-height=' + height + ' data-linkid=' + response.linkid + ' data-name=' + response.name + '>' + response.title + '</a></li>';
								
								if(divider.length) {
									jQuery(data).insertBefore(divider);
								} else {
									jQuery(data).insertAfter(jQuery('.widgetsList li:last','#tab_' + activeTabId));
								}
							}
						}

						app.helper.hideProgress();
					}
				);
			});
			// End Phu Vo
		});
	},

	registerLazyLoadWidgets : function() {
		var thisInstance = this;
		jQuery(window).bind("scroll", function() {
			var widgetList = jQuery('.dashboardWidget').not('.loadcompleted');
			if(!widgetList[0]){
				// We shouldn't unbind as we might have widgets in another tab
				//jQuery(window).unbind('scroll');
			}
			widgetList.each(function(index,widgetContainerELement){
				if(thisInstance.isScrolledIntoView(widgetContainerELement)){
					thisInstance.loadWidget(jQuery(widgetContainerELement));
					jQuery(widgetContainerELement).addClass('loadcompleted');
				}
			});
		});
	},

	registerWidgetFullScreenView : function() {
		var thisInstance = this;
		this.getContainer().on('click','a[name="widgetFullScreen"]',function(e){
			var currentTarget = jQuery(e.currentTarget);
			var widgetContainer = currentTarget.closest('li');
			var widgetName = widgetContainer.data('name');
			var widgetTitle = widgetContainer.find('.dashboardTitle').text();
			var widgetId = widgetContainer.attr('id');
			var data = widgetContainer.find('input.widgetData').val();
			var chartType = '';
			if(widgetContainer.find('input[name="charttype"]').length){
				chartType = widgetContainer.find('input[name="charttype"]').val();
			}
			var clickThrough = 0;
			if(widgetContainer.find('input[name="clickthrough"]').length){
				clickThrough = widgetContainer.find('input[name="clickthrough"]').val();
			}
			var fullscreenview = '<div class="fullscreencontents modal-dialog modal-lg">\n\
									<div class="modal-content">\n\
									<div class="modal-header backgroundColor">\n\
										<div class="clearfix">\n\
											<div class="pull-right">\n\
												<button data-dismiss="modal" class="close" title="'+app.vtranslate('JS_CLOSE')+'"><span aria-hidden="true" class="far fa-close"></span></button>\n\
											</div>\n\
											<h4 class="pull-left">'+widgetTitle+'</h4>\n\
										</div>\n\
									</div>\n\
									<div class="modal-body" style="overflow:auto;">\n\
										<ul style="list-style: none;"><li id="fullscreenpreview" class="dashboardWidget fullscreenview" data-name="'+widgetName+'">\n\
											<div class="dashboardWidgetContent" style="min-height:500px;width:100%;min-width:600px; margin: 0 auto" data-displaymode="fullscreen">';
						if(chartType != ''){
							fullscreenview += ' <input type="hidden" value="'+chartType+'" name="charttype">\n\
												<input type="hidden" value="'+clickThrough+'" name="clickthrough">\n\
												<div id="chartDiv" name="chartcontent" style="width:100%;height:100%" data-mode="preview"></div> \n\
												<input class="widgetData" type="hidden" value="" name="data">';
						} else {
							fullscreenview += ' <div class="dashboardWidgetContent" style="width:100%;height:100%" data-displaymode="fullscreen">\n\
													<div id="chartDiv" class="widgetChartContainer" style="width:100%;height:100%"></div>\n\
														<input class="widgetData" type="hidden" value="" name="data">';
						}
							fullscreenview += '</div></ul></li></div></div></div>';

			var callback = function(modalData){
				var element = jQuery(modalData);
				var modal= jQuery(".myModal",element);
				modal.parent().css({'top':'30px','left':'30px','right':'30px','bottom':'30px'});
				modal.css('height','100%');
				var modalWidgetContainer = jQuery('.fullscreenview');
				modalWidgetContainer.find('.widgetData').val(data);
				 if(chartType != ''){
					//Chart report widget 
					var chartClassName = chartType.toCamelCase();
					var chartClass = window["Report_"+chartClassName + "_Js"];
					chartClass('Vtiger_ChartReportWidget_Widget_Js',{},{
						init : function() {
								this._super(modalWidgetContainer);
							}
					});
				}
				var widgetInstance = Vtiger_Widget_Js.getInstance(modalWidgetContainer, widgetName);
				modalWidgetContainer.trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
			}
			app.helper.showModal(fullscreenview,{"cb":callback});
		});
	},

	registerFilterInitiater : function() {
		var container = this.getContainer();
		container.on('click', 'a[name="dfilter"]', function(e) {
			var widgetContainer = jQuery(e.currentTarget).closest('.dashboardWidget');
			var filterContainer = widgetContainer.find('.filterContainer');
			var dashboardWidgetFooter = jQuery('.dashBoardWidgetFooter', widgetContainer);

			widgetContainer.toggleClass('dashboardFilterExpanded');
			filterContainer.slideToggle(500);

			var callbackFunction = function() {
				widgetContainer.toggleClass('dashboardFilterExpanded');
				filterContainer.slideToggle(500);
			}
			//adding clickoutside event on the dashboardWidgetHeader
			var helper = new Vtiger_Helper_Js();
			helper.addClickOutSideEvent(dashboardWidgetFooter, callbackFunction);

			return false;
		})
	},

	registerDeleteDashboardTab : function(){
		var self = this;
		var dashBoardContainer = this.getDashboardContainer();
		dashBoardContainer.off("click",'.deleteTab');
		dashBoardContainer.on("click",'.deleteTab',function(e){
			// To prevent tab click event
			e.preventDefault();
			e.stopPropagation();

			var currentTarget = jQuery(e.currentTarget);
			var tab = currentTarget.closest(".dashboardTab");

			var tabId = tab.data("tabid");
			var tabName = tab.find('span.name').text(); // Modified by Hieu Nguyen on 2020-10-12

			// Modified by Phu Vo on 2020.10.30
			var message = app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_DASHBOARDTAB', { tab_name: tabName });

			app.helper.showConfirmationBox({
				message ,
				htmlSupportEnable : false,
				buttons: DashboardConfig.confirmButtonsDanger,
			}).then(function(e) {
				app.helper.showProgress();

				var data = {
					module : 'Vtiger',
					action : 'DashBoardTab',
					mode : 'deleteTab',
					tabid: tabId
				}

				app.request.post({ data }).then(function(err,data){
					app.helper.hideProgress();

					if(err == null){
						jQuery('li[data-tabid="' + tabId + '"]').remove();
						jQuery('.tab-content #tab_' + tabId).remove();

						if(jQuery('.dashboardTab.active').length <= 0) {
							// click the first tab if none of the tabs are active
							var firstTab = jQuery('.dashboardTab').get(0);
							jQuery(firstTab).find('a').click();
						}

						app.helper.showSuccessNotification({message:''});

						if(jQuery('.dashboardTab').length < Vtiger_DashBoard_Js.dashboardTabsLimit) {
							var element = dashBoardContainer.find('li.disabled');
							self.removeQtip(element);
						}
					} else {
						app.helper.showErrorNotification({message: err});
					}
				});
			});
			// End Phu Vo
		});
	},

	registerAddDashboardTab : function(){
		var self = this;
		var dashBoardContainer = this.getDashboardContainer();

		dashBoardContainer.on('click', '#addNewDashBoardTab', function (e) {    // Modified by Hieu Nguyen on 2020-10-14
			if (dashBoardContainer.find('ul.tabs').find('.dashboardTab').length >= Vtiger_DashBoard_Js.dashboardTabsLimit) {    // Modified condition by Hieu Nguyen on 2020-12-13 to exclude the template
				app.helper.showErrorNotification({"message":app.vtranslate("JS_TABS_LIMIT_EXCEEDED")});
				return;
			}
			var currentElement = jQuery(e.currentTarget);
			var data = {
				'module'	: 'Home',
				'view'		: 'DashBoardTab',
				'mode'		: 'showDashBoardAddTabForm'
			};

			app.request.post({"data":data}).then(function(err,res){
				if(err === null){
					var cb = function(data){
						var form = jQuery(data).find('#AddDashBoardTab');
						var params = {
							submitHandler : function(form) {
                                // Modified by Hieu Nguyen on 2020-10-12 to save tab name in both English and Vietnamese
								var inputTabNameEn = jQuery(form).find('[name="tab_name_en"]');
								var inputTabNameVn = jQuery(form).find('[name="tab_name_vn"]');

                                vtUtils.hideValidationMessage(inputTabNameEn);
                                vtUtils.hideValidationMessage(inputTabNameVn);

								var params = jQuery(form).serializeFormData();
								params['tab_name_en'] = params['tab_name_en'].trim();
								params['tab_name_vn'] = params['tab_name_vn'].trim();
                                
								app.request.post({ 'data': params }).then(function (err,data) {
									if (err) {
										app.helper.showErrorNotification({ 'message': err.message });
                                        return;
									}
                                    else {
                                        app.helper.hideModal();

                                        // Append new tab into the tab list
										var tabId = data['tabid'];
										var tabName = data['tabname'];
										var template = dashBoardContainer.find('#dashboard-tab-template').clone();
                                        template.find('li').attr('data-tabid', tabId);
                                        template.find('span.name').text(tabName);
                                        dashBoardContainer.find('ul.tabs').append(template.html());

                                        // Reload to select the newly added tab
                                        location.href = 'index.php?module=Home&view=DashBoard&tabid=' + tabId;
									}
								});
                                // End Hieu Nguyen
							}
						}
						form.vtValidate(params);
					}
					app.helper.showModal(res,{"cb":cb});
				}
			})

		})
	},
	removeQtip : function(element){
		jQuery(element).qtip("destroy");
		element.removeClass('disabled');
	},

	registerQtipMessage: function(){
		var dashBoardContainer = this.getDashboardContainer();
		var element = dashBoardContainer.find('li.disabled');
		var title = app.vtranslate("JS_TABS_LIMIT_EXCEEDED")
			jQuery(element).qtip({
				content: title,
				hide: {
					event:'click mouseleave',
				},
				position: {
					my: 'bottom center',
					at: 'top left',
					adjust: {
						x: 30,
						y: 10
					}
				},
				style: {
					classes: 'qtip-dark'
				}
			});
	},
    // Modified by Hieu Nguyen on 2020-10-12 to handle rename dashboard tab
	registerDashBoardTabRename: function() {
		var self = this;
		var dashBoardContainer = this.getDashboardContainer();

		dashBoardContainer.on('click', '.renameTab', function (e) {
            app.helper.showProgress();
            var tabElement = $(this).closest('.dashboardTab');
            var tabId = tabElement.data('tabid');

            var params = {
				'module': 'Home',
				'view': 'DashBoardTab',
				'mode': 'showDashBoardEditTabForm',
                'tab_id': tabId
			};

			app.request.post({ 'data': params }).then(function (err, res) {
                app.helper.hideProgress();

				if (err) {
                    console.log('Error loading dashboard tab edit form', err);
                    return;
                }

                var cb = function (data) {
                    var form = jQuery(data).find('form#EditDashBoardTab');

                    var params = {
                        submitHandler : function(form) {
                            var inputTabNameEn = jQuery(form).find('[name="tab_name_en"]');
                            var inputTabNameVn = jQuery(form).find('[name="tab_name_vn"]');

                            vtUtils.hideValidationMessage(inputTabNameEn);
                            vtUtils.hideValidationMessage(inputTabNameVn);

                            var params = jQuery(form).serializeFormData();
                            params['tab_name_en'] = params['tab_name_en'].trim();
                            params['tab_name_vn'] = params['tab_name_vn'].trim();

                            app.request.post({ 'data': params }).then(function (err, data) {
                                if (err) {
                                    app.helper.showErrorNotification({ 'message': err.message });
                                    return;
                                }
                                
                                app.helper.hideModal();
                                tabElement.find('span.name').text(data.tab_name);
                            });
                        }
                    }

                    form.vtValidate(params); 
                }
                
                app.helper.showModal(res, { 'cb': cb });
            });
		});
	},
    // End Hieu Nguyen

	registerDashBoardTabClick : function(){
		var thisInstance = this;
		var container = this.getContainer();
		var dashBoardContainer = jQuery(container).closest(".dashBoardContainer");

		dashBoardContainer.on("shown.bs.tab",".dashboardTab",function(e){
			var currentTarget = jQuery(e.currentTarget);
			var tabid = currentTarget.data('tabid');

            // Added by Hieu Nguyen on 2020-02-28 to reload the page on tab click (quick hack to prevent issue when a widget was added in multiple tab that cause id duplicate)
            location.href = 'index.php?module=Home&view=DashBoard&tabid=' + tabid;
            return;
            // End Hieu Nguyen

			app.changeURL("index.php?module=Home&view=DashBoard&tabid="+tabid);

			// If tab is already loaded earlier then we shouldn't reload tab and register gridster
			if(typeof jQuery("#tab_"+tabid).find(".dashBoardTabContainer").val() !== 'undefined'){
				// We should overwrite gridster with current tab which is clicked

				var widgetMargin = 10;
				var cols = thisInstance.getgridColumns();
				$(".mainContainer").css('min-width', "500px");
				var colWidth = (cols === 1)?(Math.floor(($(".mainContainer").width()-41)/cols) - (2*widgetMargin)):(Math.floor(($(window).width()-41)/cols) - (2*widgetMargin));

				Vtiger_DashBoard_Js.gridster = thisInstance.getContainer(tabid).gridster({ 
					// Need to set the base dimensions to eliminate widgets overlapping
					widget_base_dimensions: [colWidth, 320]
				}).data("gridster");

				return;
			}
			var data = {
				'module': 'Home',
				'view': 'DashBoardTab',
				'mode': 'getTabContents',
				'tabid' : tabid
			}

			app.request.post({"data":data}).then(function(err,data){
				if(err === null){
					var dashBoardModuleName = jQuery("#tab_"+tabid,".tab-content").html(data).find('[name="dashBoardModuleName"]').val();
					if(typeof dashBoardModuleName != 'undefined' && dashBoardModuleName.length > 0 ) {
						var dashBoardInstanceClassName = app.getModuleSpecificViewClass(app.view(),dashBoardModuleName);
						if(dashBoardInstanceClassName != null) {
							var dashBoardInstance = new window[dashBoardInstanceClassName]();
						}
					}
					app.event.trigger("post.DashBoardTab.load", dashBoardInstance);
				}
			});
		});
	},

	registerRearrangeTabsEvent : function(){
		var dashBoardContainer = this.getDashboardContainer();

		// on click of Rearrange button
		dashBoardContainer.on('click', '#reArrangeDashboardTabs', function (e) {    // Modified by Hieu Nguyen on 2020-10-14
			var currentEle = jQuery(e.currentTarget);
			dashBoardContainer.find(".dashBoardDropDown").addClass('hide');

			var sortableContainer = dashBoardContainer.find(".tabContainer");
			var sortableEle = sortableContainer.find(".sortable");

			currentEle.addClass("hide");
			dashBoardContainer.find('.edit-buttons').addClass('hide');  // Modified by Hieu Nguyen on 2020-10-12
			dashBoardContainer.find(".moveTab").removeClass("hide");
			dashBoardContainer.find('#saveDashboardTabsOrder').removeClass('hide'); // Modified by Hieu Nguyen on 2020-10-14

			sortableEle.sortable({
				'containment': sortableContainer,
				stop : function(){}
			});
		});

		// On click of save sequence
		dashBoardContainer.on('click', '#saveDashboardTabsOrder', function (e) {    // Modified by Hieu Nguyen on 2020-10-14
			var reArrangedList = {};
			var currEle = jQuery(e.currentTarget);
			jQuery(".sortable li").each(function(i,el){
				var el = jQuery(el);
				var tabid = el.data("tabid");
				reArrangedList[tabid] = ++i;
			});

			var data = {
				"module" : "Vtiger",
				"action" : "DashBoardTab",
				"mode" : "updateTabSequence",
				"sequence" : JSON.stringify(reArrangedList)
			}

			app.request.post({"data":data}).then(function(err,data){
				if(err == null){
					currEle.addClass("hide");
					dashBoardContainer.find(".moveTab").addClass("hide");
					dashBoardContainer.find('#reArrangeDashboardTabs').removeClass('hide'); // Modified by Hieu Nguyen on 2020-10-14
					dashBoardContainer.find('.edit-buttons').removeClass('hide');   // Modified by Hieu Nguyen on 2020-10-12
					dashBoardContainer.find(".dashBoardDropDown").removeClass('hide');

					var sortableEle = dashBoardContainer.find(".tabContainer").find(".sortable");
					sortableEle.sortable('destroy');

					app.helper.showSuccessNotification({"message":''});
				} else {
					app.helper.showErrorNotification({"message":err});
				}
			});
		});

	},

	// [DashletGuide] Added by Hieu Nguyen on 2022-03-10
	registerDashletGuide: function () {
		let container = this.getDashboardContainer();

		container.on('click', '.btn-show-dashlet-guide', function () {
			let guideContent = $(this).data('guideContent');
			let modal = $('.modal-template-lg').clone().addClass('modal-dashlet-guide');
			modal.find('.modal-body').append(guideContent);
			modal.find('.modal-footer').remove();

			// Process modal title
			let reportTitle = $(this).closest('div').find('.dashboardTitle').text().trim();
			let title = app.vtranslate('Home.JS_DASHLET_GUIDE_POPUP_TITLE', { 'dashlet_name': reportTitle })
			modal.find('.modal-header .pull-left').html(title);

			var modalParams = {
				backdrop: 'static',
				keyboard: false
			};

			// Show modal
			app.helper.showModal(modal, modalParams);
		});
	},

	registerEvents : function() {
		var thisInstance = this;
		this.registerLazyLoadWidgets();
		this.registerAddDashboardTab();
		this.registerDashBoardTabClick();
		this.registerDashBoardTabRename();
		this.registerDeleteDashboardTab();
		this.registerRearrangeTabsEvent();
		this.registerQtipMessage();
		app.event.off("post.DashBoardTab.load");
		app.event.on("post.DashBoardTab.load",function(event, dashBoardInstance){
			var instance = thisInstance;
			if(typeof dashBoardInstance != 'undefined') {
				instance = dashBoardInstance;
				instance.registerEvents();
			}
			instance.registerGridster();
			instance.loadWidgets();
			instance.registerRefreshWidget();
			instance.removeWidget();
			instance.registerWidgetFullScreenView();
			instance.registerFilterInitiater();
			instance.registerDashletGuide();	// [DashletGuide] Added by Hieu Nguyen on 2022-03-10
		});
		app.event.trigger("post.DashBoardTab.load");
	}
});
