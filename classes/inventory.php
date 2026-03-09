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
}