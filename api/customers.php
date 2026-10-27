<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Customer.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    $customerModel = new Customer($pdo);
    $customers = $customerModel->getCustomers();

    $totalCredit = 0.0;

    foreach ($customers as $customer) {
        $totalCredit += (float) ($customer['credit_balance'] ?? 0);
    }

    echo json_encode(
        [
            'success' => true,
            'count' => count($customers),
            'total_credit' => number_format($totalCredit, 2, '.', ''),
            'customers' => $customers,
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $exception) {
    http_response_code(500);

    echo json_encode(
        [
            'success' => false,
            'message' => $exception->getMessage(),
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
}
