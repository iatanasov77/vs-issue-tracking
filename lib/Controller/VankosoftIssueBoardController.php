<?php namespace Vankosoft\IssueTrackingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

use Vankosoft\UsersBundle\Security\SecurityBridge;
use Vankosoft\ApplicationBundle\Component\Status;
use Vankosoft\IssueTrackingBundle\Component\Exception\VankosoftApiException;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\ProjectIssue;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\KanbanboardTask as VsKanbanboardTask;
use Vankosoft\IssueTrackingBundle\Form\ProjectIssueForm;
use Vankosoft\IssueTrackingBundle\Form\KanbanboardTaskForm;
use Vankosoft\IssueTrackingBundle\Form\KanbanBoardSubTaskForm;
use Vankosoft\IssueTrackingBundle\Form\KanbanBoardTaskAttachmentForm;
use Vankosoft\IssueTrackingBundle\Form\ProjectIssueCommentForm;

class VankosoftIssueBoardController extends AbstractController
{
    /** @var HttpClientInterface */
    private $httpClient;
    
    /** @var SecurityBridge */
    private $securityBridge;
    
    /** @var ProjectIssue */
    private $vsProject;
    
    public function __construct(
        HttpClientInterface $httpClient,
        SecurityBridge $securityBridge,
        ProjectIssue $vsProject
    ) {
        $this->httpClient       = $httpClient;
        $this->securityBridge   = $securityBridge;
        $this->vsProject        = $vsProject;
    }
    
