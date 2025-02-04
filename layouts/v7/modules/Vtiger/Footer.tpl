{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
<!-- Modified By Kelvin Thang -- Date: 2018-06-27 -->
{strip}
{* Modified by Phu Vo on 2021.05.21 *}
<style>
	.app-footer {
		position: fixed;
		left: 0;
		bottom: 0;
		margin-bottom: 0;
		border-top: 1px solid #DDDDDD;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
        height: auto;
        background-color: #f6f8f8;
        width: 100%;
        margin: 0px;
        z-index: 10;
    }

    .app-footer [class^="col"] {
        padding: 0px;
    }

    .footer-links-container.right {
        display: flex;
        justify-content: flex-end;
    }

    .footer-links {
        list-style-type: none;
        padding-inline-start: 0px;
        display: flex;
        margin: 0px;
        font-size: 13px;
    }

    .footer-links a {
        color: #008ecf;
        cursor: pointer;
    }

    .footer-links a:hover {
        color: #008ecf;
        text-decoration: underline;
    }

    .footer-links a:focus {
        color: #008ecf;
    }

    .footer-links a:visited {
        color: #008ecf;
    }

    .footer-links .language-select button {
        border: none;
        background: transparent;
        display: inline-flex;
        justify-content: flex-start;
        align-items: flex-start;
    }

    .footer-links .language-select button img {
        margin-right: 4px;
        position: relative;
        top: 2px;
    }

    .footer-link {
        min-height: 48px;
        padding: .75em 1em;
        display: inline-flex;
        justify-content: flex-start;
        align-items: center;
    }

    .footer-link-gap {
        margin-left: 2px;
        margin-right: 2px;
    }

    .footer-links .language-select button i {
        margin: 2px 4px;
    }

    @media only screen and (max-width: 768px) {
        .information-section {
            display: none !important;
        }

        .footer-links-container {
            display: flex;
            justify-content: center;
        }

        .footer-links-container.right {
            justify-content: center;
        }

        .footer-link {
            padding: .2em 1.2em;
        }
    }
</style>
{/strip}

{assign var=LOGIN_PAGE_CONFIG value=vglobal('loginPageConfig')}
{assign var=LOGIN_CONFIG value=$LOGIN_PAGE_CONFIG['vn_vn']}

<footer class="app-footer {if $PIN_MENU == 'true'}fixed-menu{/if}"> <!-- [CustomMenu] Modified by Vu Mai on 2023-02-02 -->
    <div class="row">
        <div class="col-sm-6">
            <div class="footer-links-container">
                <ul class="footer-links">
                    {* Modified by Phu Vo on 2021.11.20 *}
                    {* Added by Hieu Nguyen on 2021-08-24 *}
                    <li class="footer-link">
                        <a id="btn-open-fb-chat" href="javascript:void(0)" title="{vtranslate('LBL_FB_CHAT_WIDGET_TOOLTIP', 'Vtiger')}" data-toggle="tooltip">
                            <img src="resources/images/messenger.png" style="width: 20px; height: 20px"/>
                        </a>
                        &nbsp;&nbsp;
                        <span>{vtranslate('LBL_BOT_DESCRIPTION', 'Vtiger')}</span>
                    </li>
                    {* End Hieu Nguyen *}
                        
                    </li>
                    {* End Phu Vo *}
                </ul>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="footer-links-container right">
                <ul class="footer-links">
                    <li class="footer-link credit">
                        <span>Copyright © CloudGO</span>	{* Modified by Vu Mai on 2023-03-03 *}
                        <span class="footer-link-gap">|</span>
                        <span>Hotline: <a class="hotline" href="tel:{$LOGIN_CONFIG.hotline|regex_replace:"/[-()\s]/":""}">{$LOGIN_CONFIG.hotline}</a></span>
                        {* <span> | </span>
                        <span class="dropdown dropup language-select">
                            <button class="dropdown-toggle" data-toggle="dropdown">
                                <img width="20" src="{$LOGIN_CONFIG.icon}" />
                                <span> {$LOGIN_CONFIG['language']} </span>
                                <i class="far fa-chevron-up" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {foreach from=$LOGIN_PAGE_CONFIG key=LANGUAGE item=CONFIG}
                                    <li>
                                        <a href="index.php?language={$LANGUAGE}">
                                            <img width="20" src="{$CONFIG.icon}" />
                                            <span> {$CONFIG['language']}</span>
                                        </a>
                                    </li>
                                {/foreach}
                            </ul>
                        </span> *}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

{* [FBChatWidget] Added by Hieu Nguyen on 2021-08-26 *}
<div id="fb-root"></div>
<div id="fb-customer-chat" class="fb-customerchat" page_id="294162338044491" attribution="biz_inbox"></div>

<style type="text/css">
    .fb_dialog.fb_dialog_advanced {
        display: none !important;
    }

    #fb-customer-chat iframe {
        left: -20px !important;
        bottom: 30px !important;
    }
