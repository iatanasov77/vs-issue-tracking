<?php namespace Vankosoft\IssueTrackingBundle\Component\ProjectIssue;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Vankosoft\IssueTrackingBundle\Component\ProjectApiClient;
use Vankosoft\IssueTrackingBundle\Component\Exception\VankosoftApiException;
use Vankosoft\ApplicationBundle\Component\Status;

final class ProjectIssue extends ProjectApiClient
{
    const PROJECT_UNDEFINED = 'not-defined';
    const BOARD_UNDEFINED   = 'not-defined';
    
    const ISSUE_OPENED      = 'opened';
    const ISSUE_CLOSED      = 'closed';
    const ISSUE_COMPLETED   = 'completed';
    
    const ISSUE_STATUS  = [
        self::ISSUE_OPENED      => 'vs_application.form.project_issue.status_opened',
        self::ISSUE_CLOSED      => 'vs_application.form.project_issue.status_closed',
        self::ISSUE_COMPLETED   => 'vs_application.form.project_issue.status_completed',
    ];
    
    public function getIssues(): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/project-issues';
        
        $response       = $this->httpClient->request( 'GET', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'query'      => [
                'projectSlug' => $this->projectSlug
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function getIssue( int $id ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/project-issues/' . $id;
        
        $response       = $this->httpClient->request( 'GET', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'query'      => [
                'projectSlug' => $this->projectSlug
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function createIssue( array $formData ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/project-issues/new';
        
        $formData['projectSlug']    = $this->projectSlug;
        $response       = $this->httpClient->request( 'POST', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function updateIssue( int $id, array $formData ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/project-issues/' . $id;
        
        $formData['projectSlug']    = $this->projectSlug;
        $response       = $this->httpClient->request( 'PUT', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function deleteIssue( int $id ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/project-issues/' . $id;
        
        $formData       = [
            'projectSlug'   => $this->projectSlug,
        ];
        $response = $this->httpClient->request('DELETE', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function getIssueLabelWhitelist(): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/project-issue-label-whitelist';
        
        $response       = $this->httpClient->request( 'GET', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function getKanbanboard(): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/kanbanboards/1';
        
        $response       = $this->httpClient->request( 'GET', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'query'      => [
                'slug' => $this->kanbanboardSlug
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function getKanbanboardTask( $id ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/kanbanboard-tasks/' . $id;
        
        $response       = $this->httpClient->request( 'GET', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    /**
     * Options for Kanbanboard Create Task Form
     *
     * @return array
     */
    public function getPipelineTaskFormData(): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/pipeline-task-form-data';
        
        $response       = $this->httpClient->request( 'GET', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'query'      => [
                'board'     => $this->kanbanboardSlug,
                'project'   => $this->projectSlug
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function createKanbanboardTask( array $formData ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/new';
        
        $response       = $this->httpClient->request( 'POST', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function editKanbanboardTask( $id, array $formData ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/' . $id;
        
        $response       = $this->httpClient->request( 'PUT', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function deleteKanbanboardTask( $id ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/' . $id;
        
        $response       = $this->httpClient->request( 'DELETE', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function createKanbanboardTaskSubTask( array $formData ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/create-sub-task';
        
        $response       = $this->httpClient->request( 'POST', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function moveKanbanboardTask( $taskId, $pipelineId ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/move';
        
        $response       = $this->httpClient->request( 'POST', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => [
                'taskId'        => $taskId,
                'pipelineId'    => $pipelineId,
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function assignKanbanboardTaskMember( $taskId, $memberId ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/assign-memmber';
        
        $response       = $this->httpClient->request( 'POST', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => [
                'taskId'    => $taskId,
                'memberId'  => $memberId,
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function deleteKanbanboardTaskMember( array $formData ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/pipeline-task/delete-member';
        
        $response = $this->httpClient->request( 'POST', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function createKanbanboardTaskAttachment( array $formData ): array
    {
        $apiToken       = $this->login();
        $boardsEndpoint = $this->apiConnection['host'] . '/pipeline-task/create-attachment';
        
        $response       = $this->httpClient->request( 'POST', $boardsEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function deleteKanbanboardTaskAttachment( array $formData ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/pipeline-task/delete-attachment';
        
        $response = $this->httpClient->request( 'POST', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'json'      => $formData,
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function getKanbanboardTaskAttachment( $id ): array
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/pipeline-task/attachment/' . $id;
        
        $response = $this->httpClient->request( 'GET', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
        ]);
        
        return $this->processApiResponse( $response );
    }
    
    public function downloadKanbanboardTaskAttachment( $id ): ResponseInterface
    {
        $apiToken       = $this->login();
        $issuesEndpoint = $this->apiConnection['host'] . '/pipeline-task/download-attachment/' . $id;
        
        $response = $this->httpClient->request( 'GET', $issuesEndpoint, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiToken,
            ],
        ]);
        
        return $response;
    }
    
    private function processApiResponse( ResponseInterface $response ): array
    {
        try {
            //echo '<pre>'; var_dump( $response ); die;
            $payload = $response->toArray( false );
        } catch ( \JsonException $e ) {
            //echo '<pre>'; var_dump( $e ); die;
            throw new VankosoftApiException( 'Invalid JSON Payload !!!' );
        }
        //echo '<pre>'; var_dump( $payload ); die;
        
        if ( ! isset( $payload['status'] ) || $payload['status'] == Status::STATUS_ERROR ) {
            //echo '<pre>'; var_dump( $payload ); die;
            throw new VankosoftApiException( 'ERROR: ' . $payload['message'] );
        }
        
        return $payload['payload'];
    }
}