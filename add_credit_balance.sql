SET @credit_balance_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'customers'
      AND COLUMN_NAME = 'credit_balance'
);

SET @credit_balance_alter = IF(
    @credit_balance_column_exists = 0,
    'ALTER TABLE customers ADD COLUMN credit_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER address',
    'SELECT 1'
);

PREPARE credit_balance_statement FROM @credit_balance_alter;
EXECUTE credit_balance_statement;
DEALLOCATE PREPARE credit_balance_statement;

CREATE TABLE IF NOT EXISTS transactions (
    transaction_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transactions_sender (sender_id),
    INDEX idx_transactions_receiver (receiver_id),
    CONSTRAINT fk_transactions_sender
        FOREIGN KEY (sender_id) REFERENCES customers (customer_id),
    CONSTRAINT fk_transactions_receiver
        FOREIGN KEY (receiver_id) REFERENCES customers (customer_id)
) ENGINE=InnoDB;