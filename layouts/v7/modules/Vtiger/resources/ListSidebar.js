/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class('Vtiger_ListSidebar_Js',{},{
    
    
    registerFilterSeach : function () {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.find('.search-list').on('keyup',function(e){
            var element = jQuery(e.currentTarget);
            var val = element.val().toLowerCase();
            filters.find('.toggleFilterSize').removeClass('hide');
            jQuery('li.listViewFilter').each(function(){
                var filterEle = jQuery(this);
                var filterName = filterEle.find('a.filterName').html();
                var listsMenu = filterEle.closest('ul.lists-menu');
                if(typeof filterName != 'undefined') {
                    filterName = filterName.toLowerCase();
                    if(filterName.indexOf(val) === -1){
                        filterEle.addClass('filter-search-hide').removeClass('filter-search-show');    
                        if(listsMenu.find('li.listViewFilter').filter(':visible').length == 0) {
                            listsMenu.closest('.list-group').addClass('hide');
                        }
                        if(jQuery('#module-filters').find('ul.lists-menu li').filter(':visible').length == 0) {
                            jQuery('#module-filters').find('.noLists').removeClass('hide');
                        }
                    }else{
                        if(val) {
                            listsMenu.closest('.list-group').find('.toggleFilterSize').addClass('hide');
                        }
                        filterEle.removeClass('filter-search-hide').addClass('filter-search-show');
                        listsMenu.closest('.list-group').removeClass('hide');
                        jQuery('#module-filters').find('.noLists').addClass('hide');
                    }
                }
            });
        })
    },
    
	registerFilters: function() {
		var self = this;
        var filters = jQuery('.module-filters').not('.module-extensions');
        var scrollContainers = filters.find(".scrollContainer");
        // applying scroll to filters, tags & extensions
        jQuery.each(scrollContainers,function(key,scroll){
            var scroll = jQuery(scroll);
            // var listcontentHeight = scroll.height(); // Modified by Phu Vo on 2021.05.21 to fix sidebar sizing
            // scroll.css("height",listcontentHeight);
            scroll.perfectScrollbar({});
        })
        
        this.registerFilterSeach();
        filters.on('click','.listViewFilter', function(e){
			e.preventDefault();
            var targetElement = jQuery(e.target);
            if(targetElement.is('.dropdown-toggle') || targetElement.closest('ul').hasClass('dropdown-menu') ) return;
            var element = jQuery(e.currentTarget);
            var el = jQuery('a[data-filter-id]',element);
            self.getParentInstance().resetData();
            self.unMarkAllFilters();
            self.unMarkAllTags();
            el.closest('li').addClass('active');
            self.getParentInstance().filterClick = true;
            self.getParentInstance().loadFilter(el.data('filter-id'), {'page' : ''});
			var filtername = jQuery('a[class="filterName"]',element).text();
			jQuery('.module-action-content').find('.filter-name').html('&nbsp;&nbsp;<span class="far fa-angle-right" aria-hidden="true"></span>').text(filtername);
        });
        
        jQuery('#createFilter').on('click',function(e){
            var element = jQuery(e.currentTarget);
            element.trigger('post.CreateFilter.click',{'url':element.data('url')});
        });
        
        filters.on('click','li.editFilter,li.duplicateFilter',function(e){
            if (window.skipDefaultEditFilterEvent) return;  // Added by Hieu Nguyen on 2021-09-17 to allow skip default edit filter event handler
            var element = jQuery(e.currentTarget);
            if(typeof element.data('url') == "undefined") return;
            element.trigger('post.CreateFilter.click',{'url':element.data('url')});
        });
        
        // Modified by Hieu Nguyen on 2021-07-06 to handle button remove filter
        filters.on('click', 'li.removeFilter, li.deleteFilter', function (e) {  // Modified by Hieu Nguyen on 2020-06-26 to allow removing shared list
            if (window.skipDefaultEditFilterEvent) return;  // Added by Hieu Nguyen on 2021-09-17 to allow skip default delete filter event handler
            var clickedDeleteBtn = jQuery(e.currentTarget);
            if (typeof clickedDeleteBtn.data('url') == 'undefined') return;
            var url = clickedDeleteBtn.data('url');

            var message = app.vtranslate('CustomView.JS_DELETE_FILTER_CONFIRM_MSG');

            if (url.indexOf('is_shared=true') > 0) {
                message = app.vtranslate('CustomView.JS_REMOVE_SHARED_FILTER_CONFIRM_MSG');
            }
            
			app.helper.showConfirmationBox({ 'message': message }).then(function() {
                app.helper.showProgress();

                app.request.post({ 'url': url }).then(function () {
                    app.helper.hideProgress();
                    let activePopover = clickedDeleteBtn.closest('.popover');
                    let filterId = activePopover.find('.popover-content').data('filterId');

                    // Remove the selected filter item on the filter list
                    jQuery('#module-filters').find('.filterName[data-filter-id="'+ filterId +'"]').closest('li').remove();
                    activePopover.remove();

                    // Then make filter 'All' active
                    let idOfFilterAll = jQuery('.module-filters input[name=allCvId]').val();
                    let filterAll = jQuery('.module-filters').find('.filterName[data-filter-id="'+ idOfFilterAll +'"]');
                    filterAll.trigger('click');
                });
            });
        });
    
        // Modified by Hieu Nguyen on 2021-07-02 to handle toggle saving default filter
        filters.on('click', 'li.toggleDefault',function(e) {
            let clickedToggleDefaultBtn = jQuery(e.currentTarget);
            let activePopover = clickedToggleDefaultBtn.closest('.popover');
            let filterId = activePopover.find('.popover-content').data('filterId');
            let selectedPopoverTriggerBtn = jQuery('#module-filters').find('[rel="popover"][data-filter-id="'+ filterId +'"]');
            let isDefault = selectedPopoverTriggerBtn.data('isDefault');
            
            var params = {
                'url': clickedToggleDefaultBtn.data('url'),
                'data': {
                    'setdefault': (isDefault == '1' ? 0 : 1)
                }
            };

            app.request.post(params).then(function (err, data) {
                if (err) return;

                if (data.is_default == '1') {
                    // Mark all filters as not default
                    let toggleDefaultBtns = jQuery('#module-filters').find('[rel="popover"]');
                    toggleDefaultBtns.data('isDefault', 0);

                    // Then update this filter default status as default and toggle icon to selected
                    selectedPopoverTriggerBtn.data('isDefault', 1);
                    activePopover.find('.toggleDefault i').removeAttr('class').addClass('far fa-check-square');
                }
                else {
                    // Then update this filter default status as not default and toggle icon to unselected
                    selectedPopoverTriggerBtn.data('isDefault', 0);
                    activePopover.find('.toggleDefault i').removeAttr('class').addClass('far fa-square');
                }
			});
        });

        filters.find('.toggleFilterSize').on('click',function(e){
            var currentTarget = jQuery(e.currentTarget);
            currentTarget.closest('.list-group').find('li.filterHidden').toggleClass('hide');
            if(currentTarget.closest('.list-group').find('li.filterHidden').hasClass('hide')) {
                currentTarget.html(currentTarget.data('moreText'));
            }else{
                currentTarget.html(currentTarget.data('lessText'));
            }
        })
        
        app.event.on('ListViewFilterLoaded', function(event, container, params) {
			// TODO - Update pagination...
		});
	},
    
    loadListView : function(viewId, params){
        this.getParentInstance().resetData();
        this.getParentInstance().loadFilter(viewId, params);
    },
    
    unMarkAllFilters : function() {
        jQuery('.listViewFilter').removeClass('active');
    },
    
    unMarkAllTags : function() {
        var container = jQuery('#listViewTagContainer');
        container.find('.tag').removeClass('active').find('i.activeToggleIcon').removeClass('fa-circle').addClass('fa-circle');
    },
    
    registerPopOverContent: function () {
        var element = jQuery(".list-group");

        element.find('[rel="popover"]').on('click', function (e) {  // Modified by Hieu Nguyen on 2021-07-02 to init popover on button click instead of on page load
            let ele = e.target;

            // Move here by Hieu Nguyen on 2020-06-26 to make cloned popover html independ from others
            var contentEle = jQuery('#filterActionPopoverHtml').clone();
            contentEle.find('.listmenu').removeClass('hide');
            var editEle = contentEle.find('.editFilter');
            var removeEle = contentEle.find('.removeFilter');   // Added by Hieu Nguyen on 2020-06-26 to allow removing shared list
            var deleteEle = contentEle.find('.deleteFilter');
            var duplEle = contentEle.find('.duplicateFilter');
            var toggleEle = contentEle.find('.toggleDefault');
            // End Hieu Nguyen

            editEle.attr('data-url', jQuery(ele).data('editurl'));
            removeEle.attr('data-url', jQuery(ele).data('deleteurl') + '&is_shared=true');  // Added by Hieu Nguyen on 2020-06-26 to allow removing shared list
            deleteEle.attr('data-url', jQuery(ele).data('deleteurl'));
            duplEle.attr('data-url', jQuery(ele).data('default'));
            toggleEle.attr('data-url', jQuery(ele).data('defaulttoggle'));
            toggleEle.find('i').attr('class', (jQuery(ele).data('is-default') == 1) ? 'far fa-check-square': 'far fa-square');  // Modified by Hieu Nguyen on 2021-07-02 to display default status
            
            if(jQuery(ele).data('ismine') === false){
                contentEle.find('.editFilter').css("display", "none");
                // contentEle.find('.deleteFilter').css("display","none");  // Commented out by Hieu Nguyen on 2020-06-26 to allow removing shared list
            }
            if (!jQuery(ele).data('editable')) {
                contentEle.find('.editFilter').remove();
            } else {
                contentEle.find('.editFilter').removeClass('disabled');
            }
            if (!jQuery(ele).data('deletable')) {
                // contentEle.find('.deleteFilter').remove();     // Commented out by Hieu Nguyen on 2020-06-26 to allow removing shared list
            } else {
                contentEle.find('.deleteFilter').removeClass('disabled');
            } 

            // Added by Hieu Nguyen on 2020-06-26 to display remove and delete buttons
            var isShared = (jQuery(ele).closest('.list-group').prop('id') == 'sharedList');
            var filterEle = jQuery(ele).closest('.listViewFilter').find('.filterName');

            if (isShared) {
                if (filterEle.data('systemFilter') == '1') {
                    removeEle.remove();
                }
            }
            else {
                removeEle.remove();
            }

            if (filterEle.data('canDelete') != '1') {
                deleteEle.remove();
            }
            // End Hieu Nguyen

            var options = {
                html: true,
                placement: 'left',
                template: '<div class="popover" style="top: 0; position:absolute; z-index:0; margin-top:5px"><div class="popover-content" data-filter-id="'+ jQuery(ele).data('filter-id') +'"></div></div>',
                content: contentEle.html(),
                container: jQuery('#module-filters')
            };
            
            // Modified by Hieu Nguyen on 2021-07-02 to hide other popover before display the selected one
            e.stopPropagation();
            
            $('#module-filters').find('[rel="popover"]').each(function () {
                $(this).popover('destroy');
            });

            setTimeout(() => {
                jQuery(ele).popover(options);
                jQuery(ele).popover('show');
            }, 200);
            // End Hieu Nguyen
            
            // Modified by Hieu Nguyen on 2021-07-02 to hide all popover when user click on the page body
            jQuery('html').on('click', function (e) {
                if (!jQuery(e.target).parent('li').hasClass('toggleDefault')) {
                    $('#module-filters').find('[rel="popover"]').each(function () {
                        $(this).popover('destroy');
                    });
                }
            });
            // End Hieu Nguyen
        });
         
    },
    
    
    registerTagClick : function() {
        var self = this;
        var container = jQuery('#listViewTagContainer');
        container.on('click', '.tag', function(e) {
            var eventTriggerSourceElement = jQuery(e.target);
            //if edit icon is clicked then we dont have to load the tag
            if(eventTriggerSourceElement.is('.editTag')) {
                return;
            }
            var element = jQuery(e.currentTarget);
            var tagId = element.data('id');
            var viewId = container.data('viewId');
            
            self.unMarkAllFilters();
            self.unMarkAllTags();
            element.addClass('active');
            element.find('i.activeToggleIcon').removeClass('fa-circle').addClass('fa-circle');
            var listSearchParams = new Array();
            listSearchParams[0] = new Array();
            var tagSearchParams = new Array();
            tagSearchParams.push('tags');
            tagSearchParams.push('e');
            tagSearchParams.push(tagId);
            listSearchParams[0].push(tagSearchParams);
            var params = {};
            params.search_params = ''; 
            params.tag_params = JSON.stringify(listSearchParams);
            params.tag = tagId;
            params.page = '';
            self.loadListView(viewId, params);
        });
        
        container.on('click', '.moreTags', function(e){
            container.find('.moreListTags').removeClass('hide');
            jQuery(e.currentTarget).addClass('hide');
        });
    },
    registerEvents : function() {
        this.registerFilters();
        this.registerTagClick();
        this.registerPopOverContent();
//        var listInstance = new Vtiger_List_Js();
//        listInstance.registerDynamicDropdownPosition("lists-menu", "list-menu-content");

        app.event.on('Vtiger.Post.MenuToggle', function() {
            if(!jQuery('.sidebar-essentials').hasClass('hide')) {
                var filters = jQuery('.module-filters').not('.module-extensions');
                var scrollContainers = filters.find(".scrollContainer");
                jQuery.each(scrollContainers,function(key,scroll){
                    var scroll = jQuery(scroll);
                    var listcontentHeight = scroll.height(); // Modified by Phu Vo on 2021.05.21 to fix sidebar sizing
                    scroll.css("height",listcontentHeight);
                    scroll.perfectScrollbar('update');
                });
            }
        });
    }
});
