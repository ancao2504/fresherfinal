<?php

/*
    BookmarksWidget
    Author: Hieu Nguyen
    Date: 2021-03-29
    Purpose: to display bookmark list in the home page
*/

class Home_BookmarksWidget_Model extends Home_BaseListCustomDashboard_Model {

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'name',
                'label' => vtranslate('LBL_BOOKMARK_NAME', 'Portal'),
            ],
            [
                'name' => 'url',
                'label' => vtranslate('LBL_BOOKMARK_URL', 'Portal'),
            ]
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;
        $data = [];

        $sql = "SELECT * FROM vtiger_portal ORDER BY portalname";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $result = $adb->pquery($sql);
        
        while ($row = $adb->fetchByAssoc($result)) {
            $data[] = [
                'name' => '<a href="'. $row['portalurl'] .'" target="_blank">'. decodeUTF8($row['portalname']) .'</a>',
                'url' => '<a href="'. $row['portalurl'] .'" target="_blank">'. decodeUTF8($row['portalurl']) .'</a>'
            ];
        }

        $totalSql = "SELECT COUNT(portalid) FROM vtiger_portal";
        $total = $adb->getOne($totalSql);

        $result = [
            'draw' => intval($params['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => array_values($data),
            'offset' => $params['start'],
            'length' => $params['length'],
        ];

        return $result;
    }
}