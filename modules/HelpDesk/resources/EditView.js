
/*
*	EditView.js
*	Author: Phuc Lu
*	Date: 2020.06.26
*   Purpose: handle edit action and UI
*/

jQuery(function($){
    $('.edit.rating-star').on('click', function () {
        var thisStar = $(this).data('star');

        $(this).parent().find('.rating-star').each(function () {
            if ($(this).data('star') <= thisStar) {
                $(this).addClass('checked');
            }
            else {
                $(this).removeClass('checked');
            }
        })

        $(this).closest('td').find('.div-rating-select').find('select').val(thisStar);
    })

    $('.edit.rating-star').on('mousemove', function () {
        var thisStar = $(this).data('star');

        $(this).parent().find('.rating-star').each(function () {
            if ($(this).data('star') <= thisStar) {
                $(this).addClass('checked');
            }
            else {
                $(this).removeClass('checked');
            }
        })
    });

    $('.edit.rating-star').on('mouseout', function () {
        var star = $(this).parent().next().find('select').val();

        $(this).parent().find('.rating-star').each(function () {
            if ($(this).data('star') <= star) {
                $(this).addClass('checked');
            }
            else {
                $(this).removeClass('checked');
            }
        })
    });

    $('[name="ticketstatus"]').change(function (firstTime = false) {
        var element = $('.helpdesk_rating');
        element.find('select').val('');
        
        if (!firstTime) element.find('.rating-star').removeClass('checked');

        if ($(this).val() == 'Closed') {
            element.closest('tr').removeClass('hide');
            element.removeClass('hide');
        }
        else {
            element.addClass('hide');   

            // Hide tr if this row only have 1 field
            if (element.closest('tr').find('td:visible').html() == '') {
                element.closest('tr').addClass('hide');
            }
        }
    })

    $('[name="ticketstatus"].inputElement').trigger('change', true);
    $('.div-rating').removeClass('hide');

    // Added by Tin Bui on 2022.03.15
    new HelpDesk_EditView_Js();
})

class HelpDesk_EditView_Js extends HelpDesk_Form_Js {
    initEvent() {
        super.initEvent();
        
        if (!this.$form.find('[name="record"]').val()) {
            this.$replyBlock = new AddReplyBlock(this.$form);
        }
    }
}

class AddReplyBlock extends EmailReplyForm {
    async initialize() {
        await this.render();
        this.initCKEditor();
        this.initAttachmentsField();
        this.registerEvents();
    }

    async getUI() {
        let html = '';

        await $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                module: 'HelpDesk',
                action: 'HandleAjax',
                mode: 'getEditViewReplyBlockHtml'
            },
            success: function (res) {
                if (res) html = res;
            },
            error: function (err) {
                console.log(err);
            }
        });

        return html;
    }

    async render() {
        let html = await this.getUI();
        this.$el.find('.editViewContents').append(html);
    }

    registerEvents() {
        app.event.on(Vtiger_Edit_Js.recordPresaveEvent, () => {
            let isSendReply = this.ckInstance.getPlainText().replace('\n', '').length != 0;

            if (isSendReply) {
                this.$el.append('<input type="hidden" name="isSendReply" value="1">')
            }
		});
    }
}