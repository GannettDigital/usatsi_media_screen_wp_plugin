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
	<div id="mexp-item-<?php echo esc_attr( $tab ); ?>-{{ data.id }}" class="mexp-item-area" data-id="{{ data.id }}">
  <div class="mexp-item-container clearfix">
	<div class="mexp-item-thumb">
	  <img class="mexp-item-img {{ data.meta.locked }}" src="{{ data.thumbnail }}"
		   data-image-id="{{ data.meta.image_id }}"
		   data-download-url="{{ data.url }}"
		   data-post-id="<?php echo esc_attr( get_the_id() ); ?>"
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
		<?php
	}
}
