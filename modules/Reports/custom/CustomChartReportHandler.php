<?php
require_once('modules/Reports/custom/CustomReportHandler.php');

/*
    CustomChartReportHandler
    Author: Hieu Nguyen
    Date: 2021-06-02
    Purpose: process backend data to render chart for chart report using HighCharts libraries
*/

class CustomChartReportHandler extends CustomReportHandler {

    protected $chartchartReportModel = null;
    protected $reportModel = null;
    protected $chartTemplate = 'modules/Reports/tpls/CustomChartReportChartWidget.tpl';

    public function setChartReportModel($chartReportModel) {
        $this->chartReportModel = $chartReportModel;
        $this->reportModel = $chartReportModel->getParent();
    }

    protected function getChartData(array $params) {
        $chartType = $this->chartReportModel->getChartType();
        $chartData = $this->chartReportModel->getData();
        $chartData = $this->convertToHighChartFormat($chartType, $chartData);
        return $chartData;
    }

    protected function convertToHighChartFormat($chartType, array $chartData) {
        $data = [
            'chart_title' => decodeUTF8($chartData['graph_label']),
            'chart_type' => $chartData['type'],
            'drilldown' => true,
        ];

        if (strtoupper($chartType) == 'PIECHART') {
            $data['chart_type'] = 'pie';
            $data['series_data'] = [];

            foreach ($chartData['values'] as $i => $value) {
                $data['series_data'][] = [
                    'name' => decodeUTF8($chartData['labels'][$i]),
                    'link' => $chartData['links'][$i],
                    'y' => $value,
                ];
            }
        }
        else if (strtoupper($chartType) == 'VERTICALBARCHART') {
            if (strtoupper($chartData['type']) == 'SINGLEBAR') {
                $data['chart_type'] = 'single_column';
                $data['categories'] = decodeUTF8($chartData['labels']);
                $data['categories_link'] = $chartData['links'];
                $data['series_name'] = $chartData['data_labels'][0];
                $data['series_data'] = $chartData['values'];
            }
            else if (strtoupper($chartData['type']) == 'MULTIBAR') {
                $data['chart_type'] = 'multi_columns';
                $data['categories'] = decodeUTF8($chartData['labels']);
                $data['categories_link'] = $chartData['links'];
                $data['series_names'] = $chartData['data_labels'];
                $data['series_datas'] = [];

                foreach ($chartData['data_labels'] as $i => $label) {
                    foreach ($chartData['values'] as $j => $values) {
                        $data['series_datas'][$i][] = $values[$i];
                    }
                }
            }
        }
        else if (strtoupper($chartType) == 'HORIZONTALBARCHART') {
            if (strtoupper($chartData['type']) == 'SINGLEBAR') {
                $data['chart_type'] = 'single_bar';
                $data['categories'] = decodeUTF8($chartData['labels']);
                $data['categories_link'] = $chartData['links'];
                $data['series_name'] = $chartData['data_labels'][0];
                $data['series_data'] = [];

                foreach ($chartData['values'] as $i => $value) {
                    $data['series_data'][] = $value[0];
                }
            }
            else if (strtoupper($chartData['type']) == 'MULTIBAR') {
                $data['chart_type'] = 'multi_bars';
                $data['categories'] = decodeUTF8($chartData['labels']);
                $data['categories_link'] = $chartData['links'];
                $data['series_names'] = $chartData['data_labels'];
                $data['series_datas'] = [];

                foreach ($chartData['data_labels'] as $i => $label) {
                    foreach ($chartData['values'] as $j => $values) {
                        $data['series_datas'][$i][] = $values[$i];
                    }
                }
            }
        }
        else if (strtoupper($chartType) == 'LINECHART') {
            if (strtoupper($chartData['type']) == 'SINGLEBAR') {
                $data['chart_type'] = 'single_line';
                $data['categories'] = decodeUTF8($chartData['labels']);
                $data['categories_link'] = $chartData['links'];
                $data['series_name'] = $chartData['data_labels'][0];
                $data['series_data'] = [];

                foreach ($chartData['values'] as $i => $value) {
                    $data['series_data'][] = $value[0];
                }
            }
            else if (strtoupper($chartData['type']) == 'MULTIBAR') {
                $data['chart_type'] = 'multi_lines';
                $data['categories'] = decodeUTF8($chartData['labels']);
                $data['categories_link'] = $chartData['links'];
                $data['series_names'] = $chartData['data_labels'];
                $data['series_datas'] = [];

                foreach ($chartData['data_labels'] as $i => $label) {
                    foreach ($chartData['values'] as $j => $values) {
                        $data['series_datas'][$i][] = $values[$i];
                    }
                }
            }
        }

        // Do not support drilldown for report that join more than 1 modules
        if ($this->reportModel->getSecondaryModules()) {
            $data['drilldown'] = false;
        }

        return $data;
    }

    function display() {
        $params = $_REQUEST;
        $chart = $this->renderChart($params);
        echo $chart;
    }
}
