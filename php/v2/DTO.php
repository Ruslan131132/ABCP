<?php

namespace NW\WebService\References\Operations\Notification\DTO;

class RequestData
{
    public int $resellerId;
    public int $notificationType;
    public int $clientId;
    public int $creatorId;
    public int $expertId;
    public int $complaintId;
    public string $complaintNumber;
    public int $consumptionId;
    public string $consumptionNumber;
    public string $agreementNumber;
    public string $date;
    public array $differences = [];

    public function __construct(array $data)
    {
        $this->resellerId = (int)($data['resellerId'] ?? 0);
        $this->notificationType = (int)($data['notificationType'] ?? 0);
        $this->clientId = (int)($data['clientId'] ?? 0);
        $this->creatorId = (int)($data['creatorId'] ?? 0);
        $this->expertId = (int)($data['expertId'] ?? 0);
        $this->complaintId = (int)($data['complaintId'] ?? 0);
        $this->complaintNumber = (string)($data['complaintNumber'] ?? '');
        $this->consumptionId = (int)($data['consumptionId'] ?? 0);
        $this->consumptionNumber = (string)($data['consumptionNumber'] ?? '');
        $this->agreementNumber = (string)($data['agreementNumber'] ?? '');
        $this->date = (string)($data['date'] ?? '');
        $this->differences = (array)($data['differences'] ?? []);
    }
}

class ResponseData
{
    public bool $notificationEmployeeByEmail = false;
    public bool $notificationClientByEmail = false;
    public SmsNotification $notificationClientBySms;

    public function __construct()
    {
        $this->notificationClientBySms = new SmsNotification();
    }
}

class SmsNotification
{
    public bool $isSent = false;
    public string $message = '';
}

class TemplateData
{
    public int $complaintId;
    public string $complaintNumber;
    public int $creatorId;
    public string $creatorName;
    public int $expertId;
    public string $expertName;
    public int $clientId;
    public string $clientName;
    public int $consumptionId;
    public string $consumptionNumber;
    public string $agreementNumber;
    public string $date;
    public string $differences;

    public function __construct(array $data)
    {
        $this->complaintId = (int)$data['complaintId'];
        $this->complaintNumber = (string)$data['complaintNumber'];
        $this->creatorId = (int)$data['creatorId'];
        $this->creatorName = (string)$data['creatorName'];
        $this->expertId = (int)$data['expertId'];
        $this->expertName = (string)$data['expertName'];
        $this->clientId = (int)$data['clientId'];
        $this->clientName = (string)$data['clientName'];
        $this->consumptionId = (int)$data['consumptionId'];
        $this->consumptionNumber = (string)$data['consumptionNumber'];
        $this->agreementNumber = (string)$data['agreementNumber'];
        $this->date = (string)$data['date'];
        $this->differences = (string)$data['differences'];
    }
}
