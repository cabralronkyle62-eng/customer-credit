<?php

declare(strict_types=1);

require_once __DIR__ . '/../classes/Customer.php';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "SKIPPED: Enable pdo_sqlite to run the automated in-memory CRUD test.\n");
    exit(1);
}

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FAILED: ' . $message);
    }

    echo 'PASSED: ' . $message . PHP_EOL;
}

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec(
    'CREATE TABLE customers (
        customer_id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        address VARCHAR(255) NOT NULL,
        credit_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$pdo->exec(
    'CREATE TABLE transactions (
        transaction_id INTEGER PRIMARY KEY AUTOINCREMENT,
        sender_id INTEGER NOT NULL,
        receiver_id INTEGER NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

$customer = new Customer($pdo);
$customer->first_name = 'Rosa';
$customer->last_name = 'Santos';
$customer->contact_number = '0912 345 6789';
$customer->address = 'Bogo City, Cebu';

$id = $customer->addCustomer();
expect($id === 1, 'CREATE returns the new customer ID');
expect(count($customer->getCustomers()) === 1, 'READ returns the added record');

$saved = $customer->getCustomerById($id);
expect($saved !== null && $saved['first_name'] === 'Rosa', 'READ ONE fetches the correct customer');

$customer->address = 'Cebu City, Cebu';
expect($customer->updateCustomer($id), 'UPDATE executes successfully');
$updated = $customer->getCustomerById($id);
expect($updated !== null && $updated['address'] === 'Cebu City, Cebu', 'UPDATE stores the new address');

$matches = $customer->searchCustomers('Santos');
expect(count($matches) === 1, 'SEARCH finds a customer by last name');
expect($customer->getCustomerCount() === 1, 'EXTRA FUNCTION counts saved customers');

$receiver = new Customer($pdo);
$receiver->first_name = 'Marco';
$receiver->last_name = 'Reyes';
$receiver->contact_number = '0912 111 2222';
$receiver->address = 'Cebu City, Cebu';
$receiverId = $receiver->addCustomer();
$pdo->exec("UPDATE customers SET credit_balance = 50.00 WHERE customer_id = {$id}");
$pdo->exec("UPDATE customers SET credit_balance = 10.00 WHERE customer_id = {$receiverId}");

$customer->transferCredit($id, $receiverId, '12.50');
$balances = $pdo->query(
    "SELECT customer_id, credit_balance FROM customers WHERE customer_id IN ({$id}, {$receiverId})"
)->fetchAll(PDO::FETCH_KEY_PAIR);
expect((float) $balances[$id] === 37.5, 'TRANSFER deducts credit from the sender');
expect((float) $balances[$receiverId] === 22.5, 'TRANSFER adds credit to the receiver');
expect(
    (int) $pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn() === 1,
    'TRANSFER records the transaction'
);

try {
    $customer->transferCredit($id, $receiverId, '100.00');
    expect(false, 'TRANSFER rejects insufficient sender credit');
} catch (InvalidArgumentException $exception) {
    expect(true, 'TRANSFER rejects insufficient sender credit');
}

try {
    $customer->transferCredit($id, $receiverId, '0.00');
    expect(false, 'TRANSFER rejects a zero amount');
} catch (InvalidArgumentException $exception) {
    expect(true, 'TRANSFER rejects a zero amount');
}

try {
    $customer->transferCredit($id, 999, '1.00');
    expect(false, 'TRANSFER rejects a missing receiver');
} catch (InvalidArgumentException $exception) {
    expect(true, 'TRANSFER rejects a missing receiver');
}

expect(
    (int) $pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn() === 1,
    'Rejected transfers do not create transaction records'
);

expect($customer->deleteCustomer($id), 'DELETE removes the selected customer');
expect(count($customer->getCustomers()) === 1, 'READ confirms that the selected customer was deleted');

echo PHP_EOL . 'All Customer CRUD tests passed.' . PHP_EOL;
