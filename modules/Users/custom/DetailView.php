
<?php
    /*
    *   CustomCode Structure
    *   Author: Hieu Nguyen
    *   Date: 2018-07-17
    *   Purpose: customize the layout easily with configurable display params
    */

    $displayParams = array(
        'scripts' => '
            <link type="text/css" rel="stylesheet" href="{vresource_url("modules/Users/resources/DetailView.css")}"></link>
            <script src="{vresource_url("modules/Users/resources/DetailView.js")}"></script>
        ',
        'form' => array(
            
        ),
        'fields' => array(
            'accesskey' => array(
                'customTemplate' => '
                    <span>****************</span> <input id="access-key" type="text" value="{$RECORD->get("accesskey")}" />
                    <button type="button" id="copy-access-key" title="Copy"><i class="far fa-clipboard"></i></button>
                ',
            ),
        ),
    );