<?php

/*
    EntryPoint ZaloApiTest
    Author: Hieu Nguyen
    Date: 2019-07-11
    Purpose: test calling Zalo API
*/

use Zalo\Zalo;
use Zalo\ZaloConfig;

class ZaloApiTest extends Vtiger_EntryPoint {

	function process (Vtiger_Request $request) {
        require_once('include/utils/ZaloUtils.php');
        $zalo = new Zalo();
        $oaId = '2197174064623873199';
        $accessToken = ZaloUtils::retrieveAccessToken($oaId);
        CPSocialIntegration_Config_Helper::isZaloMessageAllowed();

        // Get OA
        $params = [];
        $result = ZaloUtils::callZaloApi($zalo, 'GET', 'oa/getoa', $accessToken, $params);
        var_dump($result);
        
        // Get Articles
        $params = [
            'offset' => 0,
            'limit' => 10,
            'type' => 'normal'
        ];
        $result = ZaloUtils::callZaloApi($zalo, 'GET', 'article/getslice', $accessToken, $params);
        var_dump($result);

        // Get Followers: this api returns user id only
        $params = [
            'data' => json_encode([
                'offset' => 0,
                'count' => 50
            ])
        ];
        $result = ZaloUtils::callZaloApi($zalo, 'GET', 'oa/getfollowers', $accessToken, $params);
        var_dump($result);

        // Get Follower info: this api does not return phone number or email
        $params = [
            'data' => json_encode([
                'user_id' => '0984147940'
            ])
        ];
        $result = ZaloUtils::callZaloApi($zalo, 'GET', 'oa/getprofile', $accessToken, $params);
        var_dump($result);

        // Send request share contact info    
        $params = '{
            "recipient": {
                "user_id": "7827795384733453666"
            },
            "message": {
                "text": "hello, world!",
                "attachment": {
                    "type": "template",
                    "payload": {
                        "template_type": "request_user_info",
                        "elements": [{
                            "title": "OA chatbot (Testing)",
                            "subtitle": "Đang yêu cầu thông tin từ bạn",
                            "image_url": "https://developers.zalo.me/web/static/zalo.png"
                        }]
                    }
                }
            }
        }';
        $params = json_decode($params, true);
        $result = ZaloUtils::callZaloApi($zalo, 'POST', 'oa/message', $accessToken, $params);
        var_dump($result);
	}
}