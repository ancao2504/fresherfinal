<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * Vtiger Widget Model Class
 */
class Vtiger_Widget_Model extends Vtiger_Base_Model {

	public function getWidth() {
        // Modified by Hieu Nguyen on 2020-08-27 to load widget size from config
        global $dashboardWidgetConfig;
        $widgetName = $this->getName();
        $widgetSize = $dashboardWidgetConfig[$widgetName];

		if (!empty($widgetSize)) {
            $this->set('width', $widgetSize['cols']);
        }
        // End Hieu Nguyen

		$width = $this->get('width');
		if(empty($width)) {
			$this->set('width', '1');
		}
		return $this->get('width');
	}

	public function getHeight() {
        // Modified by Hieu Nguyen on 2020-08-27 to load widget size from config
        global $dashboardWidgetConfig;
        $widgetName = $this->getName();
        $widgetSize = $dashboardWidgetConfig[$widgetName];

		if (!empty($widgetSize)) {
            $this->set('height', $widgetSize['rows']);
        }
        // End Hieu Nguyen

		$height = $this->get('height');
		if(empty($height)) {
			$this->set('height', '1');
		}
		return $this->get('height');
	}

    public function getSizeX() {
        $size = $this->get('size');
		if ($size) {
			$size = Zend_Json::decode(decode_html($size));
            $width = intval($size['sizex']);
            $this->set('width', $width);
			return $width;
		}
        return $this->getWidth();
    }
    
    public function getSizeY() {
        $size = $this->get('size');
		if ($size) {
			$size = Zend_Json::decode(decode_html($size));
			$height = intval($size['sizey']);
            $this->set('height', $height);
			return $height;
		}
        return $this->getHeight();
    }

	public function getPositionCol($default=0) {
		$position = $this->get('position');
		if ($position) {
			$position = Zend_Json::decode(decode_html($position));
			return intval($position['col']);
		}
		return $default;
	}

	public function getPositionRow($default=0) {
		$position = $this->get('position');
		if ($position) {
			$position = Zend_Json::decode(decode_html($position));
			return intval($position['row']);
		}
		return $default;
	}

	/**
	 * Function to get the url of the widget
	 * @return <String>
	 */
	public function getUrl() {
		$url = decode_html($this->get('linkurl')).'&linkid='.$this->get('linkid');
		if($this->get('reportid')){
			$chartReportLinkUrl = "index.php?module=Reports&view=ShowWidget&name=ChartReportWidget&reportid=".$this->get('reportid');
			$url = decode_html($chartReportLinkUrl);
		}	
		$widgetid = $this->has('widgetid')? $this->get('widgetid') : $this->get('id');
		if ($widgetid) $url .= '&widgetid=' . $widgetid;
		return $url;
	}

	/**
	 *  Function to get the Title of the widget
	 */
	public function getTitle() {
		$title = $this->get('title');
		if(!$title) {
			$title = $this->get('linklabel');
		}
		return $title;
	}

	public function getName() {
		$widgetName = $this->get('name');
		if(empty($widgetName)){
			//since the html entitites will be encoded
			//TODO : See if you need to push decode_html to base model
			$linkUrl = decode_html($this->getUrl());
			preg_match('/name=[a-zA-Z]+/', $linkUrl, $matches);
			$matches = explode('=', $matches[0]);
			$widgetName = $matches[1];
			$this->set('name', $widgetName);
		}
		return $widgetName;
	}
	/**
	 * Function to get the instance of Vtiger Widget Model from the given array of key-value mapping
	 * @param <Array> $valueMap
	 * @return Vtiger_Widget_Model instance
	 */
	public static function getInstanceFromValues($valueMap) {
		$self = new self();
		$self->setData($valueMap);
		return $self;
	}

    // Modified by Hieu Nguyen on 2021-01-05 to load widget by its id instead of the common linkid
	public static function getInstance($widgetId, $userId) {
		global $adb;
        $sql = "SELECT * FROM vtiger_module_dashboard_widgets AS w
			INNER JOIN vtiger_links AS l ON (l.linkid = w.linkid AND l.linktype = ?)
			WHERE w.id = ? AND w.userid = ? LIMIT 1";
        $params = ['DASHBOARDWIDGET', $widgetId, $userId];
		$result = $adb->pquery($sql, $params);
		$data = $adb->fetchByAssoc($result);
		$widget = new self();

		if (!empty($data)) {
            $widget->setData($data);
		}
		
        return $widget;
	}

    // Implemented by Hieu Nguyen on 2020-03-26 to support custom chart widget
    public static function getInstanceForCustomChartWidget($widgetId, $userId) {
		global $adb;
        $params = [$widgetId, $userId];
		$result = $adb->pquery("SELECT * FROM vtiger_module_dashboard_widgets WHERE id = ? AND userid = ?", $params);
        $data = $adb->fetchByAssoc($result);
		$widget = new self();

		if (!empty($data)) {
            $widget->setData($data);
		}

		return $widget;
	}

