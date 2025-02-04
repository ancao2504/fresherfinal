<?php

/**
 * Author: Phu Vo on 2019
 * Date: 2019.04.12
 * Purpose: External Action Processor
 */
class PBXManager_HandleExternalReport_Action extends Vtiger_Action_Controller {

    var $connector = null;
    
    function __construct() {
        $this->loadActiveConnector();
		$this->exposeMethod('getReport');
    }

    protected function loadActiveConnector() {
        $serverModel = PBXManager_Server_Model::getInstance();
        $this->connector = $serverModel->getConnector();

        if ($this->connector == null) {
            throw new AppException(vtranslate('LBL_CONNECTOR_NOT_FOUND'));
        }
    }

    function checkPermission(Vtiger_Request $request) {
        if(!PBXManager_ExternalReport_Helper::isUserHasPermission()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

	function preProcess(Vtiger_Request $request) {
        parent::preProcess($request);

        $mode = $request->getMode();
        $connectorName = $this->connector->getGatewayName();

        if ($request->get('connector') != $connectorName) {
            // Request connector and active connector must be the same
            throw new AppException(vtranslate('LBL_CONNECTOR_NOT_ACTIVE'));
        }

        if ($mode == 'getReport') {
            // When method getReport be called, check if connector report exist
            $reportMethodName = "get{$connectorName}Report";

            if (!method_exists($this, $reportMethodName)) {
                throw new AppException(vtranslate('LBL_METHOD_NOT_FOUND'));
            }
        }
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

    protected function returnResponse($response) {
        $response = json_encode($response);
        echo($response);
    }

    /**
     * Method to get report row from GrandStream Mixed row
     * @access protected
     * @param Object $mixed mixed row
     * @return Object Row
     */
    protected function getGrandStreamRowFromMixedRow($mixed) {
        $result = new stdClass();

        foreach (get_object_vars($mixed) as $name => $crd) {
            if ($name === 'cdr') {
                // Make sure it will have cdr, which provided as mixed first param by default
                $result->cdr = $crd;
                continue;
            }

            foreach (get_object_vars($crd) as $key => $value) {
                // The first if make sure result object will have enough property so js data table can process
                if (empty($result->$key) && empty($value)) {
                    $result->$key = $value;
                }
                // Case by destinate number
                else if ($key === 'dst' && !empty($value)) {
                    if ($value > $result->dst) $result->dst = $value;
                }
                // By default get new value if it not empty
                else if (!empty($value)) {
                    $result->$key = $value;
                }
            }
        }

        return $result;
    }

    function getReport(Vtiger_Request $request) {
        $connectorName = $this->connector->getGatewayName();
        $reportMethodName = "get{$connectorName}Report";

        $this->$reportMethodName($request);
    }

    protected function getCloudFoneReport(Vtiger_Request $request) {
        $data = $request->getAll();

        // Get Server Model
        $serverModel = PBXManager_Server_Model::getInstance();

        // Process data
        $startDate = DateTime::createFromFormat(DateTimeField::getPHPDateFormat(), $data['date_start']);
        $endDate = DateTime::createFromFormat(DateTimeField::getPHPDateFormat(), $data['date_end']);

        $response = new Vtiger_Response();

        $params = [
            'ServiceName' => $serverModel->get('service_name'),
            'AuthUser' => $serverModel->get('auth_user'),
            'AuthKey' => $serverModel->get('auth_key'),
            'TypeGet' => $data['type_get'],
            'DateStart' => $startDate ? $startDate->format('Y-m-d') : '',
            'DateEnd' => $endDate ? $endDate->format('Y-m-d') : '',
            'CallNum' => $data['call_num'],
            'ReceiveNum' => $data['receive_num'],
            'Key' => $data['key'],
            'PageIndex' => round($data['start'] / $data['length']) + 1,
            'PageSize' => $data['length'],
        ];
        
        $callResult = $this->connector->getHistoryReport([], $params);

        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => intval($callResult->total),
            'recordsFiltered' => intval($callResult->total),
            'data' => $callResult->data,
        ];

        self::returnResponse($response);
    }

    protected function getVoIP24HReport(Vtiger_Request $request) {
        $data = $request->getAll();

        // Get Server Model
        $serverModel = PBXManager_Server_Model::getInstance();

        // Process data
        $startDateTime = DateTime::createFromFormat(DateTimeField::getPHPDateFormat() . ' H:i', $data['date_start'] . ' 00:00');
        $endDateTime = DateTime::createFromFormat(DateTimeField::getPHPDateFormat() . ' H:i', $data['date_end'] . ' 23:59');

        $response = new Vtiger_Response();

        $params = [
            'voip' => $serverModel->get('api_key'),
            'secret' => $serverModel->get('secret_key'),
            'date_start' => $startDateTime->getTimeStamp(),
            'date_end' => $endDateTime->getTimestamp(),
            'search' => $data['search'],
            'source' => $data['source'],
            'destination' => $data['destination'],
            'status' => $data['status'],
            'callid' => $data['callid'],
            'type' => $data['type'],
            'start' => $data['start'],
        ];
        
        $callResult = $this->connector->getHistoryReport([], $params);

        $result = $callResult->result->data;
        $result = $result ?? [];

        $recordsTotal = $callResult->result->recordsTotalAll;
        $recordsFiltered = $callResult->result->recordsDisplay;
        $next = $callResult->result->next;
        $prev = $callResult->result->prev;

        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'next' => $next,
            'prev' => $prev,
            'data' => $result,
        ];

        self::returnResponse($response);
    }

    protected function getGrandStreamReport(Vtiger_Request $request) {
        $data = $request->getAll();

        // Process data
        $startDateTime = DateTime::createFromFormat(DateTimeField::getPHPDateFormat() . ' H:i', $data['date_start'] . ' 00:00');
        $endDateTime = DateTime::createFromFormat(DateTimeField::getPHPDateFormat() . ' H:i', $data['date_end'] . ' 23:59');

        $response = new Vtiger_Response();

        $params = [
            'format' => 'json',
            'numRecords' => $data['length'],
            'offset' => $data['start'],
            'caller' => $data['source'],
            'callee' => $data['destination'],
            'answeredby' => $data['answered_by'],
            'startTime' => $startDateTime ? $startDateTime->format('Y-m-d\TH:i:s') : '',
            'endTime' => $endDateTime ? $endDateTime->format('Y-m-d\TH:i:s') : '',
            'minDur' => $data['min_duration'],
            'maxDur' => $data['max_duration'],
        ];

        // Process empty params
        $requestParams = [];

        foreach ($params as $key => $value) {
            if (!empty($value)) $requestParams[$key] = $value;
        }
        
        $result = $this->connector->getHistoryReport([], $requestParams);
        $result = $result ? $result->cdr_root : [];

        // Process data row has sub cdr
        foreach ($result as $index => $value) {
            if (!empty($value->main_cdr)) $result[$index] = $this->getGrandStreamRowFromMixedRow($value);
        }

        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => 999999999,
            'recordsFiltered' => 999999999,
            'data' => $result,
            'length' => intval($data['length']),
        ];

        self::returnResponse($response);
    }

