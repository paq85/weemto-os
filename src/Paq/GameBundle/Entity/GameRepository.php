<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Paq\Pro\String\RandomStringGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameRepository extends EntityRepository
{
    const GCODE_LENGTH = 4;
    const GCODE_ALPHABET = '0123456789';

    /**
     * @param User $user
     * @return Game[]
     */
    public function findByUser(User $user)
    {
        $qb = $this->createQueryBuilder('g');
        $qb->select('g')
            ->join('g.users', 'u')
            ->where('u.id = :userId');

        $qb->setParameter('userId', $user->getId());

        return $qb->getQuery()->execute();
    }

    /**
     * @return string GCode that could be assigned to new Game
     */
    public function generateGCode()
    {
        $generator = new RandomStringGenerator(self::GCODE_ALPHABET);

        $duplicate = true;
        $tries = 0;
        while ($duplicate && $tries < 100) {
            $gcode = $generator->generate(self::GCODE_LENGTH);
            ++$tries;

            $game = $this->findBy(['gcode' => $gcode]);
            if (!$game) {
                $duplicate = false;
            }
        }

        if ($tries === 100) {
            throw new \RuntimeException('Could not generate next GCode in 100 tries');
        }

        return $gcode;
    }

    /**
     * @return mixed
     */
    public function deleteAll()
    {
        return $this->createQueryBuilder('g')
            ->delete()
            ->getQuery()->execute();
    }

    /**
     * @param \DateTime $dateTime
     * @return mixed
     */
    public function removeCreatedBefore(\DateTime $dateTime)
    {
        // DB stores in UTC
        $dateTime->setTimezone(new \DateTimeZone('UTC'));

        return $this->createQueryBuilder('g')
            ->delete()
            ->where('g.createdAt < :createdAtBefore')
            ->setParameter('createdAtBefore', $dateTime, \Doctrine\DBAL\Types\Type::DATETIME)
            ->getQuery()->execute();
    }
} 