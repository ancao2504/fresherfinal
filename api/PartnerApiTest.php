<?php
    $servicePath = $_SERVER['PHP_SELF'];
    $scheme = (isset($_SERVER['HTTPS']) || ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) && $visitor->scheme == 'https') ? 'https' : 'http';
    $endPoint = $scheme .'://'. $_SERVER['HTTP_HOST'] . $servicePath;
    $endPoint = str_replace('Test', '', $endPoint);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>API Explorer</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../libraries/jquery/select2/select2.css" />
        <script type="text/javascript" src="../layouts/v7/lib/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="../libraries/jquery/select2/select2.min.js"></script>
        <script type="text/javascript">
            var enpointUrl = '<?php echo $endPoint ?>';
            var client = '<?php echo $_REQUEST['client'] ?? 'Web' ?>';

            let apis = {
                GetCategoryList: {
                    RequestAction: 'GetCategoryList',
                },
                GetProductList: {
                    RequestAction: 'GetProductList',
                },
                CreateOrder: {
                    'RequestAction': 'CreateOrder',
                    'Data': {
                        'source': 'BotBanHang',
                        'customer': {
                            'bot_id': '103151158195408',
                            'id': '4544033505636838',
                            'firstname': 'Hiếu',
                            'lastname': 'Nguyễn',
                            'full_name': 'Nguyễn Hiếu',
                            'mobile': '0984147940',
                            'email': 'hieu.nguyen@onlinecrm.vn',
                            'avatar': 'http://...',
                            'mailingstreet': '124 Phăn Văn Trị',
                            'mailingcity': 'Gò Vấp',
                            'mailingstate': 'Hồ Chí Minh City',
                            'mailingcountry': 'Việt Nam',
                            'company': 'Công ty TNHH Phần Mềm Quản Lý Khách Hàng Việt Nam'
                        },
                        'shipping': {
                            'ship_street': '123 Phan Đình Phùng',
                            'ship_city': 'Phú Nhuận',
                            'ship_state': 'Hồ Chí Minh City',
                            'ship_country': 'Việt Nam',
                            'receiver_name': 'Chị Thanh',
                            'receiver_phone': '0869955191'
                        },
                        'billing': {
                            'issue_invoice': 1,
                            'bill_street': '123 Phan Đình Phùng',
                            'bill_city': 'Phú Nhuận',
                            'bill_state': 'Hồ Chí Minh City',
                            'bill_country': 'Việt Nam'
                        },
                        'note': 'test',
                        'items': [
                            {
                                'productid': '1',
                                'product_no': 'PRO1',
                                'productname': 'iPhone X Max',
                                'purchase_price': 260000000,
                                'quantity': 1,
                                'price': 25000000
                            },
                            {
                                'productid': '1',
                                'product_no': 'PRO2',
                                'productname': 'Apple Watch Series 5',
                                'purchase_price': 14000000,
                                'quantity': 1,
                                'price': 15000000
                            }
                        ],
                        'sub_total': 40000000,
                        'discount_percent': 0,
                        'discount_amount': 0,
                        'tax_percent': 10,
                        'tax_amount': 4000000,
                        'grand_total': 44000000
                    }
                },
            };

            $(function() {
                // Display api options
                var apiOptions = '';

                Object.keys(apis).forEach((name) => {
                    apiOptions += '<option value="'+ name +'">'+ name +'</option>';
                });

                $('#slc_api').append(apiOptions);
                $('#slc_api').select2();

                // Display param when an api is selected
                $('#slc_api').change(function() {
                    var api = $('#slc_api option:selected').val();
                    var data = apis[api];

                    // Display header
                    if(api == 'Login') {
                        $('li.api_key').show();
                        $('li.accessKey').hide();
                    }
                    else {
                        $('li.api_key').hide();
                        $('li.accessKey').show();
                    }

                    $('#txt_parameters').val(JSON.stringify(data, null, 4));
                    $('#div_response').html('');
                });

                $('#slc_api').trigger('change');

                // Call API and show the result
                $('#btn_call').click(function() {
                    var accessKey = $('#txt_access_key').val();
                    var api = $('#slc_api option:selected').val();

                    if(api == '') {
                        alert('Please select an API!');
                        return;
                    }

                    try {
                        var data = JSON.parse($('#txt_parameters').val());
                        callAPI(api, data, accessKey);
                    }
                    catch(err) {
                        alert(err);
                    }
                });
            });

            function callAPI(api, parameters, accessKey){
                $('#div_response').html('');
                $('#loading').show();

                $.ajax({
                    type: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('Access-Key', accessKey);
                        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
                    },
                    url: enpointUrl,
                    data: parameters,
                    dataType: 'json',
                    success: function (response) {
                        $('#loading').hide();

                        var response = '<pre>' + JSON.stringify(response, null, 4) +'</pre>';
                        $('#div_response').html('Status Code: 200' + '<br/>' + response);

                    },
                    error: function(xhr){
                        $('#loading').hide();

                        var response = '<pre>' + xhr.responseText +'</pre>';
                        $('#div_response').html('Status Code: ' + xhr.status + '<br/>' + response);
                    }
                });
            }
        </script>

        <style type="text/css">
            .main{
                text-align: center;
                padding: 10px;
                background: #f4f4f4;
                border-radius: 5px;
                margin-left: 10%;
                width: 80%;
            }

            #slc_api{
                height: 30px;
                border: #b5b3b3 solid 1px;
                border-radius: 5px;
            }

            #btn_call{
                background: #e6e6e6;
                padding: 10px;
                margin-top: 10px;
                border-radius: 5px;
                box-shadow: none;
            }

            #btn_call:focus{
                outline:none;
            }

            #div_request, #div_response{
            }

            .main_contents{
                text-align: left;
                border: 1px solid #9a9a9a;
                padding: 10px;
                margin: 10px;
            }

            .main_contents_header{
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 15px;
            }

            input#txt_access_key {
                width: 300px;
                height: 20px;
            }

            div#div_parameters {
                overflow: scroll;
            }

            textarea#txt_parameters {
                width: 99.5%;
                height: 250px;
            }
        </style>
    </head>
    <body>
        <div class="main">
            <div>
                <select name="slc_api" id="slc_api" style="width: 200px"></select>
                </br>
                <input type="button" class="button primary" id="btn_call" value="Call">
            </div>
            <div class="main_contents">
                <div class="main_contents_header">Request</div>
                <hr/>
                <div id="div_request">
                    <p><strong>Endpoint</strong>: <?php echo $endPoint ?> (POST)</p>
                    <p><strong>Header</strong>: 
                        <ul>
                            <li>Access-Key: <input id="txt_access_key" autocomplete="off"></input></li>
                        </ul>
                    </p>
                    <p><strong>Body</strong>: 
                        <textarea cols="10" rows="10" id="txt_parameters" style="width:100%"></textarea>
                    </p>
                </div>
            </div>
            <div class="main_contents">
                <div class="main_contents_header">Response</div>
                <hr>
                <div id="loading" style="display: none;"><img src="../resources/images/fb_loading.gif"></div>
                <div id="div_response"></div>
            </div>
        </div>
    </body>
</html>