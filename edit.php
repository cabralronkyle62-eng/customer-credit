<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$customerId = positiveId($_GET['id'] ?? 0);

if ($customerId === 0) {
    setFlash('error', 'Please select a valid customer record.');
    redirect('index.php');
}

$savedCustomer = $customerModel->getCustomerById($customerId);

if ($savedCustomer === null) {
    setFlash('error', 'The selected customer record does not exist.');
    redirect('index.php');
}

$data = [
    'first_name' => $savedCustomer['first_name'],
    'last_name' => $savedCustomer['last_name'],
    'contact_number' => $savedCustomer['contact_number'],
    'address' => $savedCustomer['address'],
    'credit_balance' => $savedCustomer['credit_balance'] ?? '0.00',
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
                $data['contact_number'],
                $customerId
            );

            if ($isDuplicate) {
                $errors['database'] =
                    'Another customer already has the same name and contact number.';
            } else {
                fillCustomer($customerModel, $data);

                $customerModel->credit_balance = number_format(
                    (float) $data['credit_balance'],
                    2,
                    '.',
                    ''
                );

                $customerModel->updateCustomer($customerId);

                setFlash(
                    'success',
                    'Customer #' . $customerId . ' was updated successfully.'
                );

                redirect('index.php');
            }
        } catch (RuntimeException $exception) {
            $errors['database'] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Edit Customer';
require __DIR__ . '/partials/header.php';
?>

<section class="form-page">
    <div class="form-heading">
        <p class="eyebrow">Update operation</p>
        <h1>Edit customer #<?= $customerId ?></h1>
        <p>Change the details or update the customer's credit balance.</p>
    </div>

    <div class="card form-card">
        <?php
        $submitLabel = 'Save changes';
        require __DIR__ . '/partials/customer-form.php';
        ?>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
