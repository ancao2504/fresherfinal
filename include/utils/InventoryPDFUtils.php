<?php

/**
 * Author: Phu Vo
 * Date: 2019.06.28
 * Purpose: Inventory Util functions to work with pdf file
 */

require_once('include/utils/PDFGenerator.php');
require_once('include/utils/CurrencyUtils.php');
class InventoryPDFUtils {

	/**
	 * Contain the path to PDF template
	 * @access protected
	 */
	protected static $contentTemplatePath = 'modules/Inventory/tpls/PDF.tpl';

    // Create new Object is not allow
    protected function __construct() {
        // Leave it empty for now
    }
    
    /**
     * Export PDF file to download
     * @param Vtiger_Record_Model $record
     */
	
	// Updated by Phuc on 2019.10.24 to add mode and get file name after generation
	// Modified by Tung Nguyen on 2022.07.07
    public static function exportPDF(Vtiger_Record_Model $record, $mode = 'D') {
		$fileName = self::getPDFFileName($record, $mode);

        if (file_exists($fileName)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($fileName).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileName));
            readfile($fileName);

            unlink($fileName);
        }

		return $fileName;
	}
	// Ended by Phuc

	// Implemented by Tung Nguyen on 2022.07.07 to get file export (move code from func exportPDF)
	public static function getPDFFileName($record, $mode) {
		global $wkhtmltopdfPath;

		$moduleName = $record->getModuleName();
		$fileName = $moduleName . '_' . getModuleSequenceNumber($moduleName, $record->get('id')) . '.pdf';
		$filePathHtml = $moduleName . '_' . getModuleSequenceNumber($moduleName, $record->get('id')) . '.html';

		if ($mode == 'F') {
			$fileName = "storage/{$fileName}";
			$filePathHtml = "storage/{$filePathHtml}";
		}

		unlink($fileName);
		$html = self::getHTMLContent($record);

        file_put_contents($filePathHtml, $html);

		if (file_exists($filePathHtml)) {
			$command = $wkhtmltopdfPath . ' --enable-local-file-access --margin-left 0.6in --margin-top 0.4in --margin-right 0.6in --margin-bottom 0.5in ' . $filePathHtml . ' ' . $fileName;

			exec($command);
			unlink($filePathHtml);
		}

		return $fileName;
	}

	static protected function getHTMLContent(Vtiger_Record_Model $record) {
		global $current_user;

        $moduleName = $record->getModuleName();

        $viewer = new Vtiger_Viewer();
        
        // Process Record data
		$entity = $record->getEntity();

		$recordDetails = getAssociatedProducts($moduleName, $entity);

		// Added by Phuc on 2019.10.24 to get charges and taxes
		$recordDetails[1]['final_details']['charges_and_its_taxes'] = $record->getCharges();
		// Ended by Phuc

		// Added by Phuc on 2020.01.20 to get extra summary
		if ($record->getModuleName() == 'Invoice') {
			$extSummary = $record->getSummaryReceivedDetails($record->getId());
			$extSummary['received_symbol'] = CurrencyUtils::formatCurrency(formatPrice($extSummary['received']));
			$extSummary['balance_symbol'] = CurrencyUtils::formatCurrency(formatPrice($extSummary['balance']));
		}
		else {
			$extSummary = [];
		}
		// Ended by Phuc
		
		// Edit by Tung Bui on 2021.09.25
		
		// get tax type
        $finalDetails = $recordDetails[1]['final_details'];
        $taxType = $finalDetails['taxtype'];

		// get contact name
		$contactName = "";
		$contactId = $record->get('contact_id');

		if (!empty($contactId)) {
			$contactRecord = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
			$contactSalutaion = vtranslate($contactRecord->get('salutationtype'));
			$contactFullname = getParentName($record->get('contact_id'));
			if (!empty($contactSalutaion)) $contactName = $contactSalutaion . ". " . $contactFullname;
			else $contactName = $contactFullname;
		}
		
		// Process Company Infos
		$CompanyDetails = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$cityInfoArr = [];
		if (!empty($CompanyDetails->get('state'))) $cityInfoArr[]=$CompanyDetails->get('state');
		if (!empty($CompanyDetails->get('city'))) $cityInfoArr[]=$CompanyDetails->get('city');
		if (!empty($CompanyDetails->get('country'))) $cityInfoArr[]=$CompanyDetails->get('country');
		$cityString = implode(", ", $cityInfoArr);

		// END Edit by Tung Bui on 2021.09.25		

		// Convert money to string vn_vn
		$summaryDetails = self::_getSummaryDetails($recordDetails);
		
		if ($moduleName == 'Invoice') {
			$total = CurrencyField::convertToDBFormat($extSummary['balance']);
		} 
		else {
			$total = CurrencyField::convertToDBFormat($summaryDetails['grand_total']);
		}

		$convertor = new CurrencyUtils();
        $grandTotalString = $convertor->getAmountInWords($total, $current_user->language);
		// End by Khang Phan

		// Process Translate Label
		self::_processTranslateLabel($viewer, $moduleName);

		// Process Assign User Info
		$assignedUserModel = Vtiger_Record_Model::getInstanceById($record->get('assigned_user_id'), 'Users');

		// Process Viewer
		$viewer->assign('RECORD_MODEL', $record);
		$viewer->assign('RECORD_DETAILS', $recordDetails);
		$viewer->assign('CONTACT_NAME', $contactName);

		$viewer->assign('PRODUCTS_DETAILS', self::_getProductsDetails($recordDetails));
		$viewer->assign('SUMMARY_DETAILS', $summaryDetails);
		$viewer->assign('TAX_TYPE', $taxType);
		$viewer->assign('EXT_SUMMARY_DETAILS', $extSummary);
		$viewer->assign('COMPANY_MODEL', $CompanyDetails);
		$viewer->assign('COMPANY_CITY_INFO', $cityString);
		$viewer->assign('ASSIGNED_USER_MODEL', $assignedUserModel);
        $viewer->assign('GRAND_TOTAL_STRING', $grandTotalString);
        $viewer->assign('MODULE_NAME', $moduleName);
        
		$htmlContent = $viewer->fetch(self::$contentTemplatePath);
		
		return $htmlContent;
	}

	/**
	 * Method to process special dynamic label
	 * @access protected
	 */
	protected static function _processTranslateLabel(&$viewer, $moduleName) {
		$mappting = [
			'%target' => vtranslate("SINGLE_{$moduleName}", $moduleName),
		];
		
		$openning = html_entity_decode(replaceKeys(vtranslate('LBL_INVENTORY_PDF_OPENNING', $moduleName), $mappting));
		$ending = html_entity_decode(replaceKeys(vtranslate('LBL_INVENTORY_PDF_ENDING', $moduleName), $mappting));

		$viewer->assign('TXT_OPENNING', $openning);
		$viewer->assign('TXT_ENDING', $ending);
	}

	/**
	 * Method to get products details
	 * @access protected
	 */
	protected static function _getProductsDetails($recordDetails) {
        $productLineItemIndex = 0;
        $finalDetails = $recordDetails[1]['final_details'];
        $result = [];

        // Process by Tax type
        if ($finalDetails['taxtype'] === 'individual') {
            foreach ($recordDetails as $productLineItem) {
                ++$productLineItemIndex;
    
                $discount = $productLineItem["discountTotal{$productLineItemIndex}"];
				$discountPercentage = $productLineItem["discount_percent{$productLineItemIndex}"];

				// Edit by Tung Bui - 25/09/2021 - display discount and tax amount in export template
				$total = $productLineItem["qty{$productLineItemIndex}"] * $productLineItem["listPrice{$productLineItemIndex}"];

				// calculate total after discount
				$priceBeforeDiscount = $total;
				$priceAfterDiscount = $productLineItem["totalAfterDiscount{$productLineItemIndex}"];

				// calculate taxes
				$taxPercentage = 0;
				
				$taxes = $productLineItem['taxes'];
				if (!empty($taxes)) { // Process Tax total
					foreach($taxes as $tax) {
						$taxPercentage += $tax['percentage'];
					}
				}

				$taxAmount = $priceAfterDiscount * ($taxPercentage / 100);
				$netPrice = $priceAfterDiscount + $taxAmount;

                $result[] = [
                    'product_name' => decode_html($productLineItem["productName{$productLineItemIndex}"]),
                    'product_code' => decode_html($productLineItem["hdnProductcode{$productLineItemIndex}"]),
                    'quantity' => $productLineItem["qty{$productLindiscount_final_percenteItemIndex}"],
					'price_symbol' => CurrencyUtils::formatCurrency(formatPrice($productLineItem["listPrice{$productLineItemIndex}"])),
                    'discount_type' => $productLineItem["discount_type{$productLineItemIndex}"],
					'discount' => formatPrice($discount),
					'discount_symbol' => CurrencyUtils::formatCurrency(formatPrice($discount)),
					'discount_percentage' => $discountPercentage,
                    'price_before_discount' => formatPrice($priceBeforeDiscount),
					'price_after_discount_symbol' => CurrencyUtils::formatCurrency(formatPrice($priceAfterDiscount)),
                    'total' => formatPrice($total),
					'tax_amount_symbol' => CurrencyUtils::formatCurrency(formatPrice($taxAmount)),
                    'net_price' => formatPrice($netPrice),
                ];
				// END Edit by Tung Bui - 25/09/2021
            }
        }
        elseif ($finalDetails['taxtype'] === 'group') {
            foreach ($recordDetails as $productLineItem) {
                ++$productLineItemIndex;
    
                $discount = $productLineItem["discountTotal{$productLineItemIndex}"];
                $discountPercentage = $productLineItem["discount_percent{$productLineItemIndex}"];

				// Edit by Tung Bui - 25/09/2021 - display discount and tax amount in export template
				// calculate total after discount
				$priceBeforeDiscount = $productLineItem["qty{$productLineItemIndex}"] * $productLineItem["listPrice{$productLineItemIndex}"];
				$priceAfterDiscount = $productLineItem["totalAfterDiscount{$productLineItemIndex}"];
				$netPrice = $priceAfterDiscount; 
                
                $result[] = [
                    'product_name' => decode_html($productLineItem["productName{$productLineItemIndex}"]),
                    'product_code' => decode_html($productLineItem["hdnProductcode{$productLineItemIndex}"]),
                    'quantity' => $productLineItem["qty{$productLineItemIndex}"],
					'price_symbol' => CurrencyUtils::formatCurrency(formatPrice($productLineItem["listPrice{$productLineItemIndex}"])),
                    'discount_type' => $productLineItem["discount_type{$productLineItemIndex}"],
					'discount' => formatPrice($discount),
					'discount_symbol' => CurrencyUtils::formatCurrency(formatPrice($discount)),
					'discount_percentage' => $discountPercentage,
                    'price_before_discount' => formatPrice($priceBeforeDiscount),
					'price_after_discount_symbol' => CurrencyUtils::formatCurrency(formatPrice($priceAfterDiscount)),
                    'total' => formatPrice($priceAfterDiscount),
					'tax_percentage' => '',
                    'tax_amount' => '',
					'tax_amount_symbol' => '',
                    'net_price' => formatPrice($priceAfterDiscount),
                ];
				// END Edit by Tung Bui - 25/09/2021
            }
        }

		return $result;
	}

	/**
	 * Method to get record summary details
	 * @access protected
	 */
	protected static function _getSummaryDetails($recordDetails) {
		$finalDetails = $recordDetails[1]['final_details'];
		$netTotal = $discount = 0;
		$productLineItemIndex = 0;
		$discountAmount = $finalDetails["discount_amount_final"];
		$discountPercent = $finalDetails["discount_percentage_final"];

		foreach($recordDetails as $productLineItem) {
			++$productLineItemIndex;
			$netTotal += $productLineItem["netPrice{$productLineItemIndex}"];
		}

		// Process discount
		if($finalDetails['discount_type_final'] == 'amount') {
			$discount = $discountAmount;
			$discountFinalPercent = 0;
		} else if($finalDetails['discount_type_final'] == 'percentage') {
            $discountFinalPercent = $discountPercent;
			$discount = (($discountPercent * $finalDetails["hdnSubTotal"]) / 100);
		}

		// Applied by Phuc on 2019 to fix percentage
		// Total Taxs
		$groupTotalTaxPercent = 0;

		foreach ($finalDetails['taxes'] as $tax) {
            $groupTotalTaxPercent += $tax['percentage'];
		}
		
		// Shipping & Handling taxes
		$shTaxPercent = 0;

		if (isset($finalDetails['charges_and_its_taxes'][1])) {
			foreach ($finalDetails['charges_and_its_taxes'][1]['taxes'] as $tax){
				$shTaxPercent += $tax;
			}
		}
		// Ended by Phuc

		if ($finalDetails['taxtype'] === 'individual') {
			$result = [
				'net_total_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['hdnSubTotal'])),
				'discount_symbol' => CurrencyUtils::formatCurrency(formatPrice($discount)),
				'tax' => formatPrice(0),
				'tax_symbol' => formatPrice(0),
				'shipping_charges_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['shipping_handling_charge'])),
				'shipping_tax_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['shipping_handling_charge'] * $shTaxPercent / 100)),
				'adjustment_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['adjustment'])),
				'grand_total' => formatPrice($finalDetails['grandTotal']),
				'grand_total_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['grandTotal'])),
				'discount_final_percent' => formatPrice($discountFinalPercent) ?? formatPrice(0), // Updated by Phuc on 2019.10.24 to display format
				'group_total_tax_percent' => formatPrice(0),
				'sh_tax_percent' => formatPrice($shTaxPercent) ?? formatPrice(0), // Updated by Phuc on 2019.10.24 to display format
			];
		}
		else {
			$result = [
				'net_total_symbol' => CurrencyUtils::formatCurrency(formatPrice($netTotal)),
				'discount_symbol' => CurrencyUtils::formatCurrency(formatPrice($discount)),
				'tax_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['tax_totalamount'])),
				'shipping_charges_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['shipping_handling_charge'])),
				'shipping_tax_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['shipping_handling_charge'] * $shTaxPercent / 100)),
				'adjustment_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['adjustment'])),
				'grand_total' => formatPrice($finalDetails['grandTotal']),
				'grand_total_symbol' => CurrencyUtils::formatCurrency(formatPrice($finalDetails['grandTotal'])),
				'discount_final_percent' => formatPrice($discountFinalPercent) ?? formatPrice(0), // Updated by Phuc on 2019.10.24 to display format
				'group_total_tax_percent' => formatPrice($groupTotalTaxPercent) ?? formatPrice(0), // Updated by Phuc on 2019.10.24 to display format
				'sh_tax_percent' => formatPrice($shTaxPercent) ?? formatPrice(0), // Updated by Phuc on 2019.10.24 to display format
			];
		}

		return $result;
	}

	/**
	 * Method to get Shipping Taxs
	 * @access protected
	 */
	protected static function _getShippingTaxs() {
		global $adb;

		$sql = "SELECT * FROM vtiger_shippingtaxinfo";
		$queryResult = $adb->pquery($sql);

		$result = [];
		while($row = $adb->fetchByAssoc($queryResult)) {
			$result[] = $row;
		}

		return $result;
	}

	/**
	 * Method to get Inventory Taxs
	 * @access protected
	 */
	protected static function _getInventoryTaxs() {
		global $adb;

		$sql = "SELECT * FROM vtiger_inventorytaxinfo";
		$queryResult = $adb->pquery($sql);

		$result = [];
		while($row = $adb->fetchByAssoc($queryResult)) {
			$result[] = $row;
		}

		return $result;
	}
}