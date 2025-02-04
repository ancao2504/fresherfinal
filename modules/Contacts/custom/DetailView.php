<?php
    // Added by Hieu Nguyen on 2019-12-30
    $displayParams = array(
        'scripts' => '
			<script type="text/javascript" src="{vresource_url("resources/ChatBotHandler.js")}"></script>
			<script type="text/javascript" src="{vresource_url("modules/PBXManager/resources/RecordingPopup.js")}"></script>
			<script type="text/javascript" src="{vresource_url("modules/CPMauticIntegration/resources/MauticHistory.js")}"></script>
            <script type="text/javascript" src="{vresource_url("modules/CPEventRegistration/resources/EventRegistrationHelper.js")}"></script>

            {include file="modules/PBXManager/tpls/PhoneSelectorTemplate.tpl"}
        ',
        'form' => array(
            'hiddenFields' => '

            ',
        ),
        'fields' => array(
            
        ),
    );