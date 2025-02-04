<?php
/*
*	PopupDatatable.php
*	Author: Tin Bui
*	Date: 2022.03.16
*   Purpose: Action controller for emailtemplate datatable popup
*/

class EmailTemplates_PopupDatatable_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
		return true;
	}

    function process(Vtiger_Request $request) {
		global $adb;

        // Default datatable params
        $searchKey = $request->get('search')['value'];
        $limit = empty($request->get('length')) ? 10 : $request->get('length') ;
        $offset = empty($request->get('start')) ? 0 : $request->get('start');
        $select = "SELECT templateid, templatename, subject, description, module, body ";
        $fromAndWhere = "FROM vtiger_emailtemplates WHERE systemtemplate <> 1 AND deleted <> 1 ";
        
        $fitler = "";
        $queryParams = [];

        if (!empty($searchKey)) {
            $fitler .= " AND (templatename LIKE ? OR subject LIKE ? OR description LIKE ? OR module LIKE ?) ";
            
            $queryParams = array_merge($queryParams, [
                'templatename' => "%{$searchKey}%",
                'subject' => "%{$searchKey}%",
                'description' => "%{$searchKey}%",
                'module' => "%{$searchKey}%",
            ]);
        }

        $orderBy = "ORDER BY templateid DESC ";

        $limit = "LIMIT {$limit} OFFSET {$offset} ";

        $sql = $select . $fromAndWhere . $fitler . $orderBy . $limit;

        $result = $adb->pquery($sql, array_values($queryParams));

        if (!empty($result)) {
            $stt = $offset;
            
            while ($row = $adb->fetchByAssoc($result)) {
                $stt++;
                $row['stt'] = $stt;
                $row['module'] = vtranslate($row['module']);
                $row['body'] = html_entity_decode($row['body']);

                $row['templatename'] = "<a href='javascript:void(0);' class='row_templatename'  data-templateid='{$row['templateid']}'>{$row['templatename']}</a>";

                unset($row['body']);
                $data[$row['templateid']] = $row;
            }
        }

        $data = array_values($data);

        // get total cpbooking
        $recordTotal = $adb->getOne("SELECT COUNT(templateid) " . $fromAndWhere . $fitler, array_values($queryParams));

        $response = [
            'draw' => intval($request->get('draw')),
            'recordsTotal' => intval($recordTotal),
            'recordsFiltered' => intval($recordTotal),
            'data' => $data
        ];

        $response = DecodeUTF8($response);

        echo json_encode($response);
    }
}