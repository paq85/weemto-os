<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Paq\GameBundle\Doctrine\Filter\LocaleFilter;
use Paq\GameBundle\PaqGameBundle;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionRepository extends EntityRepository
{
    /**
     * @param array $options
     *  [
     *      'included_tag_ids' => int[],
     *      'excluded_ids' => int[],
     *      'excluded_tag_ids' => int[]
     *      'excluded_tag_names' => string[]
     * ]
     * @return Question
     * @throws NonUniqueResultException If the query result is not unique.
     * @throws NoResultException        If the query returned no result.
     */
    public function getRandomQuestion(array $options = [])
    {
        $tagsRepo = $this->_em->getRepository('PaqGameBundle:Tag');

        // Include locale filter manually as we're using a Native Query
        LocaleFilter::MANUALLY_ENABLED_HERE;
        $locales = PaqGameBundle::$LOCALES;
        $localeFilter = $this->getEntityManager()->getFilters()->getFilter(LocaleFilter::NAME);
        if ($this->getEntityManager()->getFilters()->isEnabled(LocaleFilter::NAME)) {
            $unescapedLocale = trim($localeFilter->getParameter('enabledLocale'), '\'');
            $locales = [$unescapedLocale, PaqGameBundle::LOCALE_ANY];
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'excluded_ids' => [0],
            'excluded_tag_ids' => [0],
            'excluded_tag_names' => [''],
        ]);
        $resolver->setRequired('included_tag_ids');
        $resolver->setAllowedTypes('included_tag_ids', 'array');
        $resolver->setAllowedTypes('excluded_ids', 'array');
        $resolver->setAllowedTypes('excluded_tag_ids', 'array');
        $resolver->setAllowedTypes('excluded_tag_names', 'array');
        $options = $resolver->resolve($options);
        if (count($options['excluded_ids']) === 0) {
            $options['excluded_ids'] = [0];
        }

        $tagIdsToEliminate = array_values($options['excluded_tag_ids']);
        foreach ($options['excluded_tag_names'] as $tagName) {
            if ('' !== $tagName) {
                $tagIdsToEliminate[] = $tagsRepo->get($tagName)->getId();
            }
        }

        $tagIdsToEliminate = array_unique($tagIdsToEliminate);

        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('COUNT(id)', 'total', 'integer');

        $queryCount = $this->_em->createNativeQuery(
            'SELECT COUNT(id) FROM Question q
              WHERE
              q.id NOT IN (:excludedQuestionIds)
              AND q.enabledLocale IN (:locales)
              AND EXISTS (
                SELECT * FROM QuestionHasTags qt WHERE q.id = qt.questionId AND qt.tagId IN (:includedTagIds)
              )
              AND NOT EXISTS (
                SELECT * FROM QuestionHasTags qt2 WHERE q.id = qt2.questionId AND qt2.tagId IN (:excludedTagIds)
              )
              ',
            $rsm
        );

        $queryCount->setParameter('includedTagIds', $options['included_tag_ids']);
        $queryCount->setParameter('excludedQuestionIds', $options['excluded_ids']);
        $queryCount->setParameter('excludedTagIds', $tagIdsToEliminate);
        $queryCount->setParameter('locales', $locales);

        $countResult = $queryCount->getSingleResult();
        $questionCount = $countResult['total'];

        if ($questionCount < 1) {
            throw new NoResultException('Could not find any Question matching given criteria: ' . print_r($options, true));
        }

        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'question_id', 'integer');

        $query = $this->_em->createNativeQuery(
            'SELECT id FROM Question q
              WHERE
              q.id NOT IN (:excludedQuestionIds)
              AND q.enabledLocale IN (:locales)
              AND EXISTS (
                SELECT * FROM QuestionHasTags qt WHERE q.id = qt.questionId AND qt.tagId IN (:includedTagIds)
              )
              AND NOT EXISTS (
                SELECT * FROM QuestionHasTags qt2 WHERE q.id = qt2.questionId AND qt2.tagId IN (:excludedTagIds)
              )
              LIMIT :resultOffset, 1
              ',
            $rsm
        );

        $query->setParameter('includedTagIds', $options['included_tag_ids']);
        $query->setParameter('excludedQuestionIds', $options['excluded_ids']);
        $query->setParameter('excludedTagIds', $tagIdsToEliminate);
        $query->setParameter('locales', $locales);
        $query->setParameter('resultOffset', rand(0, $questionCount - 1));

        $queryResult = $query->getSingleResult();
        $questionId = $queryResult['question_id'];

        return $this->find($questionId);
    }

    public function deleteAll()
    {
        $this->createQueryBuilder('q')
            ->delete()
            ->getQuery()->execute();
    }

    /**
     * @return Question[]
     */
    public function findAllMissingCorrectHint()
    {
        // SELECT * FROM Question q WHERE NOT EXISTS (SELECT * FROM QuestionHint qh WHERE q.id = qh.questionId AND q.properAnswer = qh.text)
        $qb = $this->createQueryBuilder('q');
        $qb->select('q')
            ->where($qb->expr()->not($qb->expr()->exists('SELECT qh FROM PaqGameBundle:QuestionHint qh WHERE q = qh.question AND q.properAnswer = qh.text')));

        return $qb->getQuery()->execute();
    }
} 