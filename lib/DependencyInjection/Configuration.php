<?php namespace Vankosoft\IssueTrackingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\ProjectIssue;

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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder    = new TreeBuilder( 'vs_issue_tracking' );
        $rootNode       = $treeBuilder->getRootNode();
        
        // Main Config
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode( 'project' )->defaultValue( ProjectIssue::PROJECT_UNDEFINED )->cannotBeEmpty()->end()
                ->scalarNode( 'kanbanboard' )->defaultValue( ProjectIssue::BOARD_UNDEFINED )->cannotBeEmpty()->end()
                ->arrayNode( 'vankosoft_api' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode( 'host' )->defaultValue( 'http://api.vankosoft.org/api' )->cannotBeEmpty()->end()
                            ->scalarNode( 'user' )->defaultValue( 'admin' )->cannotBeEmpty()->end()
                            ->scalarNode( 'password' )->defaultValue( 'admin' )->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
