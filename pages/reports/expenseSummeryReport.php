<?php
$totals = [];

$total = 0;

$parentCols = [];
$rows = [];
foreach ($expenses as $value) {
  $parentCols[$value['details']] = !empty($parentCols[$value['details']]) ? $parentCols[$value['details']] : ['title' => $value['details'], "count" => 0, 'child'=> []];
  
  $parentCols[$value['details']]['child'][] = $value['title'];

  $parentCols[$value['details']]['child'] = array_unique($parentCols[$value['details']]['child']);

  $parentCols[$value['details']]['count'] = sizeof($parentCols[$value['details']]['child']);
  $rows[$value['exp_date']][$value['details']][$value['title']] = $value;
  $footerRow[$value['details']][$value['title']] = (!empty($footerRow[$value['details']][$value['title']]) ? $footerRow[$value['details']][$value['title']] : 0);
  $footerRow[$value['details']][$value['title']] += $value['price'];

  $totals[$value['title']] = empty($totals[$value['title']]) ? 0 : $totals[$value['title']];
  $totals[$value['title']] += $value['price'];
  $total += $value['price'];

}


?>
<center>
    <h2>Expense Summary Day Wise Report</h2>
    <h4>Between <?php echo $from;?> and <?php echo $to;?></h4>
</center>
<table class="table">
    <thead>
      <tr>
        <th rowspan="2">
          Sr.#
        </th>
        <th rowspan="2">
          Date
        </th>
        <?php foreach ($parentCols as $key => $value) {?>
            <th colspan="<?php echo $value['count'];?>"><?php echo $key;?></th>
        <?php } ?>
      </tr>
      <tr>
      <?php foreach ($parentCols as $key => $value) {
            foreach ($value['child'] as $cval) {?>
          <th><?php echo $cval;?></th>
      <?php }}?>
      </tr>
    </thead>
    <tbody>
    <?php $count = 1;
        
        foreach ($rows as $date => $rest) {?>
        
        <tr>
            <td><?php echo $count;?></td>
            <td><?php echo $date;?></td>
            <?php foreach ($parentCols as $key => $value) {
                  foreach ($value['child'] as $cval) {?>
                <td align="center"><?php echo !empty($rest[$key][$cval]) ? number_format($rest[$key][$cval]['price'], 2) : '-';?></td>
            <?php }}?>
        </tr>
	<?php $count++;} ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" align="right">Item Total</th>
            <?php foreach ($parentCols as $key => $value) {
                  foreach ($value['child'] as $cval) {?>
                <th><?php echo !empty($footerRow[$key][$cval]) ? number_format($footerRow[$key][$cval], 2) : '-';?></th>
            <?php }}?>
        </tr>
    </tfoot>
</table>
<div style="width: 40%">
    <h3>Summery</h3>
    <table class="table">
        <?php foreach($totals as $key => $val) {?>
          <tr>
            <td align="left"><?php echo $key;?></td>
            <td align="center"><?php echo number_format($val, 2);?></td>
          </tr>
        <?php }?>
        <tr>
            <th align="left">Grand Total</th>
            <th><?php echo number_format($total, 2);?></th>
          </tr>
    </table>
</div>