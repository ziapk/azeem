<?php

/**
 * Inventory — The ONLY class allowed to touch store_products.qty
 *
 * Rule: Nothing in the codebase calls addProductQty(), subProductQty(),
 *       maintainProductQty(), or resetCounters() directly anymore.
 *       Every stock movement goes through Inventory::logMovement().
 *
 * Flow:
 *   logMovement()
 *       → writes one row to inventory_ledger
 *       → calls syncQty() to recompute store_products.qty from the ledger
 *
 * store_products.qty is now a READ-ONLY performance cache.
 * The ledger is the source of truth.
 */
class Inventory extends Connection
{
    private $table_ledger = 'inventory_ledger';
    private $table_st     = 'store_products';

    // ----------------------------------------------------------------
    //  MOVEMENT TYPES (use these constants everywhere — no magic strings)
    // ----------------------------------------------------------------
    const SUPPLY      = 'SUPPLY';      // stock received from supplier  (+)
    const SALE        = 'SALE';        // stock sold / dispatched        (-)
    const RETURN_IN   = 'RETURN_IN';   // customer returns to you        (+)
    const RETURN_OUT  = 'RETURN_OUT';  // you return to supplier         (-)
    const ADJUSTMENT  = 'ADJUSTMENT';  // manual correction              (+/-)

    // ----------------------------------------------------------------
    //  REF TYPES
    // ----------------------------------------------------------------
    const REF_SUPPLY       = 'supply';
    const REF_ORDER        = 'order';
    const REF_RETURN_ORDER = 'return_order';
    const REF_MANUAL       = 'manual';

