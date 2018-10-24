<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 5/12/2015
 * Time: 10:17 AM
 */
function pdf_create($html, $report_title='foo', $filename='', $stream=TRUE)
{

    //require_once(APPPATH.'third_party/dompdf/dompdf_config.inc.php');
    $ci=& get_instance();
    //load the profile model
    $ci->load->library('dompdf_gen');

//    $dompdf = new DOMPDF();
//    $dompdf->load_html($html);
//    $dompdf->render();
//    if ($stream) {
//        $dompdf->stream($filename.".pdf");
//    } else {
//        return $dompdf->output();
//    }

    // Convert to PDF
    $ci->dompdf->load_html($html);
    $ci->dompdf->render();
    $ci->dompdf->stream($report_title . ".pdf", array("Attachment" => true));
}