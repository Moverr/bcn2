<?php
//print_array($results);

if (isset($notes)) {
    echo info_template($notes);
}



$total_contract_value=array();
$total_estimated_value = array();

$available_procurement_methods=array();

$total_pdes=array();


$completed_contracts = array();
$only_awarded_contracts = array();


foreach( $results as $row){
    $total_contract_value[] = $row['total_actual_payments'];
    $total_estimated_value[] = $row['final_contract_value'];

    //get available procurement methods
    if(!in_array($row['procurement_method'],$available_procurement_methods)){
        $available_procurement_methods[]=$row['procurement_method'];
    }


    //get pdes
    if(!in_array($row['pdeid'],$total_pdes)){
        $total_pdes[]=$row['pdeid'];
    }

    //grab completed contracts
    if ($row['actual_completion_date'] != '') {
        $completed_contracts[] = $row;
    } else {
        $only_awarded_contracts[] = $row;
    }


}


$only_awarded_contracts_total_contract_value = array();
$only_awarded_contracts_total_estimated_value = array();

$only_awarded_contracts_available_procurement_methods = array();

$only_awarded_contracts_total_pdes = array();


foreach ($only_awarded_contracts as $row) {
    $only_awarded_contracts_total_contract_value[] = $row['total_actual_payments'];
    $only_awarded_contracts_total_estimated_value[] = $row['final_contract_value'];

    //get available procurement methods
    if (!in_array($row['procurement_method'], $only_awarded_contracts_available_procurement_methods)) {
        $only_awarded_contracts_available_procurement_methods[] = $row['procurement_method'];
    }


    //get pdes
    if (!in_array($row['pdeid'], $only_awarded_contracts_total_pdes)) {
    }


    $only_awarded_contracts_contracts_by_procurement_method = array();

//print_array($available_procurement_methods);
    foreach ($only_awarded_contracts_available_procurement_methods as $method) {
        //get contracts for each method for the same duration
        $only_awarded_contracts_contracts_by_procurement_method[$method] = get_contracts_by_procurement_method($method, $from, $to,$this->session->userdata('isadmin') == 'Y'?$this->input->post('pde'):$this->session->userdata('pdeid'));
    }




}




$completed_contracts_total_contract_value = array();
$completed_contracts_total_estimated_value = array();

$completed_contracts_available_procurement_methods = array();

$completed_contracts_total_pdes = array();


foreach ($completed_contracts as $row) {
    $completed_contracts_total_contract_value[] = $row['total_actual_payments'];
    $completed_contracts_total_estimated_value[] = $row['final_contract_value'];

    //get available procurement methods
    if (!in_array($row['procurement_method'], $completed_contracts_available_procurement_methods)) {
        $completed_contracts_available_procurement_methods[] = $row['procurement_method'];
    }


    //get pdes
    if (!in_array($row['pdeid'], $completed_contracts_total_pdes)) {
    }


    $completed_contracts_contracts_by_procurement_method = array();

//print_array($available_procurement_methods);

    foreach ($completed_contracts_available_procurement_methods as $method) {
        //get contracts for each method for the same duration
        $completed_contracts_contracts_by_procurement_method[$method] = get_contracts_by_procurement_method($method, $from, $to,$this->session->userdata('isadmin') == 'Y'?$this->input->post('pde'):$this->session->userdata('pdeid'));
    }




}




?>

