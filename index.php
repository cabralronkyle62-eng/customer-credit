<?php

require_once __DIR__ . '/includes/bootstrap.php';
requireLogin(); // Protects the page against unauthorized access



require_once __DIR__ . '/includes/bootstrap.php';

$search = trim((string) ($_GET['q'] ?? ''));
$sort = $_GET['sort'] ?? 'id';

$customers = $search === ''
    ? $customerModel->getCustomers()
    : $customerModel->searchCustomers($search);

/* Sort customers */
if ($sort === 'az') {
    usort($customers, function ($a, $b) {
        $nameA = $a['last_name'] . ' ' . $a['first_name'];
        $nameB = $b['last_name'] . ' ' . $b['first_name'];

        return strcasecmp($nameA, $nameB);
    });
} elseif ($sort === 'za') {
    usort($customers, function ($a, $b) {
        $nameA = $a['last_name'] . ' ' . $a['first_name'];
        $nameB = $b['last_name'] . ' ' . $b['first_name'];

        return strcasecmp($nameB, $nameA);
    });
}

$totalCustomers = $customerModel->getCustomerCount();
$flash = getFlash();

$pageTitle = 'Customers';
$activePage = 'customers';
require __DIR__ . '/partials/header.php';
?>

<style>
/* Modern sort filter - matches the system theme */
.sort-filter {
    position: relative;
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    min-width: 170px;
    height: 50px;
    background: #ffffff;
    border: 1px solid #cfe0df;
    border-radius: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.sort-filter:hover {
    border-color: #2f948c;
}

.sort-filter:focus-within {
    border-color: #2f948c;
    box-shadow: 0 0 0 3px rgba(47, 148, 140, 0.12);
}

.sort-filter-icon {
    position: absolute;
    left: 15px;
    color: #2f948c;
    font-size: 17px;
    font-weight: 700;
    line-height: 1;
    pointer-events: none;
}

.sort-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 100%;
    height: 100%;
    padding: 0 42px 0 43px;
    border: 0;
    outline: 0;
    background: transparent;
    color: #26383c;
    font: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: none;
}

.sort-select:focus {
    outline: 0;
    box-shadow: none;
}

.sort-filter::after {
    content: "";
    position: absolute;
    right: 17px;
    top: 50%;
    width: 7px;
    height: 7px;
    border-right: 2px solid #607b7e;
    border-bottom: 2px solid #607b7e;
    transform: translateY(-70%) rotate(45deg);
    pointer-events: none;
}

.sort-select option {
    background: #ffffff;
    color: #26383c;
}

@media (max-width: 700px) {
    .sort-filter {
        width: 100%;
        min-width: 100%;
    }
}


/* =========================
   PRINT CUSTOMER LIST
========================= */
.button-print {
    background: #ffffff;
    color: #2f756f;
    border: 1px solid #bcd6d3;
}

.button-print:hover {
    background: #f3f9f8;
    border-color: #2f948c;
}

@media print {
    body {
        background: #ffffff !important;
    }

    header,
    nav,
    footer,
    .controls-card,
    .summary-card,
    .alert,
    .button,
    .print-hide {
        display: none !important;
    }

    .hero-row {
        display: block !important;
        margin-bottom: 18px !important;
    }

    .hero-row h1 {
        margin: 0 0 6px !important;
        color: #000000 !important;
    }

    .page-intro {
        margin: 0 !important;
        color: #444444 !important;
    }

    .card,
    .table-card {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #ffffff !important;
    }

    .table-heading {
        padding: 0 0 12px !important;
        border: none !important;
    }

    .table-heading h2 {
        color: #000000 !important;
    }

    .table-heading p {
        color: #555555 !important;
    }

    .table-wrapper {
        overflow: visible !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 12px !important;
    }

    th,
    td {
        border: 1px solid #b8b8b8 !important;
        padding: 8px !important;
        color: #000000 !important;
        background: #ffffff !important;
        text-align: left !important;
    }

    th {
        font-weight: 700 !important;
    }

    @page {
        margin: 14mm;
    }
}

