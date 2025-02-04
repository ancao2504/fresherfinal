/*
    File RecordingPopup.js
    Author: Hieu Nguyen
    Date: 2019-12-20
    Purpose: to handle logic for recording popup
*/

var RecordingPopup = {
    canPlayRecording: function () {
        var view = app.getViewName();

        if (view == 'Detail' && $.inArray(['Leads', 'Contacts'], app.getModuleName())) {
            if ($('.tab-item.active').data('module') == 'Calendar') {   // In Activities tab
                return true;
            }
        }
    },
    init() {
        if (!this.canPlayRecording()) return;
        var thisInstance = this;
        
        // Handle play button click event
        $('.play-recording').on('click', function (e) {
            e.stopPropagation();
            app.helper.showProgress();
            var title = $(this).data('popupTitle');

            // Call ajax to load popup content
            let params = {
                module: 'PBXManager',
                view: 'RecordingPopup',
                call_log_id: $(this).data('callId')
            }

            app.request.post({ data: params }).then((err, res) => {
                app.helper.hideProgress();

                if(err) {
                    app.helper.showErrorNotification({ message: app.vtranslate('JS_RECORDING_POPUP_LOAD_ERROR_MSG', 'PBXManager') });
                    return;
                }

                thisInstance.showRecordingPopup(title, $(res));
                return;
            });
        });
    },
    showRecordingPopup(title, content) {
        var modalTemplate = $('div.modal-dialog.modal-template-md:first').clone(true, true);

        // Display modal title
        modalTemplate.find('.modal-header .pull-left').text(title);

        // Added by Phu Vo on 2020.05.07 to display footer close button label
        modalTemplate.find('.cancelLink').text(app.vtranslate('JS_CLOSE'));
        // End Phu Vo

        // Display form buttons
        modalTemplate.find('[type="submit"]').remove();

        var callBackFunction = function (data) {
            var form = data.find('form');

            // Populate form content
            form.find('.modal-body').append(content);
        };

        var modalParams = {
            cb: callBackFunction
        };

        app.helper.showModal(modalTemplate, modalParams);
        return false;
    }
}

jQuery(function ($) {
    RecordingPopup.init();  // Init at page load

    app.event.on('post.relatedListLoad.click', (e) => {
        RecordingPopup.init();  // Init on related list reload
    });
});