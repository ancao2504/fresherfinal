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

            let apis = {
                Login: 
                {
                    RequestAction: 'Login',
                    IsOpenId: 0,
                    Credentials: {
                        api_key: '',
                        email: '',
                        username: '',
                        password: ''
                    },
                    comment: 'IsOpenId: 0, Credentials: {username: "", password: ""} or IsOpenId: 1, Credentials: {api_key: "", email: ""}'
                },
                Logout: {
                    RequestAction: 'Logout'
                },
                ResetPassword: {
                    RequestAction: 'ResetPassword',
                    Params: {
                        username: 'salesman2',
                        email: 'hieu.nguyen@onlinecrm.vn'
                    }
                },
                GetProfile: {
                    RequestAction: 'GetProfile'
                },
                GetMetadata: {
                    RequestAction: 'GetMetadata',
                },
                SaveProfile: {
                    RequestAction: 'SaveProfile',
                    Data: {
                        first_name: 'Hieu',
                        last_name: 'Nguyen',
                        phone_home: '123456789',
                        phone_mobile: '0987654321',
                        email1: 'it.hieund@gmail.com',
                        language: 'vn_vn',
                        home_screen_config: {
                            performance: {
                                new_lead: '1',
                                sales: '1',
                                deal_won: '1',
                                deal_size: '1',
                                conversion_rate: '1',
                                filter_by: 'all'
                            },
                            incoming_activity: '1',
                            ticket_open: {
                                is_show: '1',
                                priority: 'DESC',
                                create_time: 'DESC',
                                filter_by: 'all',
                            }
                        }
                    }
                },
                ChangePassword: {
                    RequestAction: 'ChangePassword',
                    Params: {
                        new_password: 'crm123'
                    }
                },
                SaveStar: {
                    RequestAction: 'SaveStar',
                    Params: {
                        module: 'Contacts',
                        id: 2,
                        starred: 1
                    }
                },
                CheckinCustomer: {
                    RequestAction: 'CheckinCustomer',
                    Params: {
                        qr_code: 'bb6c24cbc99450cf3141b72d0b7e7968',
                    }
                },
                DeleteRecord: {
                    RequestAction: 'DeleteRecord',
                    Params: {
                        module: 'HelpDesk',
                        id: 168,
                    }
                },
                GetModuleMetadata: {
                    RequestAction: 'GetModuleMetadata',
                    Params: {
                        module: 'Contacts',
                    }
                },
                GetQuoteList: {
                    RequestAction: 'GetQuoteList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetLeadList: {
                    RequestAction: 'GetLeadList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetLead: {
                    RequestAction: 'GetLead',
                    Params: {
                        id: 104,
                    }
                },
                SaveLead: {
                    RequestAction: 'SaveLead',
                    Data: {
                        firstname: 'Hieu',
                        lastname: 'Nguyen',
                        mobile: '0987654321',
                        email: 'abc@gmail.com',
                        assigned_user_id: 'Users:211,Users:1'
                    }
                },
                GetDocumentList: {
                    RequestAction: 'GetDocumentList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetDocument: {
                    RequestAction: 'GetDocument',
                    Params: {
                        id: 104,
                    }
                },
                GetAccountList: {
                    RequestAction: 'GetAccountList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetAccount: {
                    RequestAction: 'GetAccount',
                    Params: {
                        id: 104,
                    }
                },
                GetCustomerByCode: {
                    RequestAction: 'GetCustomerByCode',
                    Params: {
                        contact_no: ''
                    }
                },
                SaveAccount: {
                    RequestAction: 'SaveAccount',
                    Data: {
                        accountname: 'OnlineCRM',
                        phone: '123456789',
                        email1: 'abc@gmail.com',
                        assigned_user_id: 'Users:211,Users:1'
                    }
                },
                GetContactList: {
                    RequestAction: 'GetContactList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetContact: {
                    RequestAction: 'GetContact',
                    Params: {
                        id: 104,
                    }
                },
                SaveContact: {
                    RequestAction: 'SaveContact',
                    Data: {
                        firstname: 'Hieu',
                        lastname: 'Nguyen',
                        mobile: '0987654321',
                        email: 'abc@gmail.com',
                        assigned_user_id: 'Users:211,Users:1'
                    }
                },
                SyncContacts: {
                    RequestAction: 'SyncContacts',
                    Data: {
                        direction: '',
                        local_contacts: [
                            {
                                firstname: '',
                                lastname: '',
                                phone: '',
                                mobile: '',
                                homephone: '',
                                otherphone: '',
                                email: '',
                                secondaryemail: '',
                            }
                        ]
                    }
                },
                ImportContacts: {
                    RequestAction: 'ImportContacts',
                    Data: {
                        local_contacts: [
                            {
                                firstname: '',
                                lastname: '',
                                phone: '',
                                mobile: '',
                                homephone: '',
                                otherphone: '',
                                email: '',
                                secondaryemail: '',
                                'account_name': '',
                            }
                        ]
                    }
                },
                GetOpportunityList: {
                    RequestAction: 'GetOpportunityList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetOpportunity: {
                    RequestAction: 'GetOpportunity',
                    Params: {
                        id: 104,
                    }
                },
                SaveOpportunity: {
                    RequestAction: 'SaveOpportunity',
                    Data: {
                        potentialname: 'Cơ bội bán 100tr',
                        amount: 10000000,
                        sales_stage: 'Qualification',
                        closingdate: '2018-11-31',
                        assigned_user_id: 'Users:211,Users:1'
                    }
                },
                GetTicketList: {
                    RequestAction: 'GetTicketList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        filters: {
                            category: ''
                        },
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        },
                        ordering: {
                            createdtime: 'DESC',
                            priority: 'DESC',
                        },
                        filter_by: 'all'
                    }
                },
                GetOpenTickets: {
                    RequestAction: 'GetOpenTickets',
                    Params: {
                        keyword: '',
                        filters: {
                            category: ''
                        },
                        ordering: {
                            createdtime: 'DESC',
                            priority: 'DESC',
                        },
                        filter_by: 'all'
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
                        contact_id: 2,
                        starred: 1,
                        assigned_user_id: 'Users:211,Users:1'
                    }
                },
                GetActivityList: {
                    RequestAction: 'GetActivityList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        },
                        filter: 'incoming',
                    }
                },
                GetCalendarEventDates: {
                    RequestAction: 'GetCalendarEventDates',
                    Params: {
                        selected_month: '2018-11',
                    }
                },
                GetCalendarActivityList: {
                    RequestAction: 'GetCalendarActivityList',
                    Params: {
                        selected_date: '2018-11-08',
                        view: 'MyCalendar',
                    }
                },
                GetCalendarSettings: {
                    RequestAction: 'GetCalendarSettings',
                },
                SaveCalendarSettings: {
                    RequestAction: 'SaveCalendarSettings',
                    Data: {
                        hidecompletedevents: 1,
                        shared_calendar_activity_types: ["Call", "Meeting"],
                        calendar_feeds: [
                            {
                                id: 1,
                                color: '#207bad',
                                visible: 1,
                            },
                            {
                                id: 5,
                                color: '#33c86d',
                                visible: 1,
                            },
                        ]
                    }
                },
                GetActivity: {
                    RequestAction: 'GetActivity',
                    Params: {
                        id: 311,
                    }
                },
                SaveActivity: {
                    RequestAction: 'SaveActivity',
                    Data: {
                        subject: 'Test',
                        date_start: '2020-12-29',
                        time_start: '10:00:00',
                        due_date: '2020-12-29',
                        time_end: '10:30:00',
                        assigned_user_id: 'Users:211,Users:1',
                        eventstatus: 'Planned',
                        activitytype: 'Meeting',
                        visibility: 'Public',
                        reminder_time: '1800'
                    }
                },
                GetFaqList: {
                    RequestAction: 'GetFaqList',
                    Params: {
                        cv_id: '',
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
                GetFaq: {
                    RequestAction: 'GetFaq',
                    Params: {
                        id: 312,
                    }
                },
                GetContractList: {
                    RequestAction: 'GetContractList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetContract: {
                    RequestAction: 'GetContract',
                    Params: {
                        id: 322,
                    }
                },
                SaveContract: {
                    RequestAction: 'SaveContract',
                    Data: {
                        subject: '',
                        sc_related_to: '',
                        contract_status: '',
                        contract_type: '',
                        start_date: '',
                        due_date: '',
                        contract_priority: '',
                        tracking_unit: '',
                        total_units: '',
                        used_units: '',
                        assigned_user_id: 'Users:211,Users:1'
                    }
                },
                GetSalesOrderList: {
                    RequestAction: 'GetSalesOrderList',
                    Params: {
                        cv_id: '',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetSalesOrder: {
                    RequestAction: 'GetSalesOrder',
                    Params: {
                        id: 323,
                    }
                },
                GetDataForSalesOrder: {
                    RequestAction: 'GetDataForSalesOrder',
                },
                SaveSalesOrder: {
                    RequestAction: 'SaveSalesOrder',
                    Data: {
                        subject: 'Test',
                        potential_id: '',
                        customerno: '',
                        quote_id: '',
                        vtiger_purchaseorder: '',
                        duedate: '',
                        carrier: '',
                        pending: '',
                        sostatus: 'Created',
                        salescommission: '',
                        exciseduty: '',
                        account_id: '',
                        bill_street: '',
                        bill_city: '',
                        bill_state: '',
                        bill_country: '',
                        ship_street: '',
                        ship_city: '',
                        ship_state: '',
                        ship_country: '',
                        terms_conditions: '',
                        description: '',
                        region_id: 0,
                        currency_id: 1,
                        taxtype: '',
                        discount_percent: '',
                        discount_amount: '',
                        txtAdjustment: '',
                        tax1: '',
                        tax2: '',
                        tax3: '',
                        charge1: '',
                        charge1_tax1: '',
                        charge1_tax2: '',
                        charge1_tax3: '',
                        adjustment: '',
                        subtotal: '',
                        pre_tax_total: '',
                        total: '',
                        assigned_user_id: 'Users:211,Users:1',
                        product_list: [
                            {
                                productid: 82,
                                sequence_no: 1,
                                section_num: 1,
                                quantity: '',
                                listprice: '',
                                comment: '',
                                discount_percent: '',
                                discount_amount: '',
                                comment: '',
                                tax1: '',
                                tax2: '',
                                tax3: '',
                            }
                        ],
                        service_list: [
                            {
                                productid: 86,
                                sequence_no: 2,
                                section_num: 1,
                                quantity: '',
                                listprice: '',
                                discount_percent: '',
                                discount_amount: '',
                                comment: '',
                                tax1: '',
                                tax2: '',
                                tax3: '',
                            }
                        ]
                    }
                },
                GetProductList: {
                    RequestAction: 'GetProductList',
                    Params: {
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetServiceList: {
                    RequestAction: 'GetServiceList',
                    Params: {
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetCommentList: {
                    RequestAction: 'GetCommentList',
                    Params: {
                        module: '',
                        record_related_id: ''
                    }
                },
                SaveComment: {
                    RequestAction: 'SaveComment',
                    Params: {
                        commentcontent: '',
                        related_to: '',
                        parent_comments: '',
                        reasontoedit: '',
                    }
                },
                GetNotificationList: {
                    RequestAction: 'GetNotificationList',
                    Params: {
                        type: 'notify',
                        sub_type: 'update',
                        paging: {
                            offset: 0
                        }
                    },
                    comment: 'type: notify/activity/birthday. sub_type: notify -> update/checkin, activity -> coming/overdue, birthday -> today/coming'
                },
                MarkNotificationsAsRead: {
                    RequestAction: 'MarkNotificationsAsRead',
                    Params: {
                        target: 1,
                    },
                    comment: 'target là id -> update 1 record. Khi target là "update" thì sẽ update tất cả record trong group Updates, còn khi target là "checkin" thì sẽ update tất cả record trong group Check-ins'
                },
                GetReportList: {
                    RequestAction: 'GetReportList',
                    Params: {
                        folder_id: 'All',
                        keyword: '',
                        paging: {
                            order_by: '',
                            offset: 0,
                            max_results: 5
                        }
                    }
                },
                GetCounters: {
                    RequestAction: 'GetCounters'
                },
                GetCallCenterInfo: {
                    RequestAction: 'GetCallCenterInfo'
                },
                GetDataForChart: {
                    RequestAction: 'GetDataForChart'
                },
                GlobalSearch: {
                    RequestAction: 'GlobalSearch',
                    Params: {
                        keyword: ''
                    }
                },
                Checkin: {
                    RequestAction: 'Checkin',
                    Data: {
                        id: '',
                        latitude: '',
                        longitude: '',
                        address: '',
                        note: '',
                    }
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
                LoadSettings: {
                    RequestAction: 'LoadSettings'
                },
                SaveSettings: {
                    RequestAction: 'SaveSettings',
                    Data: {
                        notification_config: {
                            receive_notifications: '1',
                            receive_notifications_method: [
                                'popup',
                                'app'
                            ],
                            receive_assignment_notifications: '1',
                            receive_record_update_notifications: '1',
                            receive_following_record_update_notifications: '1',
                            show_activity_reminders: '1',
                            show_customer_birthday_reminders: '1'
                        }
                    }
                },
                GetDataForCallLog: {
                    RequestAction: 'GetDataForCallLog',
                    Data: {
                        customer_id: '',
                        customer_number: '',
                        direction: '',
                    },
                },
                WriteOutboundCache: {
                    RequestAction: 'WriteOutboundCache',
                    Data: {
                        ext_number: '',
                        customer_id: '',
                        customer_number: '',
                        pbx_call_id: '',
                    },
                },
                SaveCallLog: {
                    RequestAction: 'SaveCallLog',
                    Data: {
                        pbx_call_id: '1',
                        subject: 'Thông tin tư vấn sản phẩm dịch vụ',
                        description: 'Hello World',
                        direction: 'INBOUND',
                        start_time: '2020-12-15 15:00:00',
                        end_time: '2020-12-15 15:25:00',
                        events_call_purpose: '',
                        events_call_purpose_other: '',
                        events_inbound_call_purpose: 'inbound_call_purpose_products_services_info_enquiry',
                        events_inbound_call_purpose_other: '',
                        events_call_result: 'call_result_customer_interested',
                        visibility: 'Public',
                        customer_id: '4',
                        call_back: {
                            call_back_time_other: 0,
                            date_start: '2020-12-15',
                            time_start: '08:00:00',
                            select_moment: 'next_morning',
                            select_time: '08:00'
                        },
                        customer_data: {
                            customer_id: '4',
                            customer_type: 'Contacts',
                            account_id: '3',
                            salutationtype: 'Ms.',
                            lastname: 'Hà Lan',
                            firstname: 'Bích',
                            mobile_phone: '054112114',
                            email: 'lan.bich@onlinecrm.vn',
                            product_ids: ['70', '71'],
                            service_ids: ['73', '75']
                        },
                    },
                },
                AcceptInvitation: {
                    RequestAction: 'AcceptInvitation',
                    Data: {
                        activity_id: '',
                    }
                },
                GetStatistic: {
                    RequestAction: 'GetStatistic',
                    Data: {
                        period: 'date',
                        filter_by: 'all',
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