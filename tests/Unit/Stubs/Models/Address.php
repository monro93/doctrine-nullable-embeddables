<?php

declare(strict_types=1);

namespace Unit\Stubs\Models;

class Address
{
    /** @var string */
    private $city;
    /** @var string */
    private $country;
    /** @var string */
    private $street;
    /** @var PostalCode|null */
    private $postalCode;

    public function __construct(
        string $city,
        string $country,
        string $street,
        ?PostalCode $postalCode
    ) {
        $this->city = $city;
        $this->country = $country;
        $this->street = $street;
        $this->postalCode = $postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostalCode(): ?PostalCode
    {
        return $this->postalCode;
    }
}