    public function getFreePBXReport(Vtiger_Request $request) {
        $data = $request->getAll();

        // Default params
        $data['since'] = $data['since'] ? strtotime($data['since']) : 0;
        $data['length'] = $data['length'] ?? 20;
        $data['start'] = $data['start'] ?? 0;

        // Process Params into url string
        $paramString = "both/{$data['since']}/{$data['length']}/{$data['start']}";

        // Call History Report
        $result = $this->connector->getHistoryReport($paramString);

        // [Remove Me]
        if ($request->get('test')) {
            var_dump($paramString);
            var_dump($result);
            die();
        }

        // Process result to display
        $processedResult = [];

        if ($result->status) foreach ($result->data as $record) {
            $type = strtolower($record->type);

            if ($type === 'start') {
                $record->starttime = $record->calldate;
            }
            elseif ($type === 'end') {
                $record->endtime = $record->calldate;
            }

            // Process recording file (find if it have / in recordingfile)
            if ($record->recordingfile && strpos($record->recordingfile, '/') > -1) {
                $record->file = $this->connector->getRecordingUrl($record->recordingfile);
            }
            
            // Process record Data
            if (!in_array($record->uuid, array_keys($processedResult))) {
                $processedResult[$record->uuid] = (array) $record;
            }
            elseif (strtolower($record->type) === 'end') {
                // Filter empty data
                $filteredRecord = array_filter((array) $record);
                $processedResult[$record->uuid] = array_merge($processedResult[$record->uuid], $filteredRecord);
            }

            // Process duration
            if ($processedResult[$record->uuid]['starttime'] && $processedResult[$record->uuid]['endtime']) {
                $duration = $processedResult[$record->uuid]['endtime'] - $processedResult[$record->uuid]['starttime'];
                $processedResult[$record->uuid]['duration'] = $duration >= 0 ? $duration : 0;
            }

            if (!$processedResult[$record->uuid]['duration']) $processedResult[$record->uuid]['duration'] = 0;
        }

        // Convert result to index array
        $processedResult = array_values($processedResult);

        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => 999999999,
            'recordsFiltered' => 999999999,
            'data' => $processedResult,
            'length' => intval($data['length']),
            'offset' => intval($data['start']),
        ];
        
