<?php

/*
	LangUtils
	Author: Hieu Nguyen
	Date: 2018-08-06
	Purpose: to provide util functions to work with language files
*/

require_once('libraries/ArrayUtils/ArrayUtils.php');

class LangUtils {

	// Util function to get sub folder for saving language file
	public static function getSubFolderForSaving() {
		global $developerTeam;
		if ($developerTeam == 'R&D') return '';
		if ($developerTeam == 'DEV') return 'dev';
		return 'cus';
	}

	// Util function to get module lang file
	public static function getLangFile($moduleName, $language, $subFolder = '') {
		if (!in_array($subFolder, ['', 'dev', 'cus'])) return;

		if (empty($language)) {
			global $default_language;
			$language = $default_language;
		}

		$langFile = 'languages/'. $language .'/'. (!empty($subFolder) ? "{$subFolder}/" : '') . $moduleName .'.php';
		return $langFile;
	}

	// Util function to read module lang file
	public static function readModStrings($moduleName, $language) {
		global $languageStrings, $jsLanguageStrings;
		$modStrings = ['languageStrings' => [], 'jsLanguageStrings' => []];
		
		// Read base core lang file
		$langFile = self::getLangFile($moduleName, $language);

		if (file_exists($langFile)) {
			require($langFile);
            if (!$languageStrings) $languageStrings = [];
            if (!$jsLanguageStrings) $jsLanguageStrings = [];

			$modStrings = ['languageStrings' => $languageStrings, 'jsLanguageStrings' => $jsLanguageStrings];
		}

		// Read developer's lang file
		$langFile = self::getLangFile($moduleName, $language, 'dev');

		if (file_exists($langFile)) {
			require($langFile);
            if (!$languageStrings) $languageStrings = [];
            if (!$jsLanguageStrings) $jsLanguageStrings = [];

			$modStrings['languageStrings'] = merge_deep_array([$modStrings['languageStrings'], $languageStrings]);
			$modStrings['jsLanguageStrings'] = merge_deep_array([$modStrings['jsLanguageStrings'], $jsLanguageStrings]);
		}

		// Read customer's lang file
		$langFile = self::getLangFile($moduleName, $language, 'cus');

		if (file_exists($langFile)) {
			require($langFile);
            if (!$languageStrings) $languageStrings = [];
            if (!$jsLanguageStrings) $jsLanguageStrings = [];

			$modStrings['languageStrings'] = merge_deep_array([$modStrings['languageStrings'], $languageStrings]);
			$modStrings['jsLanguageStrings'] = merge_deep_array([$modStrings['jsLanguageStrings'], $jsLanguageStrings]);
		}
		
		return $modStrings;
	}

	// Util function to write module lang file
	public static function writeModStrings(array $languageStringsToWrite = [], array $jsLanguageStringsToWrite = [], $moduleName, $language) {
		require_once('include/utils/FileUtils.php');
		if (empty($languageStringsToWrite) && empty($jsLanguageStringsToWrite)) return;

		$subFolder = self::getSubFolderForSaving();
		$langFile = self::getLangFile($moduleName, $language, $subFolder);
		$languageStrings = $jsLanguageStrings = [];
		$modStrings = ['languageStrings' => [], 'jsLanguageStrings' => []];

		if (file_exists($langFile)) {
			require($langFile);
			if (empty($languageStrings)) $languageStrings = [];
			if (empty($jsLanguageStrings)) $jsLanguageStrings = [];
		}

		$modStrings['languageStrings'] = merge_deep_array([$languageStrings, $languageStringsToWrite]);
		$modStrings['jsLanguageStrings'] = merge_deep_array([$jsLanguageStrings, $jsLanguageStringsToWrite]);

		if ($subFolder == 'cus') {
			$message = "\n\tTHIS FILE IS FOR CUSTOMER TO UPDATE FROM LAYOUT EDITOR. YOU MUST BACKUP THIS FILE TO YOUR PROJECT REPO AND DO NOT MODIFY THIS FILE MANUALLY!!!";
		}
		else {
			$message = "\n\tTHIS FILE IS FOR DEVELOPER TO UPDATE FROM LAYOUT EDITOR. YOU CAN MODIFY THIS FILE FOR CUSTOMIZING BUT REMEMBER THAT ALL COMMENTS WILL BE REMOVED!!!";
		}

		FileUtils::writeArrayToFile($modStrings, $langFile, $message);
	}
}

?>