    // ----------------------------------------------------------------
    /**
     * logMovement — the single entry point for all inventory changes.
     *
     * @param array $array {
     *   Required:
     *     int    product_id
     *     int    shop_id
     *     int    owner_id
     *     string movement_type   — one of the class constants above
     *     float  quantity        — ALWAYS positive; direction is set by movement_type
     *     int    created_by      — user id performing the action
     *
     *   Optional:
     *     string ref_type        — 'supply' | 'order' | 'return_order' | 'manual'
     *     int    ref_id          — the supply_id or order_id this relates to
     *     string note            — human-readable reason
     * }
     *
     * @return int  The new ledger row id, or 0 on failure.
     */
    public function logMovement(array $array): int
    {
        // Determine sign: stock OUT movements are stored as negative quantities
        $outTypes = [self::SALE, self::RETURN_OUT];
        $qty = abs((float)$array['quantity']);
        if (in_array($array['movement_type'], $outTypes)) {
            $qty = $qty * -1;
        }

        $ref_type   = $array['ref_type']  ?? self::REF_MANUAL;
        $ref_id     = $array['ref_id']    ?? null;
        $note       = $array['note']      ?? null;

        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "INSERT INTO `{$this->table_ledger}`
                        (`product_id`, `shop_id`, `owner_id`, `movement_type`,
                         `quantity`, `ref_type`, `ref_id`, `note`, `created_by`)
                     VALUES
                        (:product_id, :shop_id, :owner_id, :movement_type,
                         :quantity, :ref_type, :ref_id, :note, :created_by)";

            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':product_id',    $array['product_id'],    PDO::PARAM_INT);
            $prepare->bindParam(':shop_id',       $array['shop_id'],       PDO::PARAM_INT);
            $prepare->bindParam(':owner_id',      $array['owner_id'],      PDO::PARAM_INT);
            $prepare->bindParam(':movement_type', $array['movement_type'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity',      $qty,                    PDO::PARAM_STR);
            $prepare->bindParam(':ref_type',      $ref_type,               PDO::PARAM_STR);
            $prepare->bindParam(':ref_id',        $ref_id,                 PDO::PARAM_INT);
            $prepare->bindParam(':note',          $note,                   PDO::PARAM_STR);
            $prepare->bindParam(':created_by',    $array['created_by'],    PDO::PARAM_INT);
            $prepare->execute();

            $ledger_id = (int)$dbh->lastInsertId();

            // Keep the performance cache in sync immediately
            $this->syncQty($array['product_id'], $array['shop_id'], $dbh);

            return $ledger_id;

        } catch (PDOException $e) {
            die("Inventory::logMovement error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    // ----------------------------------------------------------------
    /**
     * logMovementBatch — log multiple products in one go (e.g. placing an order).
     * All items share the same ref_type / ref_id / movement_type / created_by.
     *
     * @param array $items   Each item: [product_id, shop_id, owner_id, quantity, note?]
     * @param array $shared  [movement_type, ref_type, ref_id, created_by]
     *
     * @return array  List of inserted ledger ids.
     */
    public function logMovementBatch(array $items, array $shared): array
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $this->logMovement(array_merge($item, $shared));
        }
        return $ids;
    }

    // ----------------------------------------------------------------
    /**
     * syncQty — recalculates store_products.qty from the ledger SUM.
     * Called automatically by logMovement; you should not need to call this
     * manually except after a bulk data fix.
     *
     * @param int        $product_id
     * @param int        $shop_id
     * @param PDO|null   $dbh   Pass an open connection to reuse it (avoids double checkout).
     */
    public function syncQty(int $product_id, int $shop_id, $dbh = null): void
    {
        $ownConnection = ($dbh === null);
        if ($ownConnection) {
            $dbh = $this->connectionPool->getConnection();
        }

        try {
            // Sum all ledger entries for this product+shop  →  new available qty
            $sumStmt = "SELECT COALESCE(SUM(quantity), 0) AS total
                        FROM `{$this->table_ledger}`
                        WHERE product_id = :product_id
                          AND shop_id    = :shop_id";

            $prepare = $dbh->prepare($sumStmt);
            $prepare->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $prepare->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $prepare->execute();
            $row = $prepare->fetch(PDO::FETCH_ASSOC);
            $newQty = (float)$row['total'];

            // Write back to store_products.qty
            // We also zero out stock_out here because the ledger now owns that info.
            // qty is the available stock; stock_out is kept as 0 (legacy column, no longer used for logic).
            $updateStmt = "UPDATE `{$this->table_st}`
                           SET `qty`       = :qty,
                               `stock_out` = 0
                           WHERE product_id = :product_id
                             AND shopId     = :shop_id";

            $upd = $dbh->prepare($updateStmt);
            $upd->bindParam(':qty',        $newQty,     PDO::PARAM_STR);
            $upd->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $upd->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $upd->execute();

        } catch (PDOException $e) {
            die("Inventory::syncQty error: " . $e->getMessage());
        } finally {
            if ($ownConnection) {
                $this->connectionPool->releaseConnection($dbh);
            }
        }
    }

    // ----------------------------------------------------------------
    /**
     * getQty — read the current available quantity for a product in a shop.
     * Reads directly from store_products (the synced cache) for speed.
     *
     * @return float  Current available qty (qty column, already synced).
     */
    public function getQty(int $product_id, int $shop_id): float
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT qty FROM `{$this->table_st}`
                     WHERE product_id = :product_id AND shopId = :shop_id
                     LIMIT 1";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $prepare->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $prepare->execute();
            $row = $prepare->fetch(PDO::FETCH_ASSOC);
            return $row ? (float)$row['qty'] : 0.0;
        } catch (PDOException $e) {
            die("Inventory::getQty error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    // ----------------------------------------------------------------
    /**
     * getLedger — full movement history for a product (paginated).
     *
     * @param array $params {
     *   int    product_id
     *   int    shop_id
     *   int    page         (default 1)
     *   int    perPage      (default 20)
     *   string from         date string  YYYY-MM-DD  (optional)
     *   string to           date string  YYYY-MM-DD  (optional)
     *   string movement_type            (optional filter)
     * }
     */
    public function getLedger(array $params): array
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $where = "WHERE il.product_id = :product_id AND il.shop_id = :shop_id";

            if (!empty($params['from'])) {
                $where .= " AND DATE(il.created_at) >= :from";
            }
            if (!empty($params['to'])) {
                $where .= " AND DATE(il.created_at) <= :to";
            }
            if (!empty($params['movement_type'])) {
                $where .= " AND il.movement_type = :movement_type";
            }

            // Count
            $countStmt = "SELECT COUNT(il.id) AS total
                          FROM `{$this->table_ledger}` il
                          $where";
            $cp = $dbh->prepare($countStmt);
            $cp->bindParam(':product_id', $params['product_id'], PDO::PARAM_INT);
            $cp->bindParam(':shop_id',    $params['shop_id'],    PDO::PARAM_INT);
            if (!empty($params['from']))          $cp->bindParam(':from',          $params['from'],          PDO::PARAM_STR);
            if (!empty($params['to']))            $cp->bindParam(':to',            $params['to'],            PDO::PARAM_STR);
            if (!empty($params['movement_type'])) $cp->bindParam(':movement_type', $params['movement_type'], PDO::PARAM_STR);
            $cp->execute();
            $total = (int)$cp->fetchColumn();

            $perPage     = (int)($params['perPage'] ?? 20);
            $totalPages  = $total > 0 ? (int)ceil($total / $perPage) : 1;
            $currentPage = min((int)($params['page'] ?? 1), $totalPages);
            $offset      = max(0, ($currentPage - 1)) * $perPage;

            // Data
            $dataStmt = "SELECT il.*, u.full_name AS created_by_name
                         FROM `{$this->table_ledger}` il
                         LEFT JOIN users u ON u.id = il.created_by
                         $where
                         ORDER BY il.id DESC
                         LIMIT :offset, :perPage";
            $dp = $dbh->prepare($dataStmt);
            $dp->bindParam(':product_id', $params['product_id'], PDO::PARAM_INT);
            $dp->bindParam(':shop_id',    $params['shop_id'],    PDO::PARAM_INT);
            $dp->bindParam(':offset',     $offset,               PDO::PARAM_INT);
            $dp->bindParam(':perPage',    $perPage,              PDO::PARAM_INT);
            if (!empty($params['from']))          $dp->bindParam(':from',          $params['from'],          PDO::PARAM_STR);
            if (!empty($params['to']))            $dp->bindParam(':to',            $params['to'],            PDO::PARAM_STR);
            if (!empty($params['movement_type'])) $dp->bindParam(':movement_type', $params['movement_type'], PDO::PARAM_STR);
            $dp->execute();
            $records = $dp->fetchAll(PDO::FETCH_ASSOC);

            return [
                'page'         => $currentPage,
                'totalPages'   => $totalPages,
                'totalRecords' => $total,
                'perPage'      => $perPage,
                'records'      => $records,
            ];

        } catch (PDOException $e) {
            die("Inventory::getLedger error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    // ----------------------------------------------------------------
    /**
     * reverseByRef — reverses ALL ledger entries tied to a specific ref.
     * Used when rolling back a completed order or supply.
     * Inserts compensating entries (opposite sign) then re-syncs qty.
     *
     * @param string $ref_type   'order' | 'supply' | 'return_order'
     * @param int    $ref_id     The order_id or supply_id
     * @param int    $created_by User performing the reversal
     * @param string $note       Reason for reversal
     */
    public function reverseByRef(string $ref_type, int $ref_id, int $created_by, string $note = ''): void
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            // Fetch all original entries for this ref
            $stmt = "SELECT * FROM `{$this->table_ledger}`
                     WHERE ref_type = :ref_type AND ref_id = :ref_id
                       AND movement_type NOT IN ('ADJUSTMENT')
                     ORDER BY id ASC";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':ref_type', $ref_type, PDO::PARAM_STR);
            $prepare->bindParam(':ref_id',   $ref_id,   PDO::PARAM_INT);
            $prepare->execute();
            $entries = $prepare->fetchAll(PDO::FETCH_ASSOC);

            $affected = []; // track which product+shop combos need syncQty

            foreach ($entries as $entry) {
                // Insert the reversal row (flip the quantity sign)
                $reverseQty = (float)$entry['quantity'] * -1;

                $ins = "INSERT INTO `{$this->table_ledger}`
                            (`product_id`, `shop_id`, `owner_id`, `movement_type`,
                             `quantity`, `ref_type`, `ref_id`, `note`, `created_by`)
                        VALUES
                            (:product_id, :shop_id, :owner_id, :movement_type,
                             :quantity, :ref_type, :ref_id, :note, :created_by)";
                $movementType = 'ADJUSTMENT';
                $reversalNote = $note ?: "Reversal of $ref_type #$ref_id";

                $ip = $dbh->prepare($ins);
                $ip->bindParam(':product_id',    $entry['product_id'], PDO::PARAM_INT);
                $ip->bindParam(':shop_id',       $entry['shop_id'],    PDO::PARAM_INT);
                $ip->bindParam(':owner_id',      $entry['owner_id'],   PDO::PARAM_INT);
                $ip->bindParam(':movement_type', $movementType,        PDO::PARAM_STR);
                $ip->bindParam(':quantity',      $reverseQty,          PDO::PARAM_STR);
                $ip->bindParam(':ref_type',      $ref_type,            PDO::PARAM_STR);
                $ip->bindParam(':ref_id',        $ref_id,              PDO::PARAM_INT);
                $ip->bindParam(':note',          $reversalNote,        PDO::PARAM_STR);
                $ip->bindParam(':created_by',    $created_by,          PDO::PARAM_INT);
                $ip->execute();

                $affected[$entry['product_id'] . '_' . $entry['shop_id']] = [
                    'product_id' => $entry['product_id'],
                    'shop_id'    => $entry['shop_id'],
                ];
            }

            // Sync all affected products
            foreach ($affected as $key => $item) {
                $this->syncQty($item['product_id'], $item['shop_id'], $dbh);
            }

        } catch (PDOException $e) {
            die("Inventory::reverseByRef error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    /**
     * resetProductLedger — deletes all ledger entries for a product and re-syncs quantity.
     * WARNING: This will recalculate quantity from scratch. Use only for corrupted data.
     *
     * @param int $product_id
     * @param int $shop_id
     * @param string $movement_type  Optional: only delete entries of this type (e.g., 'ADJUSTMENT')
     * @param bool $delete_all      If true, deletes ALL entries; if false, only specified type
     */
    public function resetProductLedger(int $product_id, int $shop_id, string $movement_type = '', bool $delete_all = false): void
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            // Build WHERE clause
            $where = "WHERE product_id = :product_id AND shop_id = :shop_id";
            $params = [
                ':product_id' => $product_id,
                ':shop_id' => $shop_id
            ];

            if (!$delete_all && !empty($movement_type)) {
                $where .= " AND movement_type = :movement_type";
                $params[':movement_type'] = $movement_type;
            }

            // Delete the specified entries
            $delStmt = "DELETE FROM `{$this->table_ledger}` $where";
            $del = $dbh->prepare($delStmt);
            foreach ($params as $key => $value) {
                $del->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $deleted = $del->execute();

            // Re-sync the quantity from remaining ledger entries
            $this->syncQty($product_id, $shop_id, $dbh);

            // Log what was done
            $action = $delete_all ? 'ALL entries' : ($movement_type ?: 'specified') . ' entries';
            error_log("Inventory::resetProductLedger: Deleted $action for product $product_id in shop $shop_id, re-synced quantity");

        } catch (PDOException $e) {
            die("Inventory::resetProductLedger error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    /**
     * resetAllProductLedgers — deletes all ledger entries for ALL products and re-syncs all quantities.
     * EXTREME WARNING: This will recalculate ALL product quantities from scratch.
     * Use only when the entire inventory ledger is corrupted.
     *
     * @param int $shop_id  Optional: only reset products in this shop, or all shops if null
     */
    public function resetAllProductLedgers(int $shop_id = null): void
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            // Get all affected product+shop combinations
            $where = "";
            $params = [];
            if ($shop_id !== null) {
                $where = "WHERE shop_id = :shop_id";
                $params[':shop_id'] = $shop_id;
            }

            $stmt = "SELECT DISTINCT product_id, shop_id FROM `{$this->table_ledger}` $where";
            $prepare = $dbh->prepare($stmt);
            foreach ($params as $key => $value) {
                $prepare->bindValue($key, $value, PDO::PARAM_INT);
            }
            $prepare->execute();
            $products = $prepare->fetchAll(PDO::FETCH_ASSOC);

            // Delete all ledger entries
            $delStmt = "DELETE FROM `{$this->table_ledger}`" . ($shop_id ? " WHERE shop_id = :shop_id" : "");
            $del = $dbh->prepare($delStmt);
            if ($shop_id) {
                $del->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
            }
            $del->execute();

            // Re-sync all affected products (quantities will be 0 since all entries deleted)
            foreach ($products as $product) {
                $this->syncQty($product['product_id'], $product['shop_id'], $dbh);
            }

            $scope = $shop_id ? "shop $shop_id" : "all shops";
            error_log("Inventory::resetAllProductLedgers: Reset ALL ledger entries for $scope, re-synced all quantities to 0");

        } catch (PDOException $e) {
            die("Inventory::resetAllProductLedgers error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    // ----------------------------------------------------------------
    /**
     * reconcileProduct — heals the ledger for one product in one shop.
     *
     * What it does:
     *   1. Looks at every completed order/supply/return that affects
     *      this product+shop in the real transaction tables.
     *   2. Checks whether a matching ledger entry already exists
     *      (matched by ref_type + ref_id + product_id + shop_id).
     *   3. Inserts any missing entries (skips ones already recorded).
     *   4. Calls syncQty() to recalculate store_products.qty from the
     *      full ledger — so the count becomes exactly correct.
     *
     * Safe to call multiple times — it never duplicates existing entries.
     *
     * @param int $product_id
     * @param int $shop_id
     * @param int $owner_id   Used as created_by for inserted rows
     *
     * @return array {
     *   inserted : int   — number of missing entries that were added
     *   qty      : float — new store_products.qty after sync
     * }
     */
    public function reconcileProduct(int $product_id, int $shop_id, int $owner_id): array
    {
        $dbh = $this->connectionPool->getConnection();
        $inserted = 0;

        try {

            // ----------------------------------------------------------
            // 1. Build a lookup set of already-recorded entries for this
            //    product+shop so we can skip them cheaply.
            //    Key format: "{ref_type}_{ref_id}"
            // ----------------------------------------------------------
            $existStmt = $dbh->prepare(
                "SELECT ref_type, ref_id
                 FROM `{$this->table_ledger}`
                 WHERE product_id = :product_id
                   AND shop_id    = :shop_id"
            );
            $existStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $existStmt->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $existStmt->execute();

            $existing = [];
            foreach ($existStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $existing[$row['ref_type'] . '_' . $row['ref_id']] = true;
            }

            // ----------------------------------------------------------
            // Helper: insert one ledger row if not already present
            // ----------------------------------------------------------
            $insertLedger = function(
                string $movement_type,
                float  $quantity,
                string $ref_type,
                int    $ref_id,
                string $note,
                string $created_at
            ) use ($dbh, $product_id, $shop_id, $owner_id, &$existing, &$inserted) {

                $key = $ref_type . '_' . $ref_id;
                if (isset($existing[$key])) {
                    return; // already recorded — skip
                }

                $stmt = $dbh->prepare(
                    "INSERT INTO `{$this->table_ledger}`
                        (`product_id`, `shop_id`, `owner_id`, `movement_type`,
                         `quantity`, `ref_type`, `ref_id`, `note`, `created_by`, `created_at`)
                     VALUES
                        (:product_id, :shop_id, :owner_id, :movement_type,
                         :quantity, :ref_type, :ref_id, :note, :created_by, :created_at)"
                );
                $stmt->bindParam(':product_id',    $product_id,    PDO::PARAM_INT);
                $stmt->bindParam(':shop_id',       $shop_id,       PDO::PARAM_INT);
                $stmt->bindParam(':owner_id',      $owner_id,      PDO::PARAM_INT);
                $stmt->bindParam(':movement_type', $movement_type, PDO::PARAM_STR);
                $stmt->bindParam(':quantity',      $quantity,      PDO::PARAM_STR);
                $stmt->bindParam(':ref_type',      $ref_type,      PDO::PARAM_STR);
                $stmt->bindParam(':ref_id',        $ref_id,        PDO::PARAM_INT);
                $stmt->bindParam(':note',          $note,          PDO::PARAM_STR);
                $stmt->bindParam(':created_by',    $owner_id,      PDO::PARAM_INT);
                $stmt->bindParam(':created_at',    $created_at,    PDO::PARAM_STR);
                $stmt->execute();

                $existing[$key] = true; // mark as recorded for this run
                $inserted++;
            };

            // ----------------------------------------------------------
            // 2. SALES — from order_items → orders
            //    flag=1, status IN (2,8,9)
            // ----------------------------------------------------------
            $salesStmt = $dbh->prepare(
                "SELECT o.id AS order_id, o.order_custom_id, oi.quantity, o.order_date
                 FROM `order_items` oi
                 INNER JOIN `orders` o ON o.id = oi.order_id
                 WHERE oi.product_id = :product_id
                   AND o.shopId      = :shop_id
                   AND o.flag        = 1
                   AND o.status      IN (2, 8, 9)
                   AND oi.quantity   > 0"
            );
            $salesStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $salesStmt->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $salesStmt->execute();

            foreach ($salesStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $insertLedger(
                    self::SALE,
                    -1 * (float)$row['quantity'],
                    self::REF_ORDER,
                    (int)$row['order_id'],
                    'Order #' . $row['order_custom_id'],
                    $row['order_date']
                );
            }

            // ----------------------------------------------------------
            // 3. SUPPLIES — from supply_items → supply
            //    flag=1, status != 1
            // ----------------------------------------------------------
            $supplyStmt = $dbh->prepare(
                "SELECT s.id AS supply_id, si.quantity, s.supply_date
                 FROM `supply_items` si
                 INNER JOIN `supply` s ON s.id = si.supply_id
                 WHERE si.product_id = :product_id
                   AND s.shopId      = :shop_id
                   AND s.flag        = 1
                   AND s.status      != 1
                   AND si.quantity   > 0"
            );
            $supplyStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $supplyStmt->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $supplyStmt->execute();

            foreach ($supplyStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $insertLedger(
                    self::SUPPLY,
                    (float)$row['quantity'],
                    self::REF_SUPPLY,
                    (int)$row['supply_id'],
                    'Supply #' . $row['supply_id'],
                    $row['supply_date']
                );
            }

            // ----------------------------------------------------------
            // 4. RETURN_IN — customer returns to you
            //    return_orders.return_type = 1, flag = 2
            // ----------------------------------------------------------
            $returnInStmt = $dbh->prepare(
                "SELECT ro.id AS ro_id, pr.quantity, ro.return_date
                 FROM `product_returns` pr
                 INNER JOIN `return_orders` ro ON ro.id = pr.order_id
                 WHERE pr.product_id  = :product_id
                   AND ro.shopId      = :shop_id
                   AND ro.return_type = 1
                   AND ro.flag        = 2
                   AND pr.quantity    > 0"
            );
            $returnInStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $returnInStmt->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $returnInStmt->execute();

            foreach ($returnInStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $insertLedger(
                    self::RETURN_IN,
                    (float)$row['quantity'],
                    self::REF_RETURN_ORDER,
                    (int)$row['ro_id'],
                    'Sale return RO #' . $row['ro_id'],
                    $row['return_date']
                );
            }

            // ----------------------------------------------------------
            // 5. RETURN_OUT — you return to supplier
            //    return_orders.return_type = 2, flag = 2
            // ----------------------------------------------------------
            $returnOutStmt = $dbh->prepare(
                "SELECT ro.id AS ro_id, pr.quantity, ro.return_date
                 FROM `product_returns` pr
                 INNER JOIN `return_orders` ro ON ro.id = pr.order_id
                 WHERE pr.product_id  = :product_id
                   AND ro.shopId      = :shop_id
                   AND ro.return_type = 2
                   AND ro.flag        = 2
                   AND pr.quantity    > 0"
            );
            $returnOutStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $returnOutStmt->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $returnOutStmt->execute();

            foreach ($returnOutStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $insertLedger(
                    self::RETURN_OUT,
                    -1 * (float)$row['quantity'],
                    self::REF_RETURN_ORDER,
                    (int)$row['ro_id'],
                    'Purchase return RO #' . $row['ro_id'],
                    $row['return_date']
                );
            }

            // ----------------------------------------------------------
            // 6. Recalculate store_products.qty from the now-complete ledger
            // ----------------------------------------------------------
            $this->syncQty($product_id, $shop_id, $dbh);

            // Read back the new qty to return it
            $qtyStmt = $dbh->prepare(
                "SELECT qty FROM `{$this->table_st}`
                 WHERE product_id = :product_id AND shopId = :shop_id LIMIT 1"
            );
            $qtyStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $qtyStmt->bindParam(':shop_id',    $shop_id,    PDO::PARAM_INT);
            $qtyStmt->execute();
            $newQty = (float)($qtyStmt->fetchColumn() ?? 0);

            return [
                'inserted' => $inserted,
                'qty'      => $newQty,
            ];

        } catch (PDOException $e) {
            die("Inventory::reconcileProduct error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    // ----------------------------------------------------------------
    /**
     * reconcileProducts — bulk version of reconcileProduct.
     *
     * Accepts an array of product IDs and reconciles each one.
     * Useful after placing/editing an order, supply, or return —
     * pass all product IDs from that transaction and everything
     * gets healed in one call.
     *
     * @param int[]  $product_ids   Array of product IDs to reconcile
     * @param int    $shop_id
     * @param int    $owner_id
     *
     * @return array {
     *   total_inserted : int     — total missing entries added across all products
     *   products       : array   — per-product result keyed by product_id
     *                             each entry: { inserted: int, qty: float }
     * }
     */
    public function reconcileProducts(array $product_ids, int $shop_id, int $owner_id): array
    {
        $product_ids = array_values(array_unique(array_filter(array_map('intval', $product_ids))));

        if (empty($product_ids)) {
            return ['total_inserted' => 0, 'products' => []];
        }

        $totalInserted = 0;
        $products      = [];

        foreach ($product_ids as $product_id) {
            $result = $this->reconcileProduct($product_id, $shop_id, $owner_id);
            $totalInserted              += $result['inserted'];
            $products[$product_id]       = $result;
        }

        return [
            'total_inserted' => $totalInserted,
            'products'       => $products,
        ];
    }

    /**
     * findDuplicateLedgerEntries — finds duplicate inventory ledger entries
     *
     * @param int $shopId Optional: limit to specific shop
     * @param int $productId Optional: limit to specific product
     * @return array Array of duplicate groups
     */
    public function findDuplicateLedgerEntries(int $shopId = null, int $productId = null): array
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $where = "";
            $params = [];

            if ($shopId) {
                $where .= " AND shop_id = :shop_id";
                $params[':shop_id'] = $shopId;
            }

            if ($productId) {
                $where .= " AND product_id = :product_id";
                $params[':product_id'] = $productId;
            }

            // Find exact duplicates (same product, shop, movement_type, quantity, ref_type, ref_id, note)
            $stmt = "SELECT
                        product_id, shop_id, movement_type, quantity, ref_type, ref_id, note,
                        COUNT(*) as count,
                        GROUP_CONCAT(id ORDER BY id) as entry_ids,
                        GROUP_CONCAT(created_at ORDER BY id) as created_dates
                     FROM `{$this->table_ledger}`
                     WHERE 1=1 $where
                     GROUP BY product_id, shop_id, movement_type, quantity, ref_type, ref_id, note
                     HAVING COUNT(*) > 1
                     ORDER BY product_id, shop_id, COUNT(*) DESC";

            $prepare = $dbh->prepare($stmt);
            foreach ($params as $key => $value) {
                $prepare->bindValue($key, $value, PDO::PARAM_INT);
            }
            $prepare->execute();
            $duplicates = $prepare->fetchAll(PDO::FETCH_ASSOC);

            // Format the results
            $result = [];
            foreach ($duplicates as $dup) {
                $entryIds = explode(',', $dup['entry_ids']);
                $createdDates = explode(',', $dup['created_dates']);

                $result[] = [
                    'product_id' => $dup['product_id'],
                    'shop_id' => $dup['shop_id'],
                    'movement_type' => $dup['movement_type'],
                    'quantity' => $dup['quantity'],
                    'ref_type' => $dup['ref_type'],
                    'ref_id' => $dup['ref_id'],
                    'note' => $dup['note'],
                    'count' => $dup['count'],
                    'entry_ids' => array_map('intval', $entryIds),
                    'created_dates' => $createdDates,
                    'total_quantity' => $dup['quantity'] * $dup['count']
                ];
            }

            return $result;

        } catch (PDOException $e) {
            die("Inventory::findDuplicateLedgerEntries error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    /**
     * deleteDuplicateLedgerEntries — deletes duplicate inventory ledger entries, keeping one
     *
     * @param array $duplicateGroups Array from findDuplicateLedgerEntries()
     * @param bool $dryRun If true, just return what would be deleted without actually deleting
     * @return array Results of the operation
     */
    public function deleteDuplicateLedgerEntries(array $duplicateGroups, bool $dryRun = true): array
    {
        $results = [
            'deleted_entries' => [],
            'kept_entries' => [],
            'errors' => [],
            'total_processed' => 0,
            'dry_run' => $dryRun,
            'affected_products' => []
        ];

        $affectedProducts = [];

        foreach ($duplicateGroups as $group) {
            $entryIds = $group['entry_ids'];

            // Keep the entry with the lowest ID (oldest)
            $keepId = min($entryIds);
            $deleteIds = array_diff($entryIds, [$keepId]);

            $results['kept_entries'][] = $keepId;
            $results['total_processed'] += count($deleteIds);

            $affectedProducts[$group['product_id'] . '_' . $group['shop_id']] = [
                'product_id' => $group['product_id'],
                'shop_id' => $group['shop_id']
            ];

            if (!$dryRun) {
                $dbh = $this->connectionPool->getConnection();
                try {
                    $dbh->beginTransaction();

                    // Delete duplicate entries
                    foreach ($deleteIds as $entryId) {
                        $stmt = "DELETE FROM `{$this->table_ledger}` WHERE id = :id";
                        $prepare = $dbh->prepare($stmt);
                        $prepare->bindParam(':id', $entryId, PDO::PARAM_INT);
                        $prepare->execute();
                        $results['deleted_entries'][] = $entryId;
                    }

                    $dbh->commit();
                } catch (Exception $e) {
                    $dbh->rollBack();
                    $results['errors'][] = "Failed to delete entries for product {$group['product_id']}: " . $e->getMessage();
                } finally {
                    $this->connectionPool->releaseConnection($dbh);
                }
            } else {
                $results['deleted_entries'] = array_merge($results['deleted_entries'], $deleteIds);
            }
        }

        // Re-sync quantities for affected products
        if (!$dryRun && !empty($affectedProducts)) {
            foreach ($affectedProducts as $product) {
                $this->syncQty($product['product_id'], $product['shop_id']);
            }
            $results['affected_products'] = array_values($affectedProducts);
        }

        return $results;
    }

    /**
     * findLedgerEntriesByRef — finds all ledger entries for a specific reference
     *
     * @param string $refType 'order', 'supply', 'return_order', 'manual'
     * @param int $refId The reference ID
     * @return array Array of ledger entries
     */
    public function findLedgerEntriesByRef(string $refType, int $refId): array
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table_ledger}`
                     WHERE ref_type = :ref_type AND ref_id = :ref_id
                     ORDER BY id";

            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':ref_type', $refType, PDO::PARAM_STR);
            $prepare->bindParam(':ref_id', $refId, PDO::PARAM_INT);
            $prepare->execute();

            return $prepare->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Inventory::findLedgerEntriesByRef error: " . $e->getMessage());
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }
}