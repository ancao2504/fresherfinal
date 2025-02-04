{* Added by Hieu Nguyen on 2020-09-07 *}

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />

        <link rel="icon" href="data:;base64,iVBORw0KGgo=">
        <title>Login</title>

        <link type='text/css' rel='stylesheet' href="{vresource_url('modules/CPChatBotIntegration/resources/ChatbotIframeAuth.css')}">
    </head>

    <body>
        <div id="auth-form-wrapper">

            <form id="auth-form" method="POST" action="">
                <div class="header">
                    {assign var="LOGO_DETAIL" value=Vtiger_CompanyDetails_Model::getInstanceById()->getLogo()}
                    <div class="logo-wrapper">
                        <img class="logo" src="{$LOGO_DETAIL->get('imagepath')}" />
                    </div>
                </div>

                <span>Please login with your username and password</span>

                <div class="body">
                    <input type="text" id="username" name="username" placeholder="Username" required /><br/>
                    <input type="password" id="password" name="password" placeholder="Password" required /><br/>
                </div>

                {if $ERROR_MSG}
                    <div class="error">{$ERROR_MSG}</div>
                {/if}

                <div class="footer">
                    <button name="submit">Login</button>
                </div>
            </form>
        </div>
    </body>
</html>