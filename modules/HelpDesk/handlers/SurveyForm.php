<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.21
 * @desc Ticket survey form handler implementation for shorturl mechanism
 */

class HelpDesk_SurveyForm_Handler {

    public function process($data) {
		if (empty(vglobal('current_user'))) vglobal('current_user', Users::getRootAdminUser());
		
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$ticketId = $data['ticket_id'];
		$surveyCreatedtime = $data['survey_createdtime'];

		if ($requestMethod === 'POST') {
			HelpDesk_SurveyUtils_Helper::handleSurveyFormSubmition($ticketId);
			return;
		}

		$this->render($ticketId, $surveyCreatedtime);
    }

	public function render($ticketId, $surveyCreatedtime) {
		$formData = HelpDesk_SurveyUtils_Helper::getFormData($ticketId, $surveyCreatedtime);

		// Not render page
		if (!empty($formData) && $formData['state'] == HelpDesk_SurveyUtils_Helper::INVALID_INPUT) {
			http_response_code(404);
			die();
		}

		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		$companyLogo = $companyDetails->getLogo();

		$viewer = $this->getViewer();
		$viewer->assign('PAGETITLE', vtranslate('LBL_TICKET_SURVEY_FORM', 'HelpDesk'));
		$viewer->assign('SCRIPTS',$this->getHeaderScripts());
		$viewer->assign('STYLES',$this->getHeaderCss());
		$viewer->assign('FORM_DATA', $formData);
		$viewer->assign('LOGO_URL', $companyLogo->get('imagepath'));
		$viewer->display('modules/HelpDesk/tpls/SurveyForm.tpl');
	}

    public function getHeaderCss() {
		return $this->checkAndConvertCssStyles([
            '~modules/HelpDesk/resources/SurveyForm.css'
        ]);
	}

    function getHeaderScripts() {
		return $this->checkAndConvertJsScripts([
            '~libraries/jquery/bootstraprating/bootstrap4-rating-input.min.js',
			'~modules/HelpDesk/resources/SurveyForm.js'
        ]);
	}

	function getViewer() {
        if (!$this->viewer) {
			global $vtiger_current_version, $vtiger_display_version, $onlyV7Instance;

			$viewer = new Vtiger_Viewer();
			$viewer->assign('APPTITLE', getTranslatedString('APPTITLE'));
			$viewer->assign('VTIGER_VERSION', $vtiger_current_version);
			$viewer->assign('VTIGER_DISPLAY_VERSION', $vtiger_display_version);
			$viewer->assign('ONLY_V7_INSTANCE', $onlyV7Instance);
			$viewer->assign('VIEWER', $viewer);

			$this->viewer = $viewer;
		}

        return $this->viewer;
    }

	function checkAndConvertCssStyles($cssFileNames, $fileExtension = 'css') {
		$cssStyleInstances = [];
		
		foreach ($cssFileNames as $cssFileName) {
			$cssScriptModel = new Vtiger_CssScript_Model();

			if (strpos($cssFileName, 'http://') === 0 || strpos($cssFileName, 'https://') === 0) {
				$cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
				continue;
			}

			$completeFilePath = Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);
			$filePath = NULL;

			if (file_exists($completeFilePath)) {
				if (strpos($cssFileName, '~') === 0) {
					$filePath = ltrim(ltrim($cssFileName, '~'), '/');

					if (substr_count($cssFileName, "~") == 2) {
						$filePath = "../" . $filePath;
					}
				}
				else {
					$filePath = str_replace('.', '/', $cssFileName) . '.'.$fileExtension;
					$filePath = Vtiger_Theme::getStylePath($filePath);
				}

				$cssStyleInstances[] = $cssScriptModel->set('href', $filePath);
			}
		}

		return $cssStyleInstances;
	}

	function checkAndConvertJsScripts($jsFileNames) {
		$fileExtension = 'js';
		$jsScriptInstances = [];

		if ($jsFileNames) {
			foreach ($jsFileNames as $jsFileName) {
				$jsScript = new Vtiger_JsScript_Model();

				if (strpos($jsFileName, 'http://') === 0 || strpos($jsFileName, 'https://') === 0) {
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
					continue;
				}

				$completeFilePath = Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);

				if (file_exists($completeFilePath)) {
					if (strpos($jsFileName, '~') === 0) {
						$filePath = ltrim(ltrim($jsFileName, '~'), '/');

						if (substr_count($jsFileName, "~") == 2) {
							$filePath = "../" . $filePath;
						}
					}
					else {
						$filePath = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
					}

					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
				}
				else {
					$fallBackFilePath = Vtiger_Loader::resolveNameToPath(Vtiger_JavaScript::getBaseJavaScriptPath().'/'.$jsFileName, 'js');
					
					if( file_exists($fallBackFilePath)) {
						$filePath = str_replace('.', '/', $jsFileName) . '.js';
						$jsScriptInstances[$jsFileName] = $jsScript->set('src', Vtiger_JavaScript::getFilePath($filePath));
					}
				}
			}
		}

		return $jsScriptInstances;
	}
}