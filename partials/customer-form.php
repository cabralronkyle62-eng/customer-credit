<?php

declare(strict_types=1);

$data = $data ?? [
    'first_name' => '',
    'last_name' => '',
    'contact_number' => '',
    'address' => '',
    'credit_balance' => '0.00',
];

$errors = $errors ?? [];
$submitLabel = $submitLabel ?? 'Save customer';
?>

<?php if (isset($errors['database'])): ?>
    <div class="alert alert-error" role="alert">
        <?= e($errors['database']) ?>
    </div>
<?php endif; ?>

<form method="post" class="customer-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-grid">
        <div class="form-group">
            <label for="first_name">
                First name <span aria-hidden="true">*</span>
            </label>

            <input
                id="first_name"
                name="first_name"
                type="text"
                maxlength="100"
                value="<?= e($data['first_name']) ?>"
                class="<?= isset($errors['first_name']) ? 'input-error' : '' ?>"
                autocomplete="given-name"
                required
            >

            <?php if (isset($errors['first_name'])): ?>
                <small class="error-text">
                    <?= e($errors['first_name']) ?>
                </small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="last_name">
                Last name <span aria-hidden="true">*</span>
            </label>

            <input
                id="last_name"
                name="last_name"
                type="text"
                maxlength="100"
                value="<?= e($data['last_name']) ?>"
                class="<?= isset($errors['last_name']) ? 'input-error' : '' ?>"
                autocomplete="family-name"
                required
            >

            <?php if (isset($errors['last_name'])): ?>
                <small class="error-text">
                    <?= e($errors['last_name']) ?>
                </small>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_number">
            Contact number <span aria-hidden="true">*</span>
        </label>

        <input
            id="contact_number"
            name="contact_number"
            type="tel"
            maxlength="20"
            value="<?= e($data['contact_number']) ?>"
            class="<?= isset($errors['contact_number']) ? 'input-error' : '' ?>"
            placeholder="Example: 0912 345 6789"
            autocomplete="tel"
            required
        >

        <?php if (isset($errors['contact_number'])): ?>
            <small class="error-text">
                <?= e($errors['contact_number']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="address">
            Address <span aria-hidden="true">*</span>
        </label>

        <textarea
            id="address"
            name="address"
            rows="4"
            maxlength="255"
            class="<?= isset($errors['address']) ? 'input-error' : '' ?>"
            autocomplete="street-address"
            required
        ><?= e($data['address']) ?></textarea>

        <?php if (isset($errors['address'])): ?>
            <small class="error-text">
                <?= e($errors['address']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="credit_balance">Credit balance</label>

        <input
            id="credit_balance"
            name="credit_balance"
            type="number"
            min="0"
            step="0.01"
            value="<?= e($data['credit_balance'] ?? '0.00') ?>"
            class="<?= isset($errors['credit_balance']) ? 'input-error' : '' ?>"
            placeholder="0.00"
        >

        <small>Enter 0.00 if the customer has no credit balance.</small>

        <?php if (isset($errors['credit_balance'])): ?>
            <small class="error-text">
                <?= e($errors['credit_balance']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="index.php">Cancel</a>

        <button class="button button-primary" type="submit">
            <?= e($submitLabel) ?>
        </button>
    </div>
</form>
