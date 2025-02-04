/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Survey form script
 */

let SurveyForm = new class {
    initEvents() {
        this.registerRatingEvents();
    }

    registerRatingEvents() {
        let self = this;
        let ratingField = $('[name="helpdesk_rating"]');
        
        if (!ratingField.val()) ratingField.val(1);
        
        ratingField.rating({
            'min': 1,
            'max': 5,
            'empty-value': 0,
            'activeIcon': 'fas fa-star',
            'inactiveIcon': 'far fa-star',
        });

        ratingField.on('change', function () {
            let score = $(this).val();
            self.renderScoreDescription(parseInt(score));
        }).trigger('change');
    }

    renderScoreDescription(score) {
        let message = 'Bạn có hài lòng về chất lượng hỗ trợ ?';
        
        switch (score) {
            case 5:
                message = 'Cực kỳ hài lòng';
                break;
            case 4:
                message = 'Hài lòng';
                break;
            case 3:
                message = 'Bình thường';
                break;
            case 2:
                message = 'Không hài lòng';
                break;
            case 1:
                message = 'Rất không hài lòng';
                break;
        }
        let descriptionPlaceholder = 'Cảm ơn bạn đã nhận xét, bạn có điều gì muốn góp ý thêm không?';
        
        if (score < 3) {
            descriptionPlaceholder = 'Nếu bạn chưa hài lòng về chất lượng hỗ trợ, vui lòng góp ý thêm để chúng tôi cải thiện trong thời gian tới.';
        }
        
        $('.scoreDescription').html(message);
        $('.ratingDescription').attr('placeholder', descriptionPlaceholder);
    }
}

$(function() {
    SurveyForm.initEvents(); 
});