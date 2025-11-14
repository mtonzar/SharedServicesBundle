<?php
// src/SharedServicesBundle/Message/Event/UserRegistered.php
namespace mtonzar\SharedServicesBundle\Message\Event;

class UserRegistered implements EventInterface
{
    private string $userId;
    private string $email;
    // private DateTime $occurredAt;

    public function __construct(string $userId, string $email)
    {
        $this->userId = $userId;
        $this->email = $email;
        // $this->occurredAt = new \DateTimeInterface;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getType(): string
    {
        return 'user.registered';
    }

    public function getPayload(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email
        ];
    }

    // public function getOccurredAt(): ?\DateTimeInterface
    // {
    //     return $this->occurredAt;
    // }
}
