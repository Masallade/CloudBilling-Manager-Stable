<?php
require_once APPPATH . 'libraries/tcpdf/tcpdf.php';

class Tcpdf_lib extends TCPDF {
    public function __construct() {
        parent::__construct();
    }

    // You can add your custom methods here to streamline PDF generation
}
