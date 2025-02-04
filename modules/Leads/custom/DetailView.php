<?php
    /*
    *	DetailView.php
    *	Author: Phuc Lu
    *	Date: 2019.11.25
    *   Purpose: handle leadstatus picklist
    */

    $displayParams = array(
        'scripts' => '
            <script type="text/javascript" src="{vresource_url("resources/ChatBotHandler.js")}"></script>
			<script type="text/javascript" src="{vresource_url("modules/Leads/resources/DetailView.js")}"></script>
			<script type="text/javascript" src="{vresource_url("modules/PBXManager/resources/RecordingPopup.js")}"></script>
			<script type="text/javascript" src="{vresource_url("modules/CPEventRegistration/resources/EventRegistrationHelper.js")}"></script>
            <script type="text/javascript" src="{vresource_url("modules/CPMauticIntegration/resources/MauticHistory.js")}"></script>

            {include file="modules/PBXManager/tpls/PhoneSelectorTemplate.tpl"}
        ',
        'form' => array(
            'hiddenFields' => '
                <input type="hidden" id="is_converted" value="{if $RECORD->get("leadstatus") == "Converted"}true{/if}" />
            ',
        ),
        'fields' => array(
        ),
    );