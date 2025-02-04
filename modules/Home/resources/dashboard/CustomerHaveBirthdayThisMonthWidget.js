/**
 * CustomerHaveBirthdayThisMonthWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

window.CustomerHaveBirthdayThisMonthWidget = {
    handleTableRenderEvent: function (widget) {
        let container = '#page';

		widget.find('.dropdown').on('click', function (e) {
            let rowAction = $(this).closest('.row-action');
			let containerTarget = jQuery(this).closest(container);
			let content = jQuery(this).closest('.dropdown');
			let dropdown = jQuery(e.currentTarget);
			if (dropdown.find('[data-toggle]').length <= 0) {
				return;
			}
            let dropdown_menu = dropdown.find('.dropdown-menu');
            
            dropdown_menu.attr('data-row', rowAction.attr('data-row'));

			let dropdownStyle = dropdown_menu.find('li a');
			dropdownStyle.css('padding', '0 6px', 'important');

			let fixed_dropdown_menu = dropdown_menu.clone(true);
			fixed_dropdown_menu.data('original-menu', dropdown_menu);
			dropdown_menu.css('position', 'relative');
			dropdown_menu.css('display', 'none');
			let currtargetTop;
			let currtargetLeft;
			let dropdownBottom;
			let ftop = 'auto';
			let fbottom = 'auto';

			if (container === '#page') {
				currtargetTop = dropdown.offset().top + dropdown.height();
				currtargetLeft = dropdown.offset().left;
				dropdownBottom = jQuery(window).height() - currtargetTop + dropdown.height();

			}
			let windowBottom = jQuery(window).height() - dropdown.offset().top;
			if (windowBottom < 250) {
				ftop = 'auto';
				fbottom = dropdownBottom + 'px';
			}
			else {
				ftop = currtargetTop + 'px';
				fbottom = 'auto';
			}
			fixed_dropdown_menu.css({
				'display': 'block',
				'position': 'absolute',
				'top': ftop,
				'left': currtargetLeft + 'px',
				'bottom': fbottom
			}).appendTo(containerTarget);

			widget.find('.mCustomScrollBox').scroll(function () {
				let tTop;
				let cBottom = widget.find('.mCustomScrollBox').height() - content.position().top;
				let tBottom;
				if (cBottom < 250) {
					tTop = 'auto';
					tBottom = dropdown.height();
				}
				else {
					tTop = dropdown.height();
					tBottom = 'auto';
				}
				if (content.hasClass('open')) {
					fixed_dropdown_menu.css({
						'display': 'block',
						'top': tTop,
						'position': 'absolute',
						'bottom': tBottom,
						'left': 0,
						'z-index': 100
					}).appendTo(content);
				}
				else {
					dropdown_menu.css('display', 'none');
				}
			});

			dropdown.on('hidden.bs.dropdown', function () {
				dropdown_menu.removeClass('invisible');
				fixed_dropdown_menu.remove();
			});
		});
        
        widget.find('.sendSMS').on('click', function() {
            let rowData = $(this).closest('.dropdown-menu').data('row');
            let recordId = rowData['record_id'];
            let detailActionUrl = 'index.php?module=Contacts&view=MassActionAjax&mode=showSendSMSOTTModal';

            app.helper.checkServerConfig('SMSNotifier').then(function(data) {
                if (data == true) {
                    var cb = function(container) {
                        $('#phoneFormatWarningPop').popover();

                        SMSNotifierHelper.initSMSValidator();
                    }
                    
                    selectedIds = [recordId];

                    var postData = {
                        'selected_ids': JSON.stringify(selectedIds)
                    };

                    app.request.post({url:detailActionUrl, data:postData, dataType:'html'}).then(function(err, data) {
                        if (data) {
                            app.helper.showModal(data);
                            cb(data);
                        }
                    });
                            
                } else {
                    app.helper.showAlertBox({message:app.vtranslate('JS_SMS_SERVER_CONFIGURATION')})
                }
            });
        });
        
        widget.find('.sendZalo').on('click', function() {
            let rowData = $(this).closest('.dropdown-menu').data('row');
            let recordId = rowData['record_id'];
			const form = $('#socialMessageModal').find('form');

			// Work around to handle send Zalo message using environment injection
			$(`<input type="hidden" name="target_record" value=${recordId} />`).appendTo(form);

            SocialHandler.composeSocialMessage("Zalo");
        });
        
        widget.find('.sendMessageFacebook').on('click', function() {
            // Leave it empty now
        });
    },
}