<div class="">

    <!-------->
    <div id="">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#report_summary" data-toggle="tab">General summary</a></li>

            <li><a href="#report_summary_awarded" data-toggle="tab">Awarded contracts summary</a></li>

            <li><a href="#report_details" data-toggle="tab">Awarded contracts details</a></li>
            <li><a href="#report_summary_completed" data-toggle="tab">Completed contracts summary</a></li>
            <li><a href="#report_completed_contracts" data-toggle="tab">Completed contracts details</a></li>

        </ul>
        <div id="my-tab-content" class="tab-content">
            <div class="tab-pane active" id="report_summary">


                <div id="print_this">
                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
                    } else {
                        echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

                    }
                    ?>
                    <table class="table table-responsive " id="vertical-1">
                        <h3><?=$report_heading?> </h3>

                        <tr>
                            <th>Financial year</th>
                            <td><?=$financial_year?></td>
                            <td></td>
                        </tr>

                        <tr>
                            <th>Reporting Period</th>
                            <td><?=$reporting_period?></td>
                            <td></td>
                        </tr>
                        <?php
                        if($this->input->post('pde')){
                            ?>
                            <tr>
                                <th>PDE</th>
                                <td><?=get_pde_info_by_id($this->input->post('pde'),'title')?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th>Total Contracts</th>
                            <td ><?=count($results)?></td>
                            <td></td>
                        </tr>

                        <?php
                        if($this->session->userdata('isadmin')=='Y'){
                            ?>
                            <tr style="border-bottom: 2px solid #C5C5C5;">
                                <th>Total PDES</th>
                                <td ><?=count($total_pdes)?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>




                        <?php
                        foreach ($available_procurement_methods as $method) {

                            //get values ny type

                            //print_array($contracts_by_procurement_method)
                            ?>
                            <tr>
                                <th><?= get_procurement_method_info_by_id($method, 'title') ?></th>
                                <td>
                                    <?php
                                    $lead_days = array();
                                    foreach ($results as $row) {
                                        if ($row['procurement_method'] == $method) {
                                            $lead_days[] = seconds_to_days(strtotime($row['date_signed']) - strtotime($row['dateofconfirmationoffunds']));
                                            //echo seconds_to_days(strtotime($row['dateofconfirmationoffunds']) - strtotime($row['contract_award_date'])).'<br>';
                                            // print_array($lead_days);
                                        }

                                    }
                                    echo count($lead_days) . '<small> contracts</small>';
                                    ?>



                                </td>
                                <td>Average lead time:
                                    <?= count($lead_days) > 0 ? round_up(array_sum($lead_days) / count($lead_days), 2) . ' <small>days</small>' : '' ?>

                                </td>

                            </tr>
                            <?php
                        }

                        ?>




                    </table>

                </div>
                <p>

                    <a class="btn" href="#" onclick="printContent('print_this')">PRINT</a>
                </p>



            </div>


            <div class="tab-pane" id="report_summary_awarded">


                <div id="print_this_awarded">
                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
                    } else {
                        echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

                    }
                    ?>
                    <table class="table table-responsive " id="vertical-1">
                        <div class="text-center">
                            <h3><?= $report_heading ?> </h3><br>
                            FOR ONLY AWARDED CONTRACTS
                        </div>


                        <tr>
                            <th>Financial year</th>
                            <td><?= $financial_year ?></td>
                            <td></td>
                        </tr>

                        <tr>
                            <th>Reporting Period</th>
                            <td><?= $reporting_period ?></td>
                            <td></td>
                        </tr>
                        <?php
                        if ($this->input->post('pde')) {
                            ?>
                            <tr>
                                <th>PDE</th>
                                <td><?= get_pde_info_by_id($this->input->post('pde'), 'title') ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th>Total Contracts</th>
                            <td><?= count($only_awarded_contracts) ?></td>
                            <td></td>
                        </tr>

                        <?php
                        if ($this->session->userdata('isadmin') == 'Y') {
                            ?>
                            <tr style="border-bottom: 2px solid #C5C5C5;">
                                <th>Total PDES</th>
                                <td><?= count($only_awarded_contracts_total_pdes) ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>




                        <?php
                        foreach ($only_awarded_contracts_available_procurement_methods as $method) {

                            //get values ny type

                            //print_array($contracts_by_procurement_method)
                            ?>
                            <tr>
                                <th><?= get_procurement_method_info_by_id($method, 'title') ?></th>
                                <td>
                                    <?php
                                    $lead_days = array();
                                    foreach ($only_awarded_contracts as $row) {
                                        if ($row['procurement_method'] == $method) {
                                            $lead_days[] = seconds_to_days(strtotime($row['date_signed']) - strtotime($row['dateofconfirmationoffunds']));
                                            //echo seconds_to_days(strtotime($row['dateofconfirmationoffunds']) - strtotime($row['contract_award_date'])).'<br>';
                                            // print_array($lead_days);
                                        }

                                    }
                                    echo count($lead_days) . '<small> contracts</small>';
                                    ?>


                                </td>
                                <td>Average lead time:
                                    <?= count($lead_days) > 0 ? round_up(array_sum($lead_days) / count($lead_days), 2) . ' <small>days</small>' : '' ?>

                                </td>

                            </tr>
                            <?php
                        }

                        ?>


                    </table>

                </div>
                <p>

                    <a class="btn" href="#" onclick="printContent('print_this_awarded')">PRINT</a>
                </p>


            </div>

            <div class="tab-pane" id="yellow">
                <h1>Yellow</h1>
                <p>yellow yellow yellow yellow yellow</p>
            </div>
            <div class="tab-pane" id="report_details">
                <div id="print_excel_area_awarded">
                    <h3>
                        Financial Year : <?= $financial_year ?>
                    </h3>
                    Reporting Period : <?= $reporting_period ?>


                    <p>

                    <div class="page-header text-center">
                        <?php
                        if ($this->session->userdata('isadmin') == 'Y') {
                            echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
                        } else {
                            echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

                        }
                        ?>
                        <?= $report_heading ?>
                        <small>FOR AWARDED CONTRACTS</small>
                    </div>
                    <table id="" class="display_awarded table table-hover dt-responsive ">
                        <thead>
                        <tr>
                            <?php
                            if($this->session->userdata('isadmin')=='Y'){
                                ?>
                                <th>PDE</th>
                                <?php
                            }

                            ?>
                            <th class="hidden-480">Procurement ref.no</th>
                            <th class="hidden-480">Subject of procurement</th>
                            <th class="hidden-480">Method of procurement</th>
                            <th class="hidden-480">Procurement type</th>
                            <th class="hidden-480">Provider</th>
                            <th class="hidden-480">date of approval of Form 5 by AO</th>
                            <th class="hidden-480"> Date signed</th>


                            <th class="hidden-480">Lead time<br>(DAYS)</th>

                            <th class="hidden-480">Estimated Amount<br>(UGX)</th>
                            <th class="hidden-480">Final Amount<br>(UGX)</th>


                        </tr>
                        </thead>
                        <tr>
                            <?php
                            if ($this->session->userdata('isadmin') == 'Y') {
                                ?>
                                <td></td>
                                <?php
                            }

                            ?>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                        </tr>
                        <?php
                        //print_array($results);
                        $total_estimated_value=array();
                        $total_final_contract_value=array();
                        foreach ($only_awarded_contracts as $row) {
                            $total_estimated_value[] = $row['estimated_amount'];
                            $total_final_contract_value[] = $row['amount'] * $row['xrate'];
                            ?>
                            <tr>

                                <?php
                                if($this->session->userdata('isadmin')=='Y'){
                                    ?>
                                    <td><?= get_pde_info_by_id($row['pdeid'], 'title') ?></td>
                                    <?php
                                }

                                ?>
                                <td><?= $row['procurement_ref_no'] ?></td>
                                <td><?= $row['subject_of_procurement'] ?></td>
                                <td><?= get_procurement_method_info_by_id($row['procurement_method'], 'title') ?></td>
                                <td><?= get_procurement_type_info_by_id($row['procurement_type'], 'title') ?></td>
                                <td><?= get_beb_by_bid($row['bidinvitation_id'],'title') ?></td>
                                <td><?= custom_date_format('d.F.Y', $row['dateofconfirmationoffunds']) ?></td>
                                <td>
                                    <?= custom_date_format('d.F.Y', $row['date_signed'] ) ?>
                                </td>


                                <td><?= my_date_diff($row['dateofconfirmationoffunds'], $row['date_signed']) ?></td>
                                <td style="text-align: right; border-top: 1px solid #000;"><?= number_format($row['estimated_amount']) ?></td>
                                <td style="text-align: right; border-top: 1px solid #000;"><?= number_format($row['amount'] * $row['xrate']) ?></td>


                            </tr>
                            <?php
                        }



                        ?>
                        <tr>
                            <?php
                            if($this->session->userdata('isadmin')=='Y'){
                                ?>
                                <td></td>
                                <?php
                            }

                            ?>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="margin-top: 2px #000;"><?= number_format(array_sum($total_estimated_value)) ?></td>
                            <td style="margin-top: 2px "><?= number_format(array_sum($total_final_contract_value))?></td>


                        </tr>



                        </tbody>
                    </table>
                    <b>Total results: <?= count($only_awarded_contracts) ?></b>
                    </p>

                    <hr>

                    <p>

                    <p>
                        <b>Declaration</b>
                    </p>

                    <p>I hereby certify that the above information is a true and accurate record of the procurement and disposal
                        contracts
                        undertaken by the entity </p>

                    <div  class="row">
                        <div  class="span6">
                            <b>Name:......................................</b><br>
                            <b>Signature:.................................</b>
                        </div>

                        <div class="span5 pull-right">
                            <b>Title:......................................</b><br>
                            <b>Date:.......................................</b>
                        </div>
                    </div>




                </div>
                <p>

                    <a class="btn " href="#"
                       onClick="$('.display_awarded').tableExport({type:'excel',escape:'false'});"> Export</a>
                    <a class="btn" href="#" onclick="printContent_excel('print_excel_area_awarded')"> PRINT </a>
                </p>
            </div>


            <div class="tab-pane" id="report_summary_completed">


                <div id="print_this_completed">
                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
                    } else {
                        echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

                    }
                    ?>
                    <table class="table table-responsive " id="vertical-1">
                        <div class="text-center">
                            <h3><?= $report_heading ?> </h3><br>
                            FOR ONLY COMPLETED CONTRACTS
                        </div>


                        <tr>
                            <th>Financial year</th>
                            <td><?= $financial_year ?></td>
                            <td></td>
                        </tr>

                        <tr>
                            <th>Reporting Period</th>
                            <td><?= $reporting_period ?></td>
                            <td></td>
                        </tr>
                        <?php
                        if ($this->input->post('pde')) {
                            ?>
                            <tr>
                                <th>PDE</th>
                                <td><?= get_pde_info_by_id($this->input->post('pde'), 'title') ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th>Total Contracts</th>
                            <td><?= count($completed_contracts) ?></td>
                            <td></td>
                        </tr>

                        <?php
                        if ($this->session->userdata('isadmin') == 'Y') {
                            ?>
                            <tr style="border-bottom: 2px solid #C5C5C5;">
                                <th>Total PDES</th>
                                <td><?= count($completed_contracts_total_pdes) ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>




                        <?php
                        foreach ($completed_contracts_available_procurement_methods as $method) {

                            //get values ny type

                            //print_array($contracts_by_procurement_method)
                            ?>
                            <tr>
                                <th><?= get_procurement_method_info_by_id($method, 'title') ?></th>
                                <td>
                                    <?php
                                    $lead_days = array();
                                    foreach ($completed_contracts as $row) {
                                        if ($row['procurement_method'] == $method) {
                                            $lead_days[] = seconds_to_days(strtotime($row['date_signed']) - strtotime($row['dateofconfirmationoffunds']));
                                            //echo seconds_to_days(strtotime($row['dateofconfirmationoffunds']) - strtotime($row['contract_award_date'])).'<br>';
                                            // print_array($lead_days);
                                        }

                                    }
                                    echo count($lead_days) . '<small> contracts</small>';
                                    ?>


                                </td>
                                <td>Average lead time:
                                    <?= count($lead_days) > 0 ? round_up(array_sum($lead_days) / count($lead_days), 2) . ' <small>days</small>' : '' ?>

                                </td>

                            </tr>
                            <?php
                        }

                        ?>


                    </table>

                </div>
                <p>

                    <a class="btn" href="#" onclick="printContent('print_this_completed')">PRINT</a>
                </p>


            </div>
            <div class="tab-pane" id="report_completed_contracts">
                <div id="print_excel_area_completed">
                    <h3>
                        Financial Year : <?= $financial_year ?>
                    </h3>
                    Reporting Period : <?= $reporting_period ?>


                    <p>

                    <div class="page-header text-center">
                        <?php
                        if ($this->session->userdata('isadmin') == 'Y') {
                            echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
                        } else {
                            echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

                        }
                        ?>
                        <?= $report_heading ?>
                        <small>FOR COMPLETED CONTRACTS</small>
                    </div>
                    <table id="" class="display_completed table table-hover dt-responsive ">
                        <thead>
                        <tr>
                            <?php
                            if ($this->session->userdata('isadmin') == 'Y') {
                                ?>
                                <th>PDE</th>
                                <?php
                            }

                            ?>
                            <th class="hidden-480">Procurement ref.no</th>
                            <th class="hidden-480">Subject of procurement</th>
                            <th class="hidden-480">Method of procurement</th>
                            <th class="hidden-480">Procurement type</th>
                            <th class="hidden-480">Provider</th>
                            <th class="hidden-480">date of approval of Form 5 by AO</th>
                            <th class="hidden-480"> Date signed</th>


                            <th class="hidden-480">Lead time<br>(DAYS)</th>

                            <th class="hidden-480">Estimated Amount<br>(UGX)</th>
                            <th class="hidden-480">Final Amount<br>(UGX)</th>


                        </tr>
                        </thead>
                        <tr>
                            <?php
                            if ($this->session->userdata('isadmin') == 'Y') {
                                ?>
                                <td></td>
                                <?php
                            }

                            ?>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                        </tr>
                        <?php
                        //print_array($results);
                        $total_estimated_value = array();
                        $total_final_contract_value = array();
                        foreach ($completed_contracts as $row) {
                            $total_estimated_value[] = $row['estimated_amount'];
                            $total_final_contract_value[] = $row['amount'] * $row['xrate'];
                            ?>
                            <tr>

                                <?php
                                if ($this->session->userdata('isadmin') == 'Y') {
                                    ?>
                                    <td><?= get_pde_info_by_id($row['pdeid'], 'title') ?></td>
                                    <?php
                                }

                                ?>
                                <td><?= $row['procurement_ref_no'] ?></td>
                                <td><?= $row['subject_of_procurement'] ?></td>
                                <td><?= get_procurement_method_info_by_id($row['procurement_method'], 'title') ?></td>
                                <td><?= get_procurement_type_info_by_id($row['procurement_type'], 'title') ?></td>
                                <td><?= get_beb_by_bid($row['bidinvitation_id'],'title') ?></td>
                                <td><?= custom_date_format('d.F.Y', $row['dateofconfirmationoffunds']) ?></td>
                                <td>
                                    <?= custom_date_format('d.F.Y', $row['date_signed']) ?>
                                </td>


                                <td><?= my_date_diff($row['dateofconfirmationoffunds'], $row['date_signed']) ?></td>
                                <td style="text-align: right;"><?= number_format($row['estimated_amount']) ?></td>
                                <td style="text-align: right;"><?= number_format($row['amount'] * $row['xrate']) ?></td>


                            </tr>
                            <?php
                        }



                        ?>
                        <tr>
                            <?php
                            if ($this->session->userdata('isadmin') == 'Y') {
                                ?>
                                <td></td>
                                <?php
                            }

                            ?>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="margin-top: 2px #000;"><?= number_format(array_sum($total_estimated_value)) ?></td>
                            <td style="margin-top: 2px "><?= number_format(array_sum($total_final_contract_value)) ?></td>


                        </tr>


                        </tbody>
                    </table>
                    <b>Total results: <?= count($completed_contracts) ?></b>
                    </p>

                    <hr>

                    <p>

                    <p>
                        <b>Declaration</b>
                    </p>

                    <p>I hereby certify that the above information is a true and accurate record of the procurement and
                        disposal
                        contracts
                        undertaken by the entity </p>

                    <div  class="row">
                        <div class="span6">
                            <b>Name:......................................</b><br>
                            <b>Signature:.................................</b>
                        </div>

                        <div class="span5 pull-right">
                            <b>Title:......................................</b><br>
                            <b>Date:.......................................</b>
                        </div>
                    </div>



                </div>
                <p>

                    <a class="btn " href="#"
                       onClick="$('.display_completed').tableExport({type:'excel',escape:'false'});"> Export</a>
                    <a class="btn" href="#" onclick="printContent_excel('print_excel_area_completed')"> PRINT </a>
                </p>
            </div>

            <div class="tab-pane" id="blue">
                <h1>Blue</h1>
                <p>blue blue blue blue blue</p>
            </div>
        </div>
    </div>

    <script>
        function printContent_excel(el) {
            var restorepage = document.body.innerHTML;
            var printcontent = document.getElementById(el).innerHTML;
            document.body.innerHTML = printcontent;
            window.print();
            document.body.innerHTML = restorepage;
        }
        function printContent(el) {
            var restorepage = document.body.innerHTML;
            var printcontent = document.getElementById(el).innerHTML;
            document.body.innerHTML = printcontent;
            window.print();
            document.body.innerHTML = restorepage;
        }
    </script>
