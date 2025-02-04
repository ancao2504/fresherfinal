<?php
    $servicePath = $_SERVER['PHP_SELF'];
    $scheme = (isset($_SERVER['HTTPS']) || ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) && $visitor->scheme == 'https') ? 'https' : 'http';
    $endPoint = $scheme .'://'. $_SERVER['HTTP_HOST'] . $servicePath;
    $endPoint = str_replace('Test', '', $endPoint);

    require_once('../vtlib/Vtiger/Functions.php');
    echo '<center>Encryped password for "123456" is ' . Vtiger_Functions::generateEncryptedPassword('123456') . '</center>';
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
                Login: 
                {
                    RequestAction: 'Login',
                    IsOpenId: 0,
                    Credentials: {
                        api_key: '',
                        email: 'it.hieund@gmail.com',
                        username: 'it.hieund@gmail.com',
                        password: '123456'
                    },
                    comment: 'IsOpenId: 0, Credentials: {username: "", password: ""} or IsOpenId: 1, Credentials: {api_key: "", email: ""}'
                },
                Logout: {
                    RequestAction: 'Logout'
                },
                ResetPassword: {
                    RequestAction: 'ResetPassword',
                    Params: {
                        username: 'it.hieund@gmail.com'
                    }
                },
                GetProfile: {
                    RequestAction: 'GetProfile'
                },
                SaveProfile: {
                    RequestAction: 'SaveProfile',
                    Data: {
                        firstname: 'Hieu',
                        lastname: 'Nguyen',
                        mobile: '0987654321',
                        email: 'it.hieund@gmail.com',
                        mailingstreet: '123 Phan Văn Trị',
                    }
                },
                ChangePassword: {
                    RequestAction: 'ChangePassword',
                    Params: {
                        new_password: 'crm123'
                    }
                },
                GetModuleMetadata: {
                    RequestAction: 'GetModuleMetadata',
                    Params: {
                        module: 'Contacts',
                    }
                },
                GetFreeCallToken: {
                    RequestAction: 'GetFreeCallToken',
                },
                GetNotificationList: {
                    RequestAction: 'GetNotificationList',
                    Params: {
                        paging: {
                            offset: 0
                        }
                    }
                },
                MarkNotificationsAsRead: {
                    RequestAction: 'MarkNotificationsAsRead',
                    Params: {
                        target: 1,
                    },
                    comment: 'target là id -> update 1 record. target là "all" -> update tất cả record'
                },
                GetCounters: {
                    RequestAction: 'GetCounters'
                },
                SavePushClientToken: {
                    RequestAction: 'SavePushClientToken',
                    Params: {
                        token: ''
                    }
                },
                RemovePushClientToken: {
                    RequestAction: 'RemovePushClientToken',
                    Params: {
                        token: ''
                    }
                },
                GetTicketList: {
                    RequestAction: 'GetTicketList',
                    Params: {
                        keyword: '',
                        filters: {
                            category: ''
                        },
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetTicket: {
                    RequestAction: 'GetTicket',
                    Params: {
                        id: 104,
                    }
                },
                SaveTicket: {
                    RequestAction: 'SaveTicket',
                    Data: {
                        id: 104,
                        ticket_title: 'Test ticket',
                        ticketstatus: 'Open',
                        ticketcategories: 'Big Problem',
                        description: 'Test ticket',
                        assigned_user_id: 'Users:211,Users:1'
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
                        $('li.token').hide();
                    }
                    else {
                        $('li.api_key').hide();
                        $('li.token').show();
                    }

                    $('#txt_parameters').val(JSON.stringify(data, null, 4));
                    $('#div_response').html('');
                });

                $('#slc_api').trigger('change');

                // Call API and show the result
                $('#btn_call').click(function() {
                    var token = $('span.token').text();
                    var api = $('#slc_api option:selected').val();

                    if(api == '') {
                        alert('Please select an API!');
                        return;
                    }

                    try {
                        var data = JSON.parse($('#txt_parameters').val());
                        callAPI(api, data, token);
                    }
                    catch(err) {
                        alert(err);
                    }
                });
            });

            function callAPI(api, parameters, token){
                $('#div_response').html('');
                $('#loading').show();

                $.ajax({
                    type: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('Client', client);   // Web or Mobile
                        xhr.setRequestHeader('Token', token);
                        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
                    },
                    url: enpointUrl,
                    data: parameters,
                    dataType: 'json',
                    success: function (response) {
                        $('#loading').hide();

                        if(api == 'Login'){
                            $('span.token').text(response.token);
                        }
                        else if(api == 'Logout'){
                            $('span.token').text('');
                        }

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
                            <li class="token">token: <span class="token"></span></li>
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