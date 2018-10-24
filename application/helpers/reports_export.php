<?php
set_time_limit(0);                   // ignore php timeout
ignore_user_abort(true);             // keep on going even if user pulls the plug*
while(ob_get_level())ob_end_clean(); // remove output buffers (OPTIONAL)
ob_implicit_flush(true);             // output stuff directly (OPTIONAL)

class Reports_export extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();
        $this->load->model('procurement_plan_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('contracts_m');
        $this->load->model('contract_price_m');
        $this->load->model('bid_invitation_m');
        $this->load->model('remoteapi_m');
        $this->load->model('disposal_m');
        $this->load->model('special_procurement_m');

    }

    /*
       INITILIZATION 
    */
    function index()
    {
        //if form is posted
        if($_POST){

            #by default financial year is assumed empty
            $financial_year='';



            # switch logic based on form type
            switch($this->input->post('report_type')){

                # case of awarded contracts
                case 'special_procurements':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Special Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Provider')
                        ->setCellValue('F5', 'Date of award of contract')
                        ->setCellValue('G5', 'Market price of the procurement')
                        ->setCellValue('H5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['SP_estimated_amount'] * $row['estimated_payment_rate']>0?$row['estimated_payment_rate']:1;
                        $grand_amount[] = $row['contract_value'] * $row['contract_payment_rate']>0?$row['contract_payment_rate']:1;

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_reference_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'Special Procurement' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['SP_provider_name'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['SP_estimated_amount'] * $row['estimated_payment_rate']>0?$row['estimated_payment_rate']:1 );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contract_value'] * $row['contract_payment_rate']>0?$row['contract_payment_rate']:1 );







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+3);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($total_count+3),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($total_count+3),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Special Procurements '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Special Procurements")
                        ->setDescription("Auto generated report on special procurements")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Special Procurement')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Special Procurements');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );









                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of awarded contracts
                case 'awarded_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                    //filter out completed call_off_orders
                    $results=array();
                    foreach($results_all as $row){
                        if($row['call_off_order_id']==''){
                            if(!in_array($row,$results)){
                                $results[]=$row;
                            }

                        }else{
                            # for call off orders filter out only awarded
                            if($row['call_off_order_status']=='awarded'){
                                if(!in_array($row,$results)){
                                    $results[]=$row;
                                }
                            }
                        }

                    }

                    $results = unique_multidim_array($results,'id');


                    $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                    $total_count=count($results) + count($sp_results);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts (except micro procurements)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'No')
                        ->setCellValue('B5', 'Procurement Reference Number')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Description')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Provider')
                        ->setCellValue('G5', 'Date of award of contract')
                        ->setCellValue('H5', 'Market price of the procurement')
                        ->setCellValue('I5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                   # exit(print_array($results));
                    $num=0;

                    foreach($results as $row)
                    {
                        $num++;
                        $estimated_amount=$row['estimated_amount']*$row['estimated_amount_exchange_rate']==0?1:$row['estimated_amount_exchange_rate'];
                        $actual_amount=$row['amount'] * $row['xrate'];
                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,get_provider_by_procurement($row['procurement_id'] ));
                        # for call off orders use date of call off order
                        if($row['call_off_order_id']>0){
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d M Y', $row['date_of_calloff_orders']) );
                        }else{
                            # for other contracts
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d M Y', $row['date_signed']) );
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($actual_amount) );

                        if($row['lotid']>0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:H$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );
                        }


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$actual_amount;
                        }







                        $x ++;

                    }

                    $y = count($results)?count($results)+count($sp_results):7;



                    foreach($sp_results as $row)
                    {
                        $estimated_amount= str_replace(',','',$row['SP_estimated_amount']) * $row['estimated_payment_rate'];
                        $actual_amount= str_replace(',','',$row['SP_total_payments']) * $row['total_payment_rate'];
                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$y,$y);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$y,$row['SP_procurement_reference_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$y,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$y,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$y,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$y,$row['SP_provider_name']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$y,custom_date_format('d M Y', $row['SP_contract_award_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$y,$estimated_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$y,$actual_amount );




                        # filter out completed contracts

                        $completed_contracts[]=$row;
                        $completed_contracts_amounts[]= $estimated_amount;


                        $y ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$totals_row,(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Awarded Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Awarded Contracts")
                        ->setDescription("Auto generated report on awarded contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts (except micro procurements)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of amended contracts (except micos)
                case 'amended_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_all_varied_awarded_contracts($from, $to, $pde,$financial_year);
                    $total_count=count($results);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Amended of varied contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'No')
                        ->setCellValue('B5', 'Procurement Reference Number')
                        ->setCellValue('C5', 'Amendment or Variation')
                        ->setCellValue('D5', 'Provider')
                        ->setCellValue('E5', 'Date of Amendment or Variation')
                        ->setCellValue('F5', 'Value of Amendment or Variation ')
                        ->setCellValue('G5', 'Revised Contract Value and Currency ');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:G5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();
                    $grand_variation_amount = array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    $num=0;

                    # exit(print_array($results));

                    foreach($results as $row)
                    {
                        $num++;
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }

                        if ($row['price_variation_type'] == 'positive') {
                            $grand_variation_amount[] = ($row['amount'] * $row['xrate']) + $row['amount'];
                        } else {
                            $grand_variation_amount[] = ($row['amount'] * $row['xrate']) - $row['amount'] ;
                        }



//                        ->setCellValue('A5', 'No')
//                        ->setCellValue('B5', 'Procurement Reference Number')
//                        ->setCellValue('C5', 'Amendment or Variation')
//                        ->setCellValue('D5', 'Provider')
//                        ->setCellValue('E5', 'Date of Amendment or Variation')
//                        ->setCellValue('F5', 'Value of Amendment or Variation ')
//                        ->setCellValue('G5', 'Revised Contract Value and Currency ');



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']  );



                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,'Contract Completion date :'.my_date_diff($row['completion_date'], $row['new_planned_date_of_completion']) );

                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d M Y', $row['variation_date']) );
                        if($row['price_variation_type']=='positive'){
                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,'+ '.number_format($row['variation_amount']*$row['variation_amount_rate']) );
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['amount'] * $row['xrate'])+($row['variation_amount']*$row['variation_amount_rate']) );
                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,'+ '.number_format($row['variation_amount']*$row['variation_amount_rate']) );
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['amount'] * $row['xrate'])-($row['variation_amount']*$row['variation_amount_rate']) );
                        }





                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }

                        if($row['lotid']>0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:K$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('J'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Amended Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Amended or varied")
                        ->setDescription("Auto generated report on amended or varied contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Amended or varied contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts (amended or varied)');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts  (amended or varied)');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts  (amended or varied)');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of completed contracts
                case 'completed_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true,'','Y');
                    $results =array();
                    foreach($results_all as $row){
                        if($row['call_off_order_id']==''){
                            $results[]=$row;
                        }else{
                            # for call off orders filter out only awarded
                            if($row['call_off_order_status']=='completed'){
                                $results[]=$row;
                            }
                        }

                    }
                    $total_count=count($results);

                    # exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Completed Contracts (except micro procurements)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'Subject of procurement')
                        ->setCellValue('C5', 'Method of procurement')
                        ->setCellValue('D5', 'Provider')
                        ->setCellValue('E5', 'Date of completion')
                        ->setCellValue('F5', 'Total Amount paid (UGX)')
                        ->setCellValue('G5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,get_provider_by_procurement($row['procurement_id'] ));
                        # for call off orders use date of call off order
                        if($row['call_off_order_id']>0){
                            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d M Y', $row['date_of_calloff_orders']) );
                        }else{
                            # for other contracts
                            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d M Y', $row['date_signed']) );
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,number_format($row['total_actual_payments'] * $row['xrate']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount'] * $row['xrate']) );


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Completed Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Completed Contracts")
                        ->setDescription("Auto generated report on Completed contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Completed Contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of micro procurement contracts
                case 'micro_procurement':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Micro Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'Subject of procurement')
                        ->setCellValue('C5', 'Method of procurement')
                        ->setCellValue('D5', 'Provider')
                        ->setCellValue('E5', 'Date of Contract Completion')
                        ->setCellValue('F5', 'Contract value (UGX)')
                        ->setCellValue('G5', 'Status');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:G5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,get_provider_by_procurement($row['procurement_id']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,($row['completion_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,($row['amount'] * $row['xrate']) );

                        if($row['actual_completion_date']!=''){
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,'Completed' );

                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,'Awarded' );

                        }




                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Micro Procurements '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Micro Procurements")
                        ->setDescription("Auto generated report on Micro Procurements")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Micro Procurement contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # monthly quartely reports
                case 'quarterly_procurements':
                   if($this->input->post('sub_report_type')=='micros'){
                       switch($this->input->post('quarter')){
                           # first quarter
                           case '1':

                               # exit(print_array($_POST));
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-09-30'));
                               $pde=$this->input->post('pde');

                               if($from&&$to){
                                   $financial_year=NULL;
                               }

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,'Y');


                               //filter out local government pdes
                               $results=array();

                               foreach($results_all as $row){

                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){

                                       $results[]=$row;
                                   }

                               }

                               # exit(print_array($results));

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # second quarter
                           case '2':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-10-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-12-31'));
                               $pde=$this->input->post('pde');

                               if($from&&$to){
                                   $financial_year=NULL;
                               }

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,'Y');

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 2')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 2")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # third quarter
                           case '3':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));
                               $pde=$this->input->post('pde');

                               if($from&&$to){
                                   $financial_year=NULL;
                               }

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,'Y');

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 3')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 3")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 3')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # fourth quarter
                           case '4':
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-4-01'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));

                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 4')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 4")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 4')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;
                       }

                   }else{
                       switch($this->input->post('quarter')){
                           # first quarter
                           case '1':

                            # exit(print_array($_POST));
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-09-30'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);


                               //filter out local government pdes
                               $results=array();

                               foreach($results_all as $row){

                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){

                                       $results[]=$row;
                                   }

                               }

                                # exit(print_array($results));

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # second quarter
                           case '2':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-10-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-12-31'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 2')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 2")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # third quarter
                           case '3':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 3')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 3")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 3')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # fourth quarter
                           case '4':
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-4-01'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));

                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $total_count=count($results);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 4')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Method of procurement')
                                   ->setCellValue('E5', 'Provider')
                                   ->setCellValue('F5', 'Date of contract award')
                                   ->setCellValue('G5', 'Contract Value')
                                   ->setCellValue('H5', 'Estimated Contract value (UGX)')                                 
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $grand_total_actual_payments[] = $row['estimated_amount'];
                                   $grand_amount[] = $row['amount'] * $row['xrate'];

                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_provider_by_procurement($row['procurement_id'] ));
                                   # for call off orders use date of call off order
                                   # for other contracts
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['contract_award_date']) );

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['total_actual_payments'];
                                   }







                                   $x ++;

                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 4")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 4')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;
                       }
                   }
                    break;

                # case of micro procurement contracts
                case 'suspended_providers':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $suspended_providers = $this->remoteapi_m->all_providers_suspended();

                    $suspensions_in_range=array();
                    foreach ($suspended_providers as $row) {
                        if(check_in_range($from, $to, $row['sus_start'])){
                            $suspensions_in_range[]=$row;
                        }

                    }

                    //get all ever awarded providers in current contract result sey
                    $suspended_provs_in_result_set=array();
                    $all_contracts=$this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true);

                    foreach ($all_contracts as $res) {

                        foreach($suspensions_in_range as $row){

                            if(strtolower(trim(is_numeric($row['orgid'])?get_provider_info_by_id($row['orgid'],'title'):$row['orgid']))===strtolower(trim(get_provider_by_procurement($res['procurement_ref_id'])))){
                                $suspended_provs_in_result_set[]=$res;

                            }
                        }



                    }
                    $results=$all_contracts;



                    $results_suspended_provider = $suspended_provs_in_result_set;

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts awarded to suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Provider')
                        ->setCellValue('F5', 'Date of award of contract')
                        ->setCellValue('G5', 'Market price of the procurement')
                        ->setCellValue('H5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    # exit(print_array($results_suspended_provider));

                    foreach($results_suspended_provider as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+3);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($total_count+3),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($total_count+3),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='suspended providers '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Contracts awarded to suspended providers")
                        ->setDescription("Auto generated report on Contracts awarded to suspenede providers")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts awarded to suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # totals containers
                    $all_awarded_grand_total_actual_payments=array();
                    $all_awarded_grand_amount=array();



                    foreach($results as $row)
                    {
                        $all_awarded_grand_total_actual_payments[] = $row['estimated_amount'];
                        $all_awarded_grand_amount[] = $row['amount'] * $row['xrate'];




                    }



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($all_awarded_grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total contracts awarded to suspended providers');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($results_suspended_provider));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($results_suspended_provider)/count($results))*100,2).'%'  );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($grand_amount)/array_sum($all_awarded_grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # published best evaluated bidders
                case 'PBEBs':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year);


                    $results = array();

                    # restricted array
                    $restricted_methods=array('10','3','4','8');
                    # filter out restricted methods
                    foreach($results_all as $row){
                        if(!in_array($row['procurement_method'],$restricted_methods)){
                            $results[]=$row;
                        }

                    }


                     # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price')
                        ->setCellValue('I5', 'Invitation date')
                        ->setCellValue('J5', 'Submission deadline')
                        ->setCellValue('K5', 'Date of bid receipt')
                        ->setCellValue('L5', 'Date of BEB notice display');





                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();
                    $total_estimated_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {
                        $total_estimated_amount[]=$row['estimated_amount'];
                        $total_amount[] = ($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,custom_date_format('d-M-Y', $row['display_of_beb_notice']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Published BEBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Published Best Evaluated Bidders")
                        ->setDescription("Auto generated report on Published Best Evaluated Bidders")
                        ->setKeywords('Published Best Evaluated Bidders')
                        ->setCategory('Published Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # TOTAL BIDS WITH INCONSISTENT VALUATION
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with inconsistent evaluation times');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($inconsitent_evalution));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($inconsitent_evalution)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($inconsitent_evalution_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($inconsitent_evalution_amount)/array_sum($total_amount))*100,2).'%' );


                    # BIDS EXCEEDING TIME LINES
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids Exceeding time lines');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($expired_bids)/$total_count)*100,2).'%' );

                    # TODO expired percentages
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($expired_bids_amounts)/array_sum($total_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # published BEBS WHERE estimate is not normally allowed for the method
                case 'methods_not_allowed':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year);


                    $results = array();

                    # exit(print_array($results_all));

                    # filter out restricted methods
                    foreach($results_all as $row){

                        # FOR WORKS
                        if($row['procurement_type ']==3)
                        {
                            # for open domestice or open international bidding
                            if($row['procurement_method']==1 || $row['procurement_method']=2){
                                if($row['estimated_amount'] < 500000000){
                                    $results[]=$row;
                                }
                            }


                            # for restricted domestice or restricted international bidding
                            if($row['procurement_method']==3 || $row['procurement_method']=4){
                                if(!testRange($row['procurement_method'],200000000,500000000)){
                                    $results[]=$row;
                                }
                            }

                            # for RFQ
                            if($row['procurement_method']==7){
                                if(!testRange($row['procurement_method'],10000000,200000000)){
                                    $results[]=$row;
                                }
                            }


                            # for micro procurements
                            if($row['procurement_method']==10){
                                if($row['estimated_amount'] > 10000000){
                                    $results[]=$row;
                                }
                            }
                        }

                        # FOR NON-CONSULTANCY
                        if($row['procurement_type ']==2)
                        {
                            # for open domestice or open international bidding
                            if($row['procurement_method']==1 || $row['procurement_method']=2){
                                if($row['estimated_amount'] < 200000000){
                                    $results[]=$row;
                                }
                            }


                            # for restricted domestice or restricted international bidding
                            if($row['procurement_method']==3 || $row['procurement_method']=4){
                                if(!testRange($row['procurement_method'],100000000,200000000)){
                                    $results[]=$row;
                                }
                            }

                            # for RFQ
                            if($row['procurement_method']==7){
                                if(!testRange($row['procurement_method'],5000000,100000000)){
                                    $results[]=$row;
                                }
                            }



                            # for micro procurements
                            if($row['procurement_method']==10){
                                if($row['estimated_amount'] > 5000000){
                                    $results[]=$row;
                                }
                            }
                        }


                        # FOR CONSULTANCY SERVICES
                        if($row['procurement_type ']==4  )
                        {
                            # for EOI
                            if($row['procurement_method']==11){
                                if($row['estimated_amount'] < 200000000){
                                    $results[]=$row;
                                }
                            }


                            # for Direct procurement
                            if($row['procurement_method']==8){
                                if($row['estimated_amount'] > 200000000){
                                    $results[]=$row;
                                }
                            }

                            # for Direct procurement
                            if($row['procurement_method']==13){
                                if($row['estimated_amount'] > 50000000){
                                    $results[]=$row;
                                }
                            }



                        }



                    }


                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price');





                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();
                    $total_estimated_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {
                        $total_estimated_amount[]=$row['estimated_amount'];
                        $total_amount[] = ($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']) );

                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Unallowed Estimates '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate")
                        ->setDescription("Auto generated report on Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate")
                        ->setKeywords('Published Best Evaluated Bidders')
                        ->setCategory('Published Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # published best evaluated bidders
                case 'exceeding_timelines':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year,$count='',$expired='');


                    $results = array();

                    # print_array($results_all);




                    # filter out restricted methods
                    foreach($results_all as $row){

                        $datetime1 = new DateTime($row['ddate_octhe']);
                        $datetime2 = new DateTime($row['date_oaoterbt_cc']);
                        $interval = $datetime1->diff($datetime2);
                        $days = $interval->format('%a%');

                        # FOR OPEN DOMESTIC SUPPLIES
                        if($row['procurement_method']==2 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR OPEN DOMESTIC WORKS
                        if($row['procurement_method']==2 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR OPEN DOMESTIC NON CONSULTANCY
                        if($row['procurement_method']==2 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }

                        # FOR OPEN DOMESTIC CONSULTANCY
                        if($row['procurement_method']==2 &&$row['procurement_type ']==4 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------------------------





                        # FOR OPEN INTERNATIONAL SUPPLIES
                        if($row['procurement_method']==1 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR OPEN INTERNATIONAL WORKS
                        if($row['procurement_method']==1 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR OPEN INTERNATIONAL non CONSULTANCY
                        if($row['procurement_method']==1 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }

                        # FOR OPEN INTERNATIONAL CONSULTANCY
                        if($row['procurement_method']==1 &&$row['procurement_type ']==4 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------

                        # FOR RESTRICTED DOMESTIC SUPPLIES
                        if($row['procurement_method']==4 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR RESTRICTED DOMESTIC WORKS
                        if($row['procurement_method']==4 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR RESTRICTED DOMESTIC NON CONSULTANCY
                        if($row['procurement_method']==4 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------

                        # FOR RESTRICTED international SUPPLIES
                        if($row['procurement_method']==3 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR RESTRICTED international WORKS
                        if($row['procurement_method']==3 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR RESTRICTED international NON CONSULTANCY
                        if($row['procurement_method']==3 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------

                        # FOR RFQ supplies
                        if($row['procurement_method']==7 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR RFQ WORKS
                        if($row['procurement_method']==7 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR RFQ NON CONSULTANCY
                        if($row['procurement_method']==7 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }


                    }

                    # exit(print_array($results));





                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Evaluations exceeding the  statutory timelines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price')
                        ->setCellValue('I5', 'Evaluation methodology')
                        ->setCellValue('J5', 'Date of commencement of evaluation')
                        ->setCellValue('K5', 'Date of combined evaluation report')
                        ->setCellValue('L5', 'No. of days for evaluation');





                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:M5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();
                    $total_estimated_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {
                        $datetime1 = new DateTime($row['ddate_octhe']);
                        $datetime2 = new DateTime($row['date_oaoterbt_cc']);
                        $interval = $datetime1->diff($datetime2);
                        $days = $interval->format('%a%');
                        $total_estimated_amount[]=$row['estimated_amount'];
                        $total_amount[] = ($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,$row['evaluation_method_name'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,$row['ddate_octhe']!=='0000-00-00 00:00:00'?custom_date_format('d-M-Y', $row['ddate_octhe']):'' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,$row['date_oaoterbt_cc']!=='0000-00-00 00:00:00'?custom_date_format('d-M-Y', $row['date_oaoterbt_cc']):'' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,$days );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Exceeding Timelines '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Evaluations exceeding the  statutory timelines")
                        ->setDescription("Auto generated report on Evaluations exceeding the  statutory timelines")
                        ->setKeywords('Published Best Evaluated Bidders')
                        ->setCategory('Published Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Evaluations exceeding the  statutory timelines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Published bids with Evaluations exceeding the  statutory timelines');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # expired best evaluated bidders
                case 'EBEBs':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year,'',$expired='Y');
                    $results=array();
                    foreach($results_all as $row){
                        if($row['display_of_beb_notice']!='0000-00-00 00:00:00'){
                            $results[]=$row;
                        }
                    }

                    //filter out BEBs with display notices

                    #exit(print_array($results));
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Expired Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price')
                        ->setCellValue('I5', 'Invitation date')
                        ->setCellValue('J5', 'Submission deadline')
                        ->setCellValue('K5', 'Date of bid receipt')
                        ->setCellValue('L5', 'Date of BEB notice display');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings


                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();

                    $total_estimated_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {
                        $total_estimated_amount[]=$row['estimated_amount'];

                        $total_amount[] = ($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,custom_date_format('d-M-Y', $row['display_of_beb_notice']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Expired BEBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





                    # $objPHPExcel->getProperties()->setCreator($creator);
                    # $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Expired Best Evaluated Bidders")
                        ->setDescription("Auto generated report on Expired Best Evaluated Bidders")
                        ->setKeywords('Expired Best Evaluated Bidders')
                        ->setCategory('Expired Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Expired Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL Expired BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Expired contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # TOTAL BIDS WITH INCONSISTENT VALUATION
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with inconsistent evaluation times');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($inconsitent_evalution));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($inconsitent_evalution)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($inconsitent_evalution_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($inconsitent_evalution_amount)/array_sum($grand_amount))*100,2).'%' );


                    # BIDS EXCEEDING TIME LINES
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids Exceeding time lines');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($expired_bids)/$total_count)*100,2).'%' );

                    # TODO expired percentages
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($expired_bids_amounts)/array_sum($grand_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # case of bid awarded to suspened providers
                case 'SBEB':
                    //if online get suspended providers
                    $financial_year=$this->input->post('financial_year');

                    if($financial_year){
                        $from = substr($financial_year, 0, 4);
                        $to = substr($financial_year, 5, 4);
                    }else{
                        $from=$this->input->post('from_date');
                        $to=$this->input->post('to_date');
                    }




                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_attempted_beb_award_to_suspended_provider($from, $to, $pde,$financial_year);


                    # exit(print_array($results));

                    $total_results_amount = array();
                    $total_results_actual_payments=array();



                    $total_count=count($results);

                    # exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs attempting to add bids for suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement method')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Procurement value')
                        ->setCellValue('G5', 'Bidder name');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();

                    //exit(print_array($results));



                    # loop through suspended provider bis
                    foreach($results as $row)
                    {

                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames']==''?'-':$row['providernames'] );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($grand_total_actual_payments)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Suspended BEBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on PDEs attempting to add bids for suspended providers")
                        ->setDescription("Auto generated report on PDEs attempting to add bids for suspended providers")
                        ->setKeywords('PDEs attempting to add bids for suspended providers')
                        ->setCategory('PDEs attempting to add bids for suspended providers');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs attempting to add bids for suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total attempted BEB awards');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_results_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # published Invitation for evaluated bidders
                case 'published_ifbs':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year,$count='',$expired='',$micro='N');

                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Invitation For bids')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Invitation date')
                        ->setCellValue('H5', 'Submission deadline')
                        ->setCellValue('I5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:G5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $expired_bids=array();
                    $expired_bids_amounts=array();


                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_within_period=array();
                    $bid_notices_within_period_amount=array();

                    # exit(print_array($results));

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'] * $row['estimated_amount_currency'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        # calculate thresholds depending on method
                        if($row['procurement_method']==7||$row['procurement_method']==8){
                            # for RFQ and Direct Procurements
                            $threshold=date_to_seconds(get_end_date_from_working_days(5)); // 5 DAYS
                        }else{
                            $threshold=date_to_seconds(get_end_date_from_working_days(10)); // 10 DAYS
                        }


                        // within threshold
                        if (strtotime($row['bid_submission_deadline']) < $threshold) {
                            $bid_notices_within_period[] = $row;
                            $bid_notices_within_period_amount[] = $row['estimated_amount'] * $row['estimated_amount_currency'];

                        }


                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > $threshold) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $row['estimated_amount'] * $row['estimated_amount_currency'];

                        }


                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$row['estimated_amount'] * $row['estimated_amount_currency'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] * $row['estimated_amount_currency'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Published IFBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Published Invitation For bids")
                        ->setDescription("Auto generated report on Published Invitation For bids")
                        ->setKeywords('Published Published Invitation For bids')
                        ->setCategory('Published Published Invitation For bids');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Invitation For Bids')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );


                    # IFBS WITHIN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids with notices within required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($bid_notices_within_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',round_up((count($bid_notices_within_period)/$total_count)*100,2).'%' );


                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($bid_notices_within_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($bid_notices_within_period_amount)/array_sum($total_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;


                # bid submission deadlines
                case 'bid_submission_deadlines':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year);

                    #exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bid Submission Deadlines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Value')
                        ->setCellValue('E5', 'Invitation date')
                        ->setCellValue('F5', 'Submission deadline')
                        ->setCellValue('G5', 'Date Published')
                        ->setCellValue('H5', 'Status');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $expired_bids=array();
                    $expired_bids_amounts=array();


                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_within_period=array();
                    $bid_notices_within_period_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        # calculate thresholds depending on method
                        if($row['procurement_method']==7||$row['procurement_method']==8){
                            # for RFQ and Direct Procurements
                            $threshold=date_to_seconds(get_end_date_from_working_days(5)); // 5 DAYS
                        }else{
                            $threshold=date_to_seconds(get_end_date_from_working_days(10)); // 10 DAYS
                        }


                        // within threshold
                        if (strtotime($row['bid_submission_deadline']) < $threshold) {
                            $bid_notices_within_period[] = $row;
                            $bid_notices_within_period_amount[] = $row['estimated_amount'];

                        }


                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > $threshold) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $row['estimated_amount'];

                        }


                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$row['estimated_amount'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,'Expired' );

                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A'.$x.':H'.$x)
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'E05CC2')
                                        )
                                    )
                                );

                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,'Active' );
                        }


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='IFB Deadlines '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bid Submission Deadlines")
                        ->setDescription("Auto generated report on Bid Submission Deadlines")
                        ->setKeywords('Bid Submission Deadlines')
                        ->setCategory('Bid Submission Deadlines');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bid Submission Deadlines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );


                    # IFBS WITHIN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids with notices within required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($bid_notices_within_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($bid_notices_within_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($bid_notices_within_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($bid_notices_within_period_amount)/array_sum($total_amount))*100,2).'%' );



                    # EXPIRED IFBS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A12','Expired Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B12',count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C12',round_up((count($expired_bids)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D12',number_format(array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E12',round_up((array_sum($expired_bids_amounts)/array_sum($total_amount))*100,2).'%' );


                    # ACTIVE IFBS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A13','Active Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B13',$total_count-count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C13',(round_up((($total_count-count($expired_bids))/$total_count)*100,2)).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D13',number_format(array_sum($total_amount)-array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E13',100-round_up((array_sum($expired_bids_amounts)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # IFBS with notices greater than required period
                case 'bid_notices_greater':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }


                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year,$count='',$expired='',$micro='N',$direct='N');

                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices greater than required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Invitation date')
                        ->setCellValue('H5', 'Submission deadline')
                        ->setCellValue('I5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:I')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();



                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    //exit(print_array($results));

                    $thirty_days=array(1);
                    $twenty_days=array(2,3);
                    $fifteen_days=array(11,12);
                    $twelve_days=array(4);
                    $ten_days=array(11,12);
                    $five_days=array(7);

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];

                        if(in_array($row['procurement_method'],$thirty_days)){
                            $threshold=(get_end_date_from_working_days(30,$row['invitation_to_bid_date'])); // 20 DAYS
                        }

                        if(in_array($row['procurement_method'],$twenty_days)){
                            $threshold=(get_end_date_from_working_days(20,$row['invitation_to_bid_date'])); // 20 DAYS
                        }

                        if(in_array($row['procurement_method'],$fifteen_days)){
                            $threshold=(get_end_date_from_working_days(15,$row['invitation_to_bid_date'])); // 20 DAYS
                        }


                        if(in_array($row['procurement_method'],$twelve_days)){
                            $threshold=(get_end_date_from_working_days(12,$row['invitation_to_bid_date'])); // 20 DAYS
                        }


                        if(in_array($row['procurement_method'],$ten_days)){
                            $threshold=(get_end_date_from_working_days(10,$row['invitation_to_bid_date'])); // 20 DAYS
                        }

                        if(in_array($row['procurement_method'],$five_days)){
                            $threshold=(get_end_date_from_working_days(5,$row['invitation_to_bid_date'])); // 20 DAYS
                        }






                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > strtotime($threshold)) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $row['estimated_amount'];

                        }





                    }




                    foreach($bid_notices_greater_period as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']!=''?$row['procurement_method_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=(count($bid_notices_greater_period)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='IFB Notice Periods '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bids with notices greater than required period")
                        ->setDescription("Auto generated report on Bids with notices greater than required period")
                        ->setKeywords('Bids with notices greater than required period')
                        ->setCategory('Bids with notices greater than required period');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices greater than required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # Bids with notices withing required period
                case 'bid_notices_shorter':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }


                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year,$count='',$expired='',$micro='N',$direct='N');
                    #exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices shorter required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Invitation date')
                        ->setCellValue('H5', 'Submission deadline')
                        ->setCellValue('I5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();



                    $pdes=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_shorter_period=array();
                    $bid_notices_shorter_period_amount=array();

                    # exit(print_array($results));
                    $thirty_days=array(1);
                    $twenty_days=array(2,3);
                    $fifteen_days=array(11,12);
                    $twelve_days=array(4);
                    $ten_days=array(11,12);
                    $five_days=array(7);

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];

                        if(in_array($row['procurement_method'],$thirty_days)){
                            $threshold=(get_end_date_from_working_days(30,$row['invitation_to_bid_date'])); // 20 DAYS
                        }

                        if(in_array($row['procurement_method'],$twenty_days)){
                            $threshold=(get_end_date_from_working_days(20,$row['invitation_to_bid_date'])); // 20 DAYS
                        }

                        if(in_array($row['procurement_method'],$fifteen_days)){
                            $threshold=(get_end_date_from_working_days(15,$row['invitation_to_bid_date'])); // 20 DAYS
                        }


                        if(in_array($row['procurement_method'],$twelve_days)){
                            $threshold=(get_end_date_from_working_days(12,$row['invitation_to_bid_date'])); // 20 DAYS
                        }


                        if(in_array($row['procurement_method'],$ten_days)){
                            $threshold=(get_end_date_from_working_days(10,$row['invitation_to_bid_date'])); // 20 DAYS
                        }

                        if(in_array($row['procurement_method'],$five_days)){
                            $threshold=(get_end_date_from_working_days(5,$row['invitation_to_bid_date'])); // 20 DAYS
                        }






                        # within threshold

                        if (strtotime($row['bid_submission_deadline']) < strtotime($threshold)) {
                            $bid_notices_shorter_period[] = $row;
                            $bid_notices_shorter_period_amount[] = $row['estimated_amount'];

                        }





                    }


                    foreach($bid_notices_shorter_period as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );

                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=(count($bid_notices_shorter_period)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='BID Notice Periods '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bids with notices within required period")
                        ->setDescription("Auto generated report on Bids with notices within required period")
                        ->setKeywords('Bids with notices within required period')
                        ->setCategory('Bids with notices within required period');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices shorter required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES SHORTER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices shorter required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_shorter_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_shorter_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_shorter_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_shorter_period_amount)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;


                # published Invitation for evaluated bids due to close
                case 'bids_due':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');

                    $dOffsets = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
                    $prevMonday = mktime(0,0,0, date("m"), date("d")-array_search(date("l"),$dOffsets), date("Y"));
                    $oneWeek = 3600*24*7;$toSunday = 3600*24*6;



                    for ($i=1;$i<= 1;$i++)
                    {

                        $from = date("Y-m-d",$prevMonday + $oneWeek*$i);
                        $to =date("Y-m-d",$prevMonday + $oneWeek*$i + $toSunday);


                    }

                    $pde=$this->input->post('pde');

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    echo ($to);



                     $results= $this->bid_invitation_m->get_bids_due_to_close($from, $to, $pde,$financial_year);

                    #exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Invitation For bids due to close')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Invitation date')
                        ->setCellValue('H5', 'Submission deadline')
                        ->setCellValue('I5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:G5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $expired_bids=array();
                    $expired_bids_amounts=array();


                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_within_period=array();
                    $bid_notices_within_period_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        # calculate thresholds depending on method
                        if($row['procurement_method']==7||$row['procurement_method']==8){
                            # for RFQ and Direct Procurements
                            $threshold=date_to_seconds(get_end_date_from_working_days(5)); // 5 DAYS
                        }else{
                            $threshold=date_to_seconds(get_end_date_from_working_days(10)); // 10 DAYS
                        }


                        // within threshold
                        if (strtotime($row['bid_submission_deadline']) < $threshold) {
                            $bid_notices_within_period[] = $row;
                            $bid_notices_within_period_amount[] = $row['estimated_amount'];

                        }


                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > $threshold) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $row['estimated_amount'];

                        }


                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$row['estimated_amount'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,custom_date_format('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d-M-Y', $row['bid_dateadded']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Bids due '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bids due to close")
                        ->setDescription("Auto generated report on Bids due to close")
                        ->setKeywords('Bids due to close')
                        ->setCategory('Bids due to close');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids due to close')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Bids due to close');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );


                    # IFBS WITHIN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids with notices within required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($bid_notices_within_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($bid_notices_within_period)/$total_count)*100,2).'%' );


                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($bid_notices_within_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($bid_notices_within_period_amount)/array_sum($total_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # published Invitation for evaluated bids due to close
                case 'late_procurements':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->procurement_plan_entry_m->get_active_procurements($from, $to, $pde,$financial_year);

                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Late Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Value')
                        ->setCellValue('E5', 'Invitation to bid date')
                        ->setCellValue('F5', 'Actual Invitation to bid date');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:G5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $late_procurements=array();
                    $late_procurements_amounts=array();


                    $pdes=array();


                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];


                        //late procurements
                        if(strtotime($row['invitation_to_bid_date'])>strtotime($row['bid_issue_date'])){
                            $late_procurements[]=$row;

                        }


                    }

                    foreach($late_procurements as $row)
                    {
                        $late_procurements_amounts[]=$row['estimated_amount'];

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d-M-Y', $row['bid_issue_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,custom_date_format('d-M-Y', $row['invitation_to_bid_date']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=(count($late_procurements)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:F$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:F$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Late Procurements '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Late Procurements")
                        ->setDescription("Auto generated report on Late Procurements")
                        ->setKeywords('Late Procurements')
                        ->setCategory('Late Procurements');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Late Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total procurements');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # LATER PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Late Procurements');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($late_procurements));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($late_procurements)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($late_procurements_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($late_procurements_amounts)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # PDEs with no procurement plan
                case 'non_compliant':

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');


                    $results= $this->procurement_plan_m->get_pdes_with_no_procurement_plan($financial_year);

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs without procurement plans')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'PDE');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:C5")->getFont()->setBold(true);



                    # data counter
                    $x = 7; // start 2 rows after column headings


                    $pdes=array();


                    foreach($results as $row)
                    {
                        $amounts=$this->procurement_plan_entry_m->get_all_procurements_estimates_by_year($row['pdeid'],$financial_year) ;


                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);


                        $x ++;

                    }


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='PDEs-No plan'.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on PDEs with no Procurement Plan")
                        ->setDescription("Auto generated report on PDEs with no Procurement Plan")
                        ->setKeywords('PDEs with no Procurement Plan')
                        ->setCategory('PDEs with no Procurement Plan');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs with no Procurement Plan')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total PDEs');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8','-');
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # PDEs with  procurement plan
                case 'compliant':

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $pde=$this->input->post('pde');


                    $results= $this->procurement_plan_m->get_pdes_with_procurement_plan($financial_year,$pde);
//                    exit(print_array($_POST));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs with procurement plans')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'PDE')
                        ->setCellValue('B5', 'Number of procurement entries')
                        ->setCellValue('C5', 'Total value of procurement entries')
                        ->setCellValue('D5', 'Date Created');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:C5")->getFont()->setBold(true);



                    # data counter
                    $x = 7; // start 2 rows after column headings


                    $pdes=array();

                    $grand_total= array();



                    foreach($results as $row)
                    {
                        $amounts=$this->procurement_plan_entry_m->get_all_procurements_estimates_by_year($row['pdeid'],$financial_year) ;


                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        $proc_plan=$this->procurement_plan_m->get_plans_by_financial_year($financial_year,$row['pdeid']);


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,count($amounts));
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,array_sum($amounts) );
                        foreach($proc_plan as $plan){
                            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x, custom_date_format('d M Y',$plan['dateadded']));

                        }

                        $grand_total[]=array_sum($amounts);


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:C$totals_row")->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.($totals_row),(array_sum($grand_total)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='PDEs-with plan'.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on PDEs with  Procurement Plan")
                        ->setDescription("Auto generated report on PDEs with Procurement Plan")
                        ->setKeywords('PDEs with  Procurement Plan')
                        ->setCategory('PDEs with Procurement Plan');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs with Procurement Plan')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total PDEs');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8','-');
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of amended contracts
                case 'all_awarded_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year);
                    $total_count=count($results) + count($sp_results);

                    $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);




                    //exit(print_array($sp_results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract price (UGX)')
                        ->setCellValue('I5', 'Date of Contract Award')
                        ->setCellValue('J5', 'Planned Contract Completion Date')
                    ;


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();


                    # print_array($results);exit();


                    foreach($results as $row)
                    {
                        if($row['call_off_contract_value']>0){
                            $grand_total_actual_payments[] = $row['call_off_contract_value'];

                        }else{
                            $grand_total_actual_payments[] = $row['estimated_amount']*$row['estimated_amount_currency'];

                        }

                        $grand_amount[] = $row['amount'] * $row['xrate'];


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,($row['estimated_amount']*$row['estimated_amount_currency']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,get_provider_by_procurement($row['procurement_id']));//TODO PROVIDERS NOT DISPLAYING                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,custom_date_format('d M Y', $row['completion_date']) );





                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }
                        if($row['lotid']>0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:J$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );
                        }

                        if($row['call_off_contract_value']>0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:J$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'FFCCFF')
                                        )
                                    )
                                );
                        }







                        $x ++;

                    }

                    $y = count($results)?count($results)+count($sp_results):7;

                    # exit(print_array($sp_results));

                    foreach($sp_results as $row)
                    {
                        $grand_total_actual_payments[] = str_replace(',','',$row['SP_estimated_amount']);
                        $grand_amount[] = str_replace(',','',$row['SP_total_payments']);


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$y,$row['SP_procurement_reference_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$y,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$y,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$y,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$y,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$y,$row['SP_estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$y,$row['SP_provider_name']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$y,$row['SP_total_payments'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$y,custom_date_format('d M Y', $row['SP_contract_award_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$y,'-' );





                        # filter out completed contracts

                        $completed_contracts[]=$row;
                        $completed_contracts_amounts[]= str_replace(',','',$row['SP_estimated_amount']);


                        $y ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($grand_total_actual_payments)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='All Awarded Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on All Awarded Contracts")
                        ->setDescription("Auto generated report on all awarded contracts")
                        ->setKeywords("Signed contract reports")
                        ->setCategory("Signed Contract Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                # case of not_commenced
                case 'not_commenced':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_all_awarded_not_commenced($from, $to, $pde,$financial_year);

                    $total_count=count($results);

                    //exit(print_array($sp_results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Procurements scheduled to commence (awarded) but have not yet commenced')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract price (UGX)')
                        ->setCellValue('I5', 'Date of display of BEB Notice')
                        ->setCellValue('J5', 'Date of removal of BEB Notice')
                    ;


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:J')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();


                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,($row['estimated_amount']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,get_provider_by_procurement($row['procurement_id']));//TODO PROVIDERS NOT DISPLAYING                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d M Y', $row['display_of_beb_notice']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,custom_date_format('d M Y', $row['beb_expiry_date']) );





                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }

                        if($row['lotid']>0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:J$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Not Commenced '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report Procurements scheduled to commence (awarded) but have not yet commenced")
                        ->setDescription("Auto generated report on Procurements scheduled to commence (awarded) but have not yet commenced")
                        ->setKeywords("Signed contract reports")
                        ->setCategory("Signed Contract Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Procurements scheduled to commence (awarded) but have not yet commenced')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Procurements scheduled to commence (awarded) but have not yet commenced');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );






                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of all completed contracts
                case 'all_completed_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true);
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'All Completed Contract')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price')
                        ->setCellValue('I5', 'Date Of Contract Award')
                        ->setCellValue('J5', 'Planned date of contract completion')
                        ->setCellValue('K5', 'Actual Contract Completion date');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:K5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:K')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,get_provider_by_procurement($row['procurement_id']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,number_format($row['amount'] * $row['xrate']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d.F.Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,custom_date_format('d.F.Y', $row['completion_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,custom_date_format('d.F.Y', $row['actual_completion_date']) );



                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+3);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($total_count+3),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($total_count+3),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);





                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Completed Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on All Completed Contracts")
                        ->setDescription("Auto generated report on All Completed contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'All Completed Contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);


                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of contracts ue for completion
                case 'contracts_due':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_due_for_completion($from, $to, $pde,$financial_year);
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts due for completion ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Estimated value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price')
                        ->setCellValue('I5', 'Date of Contract Award')
                        ->setCellValue('J5', 'Planned Contract Completion Date');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:J')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();


                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,get_provider_by_procurement($row['procurement_id']));//TODO PROVIDERS NOT DISPLAYING
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,custom_date_format('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,custom_date_format('d M, Y', $row['completion_date']) );

                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+3);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$totals_row,(array_sum($grand_total_actual_payments)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Contracts due '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Contracts due for completion ")
                        ->setDescription("Auto generated report on Contracts due for completion ")
                        ->setKeywords("Signed contract reports")
                        ->setCategory("Signed Contract Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts due for completion ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of disposal in first quarter
                case 'first_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                    $to = date('Y-m-d', strtotime($start_year . '-09-30'));

                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (First Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on First Quarter Disposals")
                        ->setDescription("Auto generated report on First Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'First Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of disposal in second quarter
                case 'second_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                    $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));

                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (Second Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Second Quarter Disposals")
                        ->setDescription("Auto generated report on Second Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Second Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of disposal in third quarter
                case 'third_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                    $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));
                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (Third Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Third Quarter Disposals")
                        ->setDescription("Auto generated report on Third Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Third Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                # case of disposal in fourth quarter
                case 'fourth_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year + 1 . '-4-01'));
                    $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));
                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (Fourth Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,custom_date_format('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Fourth Quarter Disposals")
                        ->setDescription("Auto generated report on Fourth Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Fourth Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # attempted awarded to suspended providers
                case 'suspended_beb':

                    //print_array($_POST);

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }


                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $suspended_providers = $this->remoteapi_m->all_providers_suspended();

                    $suspensions_in_range=array();
                    foreach ($suspended_providers as $row) {
                        if(check_in_range($from, $to, $row['sus_start'])){
                            $suspensions_in_range[]=$row;
                        }

                    }


                    # exit(print_array($suspended_providers));
                    //get all ever awarded providers in current contract result sey
                    $suspended_provs_in_result_set=array();
                    $suspended_providers_info=array();
                    $all_contracts=$this->bid_invitation_m->get_active_best_evaluated_bidders($from, $to, $pde,$financial_year,$completed=true);

                    foreach ($all_contracts as $res) {

                        foreach($suspensions_in_range as $row){

                            if(strtolower(trim(is_numeric($row['orgid'])?get_provider_info_by_id($row['orgid'],'title'):$row['orgid']))===strtolower(trim(get_provider_info_by_id($row['providers'],'title')))){
                                $suspended_provs_in_result_set[]=$res;
                                $suspended_providers_info[] = $row;
                            }
                        }
                    }
                    $results=$all_contracts;

                    # exit(print_array($results));

                    $results_suspended_provider = $suspended_provs_in_result_set;

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'BEBs awarded to suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated value (UGX)')
                        ->setCellValue('G5', 'Bidder name');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:F5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('C:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_amount=array();

                    # exit(print_array($results_suspended_provider));

                    foreach($results_suspended_provider as $row)
                    {

                        $grand_amount[] = $row['contractprice'];



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,get_pde_info_by_id($row['pde_id'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,get_procurement_type_info_by_id($row['procurement_type'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,get_provider_info_by_id($row['providers'],'title') );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+3);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.($total_count+3),(array_sum($grand_total_actual_payments)) );
                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='suspended providers '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on BEBs awarded to suspended providers")
                        ->setDescription("Auto generated report on BEBs awarded to suspended providers")
                        ->setKeywords("Suspended provider reports")
                        ->setCategory("Suspended provider reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'BEBs awarded to suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total BEBs awarded to suspended providers');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results_suspended_provider));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;




                default:
                    $data['errors']='Select a report type';
            }
        }else{
            redirect(base_url().'user/dashboard');
        }









    }




}