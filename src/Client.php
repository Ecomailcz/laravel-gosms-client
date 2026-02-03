<?php

declare(strict_types = 1);

namespace EcomailGoSms;

use EcomailGoSms\Contracts\HttpClient;
use EcomailGoSms\Data\AuthRequest;
use EcomailGoSms\Data\AuthResponse;
use EcomailGoSms\Data\BulkSmsRequest;
use EcomailGoSms\Data\BulkSmsResponse;
use EcomailGoSms\Data\SmsRequest;
use EcomailGoSms\Data\SmsResponse;
use EcomailGoSms\Exceptions\Authorization;
use EcomailGoSms\Exceptions\InvalidFormat;
use EcomailGoSms\Exceptions\Request;

final class Client
{

    private ?AuthResponse $authResponse = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly int $defaultChannel,
        private readonly HttpClient $httpClient,
    ) {
    }

    /**
     * Authenticate with GoSms API
     *
     * @throws \EcomailGoSms\Exceptions\Authorization
     * @throws \EcomailGoSms\Exceptions\Request
     */
    public function authenticate(): self
    {
        $authRequest = new AuthRequest($this->clientId, $this->clientSecret);

        /** @var array<string, mixed> $data */
        $data = $authRequest->toArray();
        $response = $this->httpClient->request('POST', 'oauth/token', $data);

        if ($response['status'] !== 200) {
            throw Authorization::authenticationFailed();
        }

        $body = $response['body'];
        
        if (!isset($body['access_token'])) {
            throw Authorization::authenticationFailed();
        }

        $this->authResponse = AuthResponse::from($body);

        return $this;
    }

    /**
     * Send SMS to single phone number
     *
     * @throws \EcomailGoSms\Exceptions\Authorization
     * @throws \EcomailGoSms\Exceptions\InvalidFormat
     * @throws \EcomailGoSms\Exceptions\Request
     */
    public function sendSms(string $phoneNumber, string $message, ?int $channel = null): SmsResponse
    {
        $this->ensureAuthenticated();
        $this->validateMessage($message);
        $this->validatePhoneNumber($phoneNumber);

        $smsRequest = new SmsRequest($phoneNumber, $message, $channel ?? $this->defaultChannel);

        /** @var array<string, mixed> $data */
        $data = $smsRequest->toArray();
        $response = $this->httpClient->request(
            'POST',
            'messages/sms',
            $data,
            $this->getAuthHeaders(),
        );

        if ($response['status'] !== 201) {
            throw $this->createRequest($response);
        }

        return SmsResponse::from($response['body']);
    }

    /**
     * Send SMS to multiple phone numbers
     *
     * @param array<int, string> $phoneNumbers
     * @throws \EcomailGoSms\Exceptions\Authorization
     * @throws \EcomailGoSms\Exceptions\InvalidFormat
     * @throws \EcomailGoSms\Exceptions\Request
     */
    public function sendMultipleSms(array $phoneNumbers, string $message, ?int $channel = null): BulkSmsResponse
    {
        $this->ensureAuthenticated();
        $this->validateMessage($message);
        $this->validatePhoneNumbers($phoneNumbers);

        $bulkRequest = new BulkSmsRequest($phoneNumbers, $message, $channel ?? $this->defaultChannel);

        /** @var array<string, mixed> $data */
        $data = $bulkRequest->toArray();
        $response = $this->httpClient->request(
            'POST',
            'messages/sms/bulk',
            $data,
            $this->getAuthHeaders(),
        );

        if ($response['status'] !== 201) {
            throw $this->createRequest($response);
        }

        return BulkSmsResponse::from($response['body']);
    }

    /**
     * Make custom HTTP request to GoSms API
     *
     * @param array<string, mixed> $params
     * @return array{status: int, body: array<string, mixed>}
     * @throws \EcomailGoSms\Exceptions\Authorization
     * @throws \EcomailGoSms\Exceptions\Request
     */
    public function makeRequest(string $method, string $endpoint, ?array $params = null): array
    {
        $this->ensureAuthenticated();

        $response = $this->httpClient->request(
            $method,
            $endpoint,
            $params ?? [],
            $this->getAuthHeaders(),
        );

        if ($response['status'] >= 400) {
            throw $this->createRequest($response);
        }

        return $response;
    }

    /**
     * Check if client is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->authResponse !== null;
    }

    /**
     * Get current access token
     */
    public function getAccessToken(): ?string
    {
        return $this->authResponse?->accessToken;
    }

    /**
     * Ensure client is authenticated
     *
     * @throws \EcomailGoSms\Exceptions\Authorization
     */
    private function ensureAuthenticated(): void
    {
        if (!$this->isAuthenticated()) {
            throw Authorization::invalidCredentials();
        }
    }

    /**
     * Validate message content
     *
     * @throws \EcomailGoSms\Exceptions\InvalidFormat
     */
    private function validateMessage(string $message): void
    {
        if (trim($message) === '') {
            throw InvalidFormat::invalidMessageFormat('Message cannot be empty');
        }

        if (strlen($message) > 160) {
            throw InvalidFormat::invalidMessageFormat('Message too long (max 160 characters)');
        }
    }

    /**
     * Get authentication headers
     *
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->authResponse?->accessToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    /**
     * Create appropriate exception based on response
     *
     * @param array{status: int, body: array<string, mixed>} $response
     */
    private function createRequest(array $response): Request
    {
        $message = $response['body']['message'] ?? $response['body']['error'] ?? 'Unknown error';

        return Request::httpError($response['status'], is_string($message) ? $message : 'Unknown error');
    }

    /**
     * Validate single phone number
     *
     * @throws \EcomailGoSms\Exceptions\InvalidFormat
     */
    private function validatePhoneNumber(string $phoneNumber): void
    {
        if (trim($phoneNumber) === '') {
            throw InvalidFormat::invalidPhoneNumber('Phone number cannot be empty');
        }

        // Basic validation - check if it contains only digits, +, -, spaces and parentheses
        if (preg_match('/^[\d\s\+\-\(\)]+$/', $phoneNumber) !== 1) {
            throw InvalidFormat::invalidPhoneNumber('Phone number contains invalid characters');
        }

        // Remove all non-digit characters for length check
        $digitsOnly = preg_replace('/\D/', '', $phoneNumber);
        
        if ($digitsOnly === null || strlen($digitsOnly) < 7 || strlen($digitsOnly) > 15) {
            throw InvalidFormat::invalidPhoneNumber('Phone number must be between 7 and 15 digits');
        }
    }

    /**
     * Validate multiple phone numbers
     *
     * @param array<int, string> $phoneNumbers
     * @throws \EcomailGoSms\Exceptions\InvalidFormat
     */
    private function validatePhoneNumbers(array $phoneNumbers): void
    {
        if ($phoneNumbers === []) {
            throw InvalidFormat::invalidPhoneNumber('Phone numbers array cannot be empty');
        }

        foreach ($phoneNumbers as $phoneNumber) {
            $this->validatePhoneNumber($phoneNumber);
        }
    }

}
