<?php namespace Vankosoft\IssueTrackingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use daddl3\SymfonyCKEditor5WebpackViteBundle\Form\Ckeditor5TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\KanbanboardTask as VsKanbanboardTask;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\ProjectIssue;

class KanbanboardTaskForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
       $builder
            ->add( 'pipeline', HiddenType::class, [
                'data'  => $options['pipeline_id'],
            ])
            
            ->add( 'issue', ChoiceType::class, [
                'label'                 => 'vs_issue_tracking.form.kanbanboard_task.project_issue',
                'placeholder'           => 'vs_issue_tracking.form.kanbanboard_task.project_issue_placeholder',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'choices'               => $options['projectIssues'],
                'data'                  => $options['selectedIssue'],
               
            ])
            
            ->add( 'description', Ckeditor5TextareaType::class, [
                'label'                 => 'vs_issue_tracking.form.project_issue.description',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'required'              => false,
                
                'attr' => [
                    'data-ckeditor5-config' => 'devpage'
                ],
                
                'data'                  => $options['selectedDescription'],
            ])
           
            ->add( 'issueType', ChoiceType::class, [
                'required'              => true,
                'choices'               => \array_flip( VsKanbanboardTask::ISSUE_TYPES ),
                'label'                 => 'vs_issue_tracking.form.kanbanboard_task.issue_type',
                'translation_domain'    => 'VSIssueTrackingBundle',
            ])
            
            ->add( 'issueStatus', ChoiceType::class, [
                'label'                 => 'vs_issue_tracking.form.project_issue.status',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'choices'               => \array_flip( ProjectIssue::ISSUE_STATUS ),
                'expanded'              => true,
                'required'              => false,
                'placeholder'           => false,
            ])
           
            ->add( 'priority', ChoiceType::class, [
                'required'              => true,
                'label'                 => 'vs_issue_tracking.form.kanbanboard_task.priority',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'choices'               => \array_flip( VsKanbanboardTask::TASK_PRIORITIES ),
                'data'                  => $options['selectedPriority'],
            ])
            
            ->add( 'status', ChoiceType::class, [
                'required'              => true,
                'label'                 => 'vs_issue_tracking.form.kanbanboard_task.status',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'choices'               => \array_flip( VsKanbanboardTask::TASK_STATUSES ),
                'data'                  => $options['selectedStatus'],
            ])
            
            ->add( 'dueDate', DateType::class, [
                'label'                 => 'vs_issue_tracking.form.kanbanboard_task.due_date',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'widget'                => 'single_text',
                'html5'                 => false,
                'required'              => false,
                'data'                  => $options['selectedDueDate'] ? new \DateTime( $options['selectedDueDate'] ) : null,
            ])
            
            ->add( 'assignedTo', ChoiceType::class, [
                'label'                 => 'vs_issue_tracking.form.kanbanboard_task.members',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'choices'               => $options['boardMembers'],
                'multiple'              => true,
                'expanded'              => true,
                'required'              => false,
            ])
               
            ->add( 'btnSave', SubmitType::class, [
                'label'                 => $options['task_id'] ? 'vs_issue_tracking.form.kanbanboard_task.edit_task' : 'vs_issue_tracking.form.kanbanboard_task.add_task',
                'translation_domain'    => 'VSIssueTrackingBundle',
            ])
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'pipeline_id'       => 0,
            'task_id'           => 0,
            'projectIssues'     => [],
            'selectedIssue'     => 0,
            'selectedDescription' => null,
            
            'boardMembers'      => [],
            
            'selectedPriority'  => null,
            'selectedStatus'    => null,
            'selectedDueDate'   => null,
        ]);
    }
    
    public function getName()
    {
        return 'vs_issue_tracking_project_kanbanboard_task';
    }
}
