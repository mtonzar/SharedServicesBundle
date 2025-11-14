<?php
// src/SharedServicesBundle/Message/Event/EventInterface.php
namespace mtonzar\SharedServicesBundle\Message\Event;

interface EventInterface
{
    // Cette interface marque les événements à publier
    public function getType(): string;
    public function getPayload(): array;
    // public function getOccurredAt():  ?\DateTimeInterface;
}
