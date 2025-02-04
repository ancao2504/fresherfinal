function convertTarget (recordId) {
    const replaceParams = {
        'customer_name': '<span class="customer_name">' + $('.recordLabel').text().trim() + '</span>',
    };

    app.helper.showConfirmationBox({
        title: app.vtranslate('CPTarget.JS_CONVERT_TARGET_CONFIRM_TITLE'),
        message: app.vtranslate('CPTarget.JS_CONVERT_TARGET_CONFIRM_MSG', replaceParams),
        buttons: {
            confirm: {
                label: app.vtranslate('JS_CONFIRM'),
                className : 'confirm-box-ok confirm-box-btn-pad btn-primary'
            },
            cancel: {
                label: app.vtranslate('JS_CANCEL'),
                className : 'btn-default confirm-box-btn-pad pull-right'
            },
        },
    }).then(() => {
        const params = {
            module: 'CPTarget',
            action: 'HandleAjax',
            mode: 'convertTarget',
            record: app.getRecordId(),
        };

        app.helper.showProgress();

        // Return promiss object to handle next resolve
        return app.request.post({data: params});
    }).then((err, res) => {
        app.helper.hideProgress();

        // Handle specific error from server
        if (err && err.message) {
            app.helper.showErrorNotification({ message: err.message, replaceParams});
            return;
        }

        // Handle unknow error
        if (err || !res) {
            app.helper.showErrorNotification({ message: app.vtranslate('CPTarget.JS_CONVERT_TARGET_ERROR_MSG', replaceParams) });
            return;
        }

        // Handle success
        app.helper.showSuccessNotification({ message: app.vtranslate('CPTarget.JS_CONVERT_TARGET_SUCCESS_MSG', replaceParams) });
        window.location = `index.php?module=Leads&view=Detail&record=${res.id}`;
        return;
    });
}