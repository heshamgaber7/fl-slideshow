<?php
/**
 * Class Slideshow: FL_SLideshow
 *
 * @since 1.0
 * @package FlatLayers Basic Slideshow
 */

/**
 * Class FL_SLideshow
 *
 * @since 1.0
 */
class FL_Slideshow {

	/**
	 * Plugin Name
	 *
	 * @var string
	 */
	private $plugin_name = 'fl_slideshow';

	/**
	 * Version
	 *
	 * @var integer
	 */
	private $version = 1.0;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'translation' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ) );

		add_action( 'init', array( $this, 'post_type' ) );

		add_action( 'add_meta_boxes', array( $this, 'create_metabox' ) );
		add_action( 'save_post', array( $this, 'save_changes' ) );

		add_action( 'manage_fl_slideshow_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_fl_slideshow_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_shortcode( 'fl-slideshow', array( $this, 'slideshow_shortcode' ) );

	}

	/**
	 * Translation
	 *
	 * @since 1.0
	 */
	public function translation() {
		load_plugin_textdomain( 'fl-slideshow', false, plugin_dir_url( __FILE__ ) . '/languages' );
	}


	/**
	 * Admin Scripts & Styles
	 *
	 * @since 1.0
	 */
	public function admin_scripts() {
		global $typenow;
		// Returen if not slideshow post type!
		if ( ( 'fl_slideshow' !== $typenow ) ) {
			return true;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/admin.css', array(), $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/admin.js', array( 'jquery' ), $this->version, true );

		wp_enqueue_media();

		wp_localize_script(
			$this->plugin_name,
			'FL_Slideshow',
			array(
				'loading'      => __( 'Loading...', 'fl-slideshow' ),
				'wpspin_light' => admin_url( 'images/wpspin_light.gif' ),
				'media_title'  => __( 'Pick Slideshow Images', 'fl-slideshow' ),
				'media_button' => __( 'Add Image(s)', 'fl-slideshow' ),
			)
		);

	}

	/**
	 * Public Scripts & Styles
	 *
	 * @since 1.0
	 */
	public function public_scripts() {
		// Owl Carousel Library.
		wp_enqueue_style( 'owl-carousel', plugin_dir_url( __FILE__ ) . 'lib/owl-carousel/owl.carousel.min.css', array(), '2.3.4', 'all' );
		wp_enqueue_script( 'owl-carousel', plugin_dir_url( __FILE__ ) . 'lib/owl-carousel/owl.carousel.min.js', array( 'jquery' ), '2.3.4', true );

		// Public Scripts.
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/public.css', array(), $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/public.js', array( 'jquery' ), $this->version, true );
	}


	/**
	 * Register Slideshow Post Type
	 *
	 * @since 1.0
	 */
	public function post_type() {
		register_post_type(
			'fl_slideshow',
			array(
				'labels'              => array(
					'name'               => __( 'Slideshows', 'fl-slideshow' ),
					'singular_name'      => __( 'Slideshow', 'fl-slideshow' ),
					'add_new_item'       => __( 'Add New Slideshow', 'fl-slideshow' ),
					'new_item'           => __( 'New Slideshow', 'fl-slideshow' ),
					'edit_item'          => __( 'Edit Slideshow', 'fl-slideshow' ),
					'view_item'          => __( 'View Slideshow', 'fl-slideshow' ),
					'view_items'         => __( 'View Slideshows', 'fl-slideshow' ),
					'all_items'          => __( 'All Slideshows', 'fl-slideshow' ),
					'search_items'       => __( 'Search Slideshows', 'fl-slideshow' ),
					'not_found'          => __( 'No slideshows found.', 'fl-slideshow' ),
					'not_found_in_trash' => __( 'No slideshows found in Trash.', 'fl-slideshow' ),
				),
				'public'              => false,
				'has_archive'         => false,
				'show_ui'             => true,
				'exclude_from_search' => true,
				'supports'            => array( 'title' ),
				'menu_icon'           => 'dashicons-slides',
			)
		);
	}


	/**
	 * Add Meta Box
	 * Slideshow Settings
	 *
	 * @since 1.0
	 */
	public function create_metabox() {
		add_meta_box(
			'fl_slideshow_settings',
			__( 'Slideshow Settings', 'fl-slideshow' ),
			array( $this, 'metabox_html' ),
			'fl_slideshow',
			'normal',
			'high'
		);
	}


	/**
	 * MetaBox HTML
	 *
	 * @since 1.0
	 * @param object $post Get post to add metaboxes html.
	 */
	public function metabox_html( $post ) {

		$imgs  = get_post_meta( $post->ID, 'fl_slideshow_imgs', true );
		$trans = get_post_meta( $post->ID, 'fl_slideshow_transition', true );

		$shortcode = '[fl-slideshow id="' . $post->ID . '"]';

		$transtions = array(
			'slide'      => __( 'Slide Horizontal', 'fl-slideshow' ),
			'fade-out'   => __( 'Fade Out', 'fl-slideshow' ),
			'slide-down' => __( 'Slide Down', 'fl-slideshow' ),
			'slide-up'   => __( 'Slide Up', 'fl-slideshow' ),
		);

		wp_nonce_field( 'fl_slideshow_settings_metabox_nonce', 'fl_slideshow_settings_nonce' );

		echo '<div class="fl-slideshow-sec">';
			echo '<div class="fl-slideshow-imgs">';
		if ( ! empty( $imgs ) ) {
			foreach ( $imgs as $img ) {
				$thumbnail = wp_get_attachment_image_src( $img, 'thumbnail' );
				echo '<span data-id="' . esc_attr( $img ) . '"><img src="' . esc_url( $thumbnail[0] ) . '" alt="" /><span class="close"></span></span>';
			}
		}
			echo '</div>';
			echo '<input type="hidden" name="fl_slideshow_imgs" value="' . ( empty( $imgs ) ? '' : esc_attr( implode( ',', $imgs ) ) ) . '">';
			echo '<p><button id="fl-slideshow-button" class="button">' . esc_html__( 'Pick Gallery Images', 'fl-slideshow' ) . '</button></p>';
		echo '</div>';

		echo '<div class="fl-slideshow-sec trans">';
			echo '<label for="fl_slideshow_transition">' . esc_html__( 'Transition Type:', 'fl-slideshow ' ) . '</label>';
			echo '<select id="fl_slideshow_transition" name="fl_slideshow_transition">';
		foreach ( $transtions as $key => $val ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $trans, $key ) . '>' . esc_html( $val ) . '</option>';
		}
			echo '</select>';
		echo '</div>';

		echo '<div class="fl-slideshow-sec shortcode">';
			echo '<label>' . esc_html__( 'Shortcode:', 'fl-slideshow' ) . '</label>';
			echo '<span id="fl_slideshow_shortcode">' . esc_html( $shortcode ) . '</span>';
		echo '</div>';
	}


	/**
	 * Save Slideshow Changes
	 *
	 * @since 1.0
	 * @param integer $post_id Pass post ID to save changes.
	 */
	public function save_changes( $post_id ) {

		// nonce check.
		if ( ! isset( $_POST['fl_slideshow_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fl_slideshow_settings_nonce'] ) ), 'fl_slideshow_settings_metabox_nonce' ) ) {
			return $post_id;
		}

		// Save Images Ids as array.
		if ( isset( $_POST['fl_slideshow_imgs'] ) && '' !== $_POST['fl_slideshow_imgs'] ) {
			$images = explode( ',', sanitize_text_field( wp_unslash( $_POST['fl_slideshow_imgs'] ) ) );
		} else {
			$images = array();
		}

		update_post_meta( $post_id, 'fl_slideshow_imgs', $images );

		// Save Transition.
		if ( isset( $_POST['fl_slideshow_transition'] ) && '' !== $_POST['fl_slideshow_transition'] ) {
			update_post_meta( $post_id, 'fl_slideshow_transition', sanitize_text_field( wp_unslash( $_POST['fl_slideshow_transition'] ) ) );
		}

	}


	/**
	 * Add Shortcode Column to slideshow CPT
	 *
	 * @since 1.0
	 * @param array $columns Pass columns to add new column.
	 */
	public function add_columns( $columns ) {
		$columns['shortcode'] = __( 'ShortCode', 'fl-slideshow' );
		return $columns;
	}


	/**
	 * Manage Shortcode column
	 *
	 * @since 1.0
	 * @param string  $column To check if it's 'shortcode' column.
	 * @param integer $post_id Shortcode ID equal Post ID.
	 */
	public function manage_columns( $column, $post_id ) {

		if ( 'shortcode' === $column ) {
			echo '[fl-slideshow id="' . esc_attr( $post_id ) . '"]';
		}

	}


	/**
	 * Slideshow Shortcode
	 *
	 * @since 1.0
	 * @param array $atts Shortcode attributes.
	 */
	public function slideshow_shortcode( $atts ) {

		$defaults = array( 'id' => '' );
		$slidshow = shortcode_atts( $defaults, $atts );

		if ( ! empty( $slidshow['id'] ) ) {
			$imgs  = get_post_meta( $slidshow['id'], 'fl_slideshow_imgs', true );
			$trans = get_post_meta( $slidshow['id'], 'fl_slideshow_transition', true );

			if ( ! empty( $imgs ) ) {
				ob_start() ?>

				<div class="fl-slideshow owl-carousel <?php echo esc_attr( $trans ); ?>">
				<?php
				foreach ( $imgs as $img ) {
					echo wp_get_attachment_image( $img, 'full' );
				}
				?>
				</div>

				<?php
				$output = ob_get_clean();

				return $output;
			}
		}
	}

}
