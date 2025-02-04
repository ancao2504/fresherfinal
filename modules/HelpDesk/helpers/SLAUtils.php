<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc SLA utils
 */

class HelpDesk_SLAUtils_Helper {
    
    private static function createTicketStatusChangeTable() {
        $tableName = 'helpdesk_ticket_status_change_log';

        if (!Vtiger_Utils::CheckTable($tableName)) {
            $columns = [
                'id' => 'INT',
                'old_status' => 'TEXT',
                'new_status' => 'TEXT',
                'timestamp' => 'TEXT',
                'range_time' => 'INT',
                'user_id' => 'INT'
            ];
            $createTableCols = array_map(function ($col, $type) {
                return "$col $type";
            }, array_keys($columns), array_values($columns));

            $criteria = " (" . implode(', ', $createTableCols) . ")";

            Vtiger_Utils::CreateTable($tableName, $criteria, true);
        }
    } 
    
    public static function saveTicketChangeStatusLog($id, $oldStatus, $newStatus) {
        self::createTicketStatusChangeTable();

        // Get lastest status change time
        global $adb, $current_user;

        $sql = "SELECT MAX(timestamp)
                FROM helpdesk_ticket_status_change_log
                WHERE id = ?
                GROUP BY id";
        $lastestDate = $adb->getOne($sql, [$id]);
        $timeRange = 0; // In minutes
        $currentDate = $adb->formatDate(date('Y-m-d H:i'), true);
        
        if (!empty($lastestDate)) {
            $timeRange = self::getDateTimeDiffInMinutes($lastestDate, $currentDate);
        }

        $insertData = [
            'id' => $id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'timestamp' => $currentDate,
            'range_time' => $timeRange,
            'user_id' => $current_user->id
        ];

        $sql = "INSERT INTO helpdesk_ticket_status_change_log VALUE(" .generateQuestionMarks(array_values($insertData)) . ")";
        $adb->pquery($sql, array_values($insertData));
    }

    public static function getDateTimeDiffInMinutes($start, $end) {
        $start = strtotime($start);
        $end = strtotime($end);
        return round(abs($end - $start) / 60, 2);
    }

    public static function secondsInString($seconds, $translateZero = false) {
        if (empty($seconds)) $seconds = 0;
        if ($seconds == 0) {
            if ($translateZero) return vtranslate('LBL_JUSTNOW');
            return 0;
        }

        $minutes = $hours = $days = $months = $years = 0;

        while ($seconds > 60) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            
            while ($minutes > 60) {
                $hours = floor($minutes / 60);
                $minutes = $minutes % 60;
                
                while ($hours > 24) {
                    $days = floor($hours / 24);
                    $hours = $hours % 24;
                    
                    while ($days > 30) {
                        $months = floor($days / 30);
                        $days = $days % 30;
                        
                        while ($months > 12) {
                            $years = floor($years / 12);
                            $months = $months % 12;
                        }
                    }
                }
            }
        }

        $string = '';
        if ($years > 0) $string .= (' ' . Vtiger_Util_Helper::pluralize($years, 'LBL_YEARS'));
        if ($months > 0) $string .= (' ' . Vtiger_Util_Helper::pluralize($months, 'LBL_MONTH'));
        if ($days > 0) $string .= (' ' . Vtiger_Util_Helper::pluralize($days, 'LBL_DAY'));
        if ($hours > 0) $string .= (' ' . Vtiger_Util_Helper::pluralize($hours, 'LBL_HOUR'));
        if ($minutes > 0) $string .= (' ' . Vtiger_Util_Helper::pluralize($minutes, 'LBL_MINUTE'));
        if ($seconds > 0) $string .= (' ' . Vtiger_Util_Helper::pluralize($seconds, 'LBL_SECOND'));

        $string = trim($string);

        return $string;
    }

    // Tổng thời gian chờ phân công = tổng các khoảng thời gian từ Open => Assigned hoặc ReOpen => Assigned
    public static function getTotalWaitingAssignTime($id) {
        global $adb;

        $sql = "SELECT SUM(range_time) AS minutes
                FROM helpdesk_ticket_status_change_log
                WHERE id = ? AND old_status IN ('Open', 'Reopen') AND new_status = 'Assgined'";
        $minutes = $adb->getOne($sql, [$id]);

        return $minutes ?? false;
    }

    // Tổng thời gian xử lý	= tổng các khoảng thời gian ticket ở trạng thái In Progress
    public static function getTotalProcessingTime($id) {
        global $adb;

        $sql = "SELECT SUM(range_time) AS minutes
                FROM helpdesk_ticket_status_change_log
                WHERE id = ? AND old_status = 'In Progress'";
        $minutes = $adb->getOne($sql, [$id]);

        return $minutes ?? false;
    }

    // Tổng thời gian = tổng thời gian từ khi mở ticket đến lần cập nhật trạng thái gần nhất
    public static function getTotalTime($id) {
        global $adb;
        $minutes = false;

        $sql = "SELECT MIN(timestamp) AS startdate, MAX(timestamp) AS enddate FROM helpdesk_ticket_status_change_log WHERE id = ?";
        $result = $adb->pquery($sql, [$id]);
        
        if ($result) {
            $row = $adb->fetchByAssoc($result);
            $minutes = self::getDateTimeDiffInMinutes($row['startdate'], $row['enddate']);
        }

        return $minutes;
    }

    public static function getStandardProcessingTimeInMinute($codeId) {
        global $adb;

        $sql = "SELECT standard_processing_time, cpslacategory_processing_time_unit
                FROM vtiger_cpslacategory
                INNER JOIN vtiger_crmentity ON crmid = cpslacategoryid AND deleted = 0
                WHERE cpslacategoryid = ?";
        $result = $adb->pquery($sql, [$codeId]);
        $minutes = 0;
        
        if ($result) {
            $row = $adb->fetchByAssoc($result);
            $time = intval($row['standard_processing_time']);
            $unit = $row['cpslacategory_processing_time_unit'];

            switch ($unit) {
                case 'minutes':
                    $minutes = $time;
                    break;
                case 'hours':
                    $minutes = $time * 60;
                    break;
                case 'days':
                    $minutes = $time * 60 * 24;
                    break;
            }
        }
        
        return $minutes;
    }
}