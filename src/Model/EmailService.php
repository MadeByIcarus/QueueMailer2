<?php


namespace Icarus\QueueMailer2\Model;


use Nettrine\ORM\EntityManagerDecorator;


class EmailService
{

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;



    public function __construct(EntityManagerDecorator $entityManager)
    {
        $this->entityManager = $entityManager;
    }



    /**
     * @return array|Email[]
     */
    public function findPendingEmails(int $limit = 1): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select("e")
            ->from(Email::class, "e")
            ->where("e.sentAt IS NULL AND (e.snoozeUntil IS NULL OR e.snoozeUntil <= CURRENT_TIMESTAMP())")
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}