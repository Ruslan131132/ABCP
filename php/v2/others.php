<?php

namespace NW\WebService\References\Operations\Notification;

class Contractor
{
    const TYPE_CUSTOMER = 0;

    public function __construct(
        private int $id,
        private string $name,
        private int $type = self::TYPE_CUSTOMER,
        private ?string $email = null,
        private ?string $mobile = null
    ) {}

    public static function getById(int $resellerId): self
    {
        // Mock data for the example
        return new self($resellerId, 'Reseller Name', self::TYPE_CUSTOMER, 'client@example.com', '1234567890');
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }
}

class Seller extends Contractor
{
    public function __construct(int $id, string $name, int $type = Contractor::TYPE_CUSTOMER)
    {
        parent::__construct($id, $name, $type);
    }
}

class Employee extends Contractor
{
    public function __construct(int $id, string $name, int $type = Contractor::TYPE_CUSTOMER)
    {
        parent::__construct($id, $name, $type);
    }
}

class Status
{
    const COMPLETED = 0;
    const PENDING = 1;
    const REJECTED = 2;

    private static array $statuses = [
        self::COMPLETED => 'Completed',
        self::PENDING => 'Pending',
        self::REJECTED => 'Rejected',
    ];

    public function __construct(
        private int $id,
        private string $name
    ) {}

    public static function getNameById(int $id): string
    {
        return self::$statuses[$id] ?? 'Unknown';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

abstract class ReferencesOperation
{
    abstract public function doOperation(): array;

    public function getRequestValue(string $parameter): ?string
    {
        return $_REQUEST[$parameter] ?? null;
    }
}

// Реализовал в виде класса - а вообще нужно в конфиг перенести.
class Config
{
    public static function getResellerEmailFrom(): string
    {
        return 'contractor@example.com';
    }

    public static function getEmailsByPermit(int $resellerId, NotificationEvents $event): array
    {
        // Fake the method
        return ['someemail@example.com', 'someemail2@example.com'];
    }
}

enum NotificationEvents: string
{
    case CHANGE_RETURN_STATUS = 'changeReturnStatus';
    case NEW_RETURN_STATUS = 'newReturnStatus';
}
