Vtiger_Detail_Js("Calendar_Detail_Js", {
    
}, {
    
    _delete : function(deleteRecordActionUrl) {
        var params = app.convertUrlToDataParams(deleteRecordActionUrl+"&ajaxDelete=true");
        app.helper.showProgress();
        app.request.post({data:params}).then(
        function(err,data){
            app.helper.hideProgress();
            if(err === null) {
                if(typeof data !== 'object') {
                    window.location.href = data;
                } else {
                    app.helper.showAlertBox({'message' : data.prototype.message});
                }
            } else {
                app.helper.showAlertBox({'message' : err});
            }
        });
    },
    
    /**
    * To Delete Record from detail View
    * @param URL deleteRecordActionUrl
    * @returns {undefined}
    */
    remove : function(deleteRecordActionUrl) {
        var thisInstance = this;
        var isRecurringEvent = jQuery('#addEventRepeatUI').data('recurringEnabled');
        if(isRecurringEvent) {
            app.helper.showConfirmationForRepeatEvents({ mode: 'delete' }).then(function(postData) {    // Modified by Hieu Nguyen on 2020-03-17 to specify the desired confirm popup view
                deleteRecordActionUrl += '&' + jQuery.param(postData);
                thisInstance._delete(deleteRecordActionUrl);
            });
        } else {
            this._super(deleteRecordActionUrl);
        }
    },    
    
    registerEvents : function() {
        this._super();
    }
    
});