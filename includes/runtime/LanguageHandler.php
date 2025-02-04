<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Class to handler language translations
 */
class Vtiger_Language_Handler {

	//Contains module language translations
	protected static $languageContainer;

	/**
	 * Functions that gets translated string
	 * @param <String> $key - string which need to be translated
	 * @param <String> $module - module scope in which the translation need to be check
	 * @return <String> - translated string
	 */
	public static function getTranslatedString($key, $module = '', $currentLanguage = '') {
        // Added by Hieu Nguyen on 2021-05-31 to apply caching
        static $cache;
        $cacheKey = "{$key}_{$module}_{$currentLanguage}";
        if (isset($cache[$cacheKey])) return $cache[$cacheKey];
        // End Hieu Nguyen

		// Modified by Hieu Nguyen on 2021-09-22 to get current language from global variable $current_language if it is presetted
		if (empty($currentLanguage)) {
			global $current_language;

			if (!empty($current_language)) {
				$currentLanguage = $current_language;
			}
			else {
				$currentLanguage = self::getLanguage();
			}
		}
		// End Hieu Nguyen

		//decoding for Start Date & Time and End Date & Time 
		if (!is_array($key))
			$key = decode_html($key);
		$translatedString = self::getLanguageTranslatedString($currentLanguage, $key, $module);

		// label not found in users language pack, then check in the default language pack(config.inc.php)
		if ($translatedString === null) {
			$defaultLanguage = vglobal('default_language');
			if (!empty($defaultLanguage) && strcasecmp($defaultLanguage, $currentLanguage) !== 0) {
				$translatedString = self::getLanguageTranslatedString($defaultLanguage, $key, $module);
			}
		}

		// If translation is not found then return label
		if ($translatedString === null) {
			$translatedString = $key;
		}

        $cache[$cacheKey] = $translatedString;  // Added by Hieu Nguyen on 2021-05-31 to apply caching
		return $translatedString;
	}

	/**
	 * Function returns language specific translated string
	 * @param <String> $language - en_us etc
	 * @param <String> $key - label
	 * @param <String> $module - module name
	 * @return <String> translated string or null if translation not found
	 */
	public static function getLanguageTranslatedString($language, $key, $module = '') {
		$moduleStrings = array();

		$module = str_replace(':', '.', $module);
		if (is_array($module))
			return null;
		$moduleStrings = self::getModuleStringsFromFile($language, $module);
		if (!empty($moduleStrings['languageStrings'][$key])) {
			return $moduleStrings['languageStrings'][$key];
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($module, '.') > 0) {
			$baseModule = substr($module, 0, strpos($module, '.'));
			if ($baseModule == 'Settings') {
				$baseModule = 'Settings.Vtiger';
			}
			$moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
			if (!empty($moduleStrings['languageStrings'][$key])) {
				return $moduleStrings['languageStrings'][$key];
			}
		}

		$commonStrings = self::getModuleStringsFromFile($language);
		if (!empty($commonStrings['languageStrings'][$key]))
			return $commonStrings['languageStrings'][$key];

		return null;
	}

