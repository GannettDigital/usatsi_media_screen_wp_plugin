/**
	USAT Sports Images Media Service Plugin

	 @package V1
	created by: Thomas J. Rivera
 */

var UsatsiSportsImages = (function () {
	'use strict';

	jQuery('body').on('click', '#insert-media-button', function(e) {
    	jQuery('.media-menu a:CONTAINS("Insert USAT Sports Images")').hide();
	});

	if (typeof wp.media.view.MEXP !== "undefined") {

		var UsatsiMexpContentView = wp.media.view.MEXP;

		wp.media.view.MEXP = UsatsiMexpContentView.extend(
			{

				events: function(){
					return _.extend(
						{},UsatsiMexpContentView.prototype.events,{
							'click .usatsi-mexp-item' : 'importImage',
							'click .media-icon-import' : 'triggerImport',
							'click .media-icon-preview' : 'previewImage',
							'mouseleave .on-hover-content' : 'closePreview',
							'mouseenter .usatsi-mexp-item' : 'showActions',
							'mouseleave .usatsi-mexp-item' : 'hideActions',
							'click .media-preview-link-copy' : 'copyPreviewLink',
							'mouseleave .media-preview-link' : 'hidePreviewLink',
							'click #usatsi-mexp-backto-button' : 'showMediaTab'
						}
					);
				},

				initialize: function() {

					// remove the media browser default bindings!
					jQuery( '.mexp-item.attachment' ).unbind();
                    jQuery( ".media-menu-item:contains('Insert USAT Sports Images')" ).hide();

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
					jQuery( e.currentTarget ).parent().trigger( 'click' );
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

				showMediaTab: function(e) {
					e.preventDefault();
                	e.stopPropagation();
                    jQuery( '.media-menu a:CONTAINS("Insert USAT Sports Images")' ).trigger( 'click' );
				},

				importImage: function(e) {
					e.preventDefault();
					e.stopPropagation();
                    this.disableEvents();
                    //var enableEvents = this.enableEvents(e);

                        var imgEl = jQuery( e.currentTarget ).find( '.usatsi-media-img' );

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
								usatsi_image_ajax.attachmentId = response;
								// Triggers click on hidden media tab to open edit iframe window!
                                jQuery( '.media-menu a:CONTAINS("Insert USAT Sports Images")' ).trigger( 'click' );
							}
						)
							.fail(
								function() {
									return;
								}
							);

					} else {
						// Image is not downloadable show messaging overlay!
						jQuery( imgEl ).parent().parent().siblings( '.media-preview-link' ).fadeIn( 'fast' );
					}

				},

				disableEvents: function(e) {
                    jQuery(this.el).off('click', '.usatsi-mexp-item');
                    jQuery(this.el).off('click', '.media-icon-import');
				}

			}
		);

	}

});

if (typeof jQuery !== "undefined") {

	jQuery(
		function() {
			'use strict';
			new UsatsiSportsImages();
		}
	);

}
