<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TagRepository extends EntityRepository
{

    /**
     * @param $name
     * @return Tag
     * @throws EntityNotFoundException if Tag could not be found based on provided name
     */
    public function get($name)
    {
        $criteria = ['name' => $name];
        $tag = $this->findOneBy($criteria);

        if (null === $tag) {
            throw new EntityNotFoundException('Tag by criteria: ' . print_r($criteria, true));
        }

        return $tag;
    }

    /**
     * @param bool $onlyFeatured
     * @return Tag[]
     */
    public function findAllCategoryTags($onlyFeatured = false)
    {
        $criteria = ['type' => Tag::TYPE_CATEGORY];
        if ($onlyFeatured) {
            $criteria['isFeatured'] = true;
        }

        return $this->findBy($criteria);
    }

    /**
     * @param string|null $locale
     * @return Tag[]
     */
    public function findAllChallengeTags($locale = null)
    {
        $locales = [null];
        if (null !== $locale) {
            $locales[] = $locale;
        }

        return $this->findBy(['type' => Tag::TYPE_CHALLENGE, 'enabledLocale' => $locales], ['id' => 'DESC']);
    }

    /**
     * @param $name
     * @return null|object
     */
    public function findChallenge($name)
    {
        return $this->findOneBy(['name' => $name, 'type' => Tag::TYPE_CHALLENGE]);
    }

    /**
     * @param $tags
     * @param TranslatorInterface $translator
     */
    public function sortAlphabetically(&$tags, TranslatorInterface $translator)
    {
        usort($tags, function(Tag $a, Tag $b) use ($translator) {
            return strcasecmp($a->getName(), $b->getName());
        });
    }

    /**
     * @param array $options [
     *  'excluded_names' => string[] Names of Tags exclude from being picked,
     *  'excluded_ids' => int[] IDs of Tags to exclude from being picked
     * ]
     * @return Tag
     */
    public function getRandom(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'excluded_names' => [''],
            'excluded_ids' => [0]
        ]);
        $options = $resolver->resolve($options);

        if (count($options['excluded_ids']) === 0) {
            $options['excluded_ids'] = [0];
        }
        if (count($options['excluded_names']) === 0) {
            $options['excluded_names'] = [''];
        }

        $qb = $this->createQueryBuilder('t');
        $count = $qb
            ->select('COUNT(t)')
            ->where('t.id NOT IN (:excludedIds)')
            ->andWhere('t.name NOT IN (:excludedNames)')
            ->getQuery()
            ->setParameter('excludedIds', $options['excluded_ids'])
            ->setParameter('excludedNames', $options['excluded_names'])
            ->getSingleScalarResult();

        if ((int) $count === 0) {
            throw new NoResultException('No Tags found for criteria: ' . print_r($options, true));
        }

        $qb = $this->createQueryBuilder('t');

        return $qb
            ->where('t.id NOT IN (:excludedIds)')
            ->andWhere('t.name NOT IN (:excludedNames)')
            ->setFirstResult(rand(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()
            ->setParameter('excludedIds', $options['excluded_ids'])
            ->setParameter('excludedNames', $options['excluded_names'])
            ->getSingleResult();
    }

}