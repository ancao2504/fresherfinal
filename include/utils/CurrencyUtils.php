<?php

/**
 * Author: Phuc
 * Date: 2019.07.18
 * Purpose: Add currency custom function
 */

class CurrencyUtils {

    /**
     * Return currency list by id
     * @param id $currencyId
     * @return Array currency info
     * @author Phuc Lu on 2019.07.18
     */
    static function getCurrencyById($currencyId) {
        $db = PearDatabase::getInstance();

        $query = "SELECT *, CONCAT(currency_name, ' (', currency_symbol, ')') AS currency_label
                    FROM vtiger_currency_info
                    WHERE currency_status = 'Active' AND deleted = 0 AND id = ?";

        $result = $db->pquery($query, [$currencyId]);
        $result = $db->fetchByAssoc($result);

        return $result;
    }

    /**
     * Return currency picklist
     * @return Array currencies
     * @author Phuc Lu on 2019.07.18
     */
    static function getCurrencyPicklistValues() {
        $db = PearDatabase::getInstance();

        $query = "SELECT id, CONCAT(currency_name, ' (', currency_symbol, ')') AS currency_label, conversion_rate
                FROM vtiger_currency_info
                WHERE currency_status = 'Active' AND deleted = 0";
        $values = array(); 
        $result = $db->pquery($query, array());
        $num_rows = $db->num_rows($result);

        for($i=0; $i<$num_rows; $i++) {
            //Need to decode the picklist values twice which are saved from old ui
            $values[$db->query_result($result,$i,'id')] = decode_html(decode_html($db->query_result($result,$i, 'currency_label')));
        }

        return $values;
    }

    /**
     * Return currency amount by locale
     * @return String currencies
     * @author Tien Pham on 2022.01.20
     */
    static function formatCurrency($amount) {
        global $current_user;

        if ($current_user->currency_symbol_placement == '1.0$') {
            $values = $amount . ' ' .  $current_user->currency_symbol;
        }
        else {
            $values = $current_user->currency_symbol . ' ' . $amount;
        }

        return $values;
    }

    /**
     * Return currency amount in words by lang
     * @return String currencies
     * @author Tien Pham on 2022.01.20
     */
    public function getAmountInWords($amount, $lang) {
        global $current_user;

        if (is_numeric($amount)) {
            $sign = $amount >= 0 ? '' : 'Negative ';

            if ($lang == 'vn_vn') {
                $currency = ($current_user->currency_code == 'VND') ?  " đồng." : " dollar.";
                $rs = $sign . $this->toQuadrillions(abs($amount)) . $currency;
                $rs = str_replace('  ', ' ', $rs);
                $rs = ucfirst(mb_strtolower($rs, 'UTF-8'));
                $rs = str_replace('mươi một', 'mươi mốt', $rs);
            }
            else {
                $currency = ($current_user->currency_code == 'VND') ?  " dong." : " dollar.";
                $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                $rs = $sign . ucfirst($f->format($amount)) . $currency;
            }

            return $rs;
        }
        else {
            throw new Exception('Only numeric values are allowed.');
        }
    }

    private function toOnes($amt) {
        $words = array(
            0 => 'Không',
            1 => 'Một',
            2 => 'Hai',
            3 => 'Ba',
            4 => 'Bốn',
            5 => 'Năm',
            6 => 'Sáu',
            7 => 'Bảy',
            8 => 'Tám',
            9 => 'Chín'
        );

        if ($amt >= 0 && $amt < 10)
            return $words[$amt];
        else
            throw new ArrayIndexOutOfBoundsException('Array Index not defined');
    }

    private function toTens($amt) { // handles 10 - 99
        $firstDigit = intval($amt / 10);
        $remainder = $amt % 10;

        if ($firstDigit == 1) {
            $words = array(
                0 => 'Mười',
                1 => 'Mười Một',
                2 => 'Mười Hai',
                3 => 'Mười Ba',
                4 => 'Mười Bốn',
                5 => 'Mười Lăm',
                6 => 'Mười Sáu',
                7 => 'Mười Bảy',
                8 => 'Mười Tám',
                9 => 'Mười Chín'
            );

            return $words[$remainder];
        } 
        elseif ($firstDigit >= 2 && $firstDigit <= 9) {
            $words = array(
                2 => 'Hai Mươi',
                3 => 'Ba Mươi',
                4 => 'Bốn Mươi',
                5 => 'Năm Mươi',
                6 => 'Sáu Mươi',
                7 => 'Bảy Mươi',
                8 => 'Tám Mươi',
                9 => 'Chín Mươi'
            );

            $rest = $remainder == 0 ? '' : $this->toOnes($remainder);
            if ($remainder == 5) $rest = 'lăm';
            
            return $words[$firstDigit] . ' ' . $rest;
        }
        else
            return $this->toOnes($amt);
    }

    private function toHundreds($amt) {
        $ones = intval($amt / 100);
        $remainder = $amt % 100;

        if ($ones >= 1 && $ones < 10) {
            $rest = $remainder == 0 ? '' : $this->toTens($remainder);
            return $this->toOnes($ones) . ' Trăm ' . $rest;
        }
        else
            return $this->toTens($amt);
    }

    private function toThousands($amt) {
        $hundreds = intval($amt / 1000);
        $remainder = $amt % 1000;

        if ($hundreds >= 1 && $hundreds < 1000) {
            $rest = $remainder == 0 ? '' : $this->toHundreds($remainder);
            return $this->toHundreds($hundreds) . ' Nghìn ' . $rest;
        }
        else
            return $this->toHundreds($amt);
    }

    private function toMillions($amt) {
        $hundreds = intval($amt / pow(1000, 2));
        $remainder = $amt % pow(1000, 2);

        if ($hundreds >= 1 && $hundreds < 1000) {
            $rest = $remainder == 0 ? '' : $this->toThousands($remainder);
            return $this->toHundreds($hundreds) . ' Triệu ' . $rest;
        }
        else
            return $this->toThousands($amt);
    }

    private function toBillions($amt) {
        $hundreds = intval($amt / pow(1000, 3));
        /* Note:taking the modulos results in a negative value, but
        this seems to work pretty fine */

        $remainder = $amt - $hundreds * pow(1000, 3);

        if ($hundreds >= 1 && $hundreds < 1000) {
            $rest = $remainder == 0 ? '' : $this->toMillions($remainder);
            return $this->toHundreds($hundreds) . ' Tỷ ' . $rest;
        }
        else
            return $this->toMillions($amt);
    }

    private function toTrillions($amt) {
        $hundreds = intval($amt / pow(1000, 4));
        $remainder = $amt - $hundreds * pow(1000, 4);

        if ($hundreds >= 1 && $hundreds < 1000) {
            $rest = $remainder == 0 ? '' : $this->toBillions($remainder);
            return $this->toHundreds($hundreds) . ' Nghìn Tỷ ' . $rest;
        }
        else
            return $this->toBillions($amt);
    }

    private function toQuadrillions($amt) {
        $hundreds = intval($amt / pow(1000, 5));
        $remainder = $amt - $hundreds * pow(1000, 5);

        if ($hundreds >= 1 && $hundreds < 1000) {
            $rest = $remainder == 0 ? '' : $this->toTrillions($remainder);
            return $this->toHundreds($hundreds) . ' Nghìn Triệu Triệu ' . $rest;
        }
        else
            return $this->toTrillions($amt);
    }
 }