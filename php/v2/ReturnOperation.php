<?php

namespace NW\WebService\References\Operations\Notification;

use NW\WebService\References\Operations\Notification\DTO\RequestData;
use NW\WebService\References\Operations\Notification\DTO\ResponseData;
use NW\WebService\References\Operations\Notification\DTO\SmsNotification;
use NW\WebService\References\Operations\Notification\DTO\TemplateData;
use Exception;

class TsReturnOperation extends ReferencesOperation
{
    public const TYPE_NEW    = 1;
    public const TYPE_CHANGE = 2;

    /**
     * @throws Exception
     */
    public function doOperation(): array
    {
        $data = new RequestData($this->getRequestValue('data'));
        $result = new ResponseData();

        if ($data->resellerId === 0) {
            $result->notificationClientBySms->message = 'Empty resellerId';

            return (array)$result;
        }

        if ($data->notificationType === 0) {
            throw new Exception('Empty notificationType', 400);
        }

        $reseller = Seller::getById($data->resellerId);
        $this->ensureEntityExists($reseller, 'Seller not found!');

        $client = Contractor::getById($data->clientId);
        $this->ensureValidClient($client, $data->resellerId);

        $creator = Employee::getById($data->creatorId);
        $this->ensureEntityExists($creator, 'Creator not found!');

        $expert = Employee::getById($data->expertId);
        $this->ensureEntityExists($expert, 'Expert not found!');

        $differences = $this->getDifferences($data->notificationType, $data->differences, $data->resellerId);

        $templateData = new TemplateData([
            'complaintId'       => $data->complaintId,
            'complaintNumber'   => $data->complaintNumber,
            'creatorId'         => $creator->getId(),
            'creatorName'       => $creator->getFullName(),
            'expertId'          => $expert->getId(),
            'expertName'        => $expert->getFullName(),
            'clientId'          => $client->getId(),
            'clientName'        => $client->getFullName() ?: $client->getName(),
            'consumptionId'     => $data->consumptionId,
            'consumptionNumber' => $data->consumptionNumber,
            'agreementNumber'   => $data->agreementNumber,
            'date'              => $data->date,
            'differences'       => $differences,
        ]);

        $this->validateTemplateData((array)$templateData);

        $emailFrom = Config::getResellerEmailFrom();
        $emails = Config::getEmailsByPermit($data->resellerId, NotificationEvents::CHANGE_RETURN_STATUS);

        if ($this->sendEmailsToEmployees($emailFrom, $emails, (array)$templateData, $data->resellerId)) {
            $result->notificationEmployeeByEmail = true;
        }

        if ($data->notificationType === self::TYPE_CHANGE && !empty($data->differences['to'])) {
            if ($this->sendClientEmail($emailFrom, $client, (array)$templateData, $data->resellerId, (int)$data->differences['to'])) {
                $result->notificationClientByEmail = true;
            }

            if ($this->sendClientSms($client, $data->resellerId, (int)$data->differences['to'], (array)$templateData, $result->notificationClientBySms)) {
                $result->notificationClientBySms->isSent = true;
            }
        }

        return (array)$result;
    }

    private function ensureEntityExists($entity, string $message): void
    {
        if ($entity === null) {
            throw new Exception($message, 400);
        }
    }

    private function ensureValidClient($client, int $resellerId): void
    {
        if ($client === null || $client->getType() !== Contractor::TYPE_CUSTOMER || $client->getId() !== $resellerId) {
            throw new Exception('Client not found or invalid!', 400);
        }
    }

    private function getDifferences(int $notificationType, array $differencesData, int $resellerId): string
    {
        if ($notificationType === self::TYPE_NEW) {
            return __('NewPositionAdded', null, $resellerId);
        } elseif ($notificationType === self::TYPE_CHANGE && !empty($differencesData)) {
            return __('PositionStatusHasChanged', [
                'FROM' => Status::getNameById((int)($differencesData['from'] ?? 0)),
                'TO'   => Status::getNameById((int)($differencesData['to'] ?? 0)),
            ], $resellerId);
        }
        return '';
    }

    private function validateTemplateData(array $templateData): void
    {
        foreach ($templateData as $key => $value) {
            if (empty($value)) {
                throw new Exception("Template Data ({$key}) is empty!", 500);
            }
        }
    }

    private function sendEmailsToEmployees(string $emailFrom, array $emails, array $templateData, int $resellerId): bool
    {
        if (empty($emailFrom) || empty($emails)) {
            return false;
        }

        foreach ($emails as $email) {
            MessagesClient::sendMessage([
                0 => [ // MessageTypes::EMAIL
                    'emailFrom' => $emailFrom,
                    'emailTo'   => $email,
                    'subject'   => __('complaintEmployeeEmailSubject', $templateData, $resellerId),
                    'message'   => __('complaintEmployeeEmailBody', $templateData, $resellerId),
                ],
            ], $resellerId, NotificationEvents::CHANGE_RETURN_STATUS);
        }
        return true;
    }

    private function sendClientEmail(string $emailFrom, Contractor $client, array $templateData, int $resellerId, int $statusTo): bool
    {
        if (empty($emailFrom) || empty($client->getEmail())) {
            return false;
        }

        MessagesClient::sendMessage([
            0 => [ // MessageTypes::EMAIL
                'emailFrom' => $emailFrom,
                'emailTo'   => $client->getEmail(),
                'subject'   => __('complaintClientEmailSubject', $templateData, $resellerId),
                'message'   => __('complaintClientEmailBody', $templateData, $resellerId),
            ],
        ], $resellerId, $client->getId(), NotificationEvents::CHANGE_RETURN_STATUS, $statusTo);
        return true;
    }

    private function sendClientSms(Contractor $client, int $resellerId, int $statusTo, array $templateData, SmsNotification $smsNotification): bool
    {
        if (empty($client->getMobile())) {
            return false;
        }
        $error = ''; // Инициализация $error - ее не было
        $res = NotificationManager::send($resellerId, $client->getId(), NotificationEvents::CHANGE_RETURN_STATUS, $statusTo, $templateData, $error);
        if (!empty($error)) {
            $smsNotification->message = $error;
        }
        return $res;
    }
}