</style>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<section class="hero-row">
    <div>
        <h1>Customer records</h1>
        <p class="page-intro">Add, view, update, delete, search, and export the store's customer information.</p>
    </div>

    <div class="summary-card" aria-label="Total customers">
        <span>Total customers</span>
        <strong><?= number_format($totalCustomers) ?></strong>
    </div>
</section>

<section class="card controls-card" aria-label="Customer search and export">
    <form class="search-form" method="get" action="index.php">

        <div class="sort-filter">
            <span class="sort-filter-icon" aria-hidden="true">⇅</span>

            <label class="sr-only" for="sort">Sort customers</label>
            <select
                name="sort"
                id="sort"
                class="sort-select"
                onchange="this.form.submit()"
            >
                <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>
                    Default
                </option>
                <option value="az" <?= $sort === 'az' ? 'selected' : '' ?>>
                    Name A–Z
                </option>
                <option value="za" <?= $sort === 'za' ? 'selected' : '' ?>>
                    Name Z–A
                </option>
            </select>
        </div>

        <label class="sr-only" for="search">Search customers</label>
        <input
            id="search"
            name="q"
            type="search"
            value="<?= e($search) ?>"
            placeholder="Search name, contact number, or address"
        >

        <button class="button button-primary" type="submit">Search</button>

        <?php if ($search !== '' || $sort !== 'id'): ?>
            <a class="button button-secondary" href="index.php">Clear</a>
        <?php endif; ?>
    </form>

    <div class="list-actions">
        <button
            type="button"
            class="button button-print"
            onclick="window.print()"
        >
            Print List
        </button>

        <a class="button button-export" href="export.php<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>">
            Export CSV
        </a>
    </div>
</section>

<section class="card table-card">
    <div class="table-heading">
        <div>
            <h2><?= $search === '' ? 'All customers' : 'Search results' ?></h2>
            <p><?= count($customers) ?> record<?= count($customers) === 1 ? '' : 's' ?> shown</p>
        </div>

        <a class="button button-primary button-small" href="create.php">
            + Add customer
        </a>
    </div>

    <?php if ($customers === []): ?>
        <div class="empty-state">
            <h3>No customer records found</h3>

            <p>
                <?= $search === ''
                    ? 'Add the first customer to begin.'
                    : 'Try a different search keyword.' ?>
            </p>

            <a
                class="button button-primary"
                href="<?= $search === '' ? 'create.php' : 'index.php' ?>"
            >
                <?= $search === '' ? 'Add first customer' : 'Show all customers' ?>
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Customer name</th>
                        <th scope="col">Contact number</th>
                        <th scope="col">Address</th>
                        <th scope="col">Credit Balance</th>
                        <th scope="col">Date Added</th>
                        <th scope="col" class="actions-column print-hide">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td data-label="ID">
                                #<?= (int) $customer['customer_id'] ?>
                            </td>

                            <td data-label="Customer name">
                                <strong>
                                    <?= e($customer['last_name'] . ', ' . $customer['first_name']) ?>
                                </strong>
                            </td>

                            <td data-label="Contact number">
                                <?= e($customer['contact_number']) ?>
                            </td>

                            <td data-label="Address">
                                <?= e($customer['address']) ?>
                            </td>

                            <td data-label="Credit Balance">
                                ₱<?= number_format(
                                    (float) ($customer['credit_balance'] ?? 0),
                                    2
                                ) ?>
                            </td>

                            <td data-label="Date Added">
                                <?= !empty($customer['created_at'])
                                    ? e(date('M d, Y h:i A', strtotime($customer['created_at'])))
                                    : '—' ?>
                            </td>

                            <td data-label="Actions" class="print-hide">
                                <div class="row-actions">
                                    <a
                                        class="button button-edit button-small"
                                        href="edit.php?id=<?= (int) $customer['customer_id'] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form method="post" action="delete.php" data-delete-form>
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrfToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="customer_id"
                                            value="<?= (int) $customer['customer_id'] ?>"
                                        >

                                        <button
                                            class="button button-danger button-small"
                                            type="submit"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
