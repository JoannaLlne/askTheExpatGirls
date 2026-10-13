<?php

namespace App\Repository;

use App\Dto\ServiceFilterDto;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    // src/Repository/ServiceRepository.php
    public function findByFilters(ServiceFilterDto $filterDto): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($filterDto->timing !== null) {
            $qb->andWhere('s.serviceTiming LIKE :timing')
                ->setParameter('timing', '%"' . $filterDto->timing->value . '"%');
        }

        if (!empty($filterDto->categories)) {
            $orX = $qb->expr()->orX();
            foreach ($filterDto->categories as $i => $category) {
                $orX->add("s.serviceCategory LIKE :category{$i}");
                $qb->setParameter("category{$i}", '%"' . $category->value . '"%');
            }
            $qb->andWhere($orX);
        }

        return $qb->orderBy('s.createdAt', 'DESC')->getQuery()->getResult();
    }

    public function findActiveService(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.archived = false')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

        //    /**
        //     * @return Service[] Returns an array of Service objects
        //     */
        //    public function findByExampleField($value): array
        //    {
        //        return $this->createQueryBuilder('s')
        //            ->andWhere('s.exampleField = :val')
        //            ->setParameter('val', $value)
        //            ->orderBy('s.id', 'ASC')
        //            ->setMaxResults(10)
        //            ->getQuery()
        //            ->getResult()
        //        ;
        //    }

        //    public function findOneBySomeField($value): ?Service
        //    {
        //        return $this->createQueryBuilder('s')
        //            ->andWhere('s.exampleField = :val')
        //            ->setParameter('val', $value)
        //            ->getQuery()
        //            ->getOneOrNullResult()
        //        ;
        //    }
    }
