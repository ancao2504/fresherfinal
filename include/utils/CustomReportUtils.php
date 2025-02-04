<?php

/*
    Class CustomReportUtils
    Creator: Hoang Duc
    Date: 2019-1-1
*/

class CustomReportUtils {

    /*
     *  Creator: Hoang Duc
     *  Date: 2019-1-1
     *  Input:
     *      $reportHandler: Object Custom Report
     *      $data: array of data
     *  Purpose: Export report to Excel
     */
    public static function writeReportToExcelFile($reportHandler, $data = array(), $fileName, $advanceFilterSql) {
        require_once('libraries/PHPExcel/PHPExcel.php');
        global $currentModule, $current_language;

        $primaryModule = $reportHandler->primarymodule;
        $modStrings = return_module_language($current_language, $currentModule);
        $numericTypes = array('currency', 'double', 'integer', 'percentage');
        $headerStyles = array(
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E1E0F7')),
        );

        $workBook = new PHPExcel();

        //Set document properties
	    //-- Modified By Kelvin Thang on 2020-05-07 set properties file
        $workBook->getProperties()
            ->setCreator($GLOBALS['productName'])
            ->setLastModifiedBy($GLOBALS['productName'])
            ->setTitle($fileName)
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        //Add export data
        $workSheet = $workBook->setActiveSheetIndex(0);

        $totalXLS = $reportHandler->GenerateReport('XLSX', $advanceFilterSql, false, false, false, 'ExcelExport');

        if (isset($data)) {
            $count = 0;
            $rowCount = 1;

            //copy the first value details
            $exelHeaders = $reportHandler->getReportHeaders();

            foreach ($exelHeaders as $key => $value) {
                // It'll not translate properly if you don't mention module of that string
                if (
                    $key == 'ACTION' ||
                    $key == vtranslate('LBL_ACTION', $primaryModule) ||
                    $key == vtranslate($primaryModule, $primaryModule) . ' ' . vtranslate('LBL_ACTION', $primaryModule)
                ) continue;

                $workSheet->setCellValueExplicitByColumnAndRow($count, $rowCount, decode_html($key), true);
                $workSheet->getStyleByColumnAndRow($count, $rowCount)->applyFromArray($headerStyles);

                $count = $count + 1;
            }

            // Added by Phuc on 2020.04.22 to using custom function for export header
            if ($exelHeaders == false && method_exists($reportHandler, 'getHeaderFromData')) {
                $exelHeaders = $reportHandler->getHeaderFromData($data);
                self::writeCustomReportHeaderToExcelFile($exelHeaders, $workSheet, $rowCount);
            }
            // Ended by Phuc

            $rowCount++;

            foreach ($data as $key => $arrayValue) {
                $count = 0;

                foreach ($arrayValue as $header => $valueDataType) {
                    if (is_array($valueDataType)) {
                        $value = $valueDataType['value'];
                        $dataType = $valueDataType['type'];
                    }
                    else {
                        $value = $valueDataType;
                        $dataType = '';
                    }

                    // It'll not translate properly if you don't mention module of that string
                    if ($header === 'ACTION'
                        || $header === vtranslate('LBL_ACTION', $primaryModule)
                        || $header === vtranslate($primaryModule, $primaryModule) . ' ' . vtranslate('LBL_ACTION', $primaryModule)
                    ) continue; // Updated by Phuc on 2020.04.22 to prevent case header is numberic

                    $value = decode_html($value);

                    if (in_array($dataType, $numericTypes)) {
                        $workSheet->setCellValueExplicitByColumnAndRow($count, $rowCount, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    }
                    else {
                        $workSheet->setCellValueExplicitByColumnAndRow($count, $rowCount, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                    }

                    $count = $count + 1;
                }

                $rowCount++;
            }

            // Summary Total
            $rowCount++;
            $count = 0;

            if (is_array($totalXLS[0])) {
                foreach ($totalXLS[0] as $key => $value) {
                    $explodedKey = explode('_', $key);
                    $labelKey = end($explodedKey);
                    $translatedString = in_array($labelKey, array_keys($modStrings)) ? $modStrings[$labelKey] : $labelKey;

                    $workSheet->setCellValueExplicitByColumnAndRow($count, $rowCount, $translatedString);
                    $workSheet->getStyleByColumnAndRow($count, $rowCount)->applyFromArray($headerStyles);

                    $count = $count + 1;
                }
            }

            $ignoreValues = array('sumcount', 'avgcount', 'mincount', 'maxcount');
            $rowCount++;

            foreach ($totalXLS as $key => $arrayValue) {
                $count = 0;

                foreach ($arrayValue as $header => $value) {
                    if (in_array($header, $ignoreValues)) continue;

                    $value = decode_html($value);
                    $excelDatatype = PHPExcel_Cell_DataType::TYPE_STRING;

                    if (is_numeric($value)) $excelDatatype = PHPExcel_Cell_DataType::TYPE_NUMERIC;

                    $workSheet->setCellValueExplicitByColumnAndRow($count, $key + $rowCount, $value, $excelDatatype);

                    $count = $count + 1;
                }
            }
        }

        // Reference Article:  http://phpexcel.codeplex.com/discussions/389578
        ob_clean();

        // Export
        $workBookWriter = PHPExcel_IOFactory::createWriter($workBook, 'Excel2007');
        $workBookWriter->save($fileName);
    }

    /*
     *  Creator: Hoang Duc
     *  Date: 2019-1-1
     *  Input:
     *      $reportHandler: Object Custom Report
     *      $data: array of data
     *  Purpose: Export report to CSV
     */
    public static function writeReportToCSVFile($reportHandler, $data = array(), $fileName) {
        $filePath = fopen($fileName, 'w+');

        $primaryModule = $reportHandler->primarymodule;
        $reportHandler->getQueryColumnsList($reportHandler->reportid, 'HTML');
        $csvHeader = $reportHandler->getReportHeaders();

        if (isset($data)) {
            // Header
            $csvValues = array_map('decode_html', array_keys($data[0]));
            $unsetValue = false;

            // It'll not translate properly if you don't mention module of that string
            if (
                end($csvValues) == vtranslate('LBL_ACTION', $primaryModule) ||
                end($csvValues) == vtranslate($primaryModule, $primaryModule) . ' ' . vtranslate('LBL_ACTION', $primaryModule)
            ) {
                unset($csvValues[count($csvValues) - 1]); //removed action header in csv file
                $unsetValue = true;
            }

            fputcsv($filePath, $csvHeader);

            foreach ($data as $key => $arrayValue) {
                if ($unsetValue) array_pop($arrayValue); //removed action link

                $csvValues = array_map('decode_html', array_values($arrayValue));
                fputcsv($filePath, $csvValues);
            }
        }

        fclose($filePath);
    }
    
    // Added by Phuc on 2020.04.22 to write custom table header
    public static function writeCustomReportHeaderToExcelFile($headerRows, &$workSheet, &$startRow) {
        $headerStyles = [
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'E1E0F7']],
        ];

        $centerStyle = [
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
        ];

        $startRow--;
        
        foreach ($headerRows as $headerRow) {            
            $startRow++;
            $count = 0;

            foreach ($headerRow as $headerParams) {
                $workSheet->setCellValueExplicitByColumnAndRow($count, $startRow, decode_html($headerParams['label']), true);
                $workSheet->getStyleByColumnAndRow($count, $startRow)->applyFromArray($headerStyles);    

                if (isset($headerParams['merge'])) {
                    $workSheet->getStyleByColumnAndRow($count, $startRow)->applyFromArray($centerStyle);

                    $mergeRow = $startRow + $headerParams['merge']['row'] - 1;
                    $mergeColumn = $count + $headerParams['merge']['column'] - 1;
                    $startColumn = PHPExcel_Cell::stringFromColumnIndex($count);
                    $endColumn = PHPExcel_Cell::stringFromColumnIndex($mergeColumn);
                    $workSheet->mergeCells("{$startColumn}{$startRow}:{$endColumn}{$mergeRow}");
                    $count = $mergeColumn + 1;
                }
                else {
                    $count = $count + 1;
                }
            }
        }
    }
    // Ended by Phuc
}

?>