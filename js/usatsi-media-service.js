jQuery(function($) {
    $(document).ready(function(){
        $('#insert-my-media').click(open_media_window);
    });

    function open_media_window() {
    }
});

function open_media_window() {
    var window = wp.media({
        title: 'Insert a media',
        library: {type: 'image'},
        multiple: false,
        button: {text: 'Insert'}
    });
}


var UsatsiMexpContentView = wp.media.view.MEXP;

wp.media.view.MEXP = UsatsiMexpContentView.extend(
	{

		events: function(){
			return _.extend(
				{},UsatsiMexpContentView.prototype.events,{
					'click .mexp-item' : 'importImage',
					'click .media-icon-import' : 'triggerImport',
					'click .media-icon-preview' : 'previewImage',
					'mouseleave .on-hover-content' : 'closePreview',
					'click .on-hover-content' : 'triggerImport',
					'mouseenter .mexp-item' : 'showActions',
					'mouseleave .mexp-item' : 'hideActions',
					'click .media-preview-link-copy' : 'copyPreviewLink',
					'mouseleave .media-preview-link' : 'hidePreviewLink'
				}
			);
		},

		initialize: function() {
			// remove the media browser default bindings
			jQuery( '.mexp-item.attachment' ).unbind();

			// Let's hide the hidden USAT SI tab we dont want users to see it
			jQuery( '.media-menu-item:contains("USAT Sports Images Hidden")' ).hide();

			mexpContentView.prototype.initialize.apply( this, arguments );
		},

		copyPreviewLink: function(e) {
			e.preventDefault();
			document.execCommand( 'copy', false, jQuery( e.currentTarget ).prev().select() );
		},

		showActions: function(e) {
			e.stopPropagation();
			jQuery( e.currentTarget ).find( '.media-actions' ).fadeIn( 'fast' );
		},

		hideActions: function(e) {
			e.stopPropagation();
			jQuery( e.currentTarget ).find( '.media-actions' ).fadeOut( 'fast' );
		},

		hidePreviewLink: function(e) {
			e.stopPropagation();
			jQuery( e.currentTarget ).fadeOut( 'fast' );
		},

		closePreview: function(e) {
			e.stopPropagation();
			jQuery( e.currentTarget ).fadeOut( 'fast' );
		},

		triggerImport: function(e) {
			e.stopPropagation();
			jQuery( e.currentTarget ).parent().parent().find( '.mexp-item-img' ).trigger( 'click' );
		},

		previewImage: function(e) {
			e.stopPropagation();
			var elm = jQuery( e.currentTarget ).parent().parent().find( '.on-hover-content' );
			elm.fadeIn(
				'fast', function() {
					if ( ! jQuery( this ).hasClass( 'media-loaded' ) ) {
						jQuery( this ).find( '.media-preview-image' ).attr( 'src', jQuery( this ).find( '.media-preview-image' ).data( 'src' ) );
					}
					jQuery( this ).addClass( 'media-loaded' );
				}
			);

		},

		importImage: function(e) {
			e.preventDefault();
			e.stopPropagation();

			var imgEl = jQuery( e.currentTarget ).find( '.mexp-item-img' );

			if ( ! jQuery( imgEl ).hasClass( 'media-locked' )  ) {

                // Show downloading spinner!
                jQuery( imgEl ).parent().parent().siblings( '.mexp-spinner' ).fadeIn( 'fast' );

				var download_url = imgEl.data( 'download-url' ),
				image_id = imgEl.data( 'image-id' ),
				post_id = imgEl.data( 'post-id' ),
				image_title = imgEl.data( 'image-title' ),
				image_caption = imgEl.data( 'image-caption' ),
				image_credit = imgEl.data( 'image-credit' ),
                wp_nonce = imgEl.data( 'nonce' );

				var data = {
					'action' : 'usatsi_download_image',
					'download_url' : download_url,
					'image_id' : image_id,
					'post_id' : post_id,
					'image_title' : image_title,
					'image_caption' : image_caption,
					'image_credit' : image_credit,
                    '_wpnonce' : wp_nonce
				};

				jQuery.post(
					usatsi_image_ajax.ajax_url + '?_wponce', data, function(response) {
						usatsi_image_ajax['attachmentId'] = response;

						// Triggers click on hidden media tab to open edit iframe window
						jQuery( '.media-menu a:last-child' ).trigger( 'click' );

					}
				)
					.fail(
						function() {
							console.log( "error" );
						}
					)

			} else {
				// Image is not downloadable show messaging overlay!
				jQuery( imgEl ).parent().parent().siblings( '.media-preview-link' ).fadeIn( 'fast' );
			}

		}


	}
);
