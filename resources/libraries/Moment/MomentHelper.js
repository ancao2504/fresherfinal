/*
*   MomentHelper.js
*   Author: Phu Vo
*   Date: 2019-02-21
*   Purpose: Provine common helper functions for moment
*/

jQuery(function($) {
    window.MomentHelper = {
        languageMapping: {
            'vn_vn': 'vi',
            'en_us': 'en',
        },
        vi: {
            months : 'tháng 1_tháng 2_tháng 3_tháng 4_tháng 5_tháng 6_tháng 7_tháng 8_tháng 9_tháng 10_tháng 11_tháng 12'.split('_'),
            monthsShort : 'Th01_Th02_Th03_Th04_Th05_Th06_Th07_Th08_Th09_Th10_Th11_Th12'.split('_'),
            monthsParseExact : true,
            weekdays : 'chủ nhật_thứ hai_thứ ba_thứ tư_thứ năm_thứ sáu_thứ bảy'.split('_'),
            weekdaysShort : 'CN_T2_T3_T4_T5_T6_T7'.split('_'),
            weekdaysMin : 'CN_T2_T3_T4_T5_T6_T7'.split('_'),
            weekdaysParseExact : true,
            meridiemParse: /am|pm/i,
            isPM : function (input) {
                return /^pm$/i.test(input);
            },
            meridiem : function (hours, minutes, isLower) {
                if (hours < 12) {
                    return isLower ? 'am' : 'AM';
                } else {
                    return isLower ? 'pm' : 'PM';
                }
            },
            longDateFormat : {
                LT : 'HH:mm',
                LTS : 'HH:mm:ss',
                L : 'DD/MM/YYYY',
                LL : 'D MMMM [năm] YYYY',
                LLL : 'D MMMM [năm] YYYY HH:mm',
                LLLL : 'dddd, D MMMM [năm] YYYY HH:mm',
                l : 'DD/M/YYYY',
                ll : 'D MMM YYYY',
                lll : 'D MMM YYYY HH:mm',
                llll : 'ddd, D MMM YYYY HH:mm'
            },
            calendar : {
                sameDay: '[Hôm nay lúc] LT',
                nextDay: '[Ngày mai lúc] LT',
                nextWeek: 'dddd [tuần tới lúc] LT',
                lastDay: '[Hôm qua lúc] LT',
                lastWeek: 'dddd [tuần rồi lúc] LT',
                sameElse: 'L'
            },
            relativeTime : {
                future : '%s tới',
                past : '%s trước',
                s : 'vài giây',
                ss : '%d giây' ,
                m : 'một phút',
                mm : '%d phút',
                h : 'một giờ',
                hh : '%d giờ',
                d : 'một ngày',
                dd : '%d ngày',
                M : 'một tháng',
                MM : '%d tháng',
                y : 'một năm',
                yy : '%d năm'
            },
            dayOfMonthOrdinalParse: /\d{1,2}/,
            ordinal : function (number) {
                return number;
            },
            week : {
                dow : 1, // Monday is the first day of the week.
                doy : 4  // The week that contains Jan 4th is the first week of the year.
            }
        },
        initLanguages: function() {
            moment.defineLocale('vi', this.vi);
        },
        getUserLanguage: function() {
            return this.languageMapping[app.getUserLanguage()];
        },
        loadUserLanguage: function() {
            this.initLanguages();
            return moment.locale(this.getUserLanguage());
        },
        /**
         * Util method to get display date from db date / date time
         * 
         * Param dateTime: Use as moment input, suported: 
         * - ISO 8601 strings: 2019-08-21 | 2019-08-21T07:25:59Z | 20190821T072559Z
         * - Javascript milisecond from 1970: 1566374062286
         * - Moment Object
         * - Date Object
         * 
         * Reference: https://momentjs.com/docs/#/parsing/
         * 
         * Param format: Use to explain dateTime specific format. Ex: DD-MM-YYYY
         * 
         * Reference: https://momentjs.com/docs/#/displaying/
         */
        getDisplayDate: function(dateTime, format = '') {
            return moment(dateTime, format).format(vtUtils.getMomentDateFormat());
        },
        /**
         * Util method to get display date from db date / date time
         * 
         * Param dateTime: Use as moment input, suported: 
         * - ISO 8601 strings: 2019-08-21 | 2019-08-21T07:25:59Z | 20190821T072559Z
         * - Javascript milisecond from 1970: 1566374062286
         * - Moment Object
         * - Date Object
         * 
         * Reference: https://momentjs.com/docs/#/parsing/
         * 
         * Param format: Use to explain dateTime specific format. Ex: DD-MM-YYYY
         * 
         * Reference: https://momentjs.com/docs/#/displaying/
         */
        getDisplayTime: function(dateTime, format = '') {
            return moment(dateTime, format).format(vtUtils.getMomentTimeFormat());
        },
        /**
         * Util method to get display date from db date / date time
         * 
         * Param dateTime: Use as moment input, suported: 
         * - ISO 8601 strings: 2019-08-21 | 2019-08-21T07:25:59Z | 20190821T072559Z
         * - Javascript milisecond from 1970: 1566374062286
         * - Moment Object
         * - Date Object
         * 
         * Reference: https://momentjs.com/docs/#/parsing/
         * 
         * Param format: Use to explain dateTime specific format. Ex: DD-MM-YYYY
         * 
         * Reference: https://momentjs.com/docs/#/displaying/
         */
        getDisplayDateTime: function(dateTime, format = '') {
            return moment(dateTime, format).format(vtUtils.getMomentCompatibleDateTimeFormat());
        }
    };
    
    // process
    if(window.moment) window.MomentHelper.loadUserLanguage();
});