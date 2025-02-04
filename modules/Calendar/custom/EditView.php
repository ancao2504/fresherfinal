<?php
    $displayParams = array(
        'scripts' => '
			<script type="text/javascript" src="{vresource_url("modules/Calendar/resources/EditView.js")}"></script>
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
            // End Hieu Nguyen
            // Added by Hieu Nguyen on 2019-11-22
            'user_invitees' => array(
                'customTemplate' => '{include file="modules/Calendar/tpls/UserInviteesFieldEditView.tpl"}'
            ),
            // End Hieu Nguyen
        ),
    );