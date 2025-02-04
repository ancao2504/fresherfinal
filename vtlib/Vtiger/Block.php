<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/
include_once('vtlib/Vtiger/Utils.php');
require_once 'includes/runtime/Cache.php';

/**
 * Provides API to work with vtiger CRM Module Blocks
 * @package vtlib
 */
class Vtiger_Block {
	/** ID of this block instance */
	var $id;
	/** Label for this block instance */
	var $label;

	var $sequence;
	var $showtitle = 0;
	var $visible = 0;
	var $increateview = 0;
	var $ineditview = 0;
	var $indetailview = 0;

    var $display_status=1;
	var $iscustom=0;

	var $module;
	var $blockTableName;	// Added by Hieu Nguyen on 2018-08-16 to specify the table where the block will be created

	/**
	 * Constructor
	 */
	function __construct() {
	}

	// Added by Hieu Nguyen on 2018-07-30
	protected function getBlockTableName() {
		if (isset(self::$blockTableName) && !empty(self::$blockTableName)) {
			return self::$blockTableName;
		}

		if (isset($this) && !empty($this->blockTableName)) {
			return $this->blockTableName;
		}

		if ($_REQUEST['parent'] == 'Settings') {
			if ($_REQUEST['module'] == 'LayoutEditor') {
				$blockTable = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'vtiger_editview_blocks' : 'vtiger_blocks';
			}
			else if (!empty($GLOBALS['current_view'])) {
				$blockTable = $GLOBALS['current_view'] == 'edit' ? 'vtiger_editview_blocks' : 'vtiger_blocks';
			}
			else {
				return 'vtiger_editview_blocks';
			}
		}
		else {
			$blockTable = $GLOBALS['current_view'] == 'edit' ? 'vtiger_editview_blocks' : 'vtiger_blocks';
		}

		return $blockTable;
	}
	// End Hieu Nguyen

	/**
	 * Get unquie id for this instance
	 * @access private
	 */
	function __getUniqueId() {
		global $adb;

		/** Sequence table was added from 5.1.0 */

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = $this->getBlockTableName();
		$maxblockid = $adb->getUniqueID($blockTable);
		// End Hieu Nguyen

		return $maxblockid;
	}

	/**
	 * Get next sequence value to use for this block instance
	 * @access private
	 */
	function __getNextSequence() {
		global $adb;

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = $this->getBlockTableName();
		$result = $adb->pquery("SELECT MAX(sequence) as max_sequence from {$blockTable} where tabid = ?", Array($this->module->id));
		// End Hieu Nguyen
		
		$maxseq = 0;
		if($adb->num_rows($result)) {
			$maxseq = $adb->query_result($result, 0, 'max_sequence');
		}
		return ++$maxseq;
	}

	/**
	 * Initialize this block instance
	 * @param Array Map of column name and value
	 * @param Vtiger_Module Instance of module to which this block is associated
	 * @access private
	 */
	function initialize($valuemap, $moduleInstance=false) {
		$this->id = isset($valuemap['blockid']) ? $valuemap['blockid'] : null;
		$this->label= isset($valuemap['blocklabel']) ? $valuemap['blocklabel'] : null;
        $this->display_status = isset($valuemap['display_status']) ? $valuemap['display_status'] : null;
		$this->sequence = isset($valuemap['sequence']) ? $valuemap['sequence'] : null;
        $this->iscustom = isset($valuemap['iscustom']) ? $valuemap['iscustom'] : null;
        $tabid = isset($valuemap['tabid']) ? $valuemap['tabid'] : null;
		$this->module= $moduleInstance ? $moduleInstance : Vtiger_Module::getInstance($tabid);
	}

	/**
	 * Create vtiger CRM block
	 * @access private
	 */
	function __create($moduleInstance) {
		global $adb;

		$this->module = $moduleInstance;

		$this->id = $this->__getUniqueId();
		if(!$this->sequence) $this->sequence = $this->__getNextSequence();

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = $this->getBlockTableName();
		$adb->pquery("INSERT INTO {$blockTable}(blockid,tabid,blocklabel,sequence,show_title,visible,create_view,edit_view,detail_view,iscustom)
			VALUES(?,?,?,?,?,?,?,?,?,?)", Array($this->id, $this->module->id, $this->label,$this->sequence,
			$this->showtitle, $this->visible,$this->increateview, $this->ineditview, $this->indetailview, $this->iscustom));
		// End Hieu Nguyen

		self::log("Creating Block $this->label ... DONE");
		self::log("Module language entry for $this->label ... CHECK");

