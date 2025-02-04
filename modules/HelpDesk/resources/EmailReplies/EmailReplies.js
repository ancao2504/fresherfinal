/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc email reply script
 */

let EmailRepliesTab = new class {
    initEvents() {
        this.EmailReplyForm = new EmailReplyForm($('#sendReplyForm'));
        this.RepliesHistory = new RepliesHistory();
        this.StatusLogHistory = new StatusLogHistory();
    }
}

$(function () {
    EmailRepliesTab.initEvents();
});

app.event.on('post.relatedListLoad.click', function () {
    EmailRepliesTab.initEvents();
});

