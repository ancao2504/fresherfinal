{*
    ChatbotIframeFooter.tpl
    Author: Phu Vo
    Data: 2020.04.20
    Description: Provide basic footer for iframe
*}

        <div id="js_strings" class="hide noprint">{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($LANGUAGE_STRINGS))}</div>

        <script type='text/javascript' src="{vresource_url('layouts/v7/modules/Vtiger/resources/validation.js')}"></script>
            <script type="text/javascript" src="{vresource_url("modules/CPChatBotIntegration/resources/ChatbotIframe.js")}"></script>
    </body>
</html>