<?php

declare(strict_types=1);

namespace EmbeNulls\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use EmbeNulls\Service\NullableEmbeddableService;
use ReflectionClass;

class NullableEmbeddableSubscriber implements EventSubscriber
{

    /** @var NullableEmbeddableService */
    private $nullableEmbeddableService;

    public function __construct(NullableEmbeddableService $nullableEmbeddableService)
    {
        $this->nullableEmbeddableService = $nullableEmbeddableService;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $nullableEmbeddeds = $this->nullableEmbeddableService->getNullableEmbeddeds(get_class($entity));
        if ($nullableEmbeddeds === []) {
            return;
        }
        $this->setNullEmbeddedPropertiesToNull($entity, $nullableEmbeddeds);
    }

    private function setNullEmbeddedPropertiesToNull($entity, $properties): void
    {
        $reflectionClass = new ReflectionClass(get_class($entity));
        foreach ($properties as $property) {
            $reflectionProperty = $reflectionClass->getProperty($property);
            $reflectionProperty->setAccessible(true);

            $embeddableObject = $reflectionProperty->getValue($entity);
            if ($embeddableObject && $this->isNullObject($embeddableObject)) {
                $reflectionProperty->setValue($entity, null);
            }

            $reflectionProperty->setAccessible(false);
        }
    }

    private function isNullObject($entity): bool
    {
        $reflectionClass = new ReflectionClass(get_class($entity));
        $isNull = true;
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue($entity);
            if (is_object($value)) {
                if ($this->isNullObject($value)) {
                    $reflectionProperty->setValue($entity, null);
                }else{
                    $isNull = false;
                }
            } elseif (!is_null($value)) {
                $isNull = false;
            }
            $reflectionProperty->setAccessible(false);
        }
        return $isNull;
    }
}
