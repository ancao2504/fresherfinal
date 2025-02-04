
<?php
    /*
    *   CustomCode Structure
    *   Author: Hieu Nguyen
    *   Date: 2018-07-17
    *   Purpose: customize the layout easily with configurable display params
    */

    $displayParams = array(
        'scripts' => '
            <script async defer src="{vresource_url("modules/Users/resources/PasswordValidator.js")}"></script>
            <script async defer src="{vresource_url("modules/Users/resources/EditView.js")}"></script>
        ',
        'form' => array(
            'hiddenFields' => '{include file="modules/Users/tpls/UsersTypeFieldEditView.tpl"}',
        ),
        'fields' => array(

        ),
    );