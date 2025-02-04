{* Added by Hieu Nguyen on 2022-11-14 to render Survey popup *}

{strip}
	<div class="modal-dialog modal-md modal-content survey-popup hide">
		<div class="modal-body">
			<a class="btn-close pull-right" data-dismiss="modal">
				<i class="far fa-xmark"></i>
			</a>
			<div class="left-side">
				<h1>CloudGO</h1>
				<h2>{vtranslate('LBL_SURVEY_POPUP_SLOGAN', 'Vtiger')}</h2>
				<div class="text-center">
					<img src="resources/images/survey.png" />
				</div>
			</div>
			<div class="right-side">
				<h2 class="title text-center">
					<div data-for="beginingSurvey" class="hide">{vtranslate('LBL_SURVEY_POPUP_TITLE_BEGINING_SURVEY', 'Vtiger')}</div>
					<div data-for="endingSurvey" class="hide">{vtranslate('LBL_SURVEY_POPUP_TITLE_ENDING_SURVEY', 'Vtiger')}</div>
				</h2>
				<div class="message">{vtranslate('LBL_SURVEY_POPUP_MESSAGE', 'Vtiger')}</div>
				<br/>
				<div class="text-center">
					<a class="btn-open-form" href="https://event.onlinecrm.vn/khao-sat-khach-hang" target="_blank">{vtranslate('LBL_SURVEY_POPUP_BTN_OPEN_FORM', 'Vtiger')}</a>
					<div class="promotion hide">{vtranslate('LBL_SURVEY_POPUP_PROMOTION', 'Vtiger')}</div>
				</div>
			</div>
		</div>
	</div>
{/strip}