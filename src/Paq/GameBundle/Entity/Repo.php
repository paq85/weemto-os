<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;


class Repo 
{
    /**
     * @param object[] $entities Doctrine Entities with "getId" methods
     * @return int[]
     */
    public static function getIds($entities)
    {
        $ids = [];

        foreach ($entities as $entity) {
            if (is_object($entity) || method_exists($entity, 'getId')) {
                $ids[] = $entity->getId();
            } else {
                throw new \InvalidArgumentException('Entity does not have getId method');
            }
        }

        return $ids;
    }
}