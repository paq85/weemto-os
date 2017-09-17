<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Symfony\EventSubscriber;


use Doctrine\ORM\Query\FilterCollection;
use Paq\GameBundle\Doctrine\Filter\LocaleFilter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Intl\Locale;

class LocaleSubscriber implements EventSubscriberInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function onKernelRequest(GetResponseEvent $event)
    {
        /**
         * Symfony automatically sets locale if route's path has "_locale"
         * But PaqGame bundle adds "_locale" as route's prefix so we need to set locale manually
         */
        $request = $event->getRequest();
        $locale = $request->attributes->has('_locale') ? $request->attributes->get('_locale') : $request->getDefaultLocale();

        // update Translator's locale
        $this->container->get('translator')->setLocale($locale);

        // update Doctrine Locale Filter
        if ($this->isSonataAdminBundleRequest($request)) {
            // disable the filter on administration pages
            $this->disableLocaleFilter();
        } else {
            $this->enableLocaleFilter($locale);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }

    public function enableLocaleFilter($locale)
    {
        /** @var FilterCollection $filters */
        $filters = $this->container->get('doctrine.orm.entity_manager')->getFilters();
        if (!$filters->isEnabled(LocaleFilter::NAME)) {
            $filters->enable(LocaleFilter::NAME);
        }
        $localeFilter = $filters->getFilter(LocaleFilter::NAME);
        $localeFilter->setParameter('enabledLocale', $locale);
    }

    public function disableLocaleFilter()
    {
        $filters = $this->container->get('doctrine.orm.entity_manager')->getFilters();
        if ($filters->isEnabled(LocaleFilter::NAME)) {
            $filters->disable(LocaleFilter::NAME);
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isSonataAdminBundleRequest(Request $request)
    {
        if ($request->attributes->has('_sonata_admin')) {
            return true;
        }

        // eg. add collection element request; sadly it does not contain "_sonata_admin" attribute
        if (stripos($request->attributes->get('_controller'), 'sonata.admin.controller') === 0) {
            return true;
        }

        return false;
    }
}