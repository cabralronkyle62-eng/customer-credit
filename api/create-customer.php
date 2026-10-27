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
require_once __DIR__ . '/../classes/Customer.php';

try {
    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        throw new RuntimeException(
            'Invalid request data.'
        );
    }

    $firstName = trim(
        (string) ($input['first_name'] ?? '')
    );

    $lastName = trim(
        (string) ($input['last_name'] ?? '')
    );

    $contactNumber = trim(
        (string) ($input['contact_number'] ?? '')
    );

    $address = trim(
        (string) ($input['address'] ?? '')
    );

    $creditBalance = trim(
        (string) ($input['credit_balance'] ?? '0.00')
    );

    if ($firstName === '') {
        throw new RuntimeException(
            'First name is required.'
        );
    }

    if ($lastName === '') {
        throw new RuntimeException(
            'Last name is required.'
        );
    }

    if ($contactNumber === '') {
        throw new RuntimeException(
            'Contact number is required.'
        );
    }

    if ($address === '') {
        throw new RuntimeException(
            'Address is required.'
        );
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

    $customer = new Customer($pdo);

    if (
        $customer->duplicateCustomerExists(
            $firstName,
            $lastName,
            $contactNumber
        )
    ) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
                'This customer already exists.',
        ]);

        exit;
    }

    $customer->first_name = $firstName;
    $customer->last_name = $lastName;
    $customer->contact_number = $contactNumber;
    $customer->address = $address;

    $customer->credit_balance = number_format(
        (float) $creditBalance,
        2,
        '.',
        ''
    );

    $customer->addCustomer();

    echo json_encode([
        'success' => true,
        'message' => 'Customer added successfully.',
    ]);
} catch (Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}