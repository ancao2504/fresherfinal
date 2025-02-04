{*
    Author: Tin Bui
    Data: 2022.03.16
    Description: Ticket survey form UI
*}

<!DOCTYPE html>
<html>
    <head>
        <title>{$PAGETITLE}</title>
        <link rel="manifest" href="manifest.json">
        <link rel="shortcut icon" href="layouts/v7/resources/Images/logo_favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/jquery/select2/select2.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/select2-bootstrap/select2-bootstrap.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/vt-icons/style.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('resources/libraries/BootstrapDatepicker/bootstrap-datetimepicker.min.css')}">
        <link type='text/css' rel='stylesheet' href="{vresource_url('resources/libraries/DataTables/css/dataTables.bootstrap4.min.css')}">
        <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css')}">
        <link type="text/css" rel="stylesheet" href="layouts/v7/resources/font.css" media="screen" />
        <!-- <link type='text/css' rel='stylesheet' href="{vresource_url('layouts/v7/lib/font-awesome/css/font-awesome.min.css')}"> -->
        <link type='text/css' rel='stylesheet' href='layouts/v7/resources/fonts/fontawsome6/css/all.css'/>

        {foreach key=index item=cssModel from=$STYLES}
            <link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
        {/foreach}

        <script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/select2/select2.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.class.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.js')}"></script>
        <script type="text/javascript" src="{vresource_url('libraries/jquery/jstorage.min.js')}"></script>
        <script type="text/javascript" src="layouts/v7/lib/todc/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/bootstrap-notify/bootstrap-notify.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mousewheel.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/daterangepicker/moment.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/daterangepicker/jquery.daterangepicker.js')}"></script>
        <script src="{vresource_url('layouts/v7/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js')}"></script>
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/bootbox/bootbox.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/BootstrapDatepicker/bootstrap-datetimepicker.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/jquery.additions.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/DataTables/js/jquery.dataTables.min.js')}"></script>
        <script type="text/javascript" src="{vresource_url('resources/libraries/DataTables/js/dataTables.bootstrap4.min.js')}"></script>

        {foreach key=index item=jsModel from=$SCRIPTS}
            <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
        {/foreach}
    </head>
    <body>
        <div class="surveyFormWrapper">
            <div class="surveyFormHeader">
                <img class="logo" src="{$LOGO_URL}">
            </div>
            <div class="surveyFormBody">
                <form action="" method="POST">
                    <div class="formTitle">Phản hồi về yêu cầu hỗ trợ mã {$FORM_DATA['rawData']['ticket_no']}</div>
                    {if $FORM_DATA['state'] == HelpDesk_SurveyUtils_Helper::FORM_ACTIVE}
                        <div class="formDescription">Vui lòng đánh giá mức độ hài lòng của bạn về chất lượng hỗ trợ</div>
                    {else if $FORM_DATA['state'] == HelpDesk_SurveyUtils_Helper::SURVEY_DONE}
                        <div class="formDescription">Cảm ơn bạn đã gửi khảo sát</div>
                    {else if $FORM_DATA['state'] == HelpDesk_SurveyUtils_Helper::FORM_EXPIRED}
                        <div class="formDescription">Form khảo sát đã hết hạn</div>
                    {/if}
                    
                    <div class="infoBlock">
                        <div class="infoTitle">Thông tin chung</div>
                        <div class="infoBody">
                            <table class="ticketInfoTable">
                                <tbody>
                                    {foreach item=FIELD from=$FORM_DATA['summaryData']}
                                        <tr>
                                            <td class="fieldLabel">{$FIELD['label']}</td>
                                            <td class="fieldValue">{$FIELD['value']}</td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {if $FORM_DATA['state'] == HelpDesk_SurveyUtils_Helper::FORM_ACTIVE}
                    <div class="infoBlock">
                        <div class="infoTitle">Đánh giá</div>
                        <div class="infoBody ratingBody">
                            <div class="ratingWrapper">
                                <div class="scoreDescription"></div>
                                <input type="number" name="helpdesk_rating">
                                <textarea name="rating_description" class="ratingDescription" rows="5"></textarea>
                            </div>
                            <button class="submitButton">Gửi</button>
                        </div>
                    </div>
                    {/if}
                </form>
            </div>
        </div>
    </body>
</html>