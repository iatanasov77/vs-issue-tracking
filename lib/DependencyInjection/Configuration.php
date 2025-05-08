<?php namespace Vankosoft\ApplicationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

use Vankosoft\ApplicationBundle\Component\Application\Project;
use Vankosoft\ApplicationBundle\Component\ProjectIssue\ProjectIssue;

use Vankosoft\ApplicationBundle\Model\Locale;
use Vankosoft\ApplicationBundle\Controller\LocalesController;
use Vankosoft\ApplicationBundle\Form\LocaleForm;

use Vankosoft\ApplicationBundle\Repository\ApplicationRepository;
use Vankosoft\ApplicationBundle\Model\Application;
use Vankosoft\ApplicationBundle\Form\ApplicationForm;

use Vankosoft\ApplicationBundle\Repository\SettingsRepository;
use Vankosoft\ApplicationBundle\Model\Settings;
use Vankosoft\ApplicationBundle\Controller\SettingsController;
use Vankosoft\ApplicationBundle\Form\SettingsForm;

use Vankosoft\ApplicationBundle\Repository\TaxonomyRepository;
use Vankosoft\ApplicationBundle\Model\Taxonomy;
use Vankosoft\ApplicationBundle\Controller\TaxonomyController;
use Vankosoft\ApplicationBundle\Form\TaxonomyForm;

use Vankosoft\ApplicationBundle\Model\TaxonImage;

use Vankosoft\ApplicationBundle\Repository\TaxonRepository;
use Vankosoft\ApplicationBundle\Model\Taxon;
use Vankosoft\ApplicationBundle\Form\TaxonForm;
//use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonTranslationType;
use Vankosoft\ApplicationBundle\Model\TaxonTranslation;
//use Sylius\Component\Taxonomy\Factory\TaxonFactory;

use Vankosoft\ApplicationBundle\Model\Translation;
use Vankosoft\ApplicationBundle\Repository\TranslationRepository;

use Vankosoft\ApplicationBundle\Model\LogEntry;
use Vankosoft\ApplicationBundle\Repository\LogEntryRepository;

use Vankosoft\ApplicationBundle\Model\CookieConsentTranslation;
use Vankosoft\ApplicationBundle\Controller\CookieConsentTranslationsController;
use Vankosoft\ApplicationBundle\Form\CookieConsentTranslationForm;

use Vankosoft\ApplicationBundle\Model\TagsWhitelistContext;
use Vankosoft\ApplicationBundle\Repository\TagsWhitelistContextsRepository;
use Vankosoft\ApplicationBundle\Controller\TagsWhitelistContextsController;
use Vankosoft\ApplicationBundle\Form\TagsWhitelistContextForm;

use Vankosoft\ApplicationBundle\Model\TagsWhitelistTag;
use Vankosoft\ApplicationBundle\Repository\TagsWhitelistTagsRepository;

use Vankosoft\ApplicationBundle\Model\WidgetGroup;
use Vankosoft\ApplicationBundle\Repository\WidgetGroupRepository;
use Vankosoft\ApplicationBundle\Controller\WidgetsGroupsController;
use Vankosoft\ApplicationBundle\Form\WidgetsGroupForm;

use Vankosoft\ApplicationBundle\Model\Widget;
use Vankosoft\ApplicationBundle\Controller\WidgetsController;
use Vankosoft\ApplicationBundle\Form\WidgetForm;

use Vankosoft\ApplicationBundle\Model\WidgetsRegistry;

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
                ->arrayNode( 'vankosoft_api' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode( 'enabled' )->defaultFalse()->end()
                        ->scalarNode( 'project' )->defaultValue( ProjectIssue::PROJECT_UNDEFINED )->cannotBeEmpty()->end()
                        ->scalarNode( 'kanbanboard' )->defaultValue( ProjectIssue::BOARD_UNDEFINED )->cannotBeEmpty()->end()
                        ->arrayNode( 'connection' )
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode( 'host' )->defaultValue( 'http://api.vankosoft.org/api' )->cannotBeEmpty()->end()
                                ->scalarNode( 'user' )->defaultValue( 'admin' )->cannotBeEmpty()->end()
                                ->scalarNode( 'password' )->defaultValue( 'admin' )->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
