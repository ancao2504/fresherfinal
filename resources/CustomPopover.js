/**
 * Name: CustomerPopover.js
 * Author: Phu Vo
 * Date: 2019.06.25
 * Requirement: jQuery, Bootstrap Popover
 */

($ => {
    let utils = new class {
        constructor() {
            this.defaultOptions = {
                html: true,
            }
        }

        /**
         * Return custom popover header string
         * @param {Object} params 
         */
        getHeaderTemplate(params) {
            params = params || {};

            let template = `
                <div class="custom-popover-header">
                    <h3 class="popover-title"></h3>
                    <i class="popover-close far fa-times"></i>
                </div>
            `;

            return template;
        }

        /**
         * Return custom popover body string
         * @param {Object} params 
         */
        getBodyTemplate(params) {
            params = params || {};
            let classList = params['body-class'] || '';
            let size = params['size'] || '';

            let template = `
                <div class="popover-content custom-popover-body fancyScrollbar ${classList} ${size}">
                </div>
            `;

            return template;
        }

        /**
         * Return custom popover template string
         * @param {Object} params 
         */
        getTemplate(params) {
            params = params || {};

            let size = params['size'] || '';
            let header = this.getHeaderTemplate(params);
            let body = this.getBodyTemplate(params);
            let template = `
                <div class="popover custom-popover-container ${size}" role="tooltip">
                    <div class="arrow"></div>
                    ${header}
                    ${body}
                </div>
            `;

            return template.trim();
        }
    }

    /**
     * Implement custom popover as jquery function
     */
    $.fn.customPopover = function(...params) {
        if (params.length > 0 && !(params[0] instanceof Object)) {
            return $(this).popover(...params);
        }

        options = params[0] || {};

        // Apply custom here
        options.template = utils.getTemplate(options);

        let wrapper = $(this).closest('.custom-popover-wrapper');
        if (wrapper[0] == 'undefined' && !options.container) {
            options.container = wrapper[0];
        }

        // Modified by Phu Vo on 2019.09.19 to fix could not get content properly
        let hasContent = options.content ? true : false;

        options.content = function() {
            let wrapper = $(this).closest('.custom-popover-wrapper');
            
            if (wrapper.find('.custom-popover-content')[0] != 'undefined' && !hasContent) {
                return wrapper.find('.custom-popover-content').html();
            }

            return '';
        }
        // End fix could not get content properly

        options = Object.assign(utils.defaultOptions, options);

        return $(this).popover(options);
    }

    // Init event listener when dom ready
    $(() => {
        $('body').on('click', (e) => {
            if (
                $(e.target).closest('.custom-popover-container')[0] == undefined
                && $(e.target).closest('.custom-popover')[0] == undefined
            ) {
                $('.custom-popover-container').popover('hide');
            }
        });

        $('body').on('show.bs.popover', '.custom-popover', (e) => {
            let popoverContainer = $(e.target).data('bs.popover').$tip;

            popoverContainer.find('.popover-close').on('click', () => {
                $(e.target).popover('hide');
            });
        });
    });
})(jQuery);