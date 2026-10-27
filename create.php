<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$data = [
    'first_name' => '',
    'last_name' => '',
    'contact_number' => '',
    'address' => '',
    'credit_balance' => '0.00',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);

    $data = customerFormData($_POST);
    $data['credit_balance'] = trim(
        (string) ($_POST['credit_balance'] ?? '0.00')
    );

    $errors = validateCustomer($data);

    if (
        $data['credit_balance'] === ''
        || !is_numeric($data['credit_balance'])
        || (float) $data['credit_balance'] < 0
    ) {
        $errors['credit_balance'] =
            'Credit balance must be 0.00 or a positive amount.';
    }

    if ($errors === []) {
        try {
            $isDuplicate = $customerModel->duplicateCustomerExists(
                $data['first_name'],
                $data['last_name'],
                $data['contact_number']
            );

            if ($isDuplicate) {
                $errors['database'] =
                    'This customer already exists. Please check the name and contact number.';
            } else {
                fillCustomer($customerModel, $data);

                $customerModel->credit_balance = number_format(
                    (float) $data['credit_balance'],
                    2,
                    '.',
                    ''
                );

                $newCustomerId = $customerModel->addCustomer();

                setFlash(
                    'success',
                    'Customer #' . $newCustomerId . ' was added successfully.'
                );

                redirect('index.php');
            }
        } catch (RuntimeException $exception) {
            $errors['database'] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Add Customer';
require __DIR__ . '/partials/header.php';
?>

<section class="form-page">
    <div class="form-heading">
        <p class="eyebrow">Create operation</p>
        <h1>Add a customer</h1>
        <p>Enter the customer information and current credit balance.</p>
    </div>

    <div class="card form-card">
        <?php
        $submitLabel = 'Add customer';
        require __DIR__ . '/partials/customer-form.php';
        ?>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
