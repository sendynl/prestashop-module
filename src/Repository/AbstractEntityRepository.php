<?php
/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */
declare(strict_types=1);

namespace Sendy\PrestaShop\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @template T of object
 */
abstract class AbstractEntityRepository
{
    protected EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<T>
     */
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(static::getEntityClass());
    }

    /**
     * @param T $entity
     */
    public function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @param mixed $id
     *
     * @return T|null
     */
    public function find($id): ?object
    {
        return $this->repository->find($id);
    }

    /**
     * @param T $entity
     */
    public function delete(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * @return class-string<T>
     */
    abstract protected static function getEntityClass(): string;
}
