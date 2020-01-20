<?php

declare(strict_types=1);

namespace Unit\Stubs\Models;

class Owner
{
    /** @var string */
    private $firstName;
    /** @var string */
    private $lastName;
    /** @var Email */
    private $email;
    /** @var Phone|null */
    private $phone;
    /** @var Address|null */
    private $address;

    public function __construct(
        string $firstName,
        string $lastName,
        Email $email,
        ?Phone $phone,
        ?Address $address
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }
}
