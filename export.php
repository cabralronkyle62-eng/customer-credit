<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$search = trim((string) ($_GET['q'] ?? ''));

$customers = $search === ''
    ? $customerModel->getCustomers()
    : $customerModel->searchCustomers($search);

$filename = 'customers-' . date('Y-m-d-His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');

$output = fopen('php://output', 'wb');

if ($output === false) {
    http_response_code(500);
    exit('The CSV file could not be created.');
}

fwrite($output, "\xEF\xBB\xBF");

fputcsv(
    $output,
    [
        'Customer ID',
        'First Name',
        'Last Name',
        'Contact Number',
        'Address',
        'Credit Balance',
        'Date Added',
    ],
    ',',
    '"',
    ''
);

foreach ($customers as $customer) {
    fputcsv(
        $output,
        [
            $customer['customer_id'],
            $customer['first_name'],
            $customer['last_name'],
            $customer['contact_number'],
            $customer['address'],
            number_format(
                (float) ($customer['credit_balance'] ?? 0),
                2,
                '.',
                ''
            ),
            $customer['created_at'] ?? '',
        ],
        ',',
        '"',
        ''
    );
}

fclose($output);
exit;
