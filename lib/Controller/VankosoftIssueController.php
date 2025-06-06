<?php namespace Vankosoft\IssueTrackingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

use Vankosoft\IssueTrackingBundle\Component\Exception\VankosoftApiException;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\ProjectIssue;
use Vankosoft\IssueTrackingBundle\Form\ProjectIssueForm;

class VankosoftIssueController extends AbstractController
{
    /** @var ProjectIssue */
    private $vsProject;
    
    public function __construct(
        ProjectIssue $vsProject
    ) {
        $this->vsProject    = $vsProject;
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
    
    public function createAction( Request $request ): Response
    {
        $labelsWhitelist    = $this->vsProject->getIssueLabelWhitelist();
        
        $form               = $this->createIssueForm();
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData   = $form->getData();
            //echo '<pre>'; var_dump( $formData ); die;
            
            $response   = $this->vsProject->createIssue( $formData );
            //echo '<pre>'; var_dump( $response ); die;
            
            if ( $form->getClickedButton() && 'btnApply' === $form->getClickedButton()->getName() ) {
                return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_update', ['id' => $response['issue_id']] ) );
            } else {
                return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_index' ) );
            }
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssues/create.html.twig', [
            'form'              => $form,
            'itemId'            => 0,
            'itemComments'      => [],
            'itemTasks'         => [],
            
            'labelsWhitelist'   => $labelsWhitelist,
        ]);
    }
    
    public function updateAction( $id, Request $request ): Response
    {
        $response           = $this->vsProject->getIssue( intval( $id ) );
        $labelsWhitelist    = $this->vsProject->getIssueLabelWhitelist();
        
        //$issue  = $this->vsProject->getIssue( $id );
        $form               = $this->createIssueForm( $response );
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData   = $form->getData();
            //echo '<pre>'; var_dump( $formData ); die;
            
            $response = $this->vsProject->updateIssue( intval( $id ), $formData );
            //echo '<pre>'; var_dump( $response ); die;
            
            if ( $form->getClickedButton() && 'btnApply' === $form->getClickedButton()->getName() ) {
                return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_update', ['id' => $response['issue_id']] ) );
            } else {
                return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_index' ) );
            }
        }
        
        return $this->render( '@VSIssueTracking/Pages/ProjectIssues/update.html.twig', [
            'form'              => $form,
            'itemId'            => $id,
            'itemComments'      => $response ['comments'],
            'itemTasks'         => $response ['tasks'],
            
            'labelsWhitelist'   => $labelsWhitelist,
        ]);
    }
    
    public function deleteAction( $id, Request $request ): Response
    {
        $response   = $this->vsProject->deleteIssue( intval( $id ) );
        
        return $this->redirect( $this->generateUrl( 'vs_issue_tracking_project_issues_index' ) );
    }
    
    private function createIssueForm( ?array $issueData = null ): FormInterface
    {
        return $this->createForm( ProjectIssueForm::class, $issueData, [
            
        ]);
    }
}