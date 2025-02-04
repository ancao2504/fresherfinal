<?php

/**
 * DetailView.php
 * Author: Phuc
 * Date: 2020.06.26
 */

$displayParams = [
    'scripts' => '
        <script type="text/javascript" src="{vresource_url("modules/HelpDesk/resources/HelpDeskModalUtils.js")}"></script>
    ',
    'form' => [
        'hiddenFields' => '

        ',
    ],
    'fields' => [
        'helpdesk_rating' => [
            'customTemplate' => '{include file="modules/HelpDesk/tpls/RatingReadView.tpl"}'
        ]
    ],
];