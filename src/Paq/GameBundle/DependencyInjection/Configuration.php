<?php

namespace Paq\GameBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('paq_game');

        $rootNode
            ->children()
                ->integerNode('game_round_question_count_limit')->end()
                ->arrayNode('http_long_polling')
                    ->children()
                    ->integerNode('timeout')->end()
                    ->integerNode('sleep')->end()
                ->end() // http_long_polling
            ->end()
        ;

        return $treeBuilder;
    }
}
