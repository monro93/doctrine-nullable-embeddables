<?php

declare(strict_types=1);

namespace EmbeNulls\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use EmbeNulls\Service\NullableEmbeddableService;
use ReflectionClass;

class NullableEmbeddableSubscriber implements EventSubscriber
{

    /** @var NullableEmbeddableService */
    private $nullableEmbeddableService;
    /** @var ObjectManager */
    private $objectManager;

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
        $this->objectManager = $args->getObjectManager();
        $nullableEmbeddeds = $this->nullableEmbeddableService->getNullableEmbeddeds($this->getRealClassName($entity));
        if ($nullableEmbeddeds === []) {
            return;
        }
        $this->setNullEmbeddedPropertiesToNull($entity, $nullableEmbeddeds);
    }

    private function setNullEmbeddedPropertiesToNull($entity, $properties): void
    {
        $reflectionClass = new ReflectionClass($this->getRealClassName($entity));
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
        $reflectionClass = new ReflectionClass($this->getRealClassName($entity));
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

    private function getRealClassName($entity) {
        return $this->objectManager->getClassMetadata(get_class($entity))->getName();
    }
}
