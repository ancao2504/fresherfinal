<?php

/**
 * BaseCustomDashboard
 * Author: Phu Vo
 * Date: 2020.08.26
 */

abstract class Home_BaseCustomDashboard_Model {

    public function getDefaultParams() {
        return [];
    }

    abstract public function getWidgetData($params);

    public function formatNumberToUser($number) {
        if (empty($number)) $number = 0;

        $number = floatval($number);
        $formatedNumber = CurrencyField::convertToUserFormat($number);
        $formatedNumber = $formatedNumber;

        return $formatedNumber;
    }

    public function calcRoi($cost, $revenue) {
        $cost = floatval($cost);
        $revenue = floatval($revenue);
        $roi = 0;

        if (!empty($revenue)) {
            $roi = ($revenue - $cost) / $cost;
        }

        $roi = round($roi, 2);

        return $roi;
    }
}
