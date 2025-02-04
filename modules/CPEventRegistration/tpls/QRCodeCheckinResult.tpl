{* Added by Hieu Nguyen on 2020-05-25 *}

{strip}
    <!DOCTYPE html>
    <html>
        <head>
            <title>{vtranslate('LBL_QR_CHECKIN_RESULT_PAGE_TITLE', 'CPEventRegistration')}</title>
            <link rel="shortcut icon" href="{vresource_url('layouts/v7/resources/Images/logo_favicon.ico')}">
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <link type="text/css" rel="stylesheet" href="{vresource_url('modules/CPChatBotIntegration/resources/QRCodeCheckinResult.css')}">
            <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/resources/fonts/Roboto/Roboto.css')}">
        </head>
        <body>
            <div id="main">
                <div id ="header">
                    <img class="company-logo" src="{$COMPANY_LOGO->get('imagepath')}" alt="{$COMPANY_LOGO->get('alt')}"/>
                </div>
                <div class="underline"></div>
                <div id="content">
                    {if $SUCCESS}
                        <h1 id="title">{vtranslate('LBL_QR_CHECKIN_SUCCESS_TITLE', 'CPEventRegistration')}</h1> 
                        <div id="attendee">{vtranslate('LBL_QR_CHECKIN_ATTENDEE_NAME', 'CPEventRegistration', ['%name' => $ATTENDEE_NAME])}</div>
                        <div id="checkin-time">{vtranslate('LBL_QR_CHECKIN_TIME', 'CPEventRegistration', ['%time' => $CHECKIN_TIME])}</div>
                    {else}
                        <h1 id="title">{vtranslate('LBL_QR_CHECKIN_ERROR_TITLE', 'CPEventRegistration')}</h1>
                        <div id="error-message">{$ERROR_MSG}</div>
                    {/if}
                </div>
                <div id="footer">
                    {if $SUCCESS}
                        <div>{vtranslate('LBL_QR_CHECKIN_SUCCESS_FOOTER_TEXT', 'CPEventRegistration')}</div> 
                    {/if}
                </div>
            </div>
        </body>
    </html>
{/strip}