/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Reply history script
 */

class RepliesHistory {
    constructor() {
        this.$el = $('#repliesHistoryBlock');
        if (this.$el.length == 0) return; 
        this.initEvents();
        this.render();
        this.showLoading();
    }

    initEvents() {
        let self = this;
        app.event.on('post.EmailReplyForm.send', function () {
            self.render();
        });
    }

    async getData() {
        let data = [];
        let ticket_id = app.getRecordId();

        await $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                module: 'HelpDesk',
                action: 'HandleAjax',
                mode: 'getTicketLogs',
                ticket_id: ticket_id
            },
            success: function (res) {
                let result = res.result;
                if (result.success && result.data) {
                    data = result.data;
                }
            },
            error: function (err) {
                console.log(err);
            }
        });

        return data;
    }

    async render() {
        this.$el.find('.repliesListWrapper').empty();
        this.showLoading();
        let logs = await this.getData();
        this.hideLoading();
        
        if (!logs || logs.length == 0) {
            this.$el.find('.repliesListWrapper').append(`<div class="blockEmptyData">${app.vtranslate('HelpDesk.JS_NO_RECORD')}</div>`)
        }

        for (let i = 0; i < logs.length; i++) {
            this.$el.find('.repliesListWrapper').append(this.getRowTemplate(logs[i]));
        }
    }

    getRowTemplate(log) {
        let logCssClass = `${log.source}${log.direction}`.toLocaleLowerCase();
        let icon = (log.source == 'EMAIL') ? 'fad fa-envelope' : 'fa-phone-square';
        let timestapp = `${(log.source == 'EMAIL') ? app.vtranslate('HelpDesk.JS_SENT_AT') : app.vtranslate('HelpDesk.JS_CALLED_AT')} ${log.createdtime}`;
        let tpl = `<div class="replyContainer ${logCssClass}">
                        <div class="senderInfosWrapper">
                            <i class="replyIcon ${icon}"></i>
                            <div class="senderName">${log.sender_fullname}</div>
                            <div class="senderEmail">${log.sender_email}</div>
                        </div>
                        <div class="contentWrapper">
                            <div class="contentHeader">
                                <div class="timestampp">${timestapp}</div>
                                <div class="headerBtns"></div>
                            </div>
                            <div class="contentBody">${log.description}</div>
                            <div class="contentFooter"></div>
                        </div>
                    </div>`;
        
        let $tpl = $('<div></div').append(tpl);

        if (log.attachments && log.source == 'EMAIL') {
            $tpl.find('.contentFooter').append('<div class="attachmentsWrapper"></div');
            
            for (let i = 0; i < log.attachments.length; i++) {
                let attachment = log.attachments[i];
                let attachmentTpl = `<a class="attachment" href="${attachment.download_url}">${attachment.file_name}</a>`;
                $tpl.find('.attachmentsWrapper').append(attachmentTpl);
            }
        }
        else if (log.source == 'call') {
            $tpl.find('.contentFooter').append(`<audio controls="controls"><source src="${log.audio_url}" type="audio/mp3"></audio>`);
        }

        return $tpl.html();
    }

    showLoading() {
        if (this.$el.find('.repliesListWrapper .elementLoadingWrapper').length == 0) {
            this.$el.find('.repliesListWrapper').append(`<div class="elementLoadingWrapper"><div class="elementLoading"></div></div>`);
        } 
    }

    hideLoading() {
        this.$el.find('.elementLoadingWrapper').remove();
    }
}