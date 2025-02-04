<?php
    // Added by Hieu Nguyen on 2018-10-19
    $displayParams = array(
        'scripts' => '

        ',
        'form' => array(
            'hiddenFields' => '

            ',
        ),
        'fields' => array(
            'recordingurl' => array(
                'customTemplate' => '<audio controls="controls"><source src="index.php?module=PBXManager&action=GetRecording&record={$RECORD->get("id")}" type="audio/mp3"></audio>',
            ),
        ),
    );