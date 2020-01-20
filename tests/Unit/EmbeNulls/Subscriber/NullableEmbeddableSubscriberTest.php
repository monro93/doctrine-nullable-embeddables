<?php

declare(strict_types=1);

namespace Unit\EmbeNulls\Subscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use EmbeNulls\Service\NullableEmbeddableService;
use EmbeNulls\Subscriber\NullableEmbeddableSubscriber;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use Unit\Stubs\Models\Address;
use Unit\Stubs\Models\Dog;
use Unit\Stubs\Models\Email;
use Unit\Stubs\Models\Owner;
use Unit\Stubs\Models\PetIdentification;
use Unit\Stubs\Models\Phone;

class NullableEmbeddableSubscriberTest extends TestCase
{
    const DEFAULT_DOG_NAME = 'Rufus';
    const DEFAULT_PET_IDENTIFICATION_NUMBER = '123-T';
    const DEFAULT_OWNER_FIRST_NAME = 'Peter';
    const DEFAULT_OWNER_LAST_NAME = 'Parker';
    const DEFAULT_OWNER_EMAIL = 'peter.parker@example.com';
    /** @var NullableEmbeddableService|ObjectProphecy */
    private $nullableEmbeddableService;
    /** @var NullableEmbeddableSubscriber */
    private $sut;

    protected function setUp(): void
    {
        $this->nullableEmbeddableService = $this->prophesize(NullableEmbeddableService::class);
        $this->setNullableEmdbeddablesStub();
        $this->sut = new NullableEmbeddableSubscriber($this->nullableEmbeddableService->reveal());
    }

    public function test_a_dog_without_pet_identification_will_not_change_anything()
    {
        $dog = new Dog(
            self::DEFAULT_DOG_NAME,
            null
        );

        $lifecycleEventArgs = $this->buildEventArgument($dog);

        $this->sut->postLoad($lifecycleEventArgs->reveal());

        $this->assertEquals(self::DEFAULT_DOG_NAME, $dog->getName());
        $this->assertNull($dog->getPetIdentification());
    }

    public function test_a_dog_with_null_object_pet_identification_will_set_it_to_null()
    {
        $dog = new Dog(
            self::DEFAULT_DOG_NAME,
            $this->createNullObjectAsDoctrineDoes(PetIdentification::class)
        );

        $lifecycleEventArgs = $this->buildEventArgument($dog);

        $this->sut->postLoad($lifecycleEventArgs->reveal());

        $this->assertEquals(self::DEFAULT_DOG_NAME, $dog->getName());
        $this->assertNull($dog->getPetIdentification());
    }

    public function test_a_dog_with_pet_identification_with_null_object_address_will_set_it_to_null()
    {
        $dog = new Dog(
            self::DEFAULT_DOG_NAME,
            new PetIdentification(
                self::DEFAULT_PET_IDENTIFICATION_NUMBER,
                new Owner(
                    self::DEFAULT_OWNER_FIRST_NAME,
                    self::DEFAULT_OWNER_LAST_NAME,
                    new Email(self::DEFAULT_OWNER_EMAIL),
                    $this->createNullObjectAsDoctrineDoes(Phone::class),
                    $this->createNullObjectAsDoctrineDoes(Address::class)
                ),
                $this->createNullObjectAsDoctrineDoes(Address::class)
            )
        );

        $lifecycleEventArgs = $this->buildEventArgument($dog);

        $this->sut->postLoad($lifecycleEventArgs->reveal());

        $this->assertEquals(self::DEFAULT_DOG_NAME, $dog->getName());

        $petIdentification = $dog->getPetIdentification();
        $this->assertInstanceOf(PetIdentification::class, $petIdentification);
        $this->assertEquals(self::DEFAULT_PET_IDENTIFICATION_NUMBER, $petIdentification->getId());
        $this->assertNull($petIdentification->getRegistrationAddress());

        $owner = $petIdentification->getOwner();
        $this->assertInstanceOf(Owner::class, $owner);
        $this->assertEquals(self::DEFAULT_OWNER_FIRST_NAME, $owner->getFirstName());
        $this->assertEquals(self::DEFAULT_OWNER_LAST_NAME, $owner->getLastName());
        $this->assertInstanceOf(Email::class, $owner->getEmail());
        $this->assertNull($owner->getPhone());
        $this->assertNull($owner->getAddress());
    }

    private function setNullableEmdbeddablesStub()
    {
        $this->nullableEmbeddableService->getNullableEmbeddeds(Dog::class)->willReturn(['petIdentification']);
        $this->nullableEmbeddableService->getNullableEmbeddeds(PetIdentification::class)->willReturn(['address']);
        $this->nullableEmbeddableService->getNullableEmbeddeds(Owner::class)->willReturn(['phone', 'address']);
        $this->nullableEmbeddableService->getNullableEmbeddeds(Address::class)->willReturn(['postalCode']);
    }

    private function createNullObjectAsDoctrineDoes(string $className)
    {
        $r = new ReflectionClass($className);

        return $r->newInstanceWithoutConstructor();
    }

    protected function buildEventArgument(Dog $dog)
    {
        $lifecycleEventArgs = $this->prophesize(LifecycleEventArgs::class);
        $lifecycleEventArgs->getObject()->willReturn($dog)->shouldBeCalledOnce();

        return $lifecycleEventArgs;
    }
}
