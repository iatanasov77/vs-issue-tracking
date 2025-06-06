<?php namespace Vankosoft\IssueTrackingBundle\Component;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Psr\Cache\CacheItemPoolInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Vankosoft\IssueTrackingBundle\Component\Exception\VankosoftApiException;

class ProjectApiClient implements ProjectApiClientInterface
{
    /** @var HttpClientInterface */
    protected $httpClient;
    
    /** @var CacheItemPoolInterface */
    protected $cache;
    
    /** @var array */
    protected $apiConnection;
    
    /** @var string */
    protected $projectSlug;
    
    /** @var string */
    protected $kanbanboardSlug;
    
    public function __construct(
        HttpClientInterface $httpClient,
        CacheItemPoolInterface $cache,
        array $apiConnection,
        string $projectSlug,
        string $kanbanboardSlug
    ) {
        $this->httpClient       = $httpClient;
        $this->cache            = $cache;
        $this->apiConnection    = $apiConnection;
        $this->projectSlug      = $projectSlug;
        $this->kanbanboardSlug  = $kanbanboardSlug;
    }
    
    /**
     * @inheritdoc
     */
    public function login(): string
    {
        $payload    = $this->getLoginPayload();
        //echo '<pre>'; var_dump( $payload ); die;
        
        if ( ! isset( $payload['payload'] ) ) {
            throw new VankosoftApiException( $payload['message'], $payload['code'] );
        }
        
        return $payload['payload']['token'];
    }
    
    private function getLoginPayload(): array
    {
        $cacheKey   = 'project_api_connection';
        $cache      = $this->cache->getItem( $cacheKey );
        
        if ( false === $cache->isHit() ) {
            $response   = $this->_doLogin();
            //echo '<pre>'; var_dump( $response ); die;
            
            try {
                $payload = $response->toArray( false );
            } catch ( \JsonException $e ) {
                throw new VankosoftApiException( 'Invalid JSON Payload !!!' );
            } catch ( TransportException $e ) {
                throw new VankosoftApiException( 'Symfony TransportException: ' . $e->getMessage() );
            }
            
            $jsonPayload   = \json_encode( $payload );
            $cache->set( $jsonPayload );
            
            // Set Cache Expiration Time
            $dt = new \DateTime();
            $dt->setTimestamp( $payload['payload']['tokenExpired'] );
            $cache->expiresAt( $dt );
            
            $this->cache->save( $cache );
        }
        
        $cachedConnection   = $cache->get();
        return \json_decode( $cachedConnection, true );
    }
    
    private function _doLogin(): ResponseInterface
    {
        $apiLoginUrl        = $this->apiConnection['host'] . '/login_check';
        //echo '<pre>'; var_dump( $apiLoginUrl ); die;
        
        try {
            $response       = $this->httpClient->request( 'POST', $apiLoginUrl, [
                'json' => [
                    'username' => $this->apiConnection['user'],
                    'password' => $this->apiConnection['password']
                ],
            ]);
        }  catch ( JWTEncodeFailureException $e ) {
            throw new VankosoftApiException( 'JWTEncodeFailureException: ' . $e->getMessage() );
        }
        
        return $response;
    }
}