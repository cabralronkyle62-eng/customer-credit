<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);

    $senderId = positiveId($_POST['sender_id'] ?? 0);
    $receiverId = positiveId($_POST['receiver_id'] ?? 0);
    $amount = trim((string) ($_POST['amount'] ?? ''));

    try {
        $customerModel->transferCredit($senderId, $receiverId, $amount);
        setFlash('success', 'Credit transfer completed successfully.');
    } catch (InvalidArgumentException $exception) {
        setFlash('error', $exception->getMessage());
    } catch (RuntimeException $exception) {
        setFlash('error', $exception->getMessage());
    }

    redirect('transfers.php');
}

$customers = $customerModel->getCustomers();
$flash = getFlash();
$pageTitle = 'Transfers';
$activePage = 'transfers';
require __DIR__ . '/partials/header.php';
?>

<section class="form-page">
    <div class="form-heading">
        <p class="eyebrow">Credit management</p>
        <h1>Transfer credit</h1>
        <p>Move available credit from one customer to another.</p>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" role="status">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card form-card">
        <form method="post" action="transfers.php" class="customer-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <div class="form-group">
                <label for="sender_id">Transfer from</label>
                <select id="sender_id" name="sender_id" required>
                    <option value="">Choose a sender</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= (int) $customer['customer_id'] ?>">
                            <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                            — <?= e(number_format((float) $customer['credit_balance'], 2)) ?> credit
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="receiver_id">Transfer to</label>
                <select id="receiver_id" name="receiver_id" required>
                    <option value="">Choose a receiver</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= (int) $customer['customer_id'] ?>">
                            <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount</label>
                <input
                    type="number"
                    id="amount"
                    name="amount"
                    min="0.01"
                    max="99999999.99"
                    step="0.01"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">Transfer credit</button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
