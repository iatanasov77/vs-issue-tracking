require( '@kanety/jquery-simple-tree-table/dist/jquery-simple-tree-table.js' );

import { VsTranslator, VsLoadTranslations } from '@@/js/includes/bazinga_js_translations.js';
VsLoadTranslations(['VSApplicationBundle', 'VSIssueTrackingBundle']);
window.commentIsSubmited   = false;

$( function()
{
    $( '.btnIssueComment' ).on( 'click', function( e )
    {
        e.preventDefault();
        
        var commentId   = $( this ).attr( 'data-commentId' );
        var redirectUrl = $( this ).attr( 'data-redirectUrl' );
        var _Translator = VsTranslator( 'VSIssueTrackingBundle' );
        
        $.ajax({
            type: "GET",
            url: $( this ).attr( 'href' ),
            success: function( response )
            {
                let modalTitle  = commentId == '0' ?
                                    _Translator.trans( 'vs_issue_tracking.template.project_issues.create_issue_comment_modal_title' ) :
                                    _Translator.trans( 'vs_issue_tracking.template.project_issues.edit_issue_comment_modal_title' );
                                    
                $( '#ProjectIssueCommentModalTitle' ).text( modalTitle );
                $( '#IssueCommentBody> div.card-body' ).html( response );
                
                if ( redirectUrl ) {
                    $( '#project_issue_comment_form_redirectUrl' ).val( redirectUrl );
                }
                
                /** Bootstrap 5 Modal Toggle */
                const myModal = new bootstrap.Modal('#ProjectIssueCommentModal', {
                    keyboard: false
                });
                myModal.show( $( '#ProjectIssueCommentModal' ).get( 0 ) );
            },
            error: function()
            {
                alert( "SYSTEM ERROR!!!" );
            }
        });
    });
    
    $( '#btnSaveIssueComment' ).on( 'click', function( e )
    {
        $( '#ProjectIssueCommentForm' ).submit();
    });
    
    $( '#tableIssueComments' ).simpleTreeTable({
        expander: $( '#expander' ),
        collapser: $( '#collapser' ),
        opened: 'all',
        //opened: []
    });
    
    $( ".btnDeleteComment" ).on( "click", function ( e )
    {
        e.preventDefault();

        $( '#deleteResourceForm' ).attr( 'action', $( this ).attr( 'href' ) );
        $( '#resource_delete__token' ).val( $( this ).attr( 'data-csrftoken' ) );
        $( '#resource_delete__redirect' ).val( $( this ).attr( 'data-redirectUrl' ) );
        
        $( '#deleteResourceForm' ).submit();
    });
});