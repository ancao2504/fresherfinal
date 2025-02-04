CustomView_BaseController_Js('Home_Component_Js', {}, {
    registerEvents: function () {
        this._super();
        this.initSwitch();
    },
    initSwitch: function () {
        $('.bootstrap-switch').bootstrapSwitch();
    },
});