</style>
<script src="{vresource_url('resources/FBChatWidgetHelper.js')}"></script>
{* End Hieu Nguyen *}

{* End Phu Vo *}
<div id='overlayPage'>
	<!-- arrow is added to point arrow to the clicked element (Ex:- TaskManagement), 
	any one can use this by adding "show" class to it -->
	<div class='arrow'></div>
	<div class='data'>
	</div>
</div>
<div id='helpPageOverlay'></div>
<div id="js_strings" class="hide noprint">{htmlentities(Zend_Json::encode($LANGUAGE_STRINGS))}</div> {* Modified by Hieu Nguyen on 2021-07-20 to support HTML tag inside JS strings *}
<div class="modal myModal fade"></div>
{include file='JSResources.tpl'|@vtemplate_path}

{* Added by Hieu Nguyen on 2018-10-02 *}
{if $CURRENT_USER_MODEL && $CURRENT_USER_MODEL->get('id')} {* [Core] Modified by Phu Vo on 2020.03.24 check current user before use it *}
    {include file="modules/PBXManager/tpls/CallPopup.tpl"}
    {include file="modules/CPSocialIntegration/tpls/SocialMessagePopup.tpl"}
    {include file="modules/CPSocialIntegration/tpls/SocialChatboxPopup.tpl"}
    {include file="modules/Vtiger/tpls/SurveyPopup.tpl"}

    {* Added by Hieu Nguyen on 2019-07-22 *}
	<div style="display: none">
        <!-- Small-Size Modal Template -->
        <div class="modal-dialog modal-sm modal-content modal-template-sm">
			{include file='ModalHeader.tpl'|vtemplate_path:$MODULE}
            <form class="form-horizontal" method="POST">
                <div class="modal-body margin10"></div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-success" type="submit">OK</button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                    </center>
                </div>
            </form>
		</div>

        <!-- Medium-Size Modal Template -->
		<div class="modal-dialog modal-md modal-content modal-template-md">
			{include file='ModalHeader.tpl'|vtemplate_path:$MODULE}
			<form class="form-horizontal" method="POST">
                <div class="modal-body margin10"></div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-success" type="submit">OK</button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                    </center>
                </div>
            </form>
		</div>

		{* Added by Phu vo on 2019.03.26 *}
		<!-- Large-Size Modal Template -->
		<div class="modal-dialog modal-lg modal-content modal-template-lg">
			{include file='ModalHeader.tpl'|vtemplate_path:$MODULE}
			<form class="form-horizontal" method="POST">
                <div class="modal-body margin10"></div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-success" type="submit">OK</button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                    </center>
                </div>
            </form>
		</div>
		{* End Phu Vo *}
        
		<!-- Extra-Large-Size Modal Template -->
		<div class="modal-dialog modal-xl modal-content modal-template-xl">
			{include file='ModalHeader.tpl'|vtemplate_path:$MODULE}
			<form class="form-horizontal" method="POST">
                <div class="modal-body margin10"></div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-success" type="submit">OK</button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                    </center>
                </div>
            </form>
		</div>
		{* End Phu Vo *}
	</div>
	{* End Hieu Nguyen *}
{/if}
{* End Hieu Nguyen *}
</body>

</html>
