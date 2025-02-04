<?php

// This file is for testing purpose only.
$data = array(
    'Zalo' => array(
        'user_send_text'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_text","timestamp":"1609984527175","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message"}}',
        'user_send_link'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_link","timestamp":"1609984527175","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","description":"description_of_link","url":"link"},"type":"link"}]}}',
        'user_send_sticker'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_sticker","timestamp":"1609984527176","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"id":"sticker_id","url":"link"},"type":"sticker"}]}}',
        'user_send_image'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_image","timestamp":"1609984527174","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","url":"url_to_thumbnail"},"type":"image"}]}}',
        'user_send_gif'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_gif","timestamp":"1609984527176","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","url":"url_to_thumbnail"},"type":"gif"}]}}',
        'user_send_audio'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_audio","timestamp":"1609984527178","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"url":"url_to_audio_file"},"type":"audio"}]}}',
        'user_send_video'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_video","timestamp":"1609984527183","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","description":"description_of_link","url":"url_to_video_file"},"type":"video"}]}}',
        'user_send_file'  => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"user_send_file","timestamp":"1609984527185","sender":{"id":"4339761418830368433"},"recipient":{"id":"2197174064623873199"},"message":{"msg_id":"This is message id","attachments":[{"payload":{"url":"link_to_download_file","size":"9999","name":"file_name","checksum":"checksum","type":"file_type"},"type":"file"}]}}',
        'oa_send_text' => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"oa_send_text","timestamp":"1609984527180","sender":{"id":"2197174064623873199"},"recipient":{"id":"4339761418830368433"},"message":{"msg_id":"This is message id","text":"This is testing message"}}',
        'oa_send_image' => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"oa_send_image","timestamp":"1609984527180","sender":{"id":"2197174064623873199"},"recipient":{"id":"4339761418830368433"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","url":"url_to_thumbnail"},"type":"image"}]}}',
        'oa_send_list' => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"oa_send_list","timestamp":"1609984527181","sender":{"id":"2197174064623873199"},"recipient":{"id":"4339761418830368433"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","description":"description","url":"link","title":"title"},"type":"link"},{"payload":{"thumbnail":"url_to_thumbnail","description":"description","url":"link","title":"title"},"type":"link"},{"payload":{"thumbnail":"url_to_thumbnail","description":"description","url":"link","title":"title"},"type":"link"}]}}',
        'oa_send_gif' => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"oa_send_gif","timestamp":"1609984527183","sender":{"id":"2197174064623873199"},"recipient":{"id":"4339761418830368433"},"message":{"msg_id":"This is message id","text":"This is testing message","attachments":[{"payload":{"thumbnail":"url_to_thumbnail","url":"url_to_gif"},"type":"gif"}]}}',
        'oa_send_file' => '{"app_id":"1163843031413526746","user_id_by_app":"2372735685249892743","event_name":"oa_send_file","timestamp":"1609984527185","sender":{"id":"2197174064623873199"},"recipient":{"id":"4339761418830368433"},"message":{"msg_id":"This is message id","attachments":[{"payload":{"url":"link_to_download_file","size":"9999","name":"file_name","checksum":"checksum","type":"file_type"},"type":"file"}]}}',
    ),
    'Facebook' => array(
        '' => '',
    )
);

$channelName = str_replace('Connector', '', $_REQUEST['name']);
$eventName = $_REQUEST['event'];

if ($data[$channelName][$eventName]) {
    $_POST = json_decode($data[$channelName][$eventName], true);
    echo 'OK<br/>';
}
else {
    echo 'No data!<br/>';
}