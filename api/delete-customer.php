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

    if ($customerId <= 0) {
        throw new RuntimeException('Invalid customer ID.');
    }

    $database = new Database();
    $pdo = $database->connect();

    $check = $pdo->prepare(
        'SELECT customer_id
         FROM customers
         WHERE customer_id = :customer_id'
    );

    $check->execute([
        'customer_id' => $customerId,
    ]);

    if (!$check->fetch()) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Customer not found.',
        ]);

        exit;
    }

    $statement = $pdo->prepare(
        'DELETE FROM customers
         WHERE customer_id = :customer_id'
    );

    $statement->execute([
        'customer_id' => $customerId,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Customer deleted successfully.',
    ]);
} catch (Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
