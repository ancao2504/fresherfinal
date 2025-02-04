{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Users/views/Login.php *}

{*
	Name: Login.tpl
	Author: Phu Vo
	Date: 2021.03.03
*}

{strip}
	<style>
		/* Hide application footer since we will use separate footer for login screen */
		.app-footer {
			display: none !important;
		}

		/* Reset page spacing properties so our container will have fully access over screen */
		#page {
			padding: 0px !important;
			margin: 0px !important;
		}

		.to-left {
			text-align: left;
		}

		.to-right {
			text-align: right;
		}

		.to-center {
			text-align: center;
		}

		.login-container {
			min-height: 100vh;
			display: flex;
			background-image: url("resources/images/login-background.jpg?v=7.1.0.20200114");
			background-repeat: no-repeat;
			background-position-x: left;
			background-position-y: bottom;
			background-size: cover;
			padding: 0px;
			font-family: 'Segoe UI Bold', 'Segoe UI Regular', 'Segoe UI', sans-serif;
			font-size: 16px;
			color: #333;
		}

		.login-container input {
			height: auto !important;
		}

		.information-section,
		.login-form-section {
			height: 100%;
			padding: 0px;
			display: flex;
			flex-direction: column;
		}

		.information-section {
			background-color: transparent;
		}

		.login-form-section {
			background-color: white;
		}

		.login-content {
			flex: 1;
			margin: 0px;
		}

		.login-footer {
			height: auto;
			background-color: rgba(242, 242, 242, 0.46);
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			margin: 0px;
			padding: 0.2em 0px;
		}

		.login-footer [class^="col"] {
			padding: 0px;
		}

		.section-content {
			flex: 1;
			display: flex;
			justify-content: center;
			align-items: center;
			padding-bottom: 30px;
		}

		.main-title {
			font-weight: 700;
			color: white;
			font-size: 32px;
			text-align: center;
		}

		.main-title p {
			margin-bottom: 0.4em;
		}

		.spacing-container {
			flex: 1;
			max-width: 540px;
			padding: 0 50px;
		}

		.main-links {
			color: white;
			font-size: 20px;
			padding-inline-start: 0px;
			list-style-type: none;
			margin-bottom: 1em;
		}

		.main-links li {
			padding: 0.3em;
		}

		.main-links a::before {
			content: '\f00c';
			margin-right: 0.5em;
			font: normal normal normal 14px/1 FontAwesome;
		}

		.main-links a {
			color: white;
			font-style: italic;
			cursor: pointer;
		}

		.main-links a:hover {
			color: white;
			text-decoration: underline;
		}

		.main-links a:focus {
			color: white;
		}

		.main-links a:visited {
			color: white;
		}

		.main-banner {
			text-align: center;
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
			padding: .75em 1em;
		}

		.footer-link-gap {
			margin-left: 2px;
			margin-right: 2px;
		}

		.company-logo-container {
			text-align: center;
			/* margin-bottom: 10px; */
			padding: 10px;
		}

		.company-logo-container .company-logo {
			width: 100%;
			margin: 0px;
			height: 70px;
			text-align: center;
		}

		.company-logo-container .company-logo img {
			display: inline-block;
		}

		.login-form-container {
			margin: auto;
		}

		.login-form-container .group {
			margin-bottom: 0.6em;
		}

		.login-form-container label {
			font-weight: normal;
		}

		.login-form-container .remember-me {
			cursor: pointer;
			user-select: none;
		}

		.login-form-container .forgot-password {
			color: #008ECF;
		}

		.login-form-container input[type="text"],
		.login-form-container input[type="password"],
		.login-form-container input[type="email"],
		.login-form-container button[type="submit"] {
			width: 100%;
			border-radius: 5px;
			padding: 0.6em;
		}

		.login-form-container input[type="text"],
		.login-form-container input[type="password"],
		.login-form-container input[type="email"] {
			border: 1px solid #e2e2e2;
		}

		.login-form-container input[type="text"]:hover,
		.login-form-container input[type="password"]:hover,
		.login-form-container input[type="email"]:hover {
			border-color: #b2b2b2;
		}

		.login-form-container input[type="text"]:focus,
		.login-form-container input[type="password"]:focus,
		.login-form-container input[type="email"]:focus {
			border-color: #66bbe2;
			box-shadow: 1px 1px 5px #e6f6ee;
		}

		.login-form-container input[type="checkbox"] {
			width: 1.2em;
			height: 1.2em;
			vertical-align: bottom;
			margin: auto;
			border: 1px solid #e2e2e2;
			border-radius: 2px;
		}

		.login-form-container input[type="checkbox"]:hover {
			border-color: #b2b2b2;
		}

		.login-form-container input[type="checkbox"]:checked {
			border-color: #008ecf;
			background-color: #008ecf;
			color: #fff;
			display: inline-flex;
			justify-content: center;
			align-items: center;
		}

		.login-form-container input[type="checkbox"]:checked::after {
			top: unset;
			left: unset;
			font-family: FontAwesome;
			content: '\f00c';
		}

		.login-form-container button[type="submit"] {
			background-color: #008ecf;
			color: #fff;
			border: none;
			margin-top: 0.3em;
			margin-bottom: 0.3em;
		}

		.login-form-container button[type="submit"]:hover {
			background-color: #33a5d9;
		}

		.login-form-container button[type="submit"]:focus {
			background-color: #66bbe2;
		}

		.social-buttons {
			list-style-type: none;
			padding-inline-start: 0px;
			display: flex;
			justify-content: center;
		}

		.social-button {
			margin: 0px 0.7em;
		}

		.social-button a {
			height: 50px;
			width: 50px;
			display: flex;
			justify-content: center;
			align-items: center;
			box-sizing: border-box;
			padding: 0.1em;
			overflow: hidden;
			border-radius: 50%;
			position: relative;
			border: 1px solid #d7d7d7;
		}

		.social-button a>* {
			position: absolute;
			max-height: 70%;
			max-width: 70%;
		}

		.footer-links-container.right {
			display: flex;
			justify-content: flex-end;
		}

		.credit .hotline {
			font-weight: bold;
		}

		.message-container {
			margin-bottom: 0.6em;
		}

		.login-container .message {
			width: 100%;
			border-radius: 5px;
			box-sizing: border-box;
		}

		.login-container .message.failure-message {
			color: #de425b;
			background-color: #f9e7e7;
			padding: 0.4em 1em;
		}

		.login-container .message.warning-message {
			color: #ffbf00;
			background-color: #fff7dc;
			padding: 0.4em 1em;
		}

		.login-container .g-recaptcha {
			display: flex;
			justify-content: center;
		}

		.social-buttons-container .contact-us {
			margin-top: 1.4em;
			margin-bottom: 0.6em;
		}

		.forgot-title-container>* {
			margin-top: 0;
			margin-bottom: 0.8em;
			font-size: 24px;
		}

		.login-form-container .button {
			color: #fff;
			border: none;
			margin-top: 0.3em;
			margin-bottom: 0.3em;
			display: block;
			border-radius: 5px;
			padding: 0.6em;
			text-align: center;
		}

		.login-form-container .button.cancel-button {
			color: #008ecf;
			border: 1px solid #e2e2e2;
		}

		.login-form-container .button-group {
			display: flex;
			justify-content: space-evenly;
			margin-left: -5px;
			margin-right: -5px;
		}

		.login-form-container .button-group > * {
			width: auto;
			flex: 1;
			margin-left: 5px;
			margin-right: 5px;
			
		}

		.login-container .rel-pos {
			position: relative;
		}

		.login-container .togglePasswordVisible {
			position: absolute;
			right: 12px;
			top: 50%;
			cursor: pointer;
			height: 16px;
			width: 16px;
			display: flex;
			justify-content: center;
			align-items: center;
			margin-top: -8px;
			color: #666;
		}

		.footer-links .language-select button i {
			margin: 2px 4px;
		}

		.main-links .main-link {
			white-space: nowrap;
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

    {* Added by Hieu Nguyen on 2020-01-08 *}
    {if $smarty.session.check_captcha}
        {assign var="GOOGLE_CONFIG" value=getGlobalVariable('googleConfig')}

        <style>
            .hasCaptcha {
                height: 510px;
            }

            .hasCaptcha .captcha {
                margin-left: 40px;
            }
        </style>

        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script type="text/javascript">
            jQuery(function ($) {
                $('form').find('button').on('click', function (e) {
                    if (grecaptcha.getResponse() == '') {
                        $('#validationMessage').removeClass('hide').find('.message-content').text('Please check the captcha');
                        e.preventDefault();
                        return false;
                    }
                });
            });
        </script>
    {/if}
    {* End Hieu Nguyen *}

	{assign var=LOGIN_PAGE_CONFIG value=vglobal('loginPageConfig')}
	
	<div class="container full-width login-container">
		<div class="row login-content">
			<div class="col-sm-6 information-section">
				<div class="section-content">
					<div class="spacing-container">
						<div class="main-title-container">
							<div class="main-title"><p>{$LOGIN_CONFIG.main_title}</p></div>
						</div>
						<div class="main-links-container">
							<ul class="main-links">
								{foreach from=$LOGIN_CONFIG.main_links item=MAIN_LINK}
									<li class="main-link">
										<a target="_blank" href="{$MAIN_LINK.url}">{$MAIN_LINK.text} <i class="far fa-external-link" aria-hidden="true"></i></a>
									</li>
								{/foreach}
							</ul>
						</div>
						<div class="main-banner-container">
							<div class="main-banner">
								<img width="380" src="{vresource_url('resources/images/banner.png')}" />
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6 login-form-section">
				<div class="section-content">
					<div class="spacing-container">
						<div class="company-logo-container">
							<div class="company-logo">
								{assign var=COMPANY_DETAILS value=Vtiger_CompanyDetails_Model::getInstanceById()}
								{assign var=COMPANY_LOGO value=$COMPANY_DETAILS->getLogo()}
								<a href="./"><img class="img-responsive user-logo" src="{$COMPANY_LOGO->get('imagepath')}" src="{$COMPANY_LOGO->get('imagepath')}"></a>
							</div>
						</div>
						{if $smarty.request.tab == 'forgot'}
							<div class="forgot-title-container">
								<h2 class="to-center">{vtranslate('LBL_LOGIN_FORGOT_PASSWORD')}</h2>
							</div>
						{/if}
						<div class="message-container {if !$ERROR}hide{/if}" id="validationMessage">
							<div class="message failure-message">
								<span><i class="far fa-exclamation-circle" aria-hidden="true"></i> <span class="message-content">{$MESSAGE}</span></span>
							</div>
						</div>
						<div class="message-container {if !$MAIL_STATUS}hide{/if}">
							<div class="message warning-message">
								<span><i class="far fa-exclamation-circle" aria-hidden="true"></i> <span class="message-content">{$MESSAGE}</span></span>
							</div>
						</div>
						{if $smarty.request.tab != 'forgot'}
							<div id="loginFormDiv" class="login-form-container">
								<form class="login-form form-horizontal" method="POST" action="index.php">
									<input type="hidden" name="module" value="Users"/>
									<input type="hidden" name="action" value="Login"/>
									<input type="hidden" name="language" value="{$LOGIN_LANGUAGE}" />
									<div class="group">
										<div class="form-label">
											<label>{vtranslate('LBL_LOGIN_USERNAME')}</label>
										</div>
										<div class="form-input">
											<input id="username" type="text" name="username" placeholder="{vtranslate('LBL_LOGIN_USERNAME')}">
										</div>
									</div>
									<div class="group">
										<div class="form-label">
											<label>{vtranslate('LBL_LOGIN_PASSWORD')}</label>
										</div>
										<div class="form-input rel-pos password">
											<input id="password" type="password" name="password" placeholder="{vtranslate('LBL_LOGIN_PASSWORD')}">
											<i class="far fa-eye togglePasswordVisible" aria-hidden="true"></i>
										</div>
									</div>
									<div class="group">
										<div class="row">
											<div class="col-xs-6">
												<div class="to-left">
													<label><a class="forgot-password" href="index.php?modules=Users&view=Login&tab=forgot">{vtranslate('LBL_LOGIN_FORGOT_PASSWORD')}?</a></label>
												</div>
											</div>
										</div>
									</div>
									{if $smarty.session.check_captcha}
										<div class="group">
											<div class="captcha">
												<div class="g-recaptcha" data-sitekey="{$GOOGLE_CONFIG.recaptcha.site_key}"></div>
											</div>
										</div>
									{/if}
									<div class="group">
										<button type="submit" class="button buttonBlue form-action">{vtranslate('LBL_LOGIN_SIGN_IN')}</button>
									</div>
								</form>
							</div>
						{else}
							<div id="forgotPasswordDiv" class="login-form-container">
								<form class="login-form form-horizontal" method="POST" action="forgotPassword.php">
									<div class="group">
										<div class="form-label">
											<label>{vtranslate('LBL_LOGIN_USERNAME')}</label>
										</div>
										<div class="form-input">
											<input id="username" type="text" name="username" placeholder="{vtranslate('LBL_LOGIN_USERNAME')}">
										</div>
									</div>
									<div class="group">
										<div class="form-label">
											<label>{vtranslate('LBL_LOGIN_EMAIL')}</label>
										</div>
										<div class="form-input">
											<input id="email" type="email" name="emailId" placeholder="{vtranslate('LBL_LOGIN_EMAIL')}" >
										</div>
									</div>
									{if $smarty.session.check_captcha}
										<div class="group">
											<div class="captcha">
												<div class="g-recaptcha" data-sitekey="{$GOOGLE_CONFIG.recaptcha.site_key}"></div>
											</div>
										</div>
									{/if}
									<div class="group button-group">
										<a href="index.php?modules=Users&view=Login" class="button form-action cancel-button">{vtranslate('LBL_LOGIN_CANCEL')}</a>
										<button type="submit" class="button buttonBlue forgot-submit-btn form-action">{vtranslate('LBL_LOGIN_SUBMIT')}</button>
									</div>
								</form>
							</div>
						{/if}
						<div class="social-buttons-container">
							<div class="contact-us">
								<div class="to-center">
									<span>{vtranslate('LBL_LOGIN_CONTACT_US')}</span>
								</div>
							</div>
							<ul class="social-buttons">
								{foreach from=$LOGIN_CONFIG.social_buttons item=SOCIAL_BUTTON}
									<li class="social-button"><a target="_blank" href="{$SOCIAL_BUTTON.url}"><img width="50" height="50" src="{$SOCIAL_BUTTON.image}" /></a></li>
								{/foreach}
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row login-footer">
			<div class="col-sm-6">
				<div class="footer-links-container">
					<ul class="footer-links">
						{foreach from=$LOGIN_CONFIG.footer_links item=FOOTER_LINK}
							<li class="footer-link">
								<a target="_blank" href="{$FOOTER_LINK.url}">{$FOOTER_LINK.text}</a>
							</li>
						{/foreach}
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
							<span class="footer-link-gap">|</span>
							<span class="dropdown dropup language-select">
								<button class="dropdown-toggle" data-toggle="dropdown">
									<img width="20" src="{$LOGIN_CONFIG.icon}" />
									<span> {$LOGIN_CONFIG['language']} </span>
									<i class="far fa-chevron-up" aria-hidden="true"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-right">
									{foreach from=$LOGIN_PAGE_CONFIG key=LANGUAGE item=CONFIG}
										<li>
											<a href="javascript:changeLanguage('{$LANGUAGE}')">
												<img width="20" src="{$CONFIG.icon}" />
												<span> {$CONFIG['language']}</span>
											</a>
										</li>
									{/foreach}
								</ul>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<script>
		jQuery(document).ready(function () {
			jQuery('.togglePasswordVisible').on('click', function() {
				let passwordInput = jQuery('#password');

				if (passwordInput.prop('type') == 'password') {
					passwordInput.prop('type', 'text');
					jQuery('.togglePasswordVisible').removeClass('fa-eye');
					jQuery('.togglePasswordVisible').addClass('fa-eye-slash');
				}
				else {
					passwordInput.prop('type', 'password');
					jQuery('.togglePasswordVisible').removeClass('fa-eye-slash');
					jQuery('.togglePasswordVisible').addClass('fa-eye');
				}
			});

			var validationMessage = jQuery('#validationMessage');
			var forgotPasswordDiv = jQuery('#forgotPasswordDiv');
			var loginFormDiv = jQuery('#loginFormDiv');

			loginFormDiv.find('#username').focus();
			forgotPasswordDiv.find('#username').focus();

			loginFormDiv.find('button').on('click', function () {
				var username = loginFormDiv.find('#username').val();
				var password = jQuery('#password').val();
				var result = true;
				var errorMessage = '';
				if (username === '') {
					errorMessage = app.vtranslate('JS_LOGIN_INVALID_USERNAME_MSG');
					result = false;
				} else if (password === '') {
					errorMessage = app.vtranslate('JS_LOGIN_INVALID_PASS_MSG');
					result = false;
				}
				if (errorMessage) {
					validationMessage.removeClass('hide').find('.message-content').text(errorMessage);
				}
				return result;
			});

			forgotPasswordDiv.find('button').on('click', function () {
				var username = jQuery('#forgotPasswordDiv #fusername').val();
				var email = jQuery('#email').val();

				var email1 = email.replace(/^\s+/, '').replace(/\s+$/, '');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/;
				var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

				var result = true;
				var errorMessage = '';
				if (username === '') {
					errorMessage = app.vtranslate('JS_LOGIN_INVALID_USERNAME_MSG');
					result = false;
				} else if (!emailFilter.test(email1) || email == '') {
					errorMessage = app.vtranslate('JS_LOGIN_INVALID_EMAIL_MSG');
					result = false;
				} else if (email.match(illegalChars)) {
					errorMessage = app.vtranslate('JS_LOGIN_INVALID_EMAIL_CONTAINS_ILLEGAL_CHARACTERS_MSG');
					result = false;
				}
				if (errorMessage) {
					validationMessage.removeClass('hide').find('.message-content').text(errorMessage);
				}
				return result;
			});
			
			jQuery('input').blur(function (e) {
				var currentElement = jQuery(e.currentTarget);
				if (currentElement.val()) {
					currentElement.addClass('used');
				} else {
					currentElement.removeClass('used');
				}
			});
		});
	</script>
	
	{* Added by Phu Vo on 2021.07.16 *}
	<script type="text/javascript">
		function changeLanguage (language) {
			localStorage.setItem('login_language', language);
			location.replace(`index.php?language={literal}${language}{/literal}`);
		}

		// Process redirect to current language if it store in local storage
		let currentLanguage = localStorage.getItem('login_language');
		let loginLanguage = $('[name="language"]').val();

		if (currentLanguage && currentLanguage != loginLanguage) {
			changeLanguage(currentLanguage);
		}
		else {
			currentLanguage = loginLanguage;
		}

		// Clear old local storage
		localStorage.clear();

		// Assign new localStorage if neededv
		localStorage.setItem('login_language', currentLanguage);
	</script>
	{* End Phu Vo *}
{/strip}