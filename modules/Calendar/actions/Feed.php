<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

vimport ('~~/include/Webservices/Query.php');

class Calendar_Feed_Action extends Vtiger_BasicAjax_Action {

    private $result = []; // Added by Hieu Nguyen on 2019-11-12 to change the Calendar Feed logic

    // Modified by Hieu Nguyen on 2019-11-12 to change the Calendar Feed logic
	public function process(Vtiger_Request $request) {
		if ($request->get('mode') === 'batch') {
			$feedsRequest = $request->get('feedsRequest', []);

			if (count($feedsRequest)) {
				foreach ($feedsRequest as $key => $value) {
					$requestParams = array();
					$requestParams['start'] = $value['start'];
					$requestParams['end'] = $value['end'];
					$requestParams['type'] = $value['type'];
					$requestParams['userid'] = $value['userid'];
					$requestParams['color'] = $value['color'];
					$requestParams['textColor'] = $value['textColor'];
					$requestParams['targetModule'] = $value['targetModule'];
					$requestParams['fieldname'] = $value['fieldname'];
					$requestParams['calendar_view'] = $value['calendar_view'];  // Changed group into calendar_view by Hieu Nguyen on 2019-11-12 to determine which view to load feed
					$requestParams['mapping'] = $value['mapping'];
					$requestParams['conditions'] = $value['conditions'];
					$this->result[$key] = $this->_process($requestParams);
				}
			}

            $response = [];
        
            // Convert event list from map format into normal array for the client in batch mode
            foreach ($this->result as $eventKey => $eventsMap) {
                $response[$eventKey] = array_values($eventsMap);
            }
		} 
        else {
			$requestParams = [];
			$requestParams['start'] = $request->get('start');
			$requestParams['end'] = $request->get('end');
			$requestParams['type'] = $request->get('type');
			$requestParams['userid'] = $request->get('userid');
			$requestParams['color'] = $request->get('color');
			$requestParams['textColor'] = $request->get('textColor');
			$requestParams['targetModule'] = $request->get('targetModule');
			$requestParams['fieldname'] = $request->get('fieldname');
			$requestParams['calendar_view'] = $request->get('calendar_view');  // Changed group into calendar_view by Hieu Nguyen on 2019-11-12 to determine which view to load feed
			$requestParams['mapping'] = $request->get('mapping');
			$requestParams['conditions'] = $request->get('conditions','');
            $this->result = $this->_process($requestParams);
            $response = array_values($this->result);
		}

        echo json_encode($response);
	}
    // End Hieu Nguyen

	public function _process($request) {
		try {
			$start = $request['start'];
			$end = $request['end'];
			$type = $request['type'];
			$userid = $request['userid'];
			$color = $request['color'];
			$textColor = $request['textColor'];
			$targetModule = $request['targetModule'];
			$fieldName = $request['fieldname'];
			$calendarView = $request['calendar_view'];  // Changed group into calendar_view by Hieu Nguyen on 2019-11-12 to determine which view to load feed
			$mapping = $request['mapping'];
			$conditions = $request['conditions'];
			$result = array();

			// BEGIN-- Modified by Phu Vo on 2020.08.17: Move all data logic to separate file
			switch ($type) {
				case 'Events' :	{
					if ($fieldName == 'date_start,due_date' || $userid) {
						Calendar_Data_Model::pullEvents($start, $end, $result,$userid,$color,$textColor,$calendarView,$conditions);   // Modified param calendarView by Hieu Nguyen on 2019-11-12 to determine which view to load feed
					} else {
						Calendar_Data_Model::pullDetails($start, $end, $result, $type, $fieldName, $color, $textColor, $conditions);
					}
					break;
				}
				case 'Calendar' : {
					if ($fieldName == 'date_start,due_date') {
						Calendar_Data_Model::pullTasks($start, $end, $result,$color,$textColor, $calendarView);  // Added param calendarView by Hieu Nguyen on 2019-11-12 to determine which view to load feed
					} else {
						Calendar_Data_Model::pullDetails($start, $end, $result, $type, $fieldName, $color, $textColor);
					}
					break;
				}
				case 'MultipleEvents' : {
					Calendar_Data_Model::pullMultipleEvents($start,$end, $result,$mapping);
					break;
				}
				case $type : {
					Calendar_Data_Model::pullDetails($start, $end, $result, $type, $fieldName, $color, $textColor);
					break;
				}
			}
			// END-- Modified by Phu Vo on 2020.08.17: Move all data logic to separate file

			return $result; // Modified by Hieu Nguyen on 2019-11-12
		} catch (Exception $ex) {
			return $ex->getMessage();
		}
	}
}