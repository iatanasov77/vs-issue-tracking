<?php namespace Vankosoft\IssueTrackingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class VSIssueTrackingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load( array $config, ContainerBuilder $container ): void
    {
        $config = $this->processConfiguration( $this->getConfiguration([], $container), $config );
        $this->prepend( $container );
        
        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yaml' );
        
        // VankoSoft API
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.enabled', $config[ 'vankosoft_api' ]['enabled'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.project', $config[ 'vankosoft_api' ]['project'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.kanbanboard', $config[ 'vankosoft_api' ]['kanbanboard'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.host', $config[ 'vankosoft_api' ]['connection']['host'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.user', $config[ 'vankosoft_api' ]['connection']['user'] );
        $container->setParameter( 'vs_issue_tracking.vankosoft_api.password', $config[ 'vankosoft_api' ]['connection']['password'] );
    }
}
