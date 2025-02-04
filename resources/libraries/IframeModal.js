/**
 * @name IframeModal.js
 * @author Phu Vo
 * @version 1.0
 * @date 2018-12-29
 * @description Custom Modal for vtiger
 */

var IframeModal = {
    defaultParams: {
        backdrop:true,
        show:true,
        keyboard: false
    },

    modal: null,

    href: "",
    
    // BUG: Loop call show method from call center quick create button
    show: function(url, params) {
        this.hide();
        
        // Init & Process Input here
        params = params || {};
        params = Object.assign(this.defaultParams, params);

        // Declare dom
        let container = jQuery(".iframeModal").clone();

        // Add change url event listener
        container.find(".md-iframe").attr("onLoad", "IframeModal.checkUrl(this)");

        // # Init iframe url
        let htmlPopupIframe = container.find('.md-iframe').get();
        htmlPopupIframe[0].src = url;

        // Add title from params
        if(params.title) container.find(".modal-header .pull-left").text(params.title);

        // # Event Handlers
        // ## Open modal
        if(params.open && typeof params.open === "function") {
            container.on("shown.bs.modal", function() {
                params.open();
            });
        }

        // ## Close modal
        if(params.beforeClose && typeof params.beforeClose === "function") {
            container.on("hide.bs.modal", function() {
                params.beforeClose();
            });
        }

        // ## Submit Handler
        if(params.submitHandler && typeof params.submitHandler === 'function') {
            container.find('[type="submit"]').off("click").on("click", () => {
                params.submitHandler();
            });
        }

        // ## Decline handler
        if(params.cancelHandler && typeof params.cancelHandler === 'function') {
            container.find('[type="reset"]').off("click").on("click", () => {
                params.cancelHandler();
            });
        }

        container.modal(params); // Show modal here

        // # We can use it in another method
        this.modal = container;
        return container;
    },

    hide: function() {
        // # Hide vtiger modal
        app.helper.hideModal();

        if(this.modal) {
            if(this.href && this.modal.find(".md-iframe").attr("src")) this.href = "";

            // remove some event listener
            this.modal.off("hide.bs.modal");
            // # Fullill Bootstrap Lifecyle
            this.modal.modal("hide");
            // Remove
            this.modal.remove();
        }
    },

    checkUrl: function(iframe) {
        // BUG iframe src didn't change after save form
        if(this.href && iframe.src !== this.href) {
            this.hide();
        }

        console.log("=======");
        console.log("Old:", this.href);
        console.log("New:", iframe.src);
        console.log("Result:", this.href && iframe.src !== this.href);

        this.href = iframe.src;
        console.log("Assigned:", this.href);
    }
}