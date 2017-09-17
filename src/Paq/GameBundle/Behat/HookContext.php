<?php
/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Paq\GameBundle\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;
use Paq\GameBundle\Doctrine\Filter\LocaleFilter;
use Paq\GameBundle\PaqGameBundle;
use Paq\GameBundle\Symfony\EventSubscriber\LocaleSubscriber;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

/**
 * Based on Sylius\Bundle\CoreBundle\Behat\HookContext
 */
class HookContext implements Context, KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function __construct()
    {
        defined('BEHAT_ERROR_REPORTING') ? : define('BEHAT_ERROR_REPORTING', 0);
        \Symfony\Component\Debug\Debug::enable(E_ALL, false);
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase(BeforeScenarioScope $scope)
    {
        $entityManager = $this->getService('doctrine.orm.entity_manager');
        $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $entityManager->clear();

        $localeSubscriber = new LocaleSubscriber();
        $localeSubscriber->setContainer($this->getContainer());
        $localeSubscriber->disableLocaleFilter();

        $purger = new ORMPurger();

        $loader = new Loader();
        $loader->loadFromDirectory(__DIR__ . '/../DataFixtures/Test');
        $fixtures = $loader->getFixtures(); // if we get directly from loadFromDirectory they could be in wrong order

        foreach ($fixtures as $fixture) {
            if ($fixture instanceof ContainerAwareInterface) {
                $fixture->setContainer($this->getContainer());
            }
        }

        $fixturesExecutor = new ORMExecutor($entityManager, $purger);
        $fixturesExecutor->execute($fixtures);

        $localeSubscriber->enableLocaleFilter(PaqGameBundle::LOCALE_PL);
    }

    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }
} 