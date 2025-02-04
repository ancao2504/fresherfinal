<?php

/*
 * Author: Phu Vo
 * Date: 2019.05.10
 * Last Update: 2019.05.10
 * Purpose: Provide a helper to use TCPDF with default params and export pdf from html content,
 * may can transform normal HTML structror to TCPDF compatible HTML
 */

require_once('libraries/tcpdf/config/lang/vn.php');
require_once('libraries/tcpdf/tcpdf.php');

class PDFGenerator {

    /**
     * PDF Object to create doc
     * @access protected
     */
    protected $pdf;

    /**
     * HTML content to generate PDF doc
     * @access protected
     */
    protected $content = '';

    /**
     * PDF Constructur configs when create new PDF Object
     * @access protected
     */
    protected $configs = [];

    /**
     * PDF document params when generate
     * @access protected
     */
    protected $params = [];

    /**
     * Default PDF Constructur configs
     * @access protected
     */
    protected $configsDefault = [
        'orientation' => PDF_PAGE_ORIENTATION,
        'unit' => PDF_UNIT,
        'format' => PDF_PAGE_FORMAT,
        'unicode' => true,
        'encoding' => 'UTF-8'
    ];

    /**
     * Default PDF Document params
     * @access protected
     */
    protected $paramsDefault = [
        'creator' => '',
        'title' => '',
        'header_logo' => '',
        'header_logo_width' => '',
        'header_title' => PDF_HEADER_TITLE,
        'header_string' => PDF_HEADER_STRING,
        'header_font' => [PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN],
        'footer_font' => [PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN],
        'default_monospaced_font' => 'helvetica',
        'margin_header' => PDF_MARGIN_HEADER,
        'margin_footer' => 16,
        'margin_top' => 10,
        'margin_right' => PDF_MARGIN_RIGHT,
        'margin_left' => PDF_MARGIN_RIGHT,
        'print_header' => false,
        'print_footer'=> false,
        'auto_break_page' => true,
        'break_page_margin' => 10,
        'font_family' => 'helvetica',
        'font_style' => '',
        'font_size' => 12,
        'font_file' => '',
    ];

    /**
     * @access public
     * @param $configs Init parameters for TCPDF Object instance
     */
    function __construct(Array $configs = []) {
        $this->_setConfigs($configs);
    }

    /**
     * Can use multiple times to add html content
     * @access public
     * @param string $content
     */
    function addContent($content) {
        $this->content .= $content;
    }

    /**
     * Export PDF File
     * @access public
     * @param string $name File name
     * @param string $outputMode Export mode: 
     * 'I' => Send PDF to the standard output
     * 'D' => Download PDF as file
     * 'F' => Save PDF to a local file
     * 'S' => Returns PDF as a string
     */
	public function generate($name = 'doc.pdf', $outputMode = 'I') {
        $this->_initPDF();
        $this->_settup();

        $this->pdf->AddPage();
        $this->pdf->writeHTML($this->content);

		$this->pdf->Output($name, $outputMode);
    }
    
    /**
     * Method return the PDF object
     * @access public
     * @return TCPDF
     */
    function getPDF() {
        return $this->pdf;
    }
    
    /**
     * Method to set PDF object
     * @access public
     * @param TCPDF $pdf
     */
    function setPDF($pdf) {
        $this->pdf = $pdf;
    }

    /**
     * Method to set PDF params
     * @access public
     * @param Array $params
     */
    function setParams(Array $params = []) {
        return $this->$params = array_merge($this->params, $params);
    }

    /**
     * Method to get PDF params (merged with default params)
     * @access public
     */
    function getParams() {
        return array_merge($this->paramsDefault, $this->params);
    }

    /**
     * Method to setup pdf with saved params
     * @access protected
     */
    protected function _settup() {
        $params = $this->getParams();

        $this->pdf->SetCreator($params['creator']);
        $this->pdf->SetTitle($params['creator']);
        $this->pdf->SetHeaderData($params['header_logo'], $params['header_logo_width'], $params['header_title'], $params['header_string']);
        $this->pdf->SetHeaderFont($params['header_font']);
        $this->pdf->SetFooterFont($params['footer_font']);
        $this->pdf->SetDefaultMonospacedFont($params['default_monospaced_font']);
        $this->pdf->SetFooterMargin($params['margin_footer']);
        $this->pdf->setHeaderMargin($params['margin_header']);
        $this->pdf->SetMargins($params['margin_left'], $params['margin_top'], $params['margin_right']);
        $this->pdf->SetPrintHeader($params['print_header']);
        $this->pdf->SetPrintFooter($params['print_footer']);
        $this->pdf->SetAutoPageBreak($params['auto_break_page'], $params['break_page_margin']);
        $this->pdf->SetFont($params['font_family'], $params['font_style'], $params['font_size'], $params['font_file']);
    }

    /**
     * Method to init PDF Object
     * @access protected
     */
    protected function _initPDF() {
        $configs = array_values($this->_getConfigs());

        return $this->pdf = new TCPDF(...$configs);
    }

    /**
     * Method to set PDF contruct params
     * @access protected
     * @param Array $configs
     */
    protected function _setConfigs($configs = []) {
        return $this->configs = array_merge($this->configs, $configs);
    }

    /**
     * Method to get PDF contruct params
     * @access protected
     */
    protected function _getConfigs() {
        return array_merge($this->configsDefault, $this->configs);
    }
}