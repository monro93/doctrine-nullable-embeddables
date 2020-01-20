<?php

declare(strict_types=1);

namespace Unit\Stubs\Models;

class Dog
{
    /** @var string */
    private $name;
    /** @var PetIdentification|null */
    private $petIdentification;

    public function __construct(
        string $name,
        ?PetIdentification $petIdentification
    ) {
        $this->name = $name;
        $this->petIdentification = $petIdentification;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPetIdentification(): ?PetIdentification
    {
        return $this->petIdentification;
    }
}
