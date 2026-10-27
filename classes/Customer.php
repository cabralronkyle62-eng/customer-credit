<?php

declare(strict_types=1);

final class Customer
{
    private PDO $connection;
    private string $table = 'customers';

    public ?int $customer_id = null;
    public string $first_name = '';
    public string $last_name = '';
    public string $contact_number = '';
    public string $address = '';
    public string $credit_balance = '0.00';

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function addCustomer(): int
    {
        $sql = "INSERT INTO {$this->table}
                    (
                        customer_id,
                        first_name,
                        last_name,
                        contact_number,
                        address,
                        credit_balance
                    )
                VALUES
                    (
                        :customer_id,
                        :first_name,
                        :last_name,
                        :contact_number,
                        :address,
                        :credit_balance
                    )";

        try {
            $this->connection->beginTransaction();

            $nextCustomerId = $this->getLowestAvailableId();
            $parameters = $this->propertyParameters();
            $parameters['customer_id'] = $nextCustomerId;

            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);

            $this->connection->commit();

            return $nextCustomerId;
        } catch (PDOException $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $this->databaseError($exception);
        }
    }

    public function getCustomers(): array
    {
        $sql = "SELECT
                    customer_id,
                    first_name,
                    last_name,
                    contact_number,
                    address,
                    credit_balance,
                    created_at
                FROM {$this->table}
                ORDER BY customer_id ASC";

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    public function getCustomerById(int $id): ?array
    {
        $sql = "SELECT
                    customer_id,
                    first_name,
                    last_name,
                    contact_number,
                    address,
                    credit_balance,
                    created_at
                FROM {$this->table}
                WHERE customer_id = :customer_id
                LIMIT 1";

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                'customer_id' => $id,
            ]);

            $customer = $statement->fetch();

            return $customer === false ? null : $customer;
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    public function transferCredit(int $senderId, int $receiverId, string $amount): void
    {
        if ($senderId <= 0 || $receiverId <= 0) {
            throw new InvalidArgumentException('Select a valid sender and receiver.');
        }

        if ($senderId === $receiverId) {
            throw new InvalidArgumentException('Sender and receiver must be different customers.');
        }

        if (!preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $amount)) {
            throw new InvalidArgumentException('Enter an amount between 0.01 and 99,999,999.99.');
        }

        [$wholeAmount, $fractionalAmount] = array_pad(explode('.', $amount, 2), 2, '');
        $fractionalAmount = str_pad($fractionalAmount, 2, '0');

        if ((int) $wholeAmount === 0 && (int) $fractionalAmount === 0) {
            throw new InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        $normalizedAmount = (string) (int) $wholeAmount . '.' . $fractionalAmount;

        try {
            $this->connection->beginTransaction();

            $lockClause = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
                ? ' FOR UPDATE'
                : '';
            $statement = $this->connection->prepare(
                "SELECT customer_id
                 FROM {$this->table}
                 WHERE customer_id IN (:sender_id, :receiver_id)
                 ORDER BY customer_id{$lockClause}"
            );
            $statement->execute([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
            ]);

            if (count($statement->fetchAll()) !== 2) {
                throw new InvalidArgumentException('The selected sender or receiver does not exist.');
            }

            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET credit_balance = credit_balance - :amount
                 WHERE customer_id = :customer_id
                   AND credit_balance >= :minimum_balance"
            );
            $statement->execute([
                'amount' => $normalizedAmount,
                'customer_id' => $senderId,
                'minimum_balance' => $normalizedAmount,
            ]);

            if ($statement->rowCount() !== 1) {
                throw new InvalidArgumentException('The sender does not have enough credit for this transfer.');
            }

            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET credit_balance = credit_balance + :amount
                 WHERE customer_id = :customer_id"
            );
            $statement->execute([
                'amount' => $normalizedAmount,
                'customer_id' => $receiverId,
            ]);

            $statement = $this->connection->prepare(
                'INSERT INTO transactions (sender_id, receiver_id, amount)
                 VALUES (:sender_id, :receiver_id, :amount)'
            );
            $statement->execute([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $normalizedAmount,
            ]);

            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            if ($exception instanceof PDOException) {
                throw $this->databaseError($exception);
            }

            throw $exception;
        }
    }

    public function updateCustomer(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET first_name = :first_name,
                    last_name = :last_name,
                    contact_number = :contact_number,
                    address = :address,
                    credit_balance = :credit_balance
                WHERE customer_id = :customer_id";

        $parameters = $this->propertyParameters();
        $parameters['customer_id'] = $id;

        try {
            $statement = $this->connection->prepare($sql);

            return $statement->execute($parameters);
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    public function deleteCustomer(int $id): bool
    {
        $sql = "DELETE FROM {$this->table}
                WHERE customer_id = :customer_id";

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                'customer_id' => $id,
            ]);

            return $statement->rowCount() > 0;
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    public function searchCustomers(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return $this->getCustomers();
        }

        $sql = "SELECT
                    customer_id,
                    first_name,
                    last_name,
                    contact_number,
                    address,
                    credit_balance,
                    created_at
                FROM {$this->table}
                WHERE first_name LIKE :first_name
                   OR last_name LIKE :last_name
                   OR contact_number LIKE :contact_number
                   OR address LIKE :address
                ORDER BY last_name ASC, first_name ASC";

        $searchTerm = '%' . $keyword . '%';

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                'first_name' => $searchTerm,
                'last_name' => $searchTerm,
                'contact_number' => $searchTerm,
                'address' => $searchTerm,
            ]);

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    public function duplicateCustomerExists(
        string $firstName,
        string $lastName,
        string $contactNumber,
        ?int $excludeId = null
    ): bool {
        $sql = "SELECT COUNT(*)
                FROM {$this->table}
                WHERE LOWER(first_name) = LOWER(:first_name)
                  AND LOWER(last_name) = LOWER(:last_name)
                  AND contact_number = :contact_number";

        $parameters = [
            'first_name' => trim($firstName),
            'last_name' => trim($lastName),
            'contact_number' => trim($contactNumber),
        ];

        if ($excludeId !== null) {
            $sql .= " AND customer_id <> :exclude_id";
            $parameters['exclude_id'] = $excludeId;
        }

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    public function getCustomerCount(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (PDOException $exception) {
            throw $this->databaseError($exception);
        }
    }

    private function propertyParameters(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'credit_balance' => $this->credit_balance,
        ];
    }

    private function getLowestAvailableId(): int
    {
        $sql = "SELECT CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM {$this->table}
                        WHERE customer_id = 1
                    ) THEN 1
                    ELSE (
                        SELECT MIN(current_customer.customer_id + 1)
                        FROM {$this->table} AS current_customer
                        LEFT JOIN {$this->table} AS next_customer
                            ON next_customer.customer_id = current_customer.customer_id + 1
                        WHERE next_customer.customer_id IS NULL
                    )
                END AS next_customer_id";

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    private function databaseError(PDOException $exception): RuntimeException
    {
        error_log(
            'Customer database operation failed: ' . $exception->getMessage()
        );

        return new RuntimeException(
            'The customer database operation could not be completed. Please try again.'
        );
    }
}
