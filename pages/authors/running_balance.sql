SELECT t1.transaction_id, t1.account_id, t1.transaction_date, t1.amount,
       SUM(
           CASE
               WHEN t2.transaction_date <= t1.transaction_date THEN
                   CASE
                       WHEN t2.account_type = 'receivable' THEN t2.amount
                       WHEN t2.account_type = 'payable' THEN -t2.amount
                   END
               ELSE 0
           END
       ) AS running_balance
FROM transactions t1
JOIN transactions t2 ON t1.account_id = t2.account_id
GROUP BY t1.transaction_id, t1.account_id, t1.transaction_date, t1.amount
ORDER BY t1.account_id, t1.transaction_date;



SELECT
    t1.transaction_id,
    t1.account_id,
    t1.transaction_date,
    t1.debit_amount,
    t1.credit_amount,
    SUM(
        CASE
            WHEN t2.transaction_date <= t1.transaction_date THEN
                CASE
                    WHEN t2.account_type = 'receivable' THEN (t2.debit_amount - t2.credit_amount)
                    WHEN t2.account_type = 'payable' THEN (t2.credit_amount - t2.debit_amount)
                END
            ELSE 0
        END
    ) AS running_balance
FROM
    transactions t1
JOIN
    transactions t2 ON t1.account_id = t2.account_id
GROUP BY
    t1.transaction_id,
    t1.account_id,
    t1.transaction_date,
    t1.debit_amount,
    t1.credit_amount
ORDER BY
    t1.account_id,
    t1.transaction_date;





    SELECT
    t1.transaction_id,
    t1.account_id,
    t1.transaction_date,
    t1.debit_amount,
    t1.credit_amount,
    SUM(
        CASE
            WHEN t2.transaction_date <= t1.transaction_date THEN
                CASE
                    WHEN t2.account_type = 'receivable' THEN (t2.debit_amount - t2.credit_amount)
                    WHEN t2.account_type = 'payable' THEN (t2.credit_amount - t2.debit_amount)
                END
            ELSE 0
        END
    ) + t1.opening_balance AS running_balance
FROM
    transactions t1
JOIN
    transactions t2 ON t1.account_id = t2.account_id
GROUP BY
    t1.transaction_id,
    t1.account_id,
    t1.transaction_date,
    t1.debit_amount,
    t1.credit_amount,
    t1.opening_balance
ORDER BY
    t1.account_id,
    t1.transaction_date;