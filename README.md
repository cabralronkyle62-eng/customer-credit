# Customer Credit Monitoring System

A PHP and MySQL customer credit management system with customer records, balance tracking, and logged credit transfers.

## Requirements

- PHP with PDO and the PDO MySQL driver
- MySQL or MariaDB
- Apache or another PHP-capable web server

## Setup

1. Create a database named `customer_credit_monitoring`.
2. Import `database.sql` to create the tables.
3. Configure the database connection using the `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables.
   For a local-only password override, create `config/database.local.php` returning an array with a `password` key. This file is ignored by Git and must not be published.
4. Configure the web server document root to this project directory.
5. Open `register.php` to create an administrator account.

For an existing installation, run `add_credit_balance.sql` once to add the credit balance column and transaction log table if they do not already exist.

## Tests

Run the customer and transfer tests with:

```sh
php tests/CustomerTest.php
```

The test requires the PDO SQLite extension.
