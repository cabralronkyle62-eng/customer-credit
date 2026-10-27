<?php

declare(strict_types=1);


function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}


function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}


function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $submittedToken): void
{
    $savedToken = $_SESSION['csrf_token'] ?? '';

    if ($submittedToken === null || $savedToken === '' || !hash_equals($savedToken, $submittedToken)) {
        http_response_code(419);
        exit('Invalid form token. Please go back, refresh the page, and try again.');
    }
}

function customerFormData(array $source): array
{
    return [
        'first_name' => trim((string) ($source['first_name'] ?? '')),
        'last_name' => trim((string) ($source['last_name'] ?? '')),
        'contact_number' => trim((string) ($source['contact_number'] ?? '')),
        'address' => trim((string) ($source['address'] ?? '')),
    ];
}

function validateCustomer(array $data): array
{
    $errors = [];

    if ($data['first_name'] === '') {
        $errors['first_name'] = 'First name is required.';
    } elseif (textLength($data['first_name']) > 100) {
        $errors['first_name'] = 'First name must be 100 characters or fewer.';
    }

    if ($data['last_name'] === '') {
        $errors['last_name'] = 'Last name is required.';
    } elseif (textLength($data['last_name']) > 100) {
        $errors['last_name'] = 'Last name must be 100 characters or fewer.';
    }

    if ($data['contact_number'] === '') {
        $errors['contact_number'] = 'Contact number is required.';
    } elseif (textLength($data['contact_number']) > 20) {
        $errors['contact_number'] = 'Contact number must be 20 characters or fewer.';
    } elseif (!preg_match('/^[0-9+()\-\s]+$/', $data['contact_number'])) {
        $errors['contact_number'] = 'Use only numbers, spaces, +, -, and parentheses.';
    }

    if ($data['address'] === '') {
        $errors['address'] = 'Address is required.';
    } elseif (textLength($data['address']) > 255) {
        $errors['address'] = 'Address must be 255 characters or fewer.';
    }

    return $errors;
}

function fillCustomer(Customer $customer, array $data): void
{
    $customer->first_name = $data['first_name'];
    $customer->last_name = $data['last_name'];
    $customer->contact_number = $data['contact_number'];
    $customer->address = $data['address'];
}

function positiveId($value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $id === false ? 0 : (int) $id;
}
