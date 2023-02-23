<?php 

include_once dirname(__FILE__).'/../../include/settings.php';
// include_once dirname(__FILE__).'/../../mpdf/mpdf.php';



$doubleEntry = new DoubleEntry();

$params = array();
$from = $_POST['from'];
$to = $_POST['to'];
$params['fromDate'] = isset($from) && !empty(trim($from)) ? $from : '';
$params['toDate'] = isset($to) && !empty(trim($to)) ? $to : '';
$report 		= isset($_GET['report']) && !empty(trim($_GET['report'])) ? $_GET['report'] : '';

$headers 		= array();
$columns 		= array();
$orientation 	= 'P';
$largeFont 		= false;
$srNo 			= true;
$mediumFont		= false;



$reportTitle = 'Bahria College Islamabad';

$subtitle = " Between ".$params['fromDate']." and ".$params['toDate'];


//$time = new datetime('Y');
$d = new DateTime('Y');

$reportData = [];



switch ($reportType) {
	case '11':
		$reportData = $doubleEntry->getTrialBalanceReport($params);
        // print_r($reportData);exit;
        $subtitle = 'Trial Balance'.$subtitle;
        $headers = ['Account Code', 'Account Title', 'Date', 'Debit', 'Credit'];
		$columns = ['accountCode', 'accountTitle', 'transaction_date', 'finalDebitAmount', 'finalCreditAmount'];

	break;

    case '12':
		$reportData = $doubleEntry->getPLStatementReport($params);
        // print_r($reportData);exit;
        $subtitle = 'Profit and Loss Statement'.$subtitle;
        $headers = ['Account Code', 'Account Title', 'Debit', 'Credit'];
		$columns = ['code', 'title', 'debitAmount', 'creditAmount'];

        include_once dirname(__FILE__).'/print/plstatement.php';
        exit;

	break;
	
	default :
		break;
}
ob_start();
?>
<style>
	table {
		font-size: 12px;
	}
	body {
		font: 16pt Arial, sans-serif;
		line-height: 1.3;
	}
	@page {
		margin-top: 25px;
		margin-left: 100px;
		margin-bottom: 25px;
		margin-right: 25px;
		<?php if(!$params['pdf']) { ?>
		size: Legal;
		<?php } ?>
	}

	td {
		padding: 5px 5px;
		<?php if($report == 'dataCollection' || $report == 'idcards_list') { ?>
			height: 32px;
			white-space: nowrap;
		<?php } ?>
	}
	<?php if($largeFont) { ?>
		td {
			font-size: 25px;
			padding: 7px 15px;
			font-family: Calibri, sans-serif;
			white-space: nowrap;
		}

	<?php } ?>
	<?php if($mediumFont) { ?>
		td {
			font-size: 15px;
			padding: 4px 5px;
			font-family: Calibri, sans-serif;
			white-space: nowrap;
		}

	<?php } ?>
	h1 {
		font-size: 20pt;
	}
	<?php if($orientation == 'L' && !$params['pdf']) { ?>
		@page {
			size: Legal landscape;			
		}
	<?php } ?>

</style>

<h1 style="margin: 0; text-align: center;"><?php echo $reportTitle; ?></h1>
<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
	<thead>
		<tr>
			<?php if($srNo) { ?>
				<th width="30">S.#</th>
			<?php } ?>
			<?php foreach ($headers as $value){ ?>
				<th><?php echo $value; ?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php $count = 1; foreach ($reportData as $s) { ?>
			<tr>
			<?php if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>

				<?php foreach ($columns as $value){ 
                    $val = '';
                    if($value == 'ifDebitAmount' || $value == 'ifCreditAmount') {
                        if($value == 'ifDebitAmount') {
                            $val = $s['entry_type'] == 'D' ? $s['amount'] : 0;
                        }
                        else if($value == 'ifCreditAmount') {
                            $val = $s['entry_type'] == 'C' ? $s['amount'] : 0;
                        }
                    }
                    else if($value == 'finalDebitAmount' || $value == 'finalCreditAmount') {
                        $debit = !empty($s['debitAmount']) ? $s['debitAmount'] : 0;
                        $credit = !empty($s['creditAmount']) ? $s['creditAmount'] : 0;
                        
                        if($debit > $credit && $value == 'finalDebitAmount') {
                            $val = $s['debitAmount'] - $s['creditAmount'];
                        }
                        elseif ($debit > $credit && $value == 'finalCreditAmount') {
                            $val = null;
                        }

                        elseif($credit > $debit && $value == 'finalCreditAmount') {
                            $val = $s['creditAmount'] - $s['debitAmount'];
                        }
                        elseif ($credit > $debit && $value == 'finalDebitAmount') {
                            $val = null;
                        }
                    }
                    else {
                        $val = $s[$value];
                    }

                    ?>

					<?php if($value == '_blank') { ?>

						<td width="300"><?php echo $val; ?></td>

					<?php } elseif($value == '_blank_normal2') { ?>
						<td width="170"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal3') { ?>
						<td width="370"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal') { ?>
						<td><?php echo $val; ?></td>
					<?php } elseif($value == 'transaction_date') { ?>
						<td><?php echo $val; ?></td>
					<?php } else { ?>

						<td><?php echo $val; ?></td>

					<?php } ?>

				<?php } ?>
			</tr>
		<?php $count++;} ?>
	</tbody>
</table>
<?php if(!empty($hasFooter)) { ?>
<table style="margin-top: 20px; font-size: 0.8em">
    <tr>
        <?php foreach ($footerCols as $value){ ?>
            <th><?php echo $value; ?></th>
        <?php } ?>
        <?php foreach ($footerVals as $value){ ?>
            <th><?php echo $footer[$value]; ?></th>
        <?php } ?>
    </tr>
</table>
<?php }?>
<?php if(empty($params['pdf'])) { ?>
<script>
	window.print();
</script>

<?php 
}

	$html = ob_get_contents();
	ob_clean();



	echo $html;



?>

