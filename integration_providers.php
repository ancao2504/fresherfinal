<?php

/**
 * Name: integration_providers.php
 * Author: Phu Vo
 * Date: 2021.04.27
 */

// Modifed by Vu Mai on 2023-03-10 to change wrong support link
$providers = [
	'ai_camera' => [
		'HanetAICamera' => [
			'display_name' => 'Hanet AI Camera',
			'logo_path' => 'modules/CPAICameraIntegration/resources/images/hanet.png',
			'intro_en' => 'More than 10 years in electronics field, pioneer provider in timekeeping solution using AI Camera',
			'intro_vn' => 'Hơn 10 năm kinh nghiệm trong lĩnh vực điện tử, là đơn vị tiên phong cung cấp giải pháp chấm công bằng Camera AI',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/ai-camera/huong-dan-dau-noi/hanet-ai',
		],
		'CMCCloudCamera' => [
			'display_name' => 'CMC Cloud Camera',
			'logo_path' => 'modules/CPAICameraIntegration/resources/images/cmc-cloud.png',
			'intro_en' => 'Provide monitoring, managing and central storage services for enterprise camera data based on cloud computing technology',
			'intro_vn' => 'Dịch vụ giám sát, quản lý và lưu trữ tập trung dữ liệu Camera của doanh nghiệp trên hạ tầng công nghệ Điện toán đám mây',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/ai-camera/huong-dan-dau-noi/cmc-cloud-camera',
		],
	],
	'mautic' => [
		'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/mautic/huong-dan-dau-noi'
	],
	'ott' => [
		'Zalo' => [
			'ZaloZNS' => [
				'display_name' => 'Zalo ZNS (Direct API)',
				'logo_path' => 'https://stc-oa.zdn.vn/resources/zcloud-landing/images/logo.svg',
				'intro_en' => 'https://zalo.cloud/zns<br/>Email: support@zalo.cloud',
				'intro_vn' => 'https://zalo.cloud/zns<br/>Email: support@zalo.cloud',
				'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/tin-nhan-ott/zalo-zns/directapi',
			],
			'ESMS' => [
				'display_name' => 'eSMS',
				'logo_path' => 'resources/images/vendors/esms.png',
				'intro_en' => 'www.esms.vn | Hotline: 0901 888 484<br/>Email: contact@esms.vn',
				'intro_vn' => 'www.esms.vn | Hotline: 0901 888 484<br/>Email: contact@esms.vn',
				'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/tin-nhan-ott/zalo-zns/esms',
			],
			'SouthTelecom' => [
				'display_name' => 'South Telecom',
				'logo_path' => 'resources/images/vendors/southtelecom.png',
				'intro_en' => 'www.worldsms.vn | Hotline: 1900 54 54 63<br/>Email: contact@southtelecom.vn',
				'intro_vn' => 'www.worldsms.vn | Hotline: 1900 54 54 63<br/>Email: contact@southtelecom.vn',
				'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/tin-nhan-ott/zalo-zns/southtelecom',
			],
		],
		'Viber' => [],
		'Telegram' => [],
	],
	// Added by Vu Mai 2022-07-14 for chatbot integration 
	'chatbot' => [
		'Hana' => [
			'display_name' => 'HANA.AI',
			'logo_path' => 'modules/CPChatBotIntegration/resources/images/logo-hana.png',
			'intro_en' => 'www.hana.ai | Hotline: 028 999 29 129<br/>Email: hotro@hana.ai',
			'intro_vn' => 'www.hana.ai | Hotline: 028 999 29 129<br/>Email: hotro@hana.ai',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/chatbot/hana/huong-dan-dau-noi',
		],
		'BotBanHang' => [
			'display_name' => 'BOT BÁN HÀNG',
			'logo_path' => 'modules/CPChatBotIntegration/resources/images/logo-botbanhang.png',
			'intro_en' => 'www.botbanhang.vn | Hotline: 0934 666 997<br/>Email: hi@botbanhang.vn',
			'intro_vn' => 'www.botbanhang.vn | Hotline: 0934 666 997<br/>Email: hi@botbanhang.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/chatbot/bot-ban-hang/huong-dan-dau-noi',
		],
		'Tawk' => [
			'display_name' => 'Tawk',
			'logo_path' => 'modules/CPChatBotIntegration/resources/images/logo-tawkto.png',
			'intro_en' => 'www.tawk.to | Email: support@tawk.to <br/> Only Livechat feature',
			'intro_vn' => 'www.tawk.to | Email: support@tawk.to <br/> Chỉ có tính năng Livechat',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/chatbot/tawk-to/huong-dan-dau-noi',
		]
	],
	// End Vu Mai

	// Added by Vu Mai 2022-07-19 for callcenter integration
	'callcenter' => [
		'Stringee' => [
			'display_name' => 'STRINGEE',
			'logo_path' => 'resources/images/logo-stringee.png',
			'intro_en' => 'www.stringee.com | Hotline: 18006670<br/>Email: info@stringee.com',
			'intro_vn' => 'www.stringee.com | Hotline: 18006670<br/>Email: info@stringee.com',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/stringee',
		],
		'CMCTelecom' => [
			'display_name' => 'CMC TELECOM',
			'logo_path' => 'resources/images/logo-cmctelecom.png',
			'intro_en' => 'www.cmctelecom.vn | Hotline: 19002020<br/>Email: info@cmctelecom.vn',
			'intro_vn' => 'www.cmctelecom.vn | Hotline: 19002020<br/>Email: info@cmctelecom.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/cmc-telecom',
		],
		'SunOcean' => [
			'display_name' => 'SUNOCEAN',
			'logo_path' => 'resources/images/logo-sunocean.png',
			'intro_en' => 'www.sunocean.com.vn | Hotline: 028 35146 178<br/>Email: sales@sunocean.com.vn',
			'intro_vn' => 'www.sunocean.com.vn | Hotline: 028 35146 178<br/>Email: sales@sunocean.com.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/sunocean',
		],
		'MiTek' => [
			'display_name' => 'MITEK',
			'logo_path' => 'resources/images/logo-mitek.png',
			'intro_en' => 'www.mitek.vn | Hotline: 1900 1238<br/>Email: contact@mitek.vn',
			'intro_vn' => 'www.mitek.vn | Hotline: 1900 1238<br/>Email: contact@mitek.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/mitek',
		],
		'VoIP24H' => [
			'display_name' => 'VOIP24H',
			'logo_path' => 'resources/images/logo-voip24h.png',
			'intro_en' => 'www.voip24h.vn | Hotline: 19002002<br/>Email: sales.hcm@voip24h.vn',
			'intro_vn' => 'www.voip24h.vn | Hotline: 19002002<br/>Email: sales.hcm@voip24h.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/voip24h',
		],
		'SouthTelecom' => [
			'display_name' => 'SOUTH TELECOM',
			'logo_path' => 'resources/images/logo-southtelecom.png',
			'intro_en' => 'www.southtelecom.vn |  Hotline: 1900 54 54 63<br/>Email: contact@southtelecom.vn',
			'intro_vn' => 'www.southtelecom.vn |  Hotline: 1900 54 54 63<br/>Email: contact@southtelecom.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/south-telecom',
		],
		'Abenla' => [
			'display_name' => 'ABENLA',
			'logo_path' => 'resources/images/logo-abenla.png',
			'intro_en' => 'www.abenla.com | Hotline: 028 7308 8669<br/>Email: info@abenla.com',
			'intro_vn' => 'www.abenla.com | Hotline: 028 7308 8669<br/>Email: info@abenla.com',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/abenla',
		],
		'CloudFone' => [
			'display_name' => 'CLOUDFONE',
			'logo_path' => 'resources/images/logo-cloudfone.png',
			'intro_en' => 'www.ods.vn | Hotline: 028 7300 7788<br/>Email: info@ods.vn',
			'intro_vn' => 'www.ods.vn | Hotline: 028 7300 7788<br/>Email: info@ods.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/cloudfone',
		],
		'OmiCall' => [
			'display_name' => 'OMICALL',
			'logo_path' => 'resources/images/logo-cloudfone.png',
			'intro_en' => 'www.omicall.com | Hotline: 028 7101 0898<br/>Email: contact@omicall.vn',
			'intro_vn' => 'www.omicall.com | Hotline: 028 7101 0898<br/>Email: contact@omicall.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/omicall',
		],
		'VoiceCloud' => [
			'display_name' => 'VOICE CLOUD',
			'logo_path' => 'resources/images/logo-voicecloud.png',
			'intro_en' => 'www.voicecloud.vn | Hotline: 1900 2028<br/>Email: info@voicecloud.vn',
			'intro_vn' => 'www.voicecloud.vn | Hotline: 1900 2028<br/>Email: info@voicecloud.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/voicecloud',
		],
		'Tel4VN' => [
			'display_name' => 'TEL4VN',
			'logo_path' => 'resources/images/logo-tel4vn.jpeg',
			'intro_en' => 'www.tel4vn.edu.vn | Hotline: 028 3622 0868<br/>Email: support@tel4vn.com',
			'intro_vn' => 'www.tel4vn.edu.vn | Hotline: 028 3622 0868<br/>Email: support@tel4vn.com',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/tel4vn',
		],
		'YeaStar' => [
			'display_name' => 'YEASTAR',
			'logo_path' => 'resources/images/logo-yeastar.png',
			'intro_en' => 'www.yeastar.vn | Hotline: 1900 6069<br/>Email: support@duhung.vn',
			'intro_vn' => 'www.yeastar.vn | Hotline: 1900 6069<br/>Email: support@duhung.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/yeastar',
		],
		'GrandStream' => [
			'display_name' => 'GRANDSTREAM',
			'logo_path' => 'resources/images/logo-grandstream.png',
			'intro_en' => 'www.tongdai.com.vn | Hotline: 19006050<br/>Email: thienan@tongdai.com.vn',
			'intro_vn' => 'www.tongdai.com.vn | Hotline: 19006050<br/>Email: thienan@tongdai.com.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/grand-stream',
		],
		'FreePBX' => [
			'display_name' => 'FREE PBX',
			'logo_path' => 'resources/images/logo-freepbx.png',
			'intro_en' => 'Sagoma switchboard and switchboards using the FreePBX core can connect via this method',
			'intro_vn' => 'Tổng đài Sagoma và các tổng đài sử dụng core FreePBX thì kết nối qua phương thức này',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/freepbx',
		],
		'FPTTelecom' => [
			'display_name' => 'FPT ONCALL',
			'logo_path' => 'resources/images/logo-fptoncall.png',
			'intro_en' => 'www.oncall.vn | Hotline: 1800 6973<br/>Email: kinhdoanh.oncall@fpt.com.vn',
			'intro_vn' => 'www.oncall.vn | Hotline: 1800 6973<br/>Email: kinhdoanh.oncall@fpt.com.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/fpt-oncall',
		],
		'BaseBS' => [
			'display_name' => 'BASEBS',
			'logo_path' => 'resources/images/logo-basebs.png',
			'intro_en' => 'www.basebs.com | Hotline: 1900 633 568<br/>Email: info@basebs.com',
			'intro_vn' => 'www.basebs.com | Hotline: 1900 633 568<br/>Email: info@basebs.com',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/basebs',
		],
		'VCS' => [
			'display_name' => 'vUC',
			'logo_path' => 'resources/images/logo-vngcloud.png',
			'intro_en' => 'www.vngcloud.vn | Hotline: 1900 555 526<br/>Email: sales@vngcloud.vn',
			'intro_vn' => 'www.vngcloud.vn | Hotline: 1900 555 526<br/>Email: sales@vngcloud.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/vcs',
		],
		'NTTCloudPBX' => [
			'display_name' => 'CLOUDPBX ',
			'logo_path' => 'resources/images/logo-cloudpbx.png',
			'intro_en' => 'www.cloudpbx.vn | Hotline: 1900 6020<br/>Email: hello@cloudpbx.vn',
			'intro_vn' => 'www.cloudpbx.vn | Hotline: 1900 6020<br/>Email: hello@cloudpbx.vn',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi',
		],
		'Xorcom' => [
			'display_name' => 'XORCOM ',
			'logo_path' => 'resources/images/logo-xorcom.png',
			'intro_en' => 'www.xorcom.com<br/>Kết nối tổng đài vật lý của hãng Xorcom',
			'intro_vn' => 'www.xorcom.com<br/>Kết nối tổng đài vật lý của hãng Xorcom',
			'guide_url' => 'https://docs.onlinecrm.vn/tich-hop/call-center/huong-dan-dau-noi/xorcom',
		],
		// Added by Vu Mai on 2023-03-16 to add CloudCall provider
		'CloudCALL' => [
			'display_name' => 'CloudCALL',
			'logo_path' => 'resources/images/logo-CloudCALL.png',
			'intro_en' => 'www.cloudgo.vn | Hotline: 1900 29 29 90<br/>Email: support@cloudgo.vn',
			'intro_vn' => 'www.cloudgo.vn | Hotline: 1900 29 29 90<br/>Email: support@cloudgo.vn',
			'guide_url' => 'https://docs.cloudgo.vn/crm-tieu-chuan/tich-hop/tong-dai-dien-thoai/huong-dan-dau-noi/cloudcall',
		],
	],
	// End Vu Mai
];