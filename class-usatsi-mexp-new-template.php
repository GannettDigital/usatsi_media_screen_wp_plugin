<?php
/**
 * Class-usatsi-mexp-new-template.php
 *
 * @author    Thomas J. Rivera
 * @copyright USA Today Sports Images
 * @license   GPL-2
 * @package   Vendor/Project
 * @see       https://www.usatsimg.com
 */

/**
 * Backbone templates for various views for your new service
 */
class Usatsi_MEXP_New_Template extends MEXP_Template {

	/**
	 * Outputs the Backbone template for an item within search results.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function item( $id, $tab ) {
		$nonce = wp_create_nonce( 'usatsi_download_image' );
	?>
		<div class="usatsi-media-paging" data-usatsi-page="{{ data.meta.page }}" data-usatsi-max-page="{{ data.meta.maxpage }}"></div>

		<div id="mexp-item-<?php echo esc_attr( $tab ); ?>-{{ data.id }}" class="usatsi-mexp-item mexp-item-area" data-id="{{ data.id }}">
		  <div class="mexp-item-container clearfix">
			<div class="mexp-item-thumb">
			  <img class="usatsi-media-img mexp-item-img {{ data.meta.locked }}" src="{{ data.thumbnail }}"
				   data-image-id="{{ data.meta.image_id }}"
				   data-download-url="{{ data.url }}"
				   data-post-id="<?php echo esc_attr( the_ID() ); ?>"
				   data-image-title="{{ data.content }}"
				   data-image-caption="{{ data.meta.caption }}"
				   data-image-credit="{{ data.meta.credit }}"
				   data-nonce="<?php echo esc_attr( $nonce ); ?>" />
			</div>

			<div class="mexp-item-main">
			  <div class="mexp-item-content">
				{{ data.content }}
			  </div>
			  <div class="mexp-item-date">
				{{ data.date }}
			  </div>
			</div>

		  </div>
		  <ul class="media-actions">
			<li class="media-icon-preview" title="Image Preview"></li>
			<li class="media-icon-import" title="Import File"></li>
		  </ul>
		  <div class="media-preview-link">
			<p>Please see your account administrator to unlock this image for import.</p>
			<input value="http://www.usatsimg.com/setImages/{{ data.meta.parent_id }}/preview/{{ data.meta.image_id }}" class="media-preview-link-input" />
			<button class="media-preview-link-copy">copy</button>
			<button class="media-preview-link-anchor">
			  <a target="_blank" href="http://www.usatsimg.com/setImages/{{ data.meta.parent_id }}/preview/{{ data.meta.image_id }}">goto</a>
			</button>
		  </div>
		  <div class="on-hover-content">
			<img class="media-preview-image" data-src="{{ data.meta.previewUrl }}" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/x.png' ); ?>">
			<div class="label-wrapper label-title">
			  {{ data.content }}
			</div>
			<div class="label-wrapper label-caption">
			  {{ data.meta.caption }}
			</div>
			<div class="label-wrapper label-credit">
			  {{ data.meta.credit }}
			</div>
		  </div>
		  <span class="mexp-spinner">
			  <b class="mexp-svg-spinner">
				  <svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="64px" height="64px" viewBox="0 0 128 128" xml:space="preserve"><g><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="1"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(45 64 64)"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(90 64 64)"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(135 64 64)"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(180 64 64)"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(225 64 64)"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(270 64 64)"/><path d="M38.52 33.37L21.36 16.2A63.6 63.6 0 0 1 59.5.16v24.3a39.5 39.5 0 0 0-20.98 8.92z" fill="#ffffff" fill-opacity="0.25" transform="rotate(315 64 64)"/><animateTransform attributeName="transform" type="rotate" values="0 64 64;45 64 64;90 64 64;135 64 64;180 64 64;225 64 64;270 64 64;315 64 64" calcMode="discrete" dur="720ms" repeatCount="indefinite"></animateTransform></g></svg>
			  </b>
		  </span>
		</div>

		<a href="#" id="mexp-check-{{ data.id }}" data-id="{{ data.id }}" class="check" title="<?php esc_attr_e( 'Deselect', 'mexp' ); ?>">
		  <div class="media-modal-icon"></div>
		</a>

<?php
	}

	/**
	 * Outputs the Backbone template for a select item's thumbnail in the footer toolbar.
	 *
	 * @param string $id The template ID.
	 */
	public function thumbnail( $id ) {
	}

	/**
	 * Outputs the Backbone template for a tab's search fields.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function search( $id, $tab ) {
		?>
	  <style>
		  .usatsi-media-exp-service .media-frame.hide-router .media-frame-title {
			  background: #333;
			  box-shadow: none;
			  border-bottom: none;
		  }
		  .usatsi-media-exp-service .media-frame-title h1 {
			  color: #333;
			  overflow: hidden;
			  text-indent: -1000px;
			  background: url(<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/si-logo-2x.png' ); ?> ) no-repeat;
			  background-size: 80%;
			  width: 250px;
			  height: 60px;
			  background-position: 16px 6px;

		  }
		  .usatsi-media-exp-service .media-frame-content {
			  border-top: none;
		  }

		  .usatsi-media-exp-service .media-modal-close {
			  color: #ccc;
		  }

		  .usatsi-media-exp-service .media-frame .spinner {
			  background: url( <?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/spinner.gif' ); ?>) no-repeat;
			  background-size: 100%;
		  }

		  .usatsi-media-exp-service #mexp-button {
			  display: none;
		  }

		  .usatsi-media-exp-service .media-frame {
			  background: #333;
		  }

		  .usatsi-media-exp-service .media-frame-toolbar .media-toolbar {
			  border-top: none;
		  }

	  </style>
	  <form action="#" class="mexp-toolbar-container clearfix tab-all">
		<input
		  type="text"
		  name="q"
		  value="{{ data.params.q }}"
		  class="mexp-input-text mexp-input-search"
		  size="40"
		  placeholder="<?php esc_attr_e( 'Search for anything!', 'mexp' ); ?>"
	>
	<input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'mexp' ); ?>">
	<div class="spinner"></div>
	  </form>
		<button class="button button-large" id="usatsi-mexp-backto-button"><?php echo esc_html( 'Back To Insert' ); ?></button>
		<?php
	}
}
