<?php namespace Vankosoft\IssueTrackingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

use Vankosoft\UsersBundle\Security\SecurityBridge;
use Vankosoft\IssueTrackingBundle\Component\Exception\VankosoftApiException;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\ProjectIssue;
use Vankosoft\IssueTrackingBundle\Form\ProjectIssueCommentForm;

class VankosoftIssueCommentController extends AbstractController
{
    /** @var SecurityBridge */
    private $securityBridge;
    
    /** @var ProjectIssue */
    private $vsProject;
    
    public function __construct(
        SecurityBridge $securityBridge,
        ProjectIssue $vsProject
    ) {
        $this->securityBridge   = $securityBridge;
        $this->vsProject        = $vsProject;
    }
    
    public function indexAction( Request $request ): Response
    {
        $apiProject = $this->getParameter( 'vs_issue_tracking.project' );
        if ( $apiProject === ProjectIssue::PROJECT_UNDEFINED ) {
            throw new VankosoftApiException( 'VankoSoft API Project Slug is NOT Defined !!!' );
        }
        
        $issues = $this->vsProject->getIssues();
        //echo '<pre>'; var_dump( $issues ); die;
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssues/index.html.twig', [
            'issues'    => $issues,
        ]);
    }
    
    public function createAction( $issueId, $parentCommentId, Request $request ): Response
    {
        $labelsWhitelist    = $this->vsProject->getIssueLabelWhitelist();
        
        //$issue = $this->vsProject->createIssueComment();
        $form       = $this->createCommentForm( $issueId );
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData   = $form->getData();
            //echo '<pre>'; var_dump( $formData ); die;
            
            $formData['memberEmail']    = $this->securityBridge->getUser()->getEmail();
            $response   = $this->vsProject->createIssueComment( $issueId, $formData );
            //echo '<pre>'; var_dump( $response ); die;
            
            return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_update', ['id' => $response['issue_id']] ) );
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssueComments/_form.html.twig', [
            'form'              => $form,
            'itemId'            => 0,
            
            'labelsWhitelist'   => $labelsWhitelist,
        ]);
    }
    
    public function updateAction( $issueId, $id, Request $request ): Response
    {
        $response           = $this->vsProject->getIssue( intval( $id ) );
        $labelsWhitelist    = $this->vsProject->getIssueLabelWhitelist();
        
        //$issue  = $this->vsProject->getIssue( $id );
        $form               = $this->createIssueForm( $response );
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData   = $form->getData();
            //echo '<pre>'; var_dump( $formData ); die;
            
            $response = $this->vsProject->updateIssueComment( $issueId, intval( $id ), $formData );
            //echo '<pre>'; var_dump( $response ); die;
            
            return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_update', ['id' => $response['issue_id']] ) );
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssueComments/_form.html.twig', [
            'form'              => $form,
            'itemId'            => $id,
            
            'labelsWhitelist'   => $labelsWhitelist,
        ]);
    }
    
    public function deleteAction( $issueId, $id, Request $request ): Response
    {
        $response   = $this->vsProject->deleteIssueComment( $issueId, intval( $id ) );
        
        return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_update', ['id' => $issueId] ) );
    }
    
    private function createCommentForm( $issueId, ?array $issueData = null ): FormInterface
    {
        $formAction     = $this->generateUrl( 'vs_issue_tracking_project_issue_comments_create', [
            'issueId'           => $issueId,
            'parentCommentId'   => 0,
        ]);
            
        return $this->createForm( ProjectIssueCommentForm::class, $issueData, [
            'action'    => $formAction,
        ]);
    }
}