<?php namespace Vankosoft\IssueTrackingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class VSIssueTrackingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load( array $configs, ContainerBuilder $container ): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );
        
        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yaml' );
        
        // VankoSoft API
        $container->setParameter( 'vs_issue_tracking.project', $config['project'] );
        $container->setParameter( 'vs_issue_tracking.kanbanboard', $config['kanbanboard'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.host', $config[ 'vankosoft_api' ]['host'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.user', $config[ 'vankosoft_api' ]['user'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.password', $config[ 'vankosoft_api' ]['password'] );
    }
}
