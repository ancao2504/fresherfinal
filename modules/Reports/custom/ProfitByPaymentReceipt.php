<?php

/*
    ProfitByPaymentReceipt.php
    Author: Phuc Lu
    Date: 2019.09.20
    Purpose: a parent class for custom profit
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class ProfitByPaymentReceipt extends CustomReportHandler {
    // Override
    function writeReportToCSVFile($fileName, $filterlist = '') {
        CustomReportUtils::writeReportToCSVFile($this, $this->getArrayDataForReport(), $fileName);
    }

    // Overide
    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        CustomReportUtils::writeReportToExcelFile($this, $this->getArrayDataForReport(), $tempFileName, $advanceFilterSql);
    }


    function getArrayDataForReport() {
        global $adb;

        // Get filter from screen
        $filter = $this->getFilterFromScreen();

        if ($filter != '') {
            $filter = ' AND '.$filter;
        }

        $sql = "SELECT SUM(amount_vnd) AS amount, COUNT('cppaymentid') AS number_of_rows
        FROM vtiger_cppayment
        INNER JOIN vtiger_crmentity ON (cppaymentid = crmid AND deleted = 0)
        WHERE cppayment_status = 'completed'" . $filter;

        $payments = $adb->pquery($sql, []);
        $payments = $adb->fetchByAssoc($payments);


        $filter = str_replace('vtiger_cppayment', 'vtiger_cpreceipt', $filter);
        $sql = "SELECT SUM(amount_vnd) AS amount, COUNT('cpreceiptid') AS number_of_rows
        FROM vtiger_cpreceipt
        INNER JOIN vtiger_crmentity ON (cpreceiptid = crmid AND deleted = 0)
        WHERE cpreceipt_status = 'completed'" . $filter;

        $receipts = $adb->pquery($sql, []);
        $receipts = $adb->fetchByAssoc($receipts);

        return [[
            'receiptAmount' => CurrencyField::convertToUserFormat($receipts['amount']),
            'paymentAmount' => CurrencyField::convertToUserFormat($payments['amount']),
            'profit' => CurrencyField::convertToUserFormat($receipts['amount'] - $payments['amount']),
        ]];
    }

    function getReportHeaders() {
        return [
            'LBL_TOTAL_RECEIVED' => vtranslate('LBL_TOTAL_RECEIVED', 'PurchaseOrder'),
            'LBL_TOTAL_PAID' => vtranslate('LBL_TOTAL_PAID', 'PurchaseOrder'),
            'LBL_PROFIT' => vtranslate('LBL_PROFIT', 'PurchaseOrder'),
        ];
    }

    function getReportResult($processor, $filterSql, $returnArray = true, $print = false) {
        global $adb;
        $result = ($returnArray) ? [] : '';

        // Init viewer for rows
        $rowViewer = new Vtiger_Viewer();
        $rowViewer->assign('PRINT', $print);

        // Get filter from screen
        $filter = $this->getFilterFromScreen();

        if ($filter != '') {
            $filter = ' AND '.$filter;
        }

        $sql = "SELECT SUM(amount_vnd) AS amount, COUNT('cppaymentid') AS number_of_rows
        FROM vtiger_cppayment
        INNER JOIN vtiger_crmentity ON (cppaymentid = crmid AND deleted = 0)
        WHERE cppayment_status = 'completed'" . $filter;

        $payments = $adb->pquery($sql, []);
        $payments = $adb->fetchByAssoc($payments);


        $filter = str_replace('vtiger_cppayment', 'vtiger_cpreceipt', $filter);
        $sql = "SELECT SUM(amount_vnd) AS amount, COUNT('cpreceiptid') AS number_of_rows
        FROM vtiger_cpreceipt
        INNER JOIN vtiger_crmentity ON (cpreceiptid = crmid AND deleted = 0)
        WHERE cpreceipt_status = 'completed'" . $filter;

        $receipts = $adb->pquery($sql, []);
        $receipts = $adb->fetchByAssoc($receipts);

        return [
            'paymentAmount' => CurrencyField::convertToUserFormat($payments['amount']),
            'receiptAmount' => CurrencyField::convertToUserFormat($receipts['amount']),
            'profit' => CurrencyField::convertToUserFormat($receipts['amount'] - $payments['amount']),
            'numberOfRows' => CurrencyField::convertToUserFormat($receipts['number_of_rows'] + $payments['number_of_rows']),
        ];
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $mainViewer = new Vtiger_Viewer();

        if ($showReportName) {
            $mainViewer->assign('REPORT_NAME', $this->reportname);
        }

        $reportResult = $this->getReportResult(false, $print);

        $mainViewer->assign('REPORT_HEADERS', $this->getReportHeaders());
        $mainViewer->assign('REPORT_RESULT', $reportResult);
        $mainViewer->assign('ROW_COUNT', $reportResult['numberOfRows']);
        $mainViewer->assign('PRIMARY_MODULE', $this->primarymodule);
        $mainViewer->assign('PRINT', $print);

        return $mainViewer->fetch('modules/Reports/tpls/ProfitByPaymentReceipt.tpl');
    }

    function getFilterFromScreen() {
        $filter = $this->_advfiltersql;
        $reportquery = $this->getReportsQuery($this->primarymodule, 'HTML');
        $reportquery = explode(" join ", $reportquery);
        $tableReplace = [];

        foreach ($reportquery as $part) {
            if (substr($part, 0, 7) == 'vtiger_') {
                $part = explode(" on ", $part);
                $part = $part[0];

                if (strpos($part, ' as ') > 0) {
                    $part = explode(' as ', $part);
                    $tableReplace[trim($part[1])] = trim($part[0]);
                }
                else {
                    $tableReplace[trim($part)] = trim($part);
                }
            }
        }

        $find = array_keys($tableReplace);
        $replace = array_values($tableReplace);
        $filter = str_ireplace($find, $replace, $filter);

        return $filter;
    }
}
