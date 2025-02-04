/*
    ModuleGuidePopup.js
    Author: Hieu Nguyen
    Date: 2021-01-18
    Purpose: to show module guide popup
*/

jQuery(function ($) {
    if (location.href.indexOf('parent=Settings') < 0) {
        renderModuleGuideButton();
        loadModuleGuidePopup();

        $('#btn-show-module-guide').on('click', function () {
            loadModuleGuidePopup(true);
        });
    }
});

function renderModuleGuideButton() {
    var moduleHeaderButtonsWrapper = $('#appnav');
    
    if (moduleHeaderButtonsWrapper.find('ul.nav')[0] == null) {
        $('<ul class="nav navbar-nav"></ul>').appendTo(moduleHeaderButtonsWrapper);
    }

    // Modified by Phu Vo on 2021.08.27 to add guide tooltip title
    var guideButtonHtml = `<li>
        <button type="button" id="btn-show-module-guide" class="btn btn-default module-buttons" data-toggle="tooltip" title="Hướng dẫn sử dụng">
            <i class="far fa-question-circle"></i>
        </button>
    </li>`;
    // End Phu Vo
    
    $(guideButtonHtml).appendTo(moduleHeaderButtonsWrapper.find('ul.nav'));
}

function loadModuleGuidePopup(userAction = false) {
    app.helper.showProgress();

    var params = {
        module: 'Vtiger',
        view: 'ModuleGuidePopupAjax',
        target_module: app.getModuleName()
    }

    app.request.post({ data: params })
    .then((err, res) => {
        app.helper.hideProgress();
        
        // When modal is loaded automatically at page load, do not show modal when the checkbox show_next_time is unchecked
        if (!userAction && !$(res).find('[name="show_next_time"]').is(':checked')) {
            return false;
        }

        // Do not show modal when the guide content is empty
        if ($(res).find('.modal-body').text().trim() == '') {
            if (userAction) {
                app.helper.showAlertBox({ message: app.vtranslate('JS_MODULE_GUIDE_NO_GUIGE_CONTENT_MSG') });
            }

            return false;
        }

        var callBackFunction = function (modal) {
            modal.find('[name="show_next_time"]').on('change', function () {
                var preferences = {
                    show_next_time: $(this).is(':checked') ? 1 : 0
                };

                saveModuleGuidePreferences(preferences);
            });
        };

        var modalParams = {
            backdrop: 'static',
            keyboard: false,
            cb: callBackFunction
        };

        app.helper.showModal(res, modalParams);
        return false;
    });
}

function saveModuleGuidePreferences(data) {
    var params = {
        module: 'Vtiger',
        action: 'ModuleGuidePopupAjax',
        mode: 'savePreferences',
        target_module: app.getModuleName(),
        show_next_time: data.show_next_time,
    }

    app.request.post({ data: params })
    .then((err, res) => {
        app.helper.hideProgress();
    });
}