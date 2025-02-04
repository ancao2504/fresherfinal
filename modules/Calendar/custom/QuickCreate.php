<?php
    $displayParams = array(
        'scripts' => '
			<script type="text/javascript" src="{vresource_url("modules/Calendar/resources/QuickCreate.js")}"></script>
            <script type="text/javascript" src="{vresource_url("modules/Calendar/resources/FormHelper.js")}"></script>
        ',
        'form' => array(
            'hiddenFields' => '

            ',
        ),
        'fields' => array(
            // Added by Hieu Nguyen on 2020-03-14
            'contact_invitees' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/ContactInviteesFieldEditView.tpl"}'
            ),
            // Added by Hieu Nguyen on 2019-11-22
            'user_invitees' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/UserInviteesFieldQuickCreateView.tpl"}'
            ),
            // End Hieu Nguyen
        ),
    );