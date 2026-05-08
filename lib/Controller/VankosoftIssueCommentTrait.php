<?php namespace Vankosoft\IssueTrackingBundle\Controller;

use Symfony\Component\Form\FormInterface;

use Vankosoft\IssueTrackingBundle\Form\ProjectIssueCommentForm;

trait VankosoftIssueCommentTrait
{
    protected function createCommentForm( $issueId, ?array $issueData = null ): FormInterface
    {
        $formAction     = $this->generateUrl( 'vs_issue_tracking_project_issue_comments_create', [
            'issueId'           => $issueId,
            'parentCommentId'   => 0,
        ]);
        
        return $this->createForm( ProjectIssueCommentForm::class, $issueData, [
            'action'    => $formAction,
        ]);
    }
    
    protected function handleCommentForm( FormInterface $form, int $issueId ): array
    {
        $formData   = $form->getData();
        //echo '<pre>'; var_dump( $formData ); die;
        
        $formData['memberEmail']    = $this->securityBridge->getUser()->getEmail();
        $response   = $this->vsProject->createIssueComment( $issueId, $formData );
        //echo '<pre>'; var_dump( $response ); die;
        
        return $response;
    }
}