	public static function updateWidgetPosition($position, $linkId, $widgetId, $userId) {
		if (!$linkId && !$widgetId) return;

		$db = PearDatabase::getInstance();
		$sql = 'UPDATE vtiger_module_dashboard_widgets SET position=? WHERE userid=?';
		$params = array($position, $userId);
		if ($linkId) {
			$sql .= ' AND linkid = ?';
			$params[] = $linkId;
		} else if ($widgetId) {
			$sql .= ' AND id = ?';
			$params[] = $widgetId;
		}
		$db->pquery($sql, $params);
	}

	public static function updateWidgetSize($size, $linkId, $widgetId, $userId, $tabId) {
		if ($linkId || $widgetId) {
			$db = PearDatabase::getInstance();
			$sql = 'UPDATE vtiger_module_dashboard_widgets SET size=? WHERE userid=?';
			$params = array($size, $userId);
			if ($linkId) {
				$sql .= ' AND linkid=?';
				$params[] = $linkId;
			} else if ($widgetId) {
				$sql .= ' AND id=?';
				$params[] = $widgetId;
			}
			$sql .= ' AND dashboardtabid=?';
			$params[] = $tabId;
			$db->pquery($sql, $params);
		}
	}

    // Implemented by Hieu Nguyen on 2020-03-26 to support custom chart widget params update
    public static function updateWidgetParams($widgetId, $params) {
		if (!empty($params)) {
			$db = PearDatabase::getInstance();

            // Get current params
            $sql = "SELECT data FROM vtiger_module_dashboard_widgets WHERE id = ?";
            $currentParams = $db->getOne($sql, [$widgetId]);
            $currentParams = json_decode(decodeUTF8($currentParams), true) ?? [];

            // Merge current params with new params get the latest and update
            $mergedParams = array_merge($currentParams, $params);
			$sql = "UPDATE vtiger_module_dashboard_widgets SET data = ? WHERE id = ?";
			$db->pquery($sql, [json_encode($mergedParams), $widgetId]);
		}
	}

	/**
	 * Function to add a widget from the Users Dashboard
	 */
	public function add() {
		$db = PearDatabase::getInstance();

		$tabid = 1;
		if ($this->get("tabid")) {
			$tabid = $this->get("tabid");
		}

		$sql = 'SELECT id FROM vtiger_module_dashboard_widgets WHERE linkid = ? AND userid = ? AND dashboardtabid=?';
		$params = array($this->get('linkid'), $this->get('userid'), $tabid);

		$filterid = $this->get('filterid');
		if (!empty($filterid)) {
			$sql .= ' AND filterid = ?';
			$params[] = $this->get('filterid');
		}

		$result = $db->pquery($sql, $params);

        // Modified by Hieu Nguyen on 2020-10-13 to save new widget with name_en and name_vn fields
		if (!$db->num_rows($result) || $this->has('data')) {
            $sql = "INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, name_en, name_vn, data, dashboardtabid) VALUES(?, ?, ?, ?, ?, ?, ?)";
            $params = [$this->get('linkid'), $this->get('userid'), $this->get('filterid'), $this->get('name_en'), $this->get('name_vn'),  Zend_Json::encode($this->get('data')), $tabid];
			$db->pquery($sql, $params);
			$this->set('id', $db->getLastInsertID());
            return true;
		}
        // End Hieu Nguyen
		
        return false;
	}

	/**
	 * Function to remove the widget from the Users Dashboard
	 */
	public function remove() {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_module_dashboard_widgets WHERE id = ? AND userid = ?',
				array($this->get('id'), $this->get('userid')));
	}

	/**
	 * Function deletes all dashboard widgets with the reportId
	 * @param type $reportId
	 */
	public static function deleteChartReportWidgets($reportId) {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_module_dashboard_widgets WHERE reportid = ?',
				array($reportId));
	}

	/**
	 * Function returns URL that will remove a widget for a User
	 * @return <String>
	 */
	public function getDeleteUrl() {
		$url = 'index.php?module=Vtiger&action=RemoveWidget&linkid='. $this->get('linkid');
		$widgetid = $this->has('widgetid')? $this->get('widgetid') : $this->get('id');
		if ($widgetid) $url .= '&widgetid=' . $widgetid;
		if ($this->get('reportid')) $url .= '&reportid=' .$this->get('reportid');
		return $url;
	}

	/**
	 * Function to check the Widget is Default widget or not
	 * @return <boolean> true/false
	 */
	public function isDefault() {
		$defaultWidgets = $this->getDefaultWidgets();
		$widgetName = $this->getName();

		if (in_array($widgetName, $defaultWidgets)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get Default widget Names
	 * @return <type>
	 */
	public function getDefaultWidgets() {
		return array();
	}
}