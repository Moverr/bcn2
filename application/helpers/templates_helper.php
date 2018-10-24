<?php
/**
 * Created by PhpStorm.
 * User: GADGETS 0752423205
 * Date: 7/13/2015
 * Time: 2:10 AM
 */

function template_awarded_contracts($report_heading,$financial_year,$reporting_period,$results){
    $ci=& get_instance();
    ob_start();
    ?>

    <style type="text/css">
        .tg  {border-collapse:collapse;border-spacing:0;border-color:#aabcfe;margin:0px auto;}
        .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#aabcfe;color:#669;background-color:#e8edff;}
        .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#aabcfe;color:#039;background-color:#b9c9fe;}
        .tg .tg-0ord{text-align:right}
        .tg .tg-ifyx{background-color:#D2E4FC;text-align:right}
        .tg .tg-s6z2{text-align:center}
        .tg .tg-vn4c{background-color:#D2E4FC}
        th.tg-sort-header::-moz-selection { background:transparent; }th.tg-sort-header::selection      { background:transparent; }th.tg-sort-header { cursor:pointer; }table th.tg-sort-header:after {  content:'';  float:right;  margin-top:7px;  border-width:0 4px 4px;  border-style:solid;  border-color:#404040 transparent;  visibility:hidden;  }table th.tg-sort-header:hover:after {  visibility:visible;  }table th.tg-sort-desc:after,table th.tg-sort-asc:after,table th.tg-sort-asc:hover:after {  visibility:visible;  opacity:0.4;  }table th.tg-sort-desc:after {  border-bottom:none;  border-width:4px 4px 0;  }@media screen and (max-width: 767px) {.tg {width: auto !important;}.tg col {width: auto !important;}.tg-wrap {overflow-x: auto;-webkit-overflow-scrolling: touch;margin: auto 0px;}}
        .page-header {
            padding-bottom: 9px;
            margin: 20px 0 30px;
            border-bottom: 1px solid #eee;
        }
        .text-center {
            text-align: center;
        }
        @media screen and (max-width: 767px) {.tg {width: auto !important;}.tg col {width: auto !important;}.tg-wrap {overflow-x: auto;-webkit-overflow-scrolling: touch;}}
    </style>

    <div class="page-header text-center">

        <?php

        echo '<p><b>'.get_pde_info_by_id($ci->session->userdata('pdeid'),'title').'</b></p>'
        ?>
        <?= $report_heading ?>

        <p>
        <h5>
            Financial Year : <?= $financial_year ?><br><br>
            <small>
                Reporting Period : <?= $reporting_period ?>
            </small>

        </h5>



        </p>
    </div>
    <div class="tg-wrap"><table id="tg-r2gGz" class="tg">

            <thead>
            <tr>
                <th>Procurement Reference Number</th>
                <?php
                if ($ci->session->userdata('isadmin') == 'Y') {
                    ?>
                    <th>PDE</th>
                <?php
                }
                ?>

                <th>Subject of procurement</th>
                <th>Method of procurement</th>
                <th>Provider</th>
                <th>Date of award of contract</th>
                <th>Market price of the procurement</th>
                <th>Contract value (UGX)</th>


            </tr>
            </thead>

            <tbody>
            <?php
            $grand_total_actual_payments = array();
            $grand_amount = array();
            //print_array($results);
            foreach ($results as $row) {
                $grand_total_actual_payments[] = $row['estimated_amount'];
                $grand_amount[] = $row['amount'] * $row['xrate'];
                ?>
                <tr>
                    <td>
                        <?= $row['procurement_ref_no'] ?>
                    </td>
                    <?php
                    if ($ci->session->userdata('isadmin') == 'Y') {
                        ?>
                        <td><?= $row['pdename'] ?></td>
                    <?php
                    }
                    ?>
                    <td>
                        <?= $row['subject_of_procurement'] ?>
                    </td>
                    <td>
                        <?= get_procurement_method_info_by_id($row['procurement_method'], 'title') ?>
                    </td>
                    <td><?= get_provider_by_procurement($row['procurement_ref_id']) ?></td>


                    <td>
                        <?= custom_date_format('d M Y', $row['contract_award_date']) ?>
                    </td>
                    <td style="text-align: right;">
                        <?= number_format($row['estimated_amount']) ?>
                    </td>

                    <td style="text-align: right;">
                        <?= number_format($row['amount'] * $row['xrate']) ?>
                    </td>


                </tr>
            <?php
            }
            ?>
            <tr>
                <td></td>
                <?php
                if ($ci->session->userdata('isadmin') == 'Y') {
                    ?>
                    <td></td>
                <?php
                }
                ?>
                <td></td>

                <td></td>
                <td></td>

                <td></td>
                <td style="border-top: 1px solid #000; text-align: right; ">
                    <b><?= number_format(array_sum($grand_total_actual_payments)) ?></b></td>
                <td style="border-top: 1px solid #000; text-align: right;">
                    <b><?= number_format(array_sum($grand_amount)) ?></b></td>

            </tr>


            </tbody>
        </table></div>

<?php
    $my_var = ob_get_clean();

    return $my_var;
}