        self::returnResponse($response);
    }

    public function getYeaStarReport(Vtiger_Request $request) {
        global $current_user;
        
        $data = $request->getAll();

        $startTimeObject = DateTime::createFromFormat(DateTimeField::getPHPDateFormat($current_user), $data['starttime']);
        $endTimeObject = DateTime::createFromFormat(DateTimeField::getPHPDateFormat($current_user), $data['endtime']);

        $startTime = $startTimeObject->format('Y-m-d') . ' 00:00:00';
        $endTime = $endTimeObject->format('Y-m-d') . ' 23:23:59';

        $params = [
            'extid' => !empty($data['extid']) ? $data['extid'] : 'all',
            'starttime' => $startTime,
            'endtime' => $endTime,
        ];

        $result = $this->connector->getHistoryReport($params);

        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => 999999999,
            'recordsFiltered' => 999999999,
            'data' => $result,
        ];

        self::returnResponse($response);
    }

    public function getAbenlaReport(Vtiger_Request $request) {
        $data = $request->getAll();

        // Get Server Model
        $serverModel = PBXManager_Server_Model::getInstance();

        // Process data
        $startDate = DateTime::createFromFormat(DateTimeField::getPHPDateFormat(), $data['date_start']);
        $endDate = DateTime::createFromFormat(DateTimeField::getPHPDateFormat(), $data['date_end']);

        $response = new Vtiger_Response();

        $params = [
            'ServiceName' => $serverModel->get('service_name'),
            'AuthUser' => $serverModel->get('auth_user'),
            'AuthKey' => $serverModel->get('auth_key'),
            'TypeGet' => $data['type_get'],
            'DateStart' => $startDate ? $startDate->format('Y-m-d') : '',
            'DateEnd' => $endDate ? $endDate->format('Y-m-d') : '',
            'CallNum' => $data['call_num'],
            'ReceiveNum' => $data['receive_num'],
            'Key' => $data['key'],
            'PageIndex' => round($data['start'] / $data['length']) + 1,
            'PageSize' => $data['length'],
        ];
        
        $callResult = $this->connector->getHistoryReport([], $params);

        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => intval($callResult->total),
            'recordsFiltered' => intval($callResult->total),
            'data' => $callResult->data,
        ];

        self::returnResponse($response);
    }

    protected function getFPTTelecomReport(Vtiger_Request $request) {
        $data = $request->getAll();

        // Process data
        $startDateTime = DateTime::createFromFormat(DateTimeField::getPHPDateFormat() . ' H:i', $data['date_start'] . ' 00:00');
        $endDateTime = DateTime::createFromFormat(DateTimeField::getPHPDateFormat() . ' H:i', $data['date_end'] . ' 23:59');
        $pagination = floor($data['start']/$data['length']) + 1;

        $response = new Vtiger_Response();

        $params = [
            'pagination' => $pagination,
            'pagesize' => $data['length'],
            'start_time' => $startDateTime ? $startDateTime->format('Y-m-d H:i:s') : '',
            'end_time' => $endDateTime ? $endDateTime->format('Y-m-d H:i:s') : '',
            'extension' => $data['extension'],
        ];

        // Process empty params
        $requestParams = [];

        foreach ($params as $key => $value) {
            if (!empty($value)) $requestParams[$key] = $value;
        }
        
        $result = $this->connector->getHistoryReport([], $requestParams);
        
        $response = [
            'draw' => intval($data['draw']),
            'recordsTotal' => 999999999,
            'recordsFiltered' => 999999999,
            'data' => $result->records ?? [],
            'length' => intval($data['length']),
        ];

        self::returnResponse($response);
    }
}