    public function showKanbanboardAction( Request $request ): Response
    {
        $apiBoard   = $this->getParameter( 'vs_issue_tracking.kanbanboard' );
        if ( $apiBoard === ProjectIssue::BOARD_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Kanbanboard Slug is NOT Defined !!!' );
        }
        
        $board = $this->vsProject->getKanbanboard();
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoard/kanbanboard.html.twig', [
            'board'         => $board,
            'addMembers'    => false,
        ]);
    }
    
    public function showTaskAction( $taskId, Request $request ): Response
    {
        $apiBoard   = $this->getParameter( 'vs_issue_tracking.kanbanboard' );
        if ( $apiBoard === ProjectIssue::BOARD_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Kanbanboard Slug is NOT Defined !!!' );
        }
        
        $response       = $this->vsProject->getKanbanboardTask( $taskId );
        $designations   = VsKanbanboardTask::BOARD_MEMBER_DESIGNATIONS;
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoardTask/show.html.twig', [
            'designations'  => $designations,
            'task'          => $response['task'],
            'board'         => $response['board'],
//             'taskId'        => $taskId,
//             'pipelineSlug'  => $board['pipelines'][$pipelineId]['slug'],
            
            'commentForm'   => $this->createForm( ProjectIssueCommentForm::class, null, [
                //'action'    => $formAction,
            ]),
        ]);
    }
    
    public function moveTaskAction( $taskId, $pipelineId, Request $request ): Response
    {
        $apiBoard   = $this->getParameter( 'vs_issue_tracking.kanbanboard' );
        if ( $apiBoard === ProjectIssue::BOARD_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Kanbanboard Slug is NOT Defined !!!' );
        }
        
        $response = $this->vsProject->moveKanbanboardTask( $taskId, $pipelineId );
        
        return new JsonResponse( $response );
    }
    
    public function getAssignMemberForm( $taskId, Request $request ): Response
    {
        $response       = $this->vsProject->getKanbanboardTask( $taskId );
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoard/partial/assign_member_form.html.twig', [
            'task'  => $response['task'],
            'board' => $response['board'],
        ]);
    }
    
    public function assignMemberAction( $taskId, $memberId, Request $request ): Response
    {
        $apiBoard   = $this->getParameter( 'vs_issue_tracking.kanbanboard' );
        if ( $apiBoard === ProjectIssue::BOARD_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Kanbanboard Slug is NOT Defined !!!' );
        }
        
        $response = $this->vsProject->assignKanbanboardTaskMember( $taskId, $memberId );
        
        return new JsonResponse( $response );
    }
    
    public function deleteMemberAction( $taskId, $memberId, Request $request ): Response
    {
        $response   = $this->vsProject->deleteKanbanboardTaskMember([
            'taskId'    => $taskId,
            'memberId'  => $memberId,
        ]);
        
        $redirectUrl    = $request->request->get( 'redirectUrl' );
        if ( $redirectUrl ) {
            return $this->redirect( $redirectUrl );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
        ]);
    }
    
    public function createIssueAction( $pipelineId, $parentTaskId, Request $request ): Response
    {
        $form   = $this->createForm( ProjectIssueForm::class, null, [
            'action'    => $this->generateUrl( 'vs_issue_tracking_project_issues_kanbanboard_task_create_issue', [
                'pipelineId'    => $pipelineId,
                'parentTaskId'  => $parentTaskId
            ]),
            'method'    => 'POST',
        ]);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData   = $form->getData();
            //echo '<pre>'; var_dump( $formData ); die;
            
            $formData['memberEmail']    = $this->securityBridge->getUser()->getEmail();
            $response   = $this->vsProject->createIssue( $formData );
            //echo '<pre>'; var_dump( $response ); die;
            
            return new JsonResponse([
                'status'   => Status::STATUS_OK,
                'payload'   => [
                    'pipelineId'    => $pipelineId,
                    'parentTaskId'  => $parentTaskId,
                    'issueId'       => $response['issue_id'],
                ],
            ]);
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoard/partial/create_issue_form.html.twig', [
            'form'              => $form,
            'labelsWhitelist'   => $this->vsProject->getIssueLabelWhitelist(),
        ]);
    }
    
    public function createTaskAction( $pipelineId, $issueId, Request $request ): Response
    {
        $apiBoard   = $this->getParameter( 'vs_issue_tracking.kanbanboard' );
        if ( $apiBoard === ProjectIssue::BOARD_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Kanbanboard Slug is NOT Defined !!!' );
        }
        
        $formOptions = $this->vsProject->getPipelineTaskFormData();
        $form = $this->createForm( KanbanboardTaskForm::class, null, [
            'action'        => $this->generateUrl( 'vs_issue_tracking_project_issues_kanbanboard_pipeline_create_task', [
                'pipelineId'    => $pipelineId,
                'issueId'       => $issueId,
            ]),
            'method'        => 'POST',
            
            'pipeline_id'   => $pipelineId,
            'projectIssues' => $formOptions['issues'],
            'selectedIssue' => $issueId,
            
            'boardMembers'  => $formOptions['members']['selectOptions'],
        ]);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData   = $form->getData();
            //echo '<pre>'; var_dump( $formData ); die;
            
            $response   = $this->vsProject->createKanbanboardTask( $formData );
            //echo '<pre>'; var_dump( $response ); die;
            
            return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_kanbanboard_show' ) );
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoard/partial/create_task_form.html.twig', [
            'form'          => $form,
            'pipelineId'    => $pipelineId,
            'boardMembers'  => $formOptions['members']['extended'],
        ]);
    }
    
    public function editTaskAction( $pipelineId, $taskId, Request $request ): Response
    {
        $apiBoard   = $this->getParameter( 'vs_issue_tracking.kanbanboard' );
        if ( $apiBoard === ProjectIssue::BOARD_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Kanbanboard Slug is NOT Defined !!!' );
        }
        
        $response       = $this->vsProject->getKanbanboardTask( $taskId );
        
        $formOptions = $this->vsProject->getPipelineTaskFormData();
        $form   = $this->createForm( KanbanboardTaskForm::class, null, [
            'action'    => $this->generateUrl( 'vs_issue_tracking_project_issues_kanbanboard_pipeline_edit_task', [
                'pipelineId'    => $pipelineId,
                'taskId'        => $taskId,
            ]),
            'method'    => 'PUT',
            
            'pipeline_id'   => $pipelineId,
            'projectIssues' => $formOptions['issues'],
            'selectedIssue' => $response['task']['issue']['id'],
            
            'boardMembers'  => $formOptions['members']['selectOptions'],
        ]);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $submitedTask    = $form->getData();
            
            $response   = $this->vsProject->editKanbanboardTask( $submitedTask );
            
            return $this->redirectToRoute( 'vs_issue_tracking_project_issues_kanbanboard_show' );
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoardTask/update.html.twig', [
            'form'          => $form,
            'item'          => $response['task'],
            'pipelineId'    => $pipelineId,
        ]);
    }
    
    public function deleteTaskAction( $taskId, Request $request ): Response
    {
        $response   = $this->vsProject->deleteKanbanboardTask( $taskId );
        
        return $this->redirectToRoute( 'vs_issue_tracking_project_issues_kanbanboard_show' );
    }
    
    public function getSubTaskFormAction( $taskId, $issueId, $subTaskId, Request $request ): Response
    {
        $response       = $this->vsProject->getKanbanboardTask( $taskId );
        
        $formOptions = $this->vsProject->getPipelineTaskFormData();
        $form   = $this->createForm( KanbanBoardSubTaskForm::class, null, [
            'action'    => $this->generateUrl( 'vs_issue_tracking_project_issues_kanbanboard_task_get_subtask_form', [
                'taskId'    => $taskId,
                'subTaskId' => $subTaskId,
                'issueId'   => $issueId,
            ]),
            'method'            => 'POST',
            
            'parent_task_id'    => $taskId,
            
            'projectIssues'     => $formOptions['issues'],
            'selectedIssue'     => $issueId,
            
            'boardMembers'      => $formOptions['members']['selectOptions'],
        ]);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $subTask    = $form->getData();
            //echo '<pre>'; var_dump( $subTask ); die;
            
            $response   = $this->vsProject->createKanbanboardTaskSubTask( $subTask );
            //echo '<pre>'; var_dump( $response ); die;
            
            return $this->redirectToRoute( 'vs_issue_tracking_project_issues_kanbanboard_task_show', [
                'taskId'        => $taskId
            ]);
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoard/partial/create_subtask_form.html.twig', [
            'form'          => $form,
            'item'          => $response['task'],
            'boardMembers'  => $response['board']['members'],
        ]);
    }
    
    public function getCommentFormAction( $taskId, Request $request ): Response
    {
        
    }
    
    public function saveCommentFormAction( $taskId, Request $request ): Response
    {
        
    }
    
    public function getAttachmentFormAction( $taskId, Request $request ): Response
    {
        $attachmentId   = 0;
        $formOptions    = [
            'action'    => $this->generateUrl( 'vs_issue_tracking_project_issues_kanbanboard_task_save_attachment', [
                'taskId'        => $taskId,
                'attachmentId'  => $attachmentId,
            ]),
            'method'    => 'POST',
            'taskId'    => $taskId,
            'fileId'    => $attachmentId,
        ];
        $form   = $this->createForm( KanbanBoardTaskAttachmentForm::class, null, $formOptions );
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssuesBoard/partial/attach_file_form.html.twig', [
            'form'      => $form,
            'fileId'    => $attachmentId,
        ]);
    }
    
    public function saveTaskAttachment( $taskId, $attachmentId, Request $request ): Response
    {
        if ( $request->isMethod( 'POST' ) ) {
            $em     = $this->doctrine->getManager();
            
            $id     = \intval( $request->request->get( 'attachmentId' ) );
            $entity = $this->attachmentsRepository->find( $id );
            
            
            $task   = $this->tasksRepository->find( $taskId );
            $entity->setTask( $task );
            
            $em->persist( $entity );
            $em->flush();
            
            return new JsonResponse([
                'status'   => Status::STATUS_OK
            ]);
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_ERROR,
            'message'   => 'Form NOT Submitted Properly !',
        ]);
    }
    
    public function deleteTaskAttachment( $taskId, $attachmentId, Request $request ): Response
    {
        $response   = $this->vsProject->deleteKanbanboardTaskAttachment([
            'attachmentId'  => $attachmentId,
        ]);
        
        $redirectUrl    = $request->request->get( 'redirectUrl' );
        if ( $redirectUrl ) {
            return $this->redirect( $redirectUrl );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
        ]);
    }
    
    public function downloadTaskAttachment( $taskId, $attachmentId, Request $request ): Response
    {
        $attachment     = $this->vsProject->getKanbanboardTaskAttachment( $attachmentId );
        $remoteResponse = $this->vsProject->downloadKanbanboardTaskAttachment( $attachmentId );
        
        $client     = $this->httpClient;
        $response   = new StreamedResponse();
        $response->setCallback( function() use ( $client, $remoteResponse )
        {
            foreach ( $client->stream( $remoteResponse ) as $chunk ) {
                print $chunk->getContent();
            }
        });
        
        $response->headers->set( 'Content-Transfer-Encoding', 'binary' );
        $response->headers->set( 'Content-Type', $attachment['type'] );
        $this->makeContentDisposition( $attachment, $response );
        
        return $response;
    }
    
    private function makeContentDisposition( array $attachment, Response &$response )
    {
        $transliterator = \Transliterator::create( 'Any-Latin' );
        $transliteratorToASCII = \Transliterator::create( 'Latin-ASCII' );
        $originalName   = $transliteratorToASCII->transliterate(
            $transliterator->transliterate( $attachment['originalName'] )
        );
        
        $disposition    = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $originalName
        );
        
        $response->headers->set( 'Content-Disposition', $disposition );
    }
}
