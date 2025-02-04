/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Status log history script
 */

class StatusLogHistory {
    constructor () {
        this.$el = $('#statusUpdateHistoryBlock');
        this.render();
        this.showLoading();
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
                mode: 'getTicketStatusHistoryLogs',
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
        this.$el.find('.statusLogWrapper').empty();
        this.showLoading();
        let logs = await this.getData();
        this.hideLoading();
        
        if (!logs || logs.length == 0) {
            this.$el.find('.statusLogWrapper').append(`<div class="blockEmptyData">${app.vtranslate('HelpDesk.JS_NO_RECORD')}</div>`)
        }

        for (let i = 0; i < logs.length; i++) {
            this.$el.find('.statusLogWrapper').append(this.getRowTemplate(logs[i]));
        }
    }

    getRowTemplate(log) {
        let avtTpl = log.user_avt_url ? `<img src="${log.user_avt_url}">` : `<i class='fad fa-user-alt'></i>`;
        return `<div class="statusLog">
                    <div class="logWrapper">
                        <div class="userAvt">
                            <div class="avtIcon">
                                ${avtTpl}
                            </div>
                        </div>
                        <div class="logMessage">${log.message}</div>
                    </div>
                    <div class="logTimestamp">${log.timestamp}</div>
                </div>`;
    }

    showLoading() {
        if (this.$el.find('.statusLogWrapper .elementLoadingWrapper').length == 0) {
            this.$el.find('.statusLogWrapper').append(`<div class="elementLoadingWrapper"><div class="elementLoading"></div></div>`);
        } 
    }

    hideLoading() {
        this.$el.find('.elementLoadingWrapper').remove();
    }
}