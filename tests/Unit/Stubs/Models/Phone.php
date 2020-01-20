<?php

declare(strict_types=1);

namespace Unit\Stubs\Models;

class Phone
{
    /** @var string */
    private $phone;

    public function __construct(
        string $phone
    ) {
        $this->phone = $phone;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
