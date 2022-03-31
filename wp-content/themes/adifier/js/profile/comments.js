jQuery(document).ready(function($){
	"use strict";
    
    $('.comment').each(function(){
        var $this = $(this);
        var ajaxing = false;
        $this.find('.mark-as-read').on('click', function(){
            var $$this = $(this);
            $.ajax({
                url: adifier_data.ajaxurl,
                method: 'POST',
                data: {
                    action:             'adifier_top_comment_mark_read',
                    adifier_nonce: adifier_data.adifier_nonce,
                    parent_id:          $$this.data('parent_id')
                },
                dataType: 'JSON',
                success: function(response){
                    if( response.error ){
                        alert( response.error );
                    }
                    else{
                        var $parent = $this.parents('.comment-parent');
                        $parent.slideUp(500, function(){
                            $parent.remove();
                        });
                    }
                }
            })
        });        
        $this.find('.reply').on('click', function(){
            var $form = $this.find('.unread-reply-form').removeClass('hidden');
        });
        $this.find('.cancel-reply').on('click', function(){
            $this.find('.unread-reply-form').addClass('hidden');
        });

        $this.find('.send-reply').on('click', function(){
            var $$this = $(this);
            if( !ajaxing ){
                ajaxing = true;
                $$this.append('<i class="aficon-circle-notch aficon-spin"></i>');
                $.ajax({
                    url: adifier_data.ajaxurl,
                    method: 'POST',
                    data: {
                        action:             'adifier_save_reply_comment',
                        adifier_nonce: adifier_data.adifier_nonce,
                        parent_id_reply:    $$this.data('parent_id_reply'),
                        parent_id:          $$this.parents('.comment-parent').data('parent_id'),
                        post_id:            $$this.data('post_id'),
                        comment_message:    $this.find('.comment_message').val()
                    },
                    dataType: 'JSON',
                    success: function(response){
                        if( response.error ){
                            alert( response.error );
                        }
                        else{
                            $this.find('.reply-holder').append( response.comment );
                            $this.find( '.unread-reply-form' ).addClass('hidden');
                        }
                    },
                    complete: function(){
                        ajaxing = false;
                        $$this.find('i').remove();
                    }
                });
            }
        });        
    });


});