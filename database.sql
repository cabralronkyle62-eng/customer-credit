CREATE TABLE IF NOT EXISTS customers (
    customer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    credit_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_name (last_name, first_name)
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;