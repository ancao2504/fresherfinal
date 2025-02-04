<?php

/*
*   ExcelHelper
*   Auhor: Hieu Nguyen
*   Date: 2018-08-24
*   Purpse: To handle all logic for the excel file format
*/

require_once('libraries/PHPExcel/PHPExcel.php');

class ExcelHelper {
	
	static $DEFAULT_VETICAL_ALIGNMENT = PHPExcel_Style_Alignment::VERTICAL_CENTER;
	static $DEFAULT_FONT = array(
		'name' => 'Verdana', 
		'size' => 10
	);

	// Util function to export the given data into excel file and return the saved file path
	static function exportToExcel($rows, $fileName, $autoWidth = false, $borderAll = false){
		// Added by Phu Vo on 2021.01.12 to prevent invalid file name
		// Update 15/10/2021: Escape character ']' in regex string, it causes preg_replace return NULL some how
		$fileName = unUnicode(decodeUTF8($fileName));
		$fileName = preg_replace('/[!@#$%^&*(),.?":{}\|<>\/\\\]/', '-', $fileName);
		// End Phu Vo

		$filePath = 'upload/'. $fileName .'.xlsx';
		if (file_exists($filePath)) unlink($filePath);

		//-- Modified By Kelvin Thang on 2020-05-07 set properties file
		// Init the worksheet
		$phpExcel = new PHPExcel();
		$workbookProperties = $phpExcel->getProperties();
		$workbookProperties->setCreator($GLOBALS['productName']);
		$workbookProperties->setLastModifiedBy($GLOBALS['productName']);
		$workbookProperties->setTitle('Excel file exported by ' . $GLOBALS['productName']);
		$workbookProperties->setSubject('Excel file exported by ' . $GLOBALS['productName']);
		$workbookProperties->setDescription('Excel file exported by ' . $GLOBALS['productName']);
		$phpExcel->setActiveSheetIndex(0);
		$worksheet = $phpExcel->getActiveSheet();
		$worksheet->setTitle('Sheet1');
		$worksheet->getDefaultStyle()->getAlignment()->setVertical(self::$DEFAULT_VETICAL_ALIGNMENT);
		$worksheet->getDefaultStyle()->getFont()->applyFromArray(self::$DEFAULT_FONT);
		$finalColIndex = 0;    // Default is column A
		
		// Assign data for PHPExcel
		for ($rowIndex = 1; $rowIndex <= count($rows); $rowIndex++) {
			$row = $rows[$rowIndex - 1];

			// Write data to each cell in a row
			for ($dataColIndex = 0; $dataColIndex < count($row); $dataColIndex++){
				if ($dataColIndex == 0) $colIndex = $dataColIndex;  // Real column index without the colspan
				
				// Check if there was a conspan in previous cell
				if ($dataColIndex != 0) {
					$prevDataColIndex = $dataColIndex - 1;
					if (is_array($row[$prevDataColIndex]) && isset($row[$prevDataColIndex]['colspan'])) {
						// Real column index should be next to the real previous cell index width the data previous cell colspan added
						$colIndex = ($colIndex - 1) + $row[$prevDataColIndex]['colspan'];
					}        
				}
				
				if (is_array($row[$dataColIndex])) {
					$worksheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $row[$dataColIndex]['value']);
					$cell = $worksheet->getCellByColumnAndRow($colIndex, $rowIndex);
					$cellStyle = $worksheet->getStyleByColumnAndRow($colIndex, $rowIndex);

					// Handle value format
					if (isset($row[$dataColIndex]['format'])) {
						if ($row[$dataColIndex]['format'] == 'string') {
							$cell->setValueExplicit($row[$dataColIndex]['value'], PHPExcel_Cell_DataType::TYPE_STRING);
						}
						else if ($row[$dataColIndex]['format'] == 'url') {
							$cell->getHyperlink()->setUrl($row[$dataColIndex]['value']);
						}
						// Normal number format using PHPExcel format code
						else {
							$cellStyle->getNumberFormat()->setFormatCode($row[$dataColIndex]['format']);
						}
					}
					
					// Handle alignment
					if (isset($row[$dataColIndex]['align'])) {
						$cellStyle->getAlignment()->setHorizontal(self::getAlignment($row[$dataColIndex]['align']));
					}
					
					// Handle background
					if (isset($row[$dataColIndex]['background'])) {
						$background = array(
							'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID, 
								'color' => array('rgb' => $row[$dataColIndex]['background'])
							)
						);
						$cellStyle->applyFromArray($background);
					}
					
					// Handle font color
					if (isset($row[$dataColIndex]['color'])) {
						$color = array(
							'color' => array('rgb' => $row[$dataColIndex]['color']),
						);
						$cellStyle->getFont()->applyFromArray($color);
					}
					
					// Handle font size
					if (isset($row[$dataColIndex]['size'])) {
						$size = array(
							'size' => $row[$dataColIndex]['size'],
						);
						
						$cellStyle->getFont()->applyFromArray($size);
					}
					
					// Handle bold style
					if (isset($row[$dataColIndex]['bold'])) {
						$bold = array(
							'bold' => ($row[$dataColIndex]['bold'] == true),
						);
						
						$cellStyle->getFont()->applyFromArray($bold);
					}
					
					// Handle italic syle
					if (isset($row[$dataColIndex]['italic'])) {
						$italic = array(
							'italic' => ($row[$dataColIndex]['italic'] == true),
						);
						
						$cellStyle->getFont()->applyFromArray($italic);
					}
					
					// Handle underline syle
					if (isset($row[$dataColIndex]['underline'])) {
						$underline = array(
							'underline' => ($row[$dataColIndex]['underline'] == true),
						);
						
						$cellStyle->getFont()->applyFromArray($underline);
					}

					// Handle width
					if (isset($row[$dataColIndex]['width'])) {
						$column = PHPExcel_Cell::stringFromColumnIndex($colIndex);
						$worksheet->getColumnDimension($column)->setWidth($row[$dataColIndex]['width']);       
					}

					// Handle colspan
					if (isset($row[$dataColIndex]['colspan'])) {
						$colSpanStart = PHPExcel_Cell::stringFromColumnIndex($colIndex);
						$colSpanEnd = PHPExcel_Cell::stringFromColumnIndex($colIndex + $row[$dataColIndex]['colspan'] - 1);
						$colSpanRange = $colSpanStart . $rowIndex .':'. $colSpanEnd . $rowIndex;
						$worksheet->mergeCells($colSpanRange);       
					}

					// Handle border
					if (isset($row[$dataColIndex]['border'])) {
						if ($row[$dataColIndex]['border'] == true)
							$cellStyle->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);       
					}

					// Handle freeze pane
					if (isset($row[$dataColIndex]['freeze'])) {
						if ($row[$dataColIndex]['freeze'] == true) {
							$column = PHPExcel_Cell::stringFromColumnIndex($colIndex);
							$worksheet->freezePane($column . $rowIndex);
						}
					}
				}  else {
					$worksheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $row[$dataColIndex]);
				}
				
				// Set final column to the real final column in each row
				if ($finalColIndex < $colIndex) $finalColIndex = $colIndex;
				
				// Increase real column index
				$colIndex++;
			}              
		}
		
		// Set auto width for all columns
		if ($autoWidth == true) {
			for ($colIndex = 0; $colIndex <= $finalColIndex; $colIndex++) {
				$columnName = PHPExcel_Cell::stringFromColumnIndex($colIndex); 
				$worksheet->getColumnDimension($columnName)->setAutoSize(true);
			}
		}
		
		// Set border for all the table
		if ($borderAll == true) {
			$finalRowIndex = count($rows);
			$finalColumnName = PHPExcel_Cell::stringFromColumnIndex($finalColIndex);
			$boderStyle = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
					),
				),
			);
			
			$worksheet->getStyle('A1:'. $finalColumnName . $finalRowIndex)->applyFromArray($boderStyle);
		}
		
		// Save the file in temp folder and return the file path
		$phpExcelWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
		$phpExcelWriter->save($filePath);
		return $filePath;  
	}
	
	// Util function to get PHPExcel alignment by the given user align style
	static function getAlignment($alignStyle) {
		switch ($alignStyle) {
			case 'right': return PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
			case 'center': return PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
			case 'left': return PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
			case 'justify': return PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY;
		}
	}
}