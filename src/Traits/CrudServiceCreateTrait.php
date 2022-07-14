<?php

namespace ZnDomain\Service\Traits;

use ZnDomain\Domain\Enums\EventEnum;
use ZnCore\Entity\Interfaces\EntityIdInterface;
use ZnCore\Validation\Helpers\ValidationHelper;

trait CrudServiceCreateTrait
{

    public function create($data): EntityIdInterface
    {
        if ($this->hasEntityManager()) {
            $this->getEntityManager()->beginTransaction();
        }
        try {
            $entityClass = $this->getEntityClass();
            $entity = $this->getEntityManager()->createEntity($this->getEntityClass(), $data);
            $event = $this->dispatchEntityEvent($entity, EventEnum::BEFORE_CREATE_ENTITY);
            if ($event->isPropagationStopped()) {
                return $entity;
            }
            ValidationHelper::validateEntity($entity);
            $this->getRepository()->create($entity);

            $event = $this->dispatchEntityEvent($entity, EventEnum::AFTER_CREATE_ENTITY);
        } catch (\Throwable $e) {
            if ($this->hasEntityManager()) {
                $this->getEntityManager()->rollbackTransaction();
            }
            throw $e;
        }
        if ($this->hasEntityManager()) {
            $this->getEntityManager()->commitTransaction();
        }
        return $entity;
    }
}
