<?php

declare(strict_types=1);

namespace Unit\Stubs\Models;

class PetIdentification
{
    /** @var string */
    private $id;
    /** @var Owner */
    private $owner;
    /** @var Address|null */
    private $registrationAddress;

    public function __construct(
        string $id,
        Owner $owner,
        ?Address $registrationAddress
    )
    {
        $this->id = $id;
        $this->owner = $owner;
        $this->registrationAddress = $registrationAddress;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwner(): Owner
    {
        return $this->owner;
    }

    public function getRegistrationAddress(): ?Address
    {
        return $this->registrationAddress;
    }


}
