var UsatsiMexpContentView = wp.media.view.MEXP;

wp.media.view.MEXP = UsatsiMexpContentView.extend({

    events: function(){
        return _.extend({},UsatsiMexpContentView.prototype.events,{
            'click .mexp-item' : 'importImage'
        });
    },

    initialize: function() {
        mexpContentView.prototype.initialize.apply( this, arguments );
    },

    importImage: function(e) {
        e.stopPropagation();

        //window.location = '/';
        console.log('hello');



        var download_url = jQuery(e.target).data('download-url');
        var image_id = jQuery(e.target).data('image-id');
        var post_id = jQuery(e.target).data('post-id');

        var data = {
            'action' : 'usatsi_download_image',
            'download_url' : download_url,
            'image_id' : image_id,
            'post_id' : post_id
        };


        console.log(data);

        jQuery.post(usatsi_image_ajax.ajax_url, data, function(response) {
            console.log(response);



            usatsi_image_ajax['attachmentId'] = response;


            //Triggers click on hidden media tab
            jQuery('.media-menu a:last-child').trigger('click');
            //console.log(data);
            //console.log('Got this from the server: ' + response);
            //jQuery('#usatsi-mexp-image-upload-form').html(response);
            // jQuery('.mexp-items.attachments').html(response);
            //jQuery('#image-form').submit();



            //jQuery('.media-frame-content').empty();
            //jQuery('.media-frame-content').html(response);
            //jQuery('#image-form').submit();



        })
            .fail(function() {
                console.log( "error" );
            })

    }


});
