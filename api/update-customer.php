<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.',
    ]);

    exit;
}

require_once __DIR__ . '/../config/Database.php';

try {
    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        throw new RuntimeException('Invalid request data.');
    }

    $customerId = (int) ($input['customer_id'] ?? 0);
    $firstName = trim((string) ($input['first_name'] ?? ''));
    $lastName = trim((string) ($input['last_name'] ?? ''));
    $contactNumber = trim((string) ($input['contact_number'] ?? ''));
    $address = trim((string) ($input['address'] ?? ''));
    $creditBalance = trim((string) ($input['credit_balance'] ?? '0.00'));

    if ($customerId <= 0) {
        throw new RuntimeException('Invalid customer ID.');
    }

    if ($firstName === '') {
        throw new RuntimeException('First name is required.');
    }

    if ($lastName === '') {
        throw new RuntimeException('Last name is required.');
    }

    if ($contactNumber === '') {
        throw new RuntimeException('Contact number is required.');
    }

    if ($address === '') {
        throw new RuntimeException('Address is required.');
    }

    if (
        !is_numeric($creditBalance) ||
        (float) $creditBalance < 0
    ) {
        throw new RuntimeException(
            'Credit balance must be 0 or higher.'
        );
    }

    $database = new Database();
    $pdo = $database->connect();

    $duplicate = $pdo->prepare(
        'SELECT COUNT(*)
         FROM customers
         WHERE LOWER(first_name) = LOWER(:first_name)
           AND LOWER(last_name) = LOWER(:last_name)
           AND contact_number = :contact_number
           AND customer_id <> :customer_id'
    );

    $duplicate->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'contact_number' => $contactNumber,
        'customer_id' => $customerId,
    ]);

    if ((int) $duplicate->fetchColumn() > 0) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
                'Another customer already has the same name and contact number.',
        ]);

        exit;
    }

    $statement = $pdo->prepare(
        'UPDATE customers
         SET first_name = :first_name,
             last_name = :last_name,
             contact_number = :contact_number,
             address = :address,
             credit_balance = :credit_balance
         WHERE customer_id = :customer_id'
    );

    $statement->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'contact_number' => $contactNumber,
        'address' => $address,
        'credit_balance' => number_format(
            (float) $creditBalance,
            2,
            '.',
            ''
        ),
        'customer_id' => $customerId,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Customer updated successfully.',
    ]);
} catch (Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
