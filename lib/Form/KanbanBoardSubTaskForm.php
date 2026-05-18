<?php namespace Vankosoft\IssueTrackingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use daddl3\SymfonyCKEditor5WebpackViteBundle\Form\Ckeditor5TextareaType;

use Doctrine\ORM\EntityRepository;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\KanbanboardTask as VsKanbanboardTask;
use Vankosoft\IssueTrackingBundle\Component\ProjectIssue\ProjectIssue;

class KanbanBoardSubTaskForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        $builder
            ->add( 'parentTask', HiddenType::class, [
                'data'  => $options['parent_task_id'],
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
            ])
            
            ->add( 'issueStatus', ChoiceType::class, [
                'label'                 => 'vs_issue_tracking.form.project_issue.status',
                'translation_domain'    => 'VSIssueTrackingBundle',
                'expanded'              => true,
                'required'              => false,
                'placeholder'           => false,
                'choices'               => \array_flip( ProjectIssue::ISSUE_STATUS ),
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
            
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'parent_task_id'    => 0,
            
            'projectIssues'     => [],
            'selectedIssue'     => 0,
            
            'boardMembers'      => [],
            
            'selectedPriority'  => null,
            'selectedStatus'    => null,
            'selectedDueDate'   => null,
        ]);
    }
    
    public function getName()
    {
        return 'vs_issue_tracking_project_kanbanboard_subtask';
    }
}
