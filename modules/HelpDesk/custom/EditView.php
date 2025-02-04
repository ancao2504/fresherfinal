<?php

/**
 * EditView.php
 * Author: Phuc
 * Date: 2020.06.26
 */

$displayParams = [
    'scripts' => '
        <script type="text/javascript" src="{vresource_url("modules/HelpDesk/resources/EmailReplies/EmailReplyForm.js")}"></script>
		<script type="text/javascript" src="{vresource_url("modules/HelpDesk/resources/Form.js")}"></script>
        <script type="text/javascript" src="{vresource_url("modules/HelpDesk/resources/EditView.js")}"></script>
        <link type="text/css" rel="stylesheet" href="{vresource_url("modules/HelpDesk/resources/EmailReplies.css")}">
    ',
    'form' => [
        'hiddenFields' => '

        ',
    ],
    'fields' => [
        'helpdesk_rating' => [
            'customTemplate' => '{include file="modules/HelpDesk/tpls/RatingEditView.tpl"}'
        ],
        'helpdesk_related_emails' => [
            'customTemplate' => '{include file="modules/HelpDesk/tpls/RelatedEmailsEditView.tpl"}'
        ]
    ],
];