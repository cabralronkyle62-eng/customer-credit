<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);
$customerId = positiveId($_POST['customer_id'] ?? 0);

if ($customerId === 0) {
    setFlash('error', 'Please select a valid customer record.');
    redirect('index.php');
}

try {
    $deleted = $customerModel->deleteCustomer($customerId);
    setFlash(
        $deleted ? 'success' : 'error',
        $deleted
            ? 'Customer #' . $customerId . ' was deleted successfully.'
            : 'The selected customer record was not found.'
    );
} catch (RuntimeException $exception) {
    setFlash('error', $exception->getMessage());
}

redirect('index.php');
