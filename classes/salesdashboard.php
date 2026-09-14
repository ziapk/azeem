<?php

/**
 * Data for the owner's Sales Dashboard (pages/sales-dashboard/).
 */
class SalesDashboard extends Connection
{
	private $table_orders = 'orders';
	private $table_items = 'order_items';
	private $table_products = 'products';
	private $table_transactions = 'account_transactions';

	/**
	 * Everything the dashboard shows for one shop and period.
	 *
	 * Which bills count mirrors the Profit Summary report: parked, cancelled and
	 * deleted bills, zero-value write-off bills and Giveaway customers are out.
	 *
	 * Time of sale: orders.created_at is a DATETIME written in the live server's
	 * own wall-clock time (US Eastern), 9-10 hours behind Pakistan, so it cannot
	 * be read as local time. The bill's SALE transaction has a TIMESTAMP column,
	 * which stores a true instant; its Unix epoch plus five hours is Pakistan
	 * time on any server, whatever its timezone setting. Pakistan keeps no
	 * daylight saving.
	 */
	public function getDashboard($shopId, $from, $to, $hotLimit = 15, $view = 'all')
	{
		$shopId   = (int) $shopId;
		$hotLimit = (int) $hotLimit;
		$orders   = new Orders();
		$giveaway = $orders->giveawayCondition($shopId);

		// View. A paid sale has payment recorded on the bill; a credit sale was saved
		// with nothing paid, onto the customer's account. Customers pay their account
		// rather than individual bills, so a credit bill stays a credit sale even after
		// it is settled -- this is how the sale was made, not what is still owed.
		$view     = in_array($view, ['paid', 'credit'], true) ? $view : 'all';
		$viewCond = $view === 'paid' ? 'AND o.paid_amount > 0' : ($view === 'credit' ? 'AND o.paid_amount <= 0' : '');

		$saleCond = "o.shopId = :shopId
		             AND o.flag = 1
		             AND o.status NOT IN (1, 3, 4)
		             AND NOT (o.price > 0 AND o.discount >= o.price)
		             $giveaway
		             $viewCond
		             AND o.order_date BETWEEN :fromDate AND :toDate";

		$dbh = $this->connectionPool->getConnection();
		try {
			// Totals over every counted bill, timed or not.
			$stmt = "SELECT COUNT(*) AS bills,
			                COALESCE(SUM(o.price - o.discount), 0) AS sales,
			                COALESCE(SUM(o.paid_amount > 0), 0) AS paid_bills,
			                COALESCE(SUM(CASE WHEN o.paid_amount > 0 THEN o.price - o.discount ELSE 0 END), 0) AS paid_sales
			         FROM `{$this->table_orders}` o
			         WHERE $saleCond";
			$prepare = $dbh->prepare($stmt);
			$this->bindRange($prepare, $shopId, $from, $to);
			$prepare->execute();
			$summary = $prepare->fetch(PDO::FETCH_ASSOC);

			// Weekday x hour in Pakistan time. The transaction window is padded so a
			// bill posted a few days either side of its sale date still finds its time.
			$pkt  = "DATE_ADD('1970-01-01 00:00:00', INTERVAL s.ep + 18000 SECOND)";
			$stmt = "SELECT WEEKDAY($pkt) AS wd,
			                HOUR($pkt)    AS hr,
			                COUNT(*)      AS bills,
			                SUM(o.price - o.discount) AS sales
			         FROM `{$this->table_orders}` o
			         JOIN (
			             SELECT CAST(t.order_ref AS UNSIGNED) AS order_id,
			                    MIN(UNIX_TIMESTAMP(t.`datetime`)) AS ep
			             FROM `{$this->table_transactions}` t
			             WHERE t.flag = 1
			               AND t.shopId = :txShopId
			               AND t.transsaction_type = 'SALE'
			               AND t.order_ref IS NOT NULL AND t.order_ref <> ''
			               AND t.transaction_date BETWEEN :txFrom AND :txTo
			             GROUP BY CAST(t.order_ref AS UNSIGNED)
			         ) s ON s.order_id = o.id
			         WHERE $saleCond
			         GROUP BY wd, hr";
			$prepare = $dbh->prepare($stmt);
			$this->bindRange($prepare, $shopId, $from, $to);
			$prepare->bindValue(':txShopId', $shopId, PDO::PARAM_INT);
			$prepare->bindValue(':txFrom', date('Y-m-d', strtotime($from . ' -7 days')), PDO::PARAM_STR);
			$prepare->bindValue(':txTo', date('Y-m-d', strtotime($to . ' +7 days')), PDO::PARAM_STR);
			$prepare->execute();
			$cells = $prepare->fetchAll(PDO::FETCH_ASSOC);

			// Per product. STRAIGHT_JOIN pins the join order: left to choose, MySQL
			// drove this from `orders` and rescanned all of order_items for every
			// bill, which took minutes. Reading order_items once and looking the rest
			// up by primary key takes well under a second. Bill-level discount is
			// spread across each bill's lines exactly as in the Profit Summary, so
			// the totals agree with that report.
			$stmt = "SELECT oi.product_id,
			                p.full_name,
			                p.code,
			                SUM(oi.quantity)     AS units,
			                COUNT(DISTINCT o.id) AS bills,
			                SUM((oi.price - oi.discount) * oi.quantity * sub.keep_factor) AS sales,
			                SUM(COALESCE(p.pprice, 0) * oi.quantity) AS cost
			         FROM `{$this->table_items}` oi
			         STRAIGHT_JOIN `{$this->table_orders}` o ON o.id = oi.order_id
			         STRAIGHT_JOIN `{$this->table_products}` p ON p.id = oi.product_id AND p.is_stock_item = 1
			         STRAIGHT_JOIN (
			             SELECT s2.order_id,
			                    CASE WHEN s2.subtotal > 0
			                         THEN GREATEST(0, 1 - (o2.discount / s2.subtotal))
			                         ELSE 1 END AS keep_factor
			             FROM (
			                 SELECT oi2.order_id, SUM((oi2.price - oi2.discount) * oi2.quantity) AS subtotal
			                 FROM `{$this->table_items}` oi2
			                 GROUP BY oi2.order_id
			             ) s2
			             STRAIGHT_JOIN `{$this->table_orders}` o2 ON o2.id = s2.order_id
			             WHERE o2.shopId = :subShopId
			               AND o2.order_date BETWEEN :subFrom AND :subTo
			         ) sub ON sub.order_id = o.id
			         WHERE $saleCond
			         GROUP BY oi.product_id, p.full_name, p.code";
			$prepare = $dbh->prepare($stmt);
			$this->bindRange($prepare, $shopId, $from, $to);
			$prepare->bindValue(':subShopId', $shopId, PDO::PARAM_INT);
			$prepare->bindValue(':subFrom', $from, PDO::PARAM_STR);
			$prepare->bindValue(':subTo', $to, PDO::PARAM_STR);
			$prepare->execute();
			$productRows = $prepare->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}

		// Hours and weekday x hour grid, zero-filled so the page never has gaps.
		$hours = [];
		for ($h = 0; $h < 24; $h++) {
			$hours[$h] = ['hour' => $h, 'bills' => 0, 'sales' => 0.0];
		}
		$grid = [];
		$weekdays = [];
		for ($d = 0; $d < 7; $d++) {
			$weekdays[$d] = ['weekday' => $d, 'bills' => 0, 'sales' => 0.0];
			for ($h = 0; $h < 24; $h++) {
				$grid[$d][$h] = ['bills' => 0, 'sales' => 0.0];
			}
		}
		$timedBills = 0;
		foreach ($cells as $c) {
			$d = (int) $c['wd'];
			$h = (int) $c['hr'];
			$b = (int) $c['bills'];
			$s = (float) $c['sales'];
			$grid[$d][$h] = ['bills' => $b, 'sales' => $s];
			$hours[$h]['bills'] += $b;
			$hours[$h]['sales'] += $s;
			$weekdays[$d]['bills'] += $b;
			$weekdays[$d]['sales'] += $s;
			$timedBills += $b;
		}

		$peakHour = null;
		foreach ($hours as $row) {
			if ($row['bills'] > 0 && ($peakHour === null || $row['sales'] > $peakHour['sales'])) {
				$peakHour = $row;
			}
		}
		$peakDay = null;
		foreach ($weekdays as $row) {
			if ($row['bills'] > 0 && ($peakDay === null || $row['sales'] > $peakDay['sales'])) {
				$peakDay = $row;
			}
		}

		$items = [];
		$totalUnits = 0.0;
		foreach ($productRows as $r) {
			$sales = (float) $r['sales'];
			$cost  = (float) $r['cost'];
			$units = (float) $r['units'];
			$totalUnits += $units;
			$items[] = [
				'product_id' => (int) $r['product_id'],
				'full_name'  => (string) $r['full_name'],
				'code'       => (string) $r['code'],
				'units'      => $units,
				'bills'      => (int) $r['bills'],
				'sales'      => $sales,
				'cost'       => $cost,
				'profit'     => $sales - $cost,
			];
		}

		// Top items under each ranking; ties fall back to sales value.
		$rank = function ($key) use ($items, $hotLimit) {
			$list = array_values(array_filter($items, function ($it) use ($key) {
				return $it[$key] > 0;
			}));
			usort($list, function ($a, $b) use ($key) {
				$cmp = $b[$key] <=> $a[$key];
				return $cmp !== 0 ? $cmp : ($b['sales'] <=> $a['sales']);
			});
			return array_slice($list, 0, $hotLimit);
		};

		$bills = (int) $summary['bills'];

		return [
			'from'          => $from,
			'to'            => $to,
			'days'          => (int) round((strtotime($to) - strtotime($from)) / 86400) + 1,
			'view'          => $view,
			'summary'       => [
				'bills'        => $bills,
				'sales'        => (float) $summary['sales'],
				'paid_bills'   => (int) $summary['paid_bills'],
				'paid_sales'   => (float) $summary['paid_sales'],
				'credit_bills' => $bills - (int) $summary['paid_bills'],
				'credit_sales' => (float) $summary['sales'] - (float) $summary['paid_sales'],
			],
			'hours'         => $hours,
			'grid'          => $grid,
			'peak_hour'     => $peakHour,
			'peak_day'      => $peakDay,
			'untimed_bills' => max(0, $bills - $timedBills),
			'items'         => ['units' => $totalUnits, 'products' => count($items)],
			'hot'           => ['sales' => $rank('sales'), 'units' => $rank('units'), 'bills' => $rank('bills')],
			'hot_limit'     => $hotLimit,
		];
	}

	private function bindRange($prepare, $shopId, $from, $to)
	{
		$prepare->bindValue(':shopId', (int) $shopId, PDO::PARAM_INT);
		$prepare->bindValue(':fromDate', $from, PDO::PARAM_STR);
		$prepare->bindValue(':toDate', $to, PDO::PARAM_STR);
	}
}