	/**
	 * Functions that gets translated string for Client side
	 * @param <String> $key - string which need to be translated
	 * @param <String> $module - module scope in which the translation need to be check
	 * @return <String> - translated string
	 */
	public static function getJSTranslatedString($language, $key, $module = '') {
		$moduleStrings = array();

		$module = str_replace(':', '.', $module);
		$moduleStrings = self::getModuleStringsFromFile($language, $module);
		if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
			return $moduleStrings['jsLanguageStrings'][$key];
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($module, '.') > 0) {
			$baseModule = substr($module, 0, strpos($module, '.'));
			if ($baseModule == 'Settings') {
				$baseModule = 'Settings.Vtiger';
			}
			$moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
			if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
				return $moduleStrings['jsLanguageStrings'][$key];
			}
		}

		$commonStrings = self::getModuleStringsFromFile($language);
		if (!empty($commonStrings['jsLanguageStrings'][$key]))
			return $commonStrings['jsLanguageStrings'][$key];

		return $key;
	}

	/**
	 * Function that returns translation strings from file
	 * @global <array> $languageStrings - language specific string which is used in translations
	 * @param <String> $module - module Name
	 * @return <array> - array if module has language strings else returns empty array
	 */
    // Modified by Hieu Nguyen on 2021-06-14 to load labels from base file + developer file + customer file
	public static function getModuleStringsFromFile($language, $module = 'Vtiger') {
        require_once('libraries/ArrayUtils/ArrayUtils.php');
		$module = str_replace(':', '.', $module);

		if (empty(self::$languageContainer[$language][$module])) {
            // Init cache array
            self::$languageContainer[$language][$module] = [
                'languageStrings' => [],
                'jsLanguageStrings' => []
            ];

            $languageStrings = $jsLanguageStrings = [];

            // Load base core file
			$baseLanguageFile = 'languages.'. $language .'.'. $module;
			$file = Vtiger_Loader::resolveNameToPath($baseLanguageFile);

			if (file_exists($file)) {
				require($file);
                if (!$languageStrings) $languageStrings = [];
                if (!$jsLanguageStrings) $jsLanguageStrings = [];

				self::$languageContainer[$language][$module]['languageStrings'] = $languageStrings;
				self::$languageContainer[$language][$module]['jsLanguageStrings'] = $jsLanguageStrings;
			}

            // Load developer's file
            $devLanguageFile = 'languages.'. $language .'.dev.'. $module;
			$file = Vtiger_Loader::resolveNameToPath($devLanguageFile);

			if (file_exists($file)) {
				require($file);
                if (!$languageStrings) $languageStrings = [];
                if (!$jsLanguageStrings) $jsLanguageStrings = [];

				self::$languageContainer[$language][$module]['languageStrings'] = merge_deep_array([self::$languageContainer[$language][$module]['languageStrings'], $languageStrings]);
				self::$languageContainer[$language][$module]['jsLanguageStrings'] = merge_deep_array([self::$languageContainer[$language][$module]['jsLanguageStrings'], $jsLanguageStrings]);
			}

            // Load customer's file
            $cusLanguageFile = 'languages.'. $language .'.cus.'. $module;
			$file = Vtiger_Loader::resolveNameToPath($cusLanguageFile);

			if (file_exists($file)) {
				require($file);
                if (!$languageStrings) $languageStrings = [];
                if (!$jsLanguageStrings) $jsLanguageStrings = [];

				self::$languageContainer[$language][$module]['languageStrings'] = merge_deep_array([self::$languageContainer[$language][$module]['languageStrings'], $languageStrings]);
				self::$languageContainer[$language][$module]['jsLanguageStrings'] = merge_deep_array([self::$languageContainer[$language][$module]['jsLanguageStrings'], $jsLanguageStrings]);
			}
		}

		$result = [];
		
        if (isset(self::$languageContainer[$language][$module])) {
			$result = self::$languageContainer[$language][$module];
		}

		return $result;
	}

	/**
	 * Function that returns current language
	 * @return <String> -
	 */
	public static function getLanguage() {
        // Added by Hieu Nguyen on 2021-05-31 to apply caching
		static $cache = null;
        if (!empty($cache)) return $cache;
        // End Hieu Nguyen

		$userModel = Users_Record_Model::getCurrentUserModel();
		$language = '';
		if (!empty($userModel) && $userModel->has('language')) {
			$language = $userModel->get('language');
		}

        // Modified by Hieu Nguyen on 2021-05-31 to apply caching
		$language = empty($language) ? vglobal('default_language') : $language;
        $cache = $language;
        return $language;
        // End Hieu Nguyen
	}

	/**
	 * Function that returns current language short name
	 * @return <String> -
	 */
	public static function getShortLanguageName() {
		$language = self::getLanguage();
		return substr($language, 0, 2);
	}

	/**
	 * Function returns module strings
	 * @param <String> $module - module Name
	 * @param <String> languageStrings or jsLanguageStrings
	 * @return <Array>
	 */
	public static function export($module, $type = 'languageStrings', $loadCommonStrings = true) {  // Added param $loadCommonStrings by Hieu Nguyen on 2019-11-21
		$userSelectedLanguage = self::getLanguage();
		$defaultLanguage = vglobal('default_language');
		$languages = array($userSelectedLanguage);
		//To merge base language and user selected language translations
		if ($userSelectedLanguage != $defaultLanguage) {
			array_push($languages, $defaultLanguage);
		}


		$resultantLanguageString = array();
		foreach ($languages as $currentLanguage) {
			$exportLangString = array();

			$moduleStrings = self::getModuleStringsFromFile($currentLanguage, $module);
			if (!empty($moduleStrings[$type])) {
				$exportLangString = $moduleStrings[$type];
			}

			// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
			if (strpos($module, '.') > 0) {
				$baseModule = substr($module, 0, strpos($module, '.'));
				if ($baseModule == 'Settings') {
					$baseModule = 'Settings.Vtiger';
				}
				$moduleStrings = self::getModuleStringsFromFile($currentLanguage, $baseModule);
				if (!empty($moduleStrings[$type])) {
					$exportLangString += $commonStrings[$type];
				}
			}

			// Modified by Hieu Nguyen on 2019-11-21 to load common string based on the param value
            if ($loadCommonStrings) {
                $commonStrings = self::getModuleStringsFromFile($currentLanguage);

                if (!empty($commonStrings[$type])) {
                    $exportLangString += $commonStrings[$type];
                }
            }
			// End Hieu Nguyen

			$resultantLanguageString += $exportLangString;
		}

		return $resultantLanguageString;
	}

	/**
	 * Function to returns all language information
	 * @return <Array>
	 */
	public static function getAllLanguages() {
		return Vtiger_Language::getAll();
	}

	/**
	 * Function to get the label name of the Langauge package
	 * @param <String> $name
	 */
	public static function getLanguageLabel($name) {
		$db = PearDatabase::getInstance();
		$languageResult = $db->pquery('SELECT label FROM vtiger_language WHERE prefix = ?', array($name));
		if ($db->num_rows($languageResult)) {
			return $db->query_result($languageResult, 0, 'label');
		}
		return false;
	}

}

function vtranslate($key, $moduleName = '') {
	$args = func_get_args();

	// Modified by Phu Vo to call language handler directly, fix bug didn't recognize user language when using replaced params
	$language = !is_array($args[2]) ? $args[2] : '';
	$formattedString = Vtiger_Language_Handler::getTranslatedString($key, $moduleName, $language);
	// End Phu Vo

	array_shift($args);
	array_shift($args);
	if (is_array($args) && !empty($args)) {
		// Modified by Hieu Nguyen on 2018-07-05
		//$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
		$formattedString = formatString($formattedString, $args);
		// End Hieu Nguyen
	}
	return $formattedString;
}

function vJSTranslate($key, $moduleName = '') {
	$args = func_get_args();
	return call_user_func_array(array('Vtiger_Language_Handler', 'getJSTranslatedString'), $args);
}

// Added by Hieu Nguyen on 2018-07-05 to support format string with array of key and value
function formatString($str, $args) {
	if(count($args) == 1 && is_array($args[0])) {
		return replaceKeys($str, $args[0]);
	}
		
	return call_user_func_array('vsprintf', array($str, $args));
}

function replaceKeys($str, $keys) {
	return str_replace(array_keys($keys), array_values($keys), $str);
}
// End Hieu Nguyen