        return $this->id;   // Added by Hieu Nguyen on 2019-03-05 to get the id as result
	}

	/**
	 * Update vtiger CRM block
	 * @access private
	 * @internal TODO
	 */
	function __update() {
		self::log("Updating Block $this->label ... DONE");
	}

	/**
	 * Delete this instance
	 * @access private
	 */
	function __delete() {
		global $adb;
		self::log("Deleting Block $this->label ... ", false);

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = $this->getBlockTableName();
		$adb->pquery("DELETE FROM {$blockTable} WHERE blockid=?", Array($this->id));
		// End Hieu Nguyen

		self::log("DONE");
	}

	/**
	 * Save this block instance
	 * @param Vtiger_Module Instance of the module to which this block is associated
	 */
	function save($moduleInstance=false) {
		if($this->id) $this->__update();
		else $this->__create($moduleInstance);
		return $this->id;
	}

	/**
	 * Delete block instance
	 * @param Boolean True to delete associated fields, False to avoid it
	 */
    // Modified by Hieu Nguyen on 2021-07-01 to prevent all belonging fields to be deleted with the block
	function delete() {
        /*
		if($recursive) {
			$fields = Vtiger_Field::getAllForBlock($this);
			foreach($fields as $fieldInstance) $fieldInstance->delete($recursive);
		}
        */
		
        $this->__delete();
	}

	/**
	 * Add field to this block
	 * @param Vtiger_Field Instance of field to add to this block.
	 * @return Reference to this block instance
	 */
	function addField($fieldInstance) {
		$fieldInstance->save($this);
		return $this;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim=true) {
		Vtiger_Utils::Log($message, $delim);
	}

	/**
	 * Get instance of block
	 * @param mixed block id or block label
	 * @param Vtiger_Module Instance of the module if block label is passed
	 */
	static function getInstance($value, $moduleInstance=false) {
		global $adb;

        // Added by Hieu Nguyen on 2021-05-31 to apply caching
        static $cache = [];
        $cacheKey = $value .'_'. ($moduleInstance ? $moduleInstance->id : '');
        if (isset($cache[$cacheKey])) return $cache[$cacheKey];
        // End Hieu Nguyen

		$instance = false;

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = self::getBlockTableName();

		if(Vtiger_Utils::isNumber($value)) {
			$query = "SELECT * FROM {$blockTable} WHERE blockid=?";
			$queryParams = Array($value);
		} else {
			$query = "SELECT * FROM {$blockTable} WHERE blocklabel=? AND tabid=?";
			$queryParams = Array($value, $moduleInstance->id);
		}
		// End Hieu Nguyen

		$result = $adb->pquery($query, $queryParams);
		if($adb->num_rows($result)) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
		}

        $cache[$cacheKey] = $instance;  // Added by Hieu Nguyen on 2021-05-31 to apply caching
		return $instance;
	}

	/**
	 * Get all block instances associated with the module
	 * @param Vtiger_Module Instance of the module
	 */
	static function getAllForModule($moduleInstance) {
        // Added by Hieu Nguyen on 2021-05-31 to apply caching
        static $cache = [];
        $cacheKey = $moduleInstance->id;
        if (isset($cache[$cacheKey])) return $cache[$cacheKey];
        // End Hieu Nguyen
        
		global $adb;
		$instances = false;

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = self::getBlockTableName();
		$query = "SELECT * FROM {$blockTable} WHERE tabid=? ORDER BY sequence";
		// End Hieu Nguyen

		$queryParams = Array($moduleInstance->id);

		$result = $adb->pquery($query, $queryParams);
		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
			$instances[] = $instance;
		}
		
        $cache[$cacheKey] = $instances; // Added by Hieu Nguyen on 2021-05-31 to apply caching
		return $instances;
	}

	/**
	 * Delete all blocks associated with module
	 * @param Vtiger_Module Instnace of module to use
	 * @param Boolean true to delete associated fields, false otherwise
	 * @access private
	 */
	static function deleteForModule($moduleInstance, $recursive=true) {
		global $adb;
		if($recursive) Vtiger_Field::deleteForModule($moduleInstance);

		// Modified by Hieu Nguyen on 2018-07-30
		$blockTable = self::getBlockTableName();
		$adb->pquery("DELETE FROM {$blockTable} WHERE tabid=?", Array($moduleInstance->id));
		// End Hieu Nguyen

		self::log("Deleting blocks for module ... DONE");
	}
}
?>
