/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Reports_Detail_Js",{},{
	advanceFilterInstance : false,
	detailViewContentHolder : false,
	HeaderContentsHolder : false, 
	
	detailViewForm : false,
	getForm : function() {
		if(this.detailViewForm == false) {
			this.detailViewForm = jQuery('form#detailView');
		}
	},
	
	getRecordId : function(){
		return app.getRecordId();
	},
	
	getContentHolder : function() {
		if(this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.editViewPageDiv');
		}
		return this.detailViewContentHolder;
	},
	
	getHeaderContentsHolder : function(){
		if(this.HeaderContentsHolder == false) {
			this.HeaderContentsHolder = jQuery('div.reportsDetailHeader ');
		}
		return this.HeaderContentsHolder;
	},
	
	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		return JSON.stringify(advfilterlist);
	},
		
	registerSaveOrGenerateReportEvent : function(){
		var thisInstance = this;
		jQuery('.generateReport').on('click',function(e){
            e.preventDefault();
			var advFilterCondition = thisInstance.calculateValues();
            var recordId = thisInstance.getRecordId();
			var currentMode = jQuery(e.currentTarget).data('mode');
			
			// Added by Hieu Nguyen on 2018-12-09
			if(jQuery('#custom_handler_file').val() != '') {
				currentMode = 'save';
			}
			// End Hieu Nguyen

            var postData = {
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'view' : "SaveAjax",
                'module' : app.getModuleName(),
                'mode' : currentMode
            };
			app.helper.showProgress();
			app.request.post({data:postData}).then(
				function(error,data){
					// Added by Hieu Nguyen on 2018-12-09
					if(jQuery('#custom_handler_file').val() != '') {
						location.reload();
						return;
					}
					// End Hieu Nguyen

					app.helper.hideProgress();
					thisInstance.getContentHolder().find('#reportContentsDiv').html(data);

					// Updated by Phuc on 2019.10.10 to prevent hide reportActionButtons
					// jQuery('.reportActionButtons').addClass('hide');
					// Ended by Phuc

//					app.helper.showHorizontalScroll(jQuery('#reportDetails'));

					// To get total records count
					var count  = parseInt(jQuery('#updatedCount').val());
					thisInstance.generateReportCount(count);

					// Added by Phu Vo on 2019.06.24 to trigger report reload event
					app.event.trigger('post.generateReport.load', thisInstance.getContentHolder().find('#reportContentsDiv'));
					// End trigger report reload event
				}
			);
		});
	},
	
    registerEventsForActions : function() {
      var thisInstance = this;
      jQuery('.reportActions').click(function(e){
        var element = jQuery(e.currentTarget); 
        var href = element.data('href');
        var type = element.attr("name");
        var advFilterCondition = thisInstance.calculateValues();
        var headerContainer = thisInstance.getHeaderContentsHolder();
        if(type.indexOf("Print") != -1){
            var newEle = '<form action='+href+' method="POST" target="_blank">\n\
                    <input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>\n\
                    <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';
        }else{
            newEle = '<form action='+href+' method="POST">\n\
                    <input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>\n\
                    <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';
        }
        var ele = jQuery(newEle); 
        var form = ele.appendTo(headerContainer);
        form.find('#advanced_filter').val(advFilterCondition);
        form.submit();
      })  
    },
    
    generateReportCount : function(count){
      var thisInstance = this;  
      var advFilterCondition = thisInstance.calculateValues();
      var recordId = thisInstance.getRecordId();
      
      var reportLimit = parseInt(jQuery("#reportLimit").val());
      
        if(count < reportLimit){
            jQuery('#countValue').text(count);
            jQuery('#moreRecordsText').addClass('hide');
        }else{        
            jQuery('#countValue').html('<img src="layouts/v7/skins/images/loading.gif">');
            var params = {
                'module' : app.getModuleName(),
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'action' : "DetailAjax",
                'mode': "getRecordsCount"
            };
            jQuery('.generateReport').attr("disabled","disabled");
            app.request.post({data:params}).then(
                function(error,data){
                    jQuery('.generateReport').removeAttr("disabled");
                    var count = parseInt(data);
                    jQuery('#countValue').text(count);
                    if(count > reportLimit)
                        jQuery('#moreRecordsText').removeClass('hide');
                    else
                        jQuery('#moreRecordsText').addClass('hide');
                }
            );
        }
      
    },
	
	registerConditionBlockChangeEvent : function() {
		jQuery('.reportsDetailHeader').find('#groupbyfield,#datafields,[name="columnname"],[name="comparator"]').on('change', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery('.fieldUiHolder').find('[data-value="value"]').on('change input', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery('.deleteCondition').on('click', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery(document).on('datepicker-change', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
	},
	
	// Modified by Hieu Nguyen on 2022-01-24 to show/hide report filter
	registerEventForModifyCondition: function () {
		jQuery('button#toggle-filter').on('click', function (e) {
			jQuery(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
			
			if(jQuery(this).find('i').hasClass('fa-chevron-up')) {
				jQuery('#filterContainer').removeClass('hide').show('slow');
			}
			else {
				jQuery('#filterContainer').addClass('hide').hide('slow');
			}

			return false;
		});
	},
	// End Hieu Nguyen

	// Added by Phuc on 2019.10.10 to prevent submit form when enter in filters
	registerEventsToPreventSubmitActionOnEnterFilters: function() {
        jQuery('#filterContainer input').keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
	},
	// Ended by Phuc

	// Added by Phuc on 2019.10.10 to display report action at the first time
	registerEventDisplayReportActionButtonAtFirstTime: function() {
		if (!jQuery('#filterContainer').hasClass('hide')) {			
			jQuery('.reportActionButtons').removeClass('hide');
		}
	},
	//Ended by Phuc

	generateHelpText() {
		let reportDescriptionInput = jQuery('[name="report_description"]');
		let helpText = reportDescriptionInput.val().trim();
		if (!helpText) return;
		let helpTextLinkHtml = '<a href="javascript:void(0);" id="btn-show-help">'+ app.vtranslate('Reports.JS_REPORT_HELP_BTN_TITLE') +'</a>';
		
		// Custom report
		if (jQuery('#custom-report-detail')[0] != null) {
			jQuery('#custom-report-detail').find('#result-actions').append(helpTextLinkHtml);
		}
		// Detail Report & Chart report
		else {
			jQuery('#reportContentsDiv').prepend('<div id="result-actions">'+ helpTextLinkHtml +'</div>');
		}

		// Show help
		let helpTextLink = jQuery('#btn-show-help');

		helpTextLink.on('click', function () {
			let modal = jQuery('.modal-template-lg').clone().addClass('modal-report-help');
			modal.find('.modal-body').append(helpText);
			modal.find('.modal-footer').remove();

			// Process modal title
			let reportTitle = jQuery('#reportTitle').text().trim();
			let title = app.vtranslate('Reports.JS_REPORT_HELP_POPUP_TITLE', { 'report_name': reportTitle })
			modal.find('.modal-header .pull-left').html(title);

			var modalParams = {
				backdrop: 'static',
				keyboard: false
			};

			// Show modal
			app.helper.showModal(modal, modalParams);
		});
	},
	
	registerEvents : function(){
		this.registerSaveOrGenerateReportEvent();
        this.registerEventsForActions();
		var container = this.getContentHolder();
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
        this.generateReportCount(parseInt(jQuery("#countValue").text()));
		this.registerConditionBlockChangeEvent();
		this.registerEventForModifyCondition();
		// Added by Phuc on 2019.10.10
		this.registerEventsToPreventSubmitActionOnEnterFilters();
		this.registerEventDisplayReportActionButtonAtFirstTime();
		//Ended by Phuc

		this.generateHelpText();
	}
});