<?php
/**
 * Plugin Name.
 *
 * @package   Energ1zer_Admin
 * @author    Francois Oligny-Lemieux <frank.quebec@gmail.com>
 * @license   GPL-2.0+
 * @link      http://oligny.com
 * @copyright 2014 Francois Oligny-Lemieux
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-energ1zer.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Energ1zer_Admin
 * @author  Your Name <email@example.com>
 */
class Energ1zer_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() 
	{

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 * @TODO:
		 *
		 * - Rename "Energ1zer" to the name of your initial plugin class
		 *
		 */
		$plugin = Energ1zer::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );
			
		/**
	    * Adds a box to the main column on the Post and Page edit screens.
	    */
		function energ1zer_add_meta_box()
		{
			$screens = array( 'post', 'page' );
			foreach ($screens as $screen) 
			{
				add_meta_box('energ1zer_sectionid', __( 'Energ1zer Options', 'energ1zer_textdomain' ),
					'energ1zer_meta_box_callback', $screen);
			}
		}
		add_action('add_meta_boxes', 'energ1zer_add_meta_box');
		
		/**
		 * Prints the box content.
		 * 
		 * @param WP_Post $post The object for the current post/page.
		 */
		function energ1zer_meta_box_callback($post)
		{	
			// Add an nonce field so we can check for it later.
			wp_nonce_field('energ1zer_meta_box', 'energ1zer_meta_box_nonce');
		
			/*
			 * Use get_post_meta() to retrieve an existing value
			 * from the database and use the value for the form.
			 */
			$position_x = get_post_meta($post->ID, '_energ1zer_meta_widget_position_x', true );
			$position_y = get_post_meta($post->ID, '_energ1zer_meta_widget_position_y', true );
			$showtext = get_post_meta($post->ID, '_energ1zer_meta_widget_showtext', true );
			$showtext_esc = esc_attr($showtext);
			$grayscale = get_post_meta($post->ID, '_energ1zer_meta_widget_grayscale', true );
			if ($grayscale === "yes")
			{	$grayscale = 'checked="checked"';
			}
			else
			{	$grayscale = "";
			}
		
			echo '<label for="energ1zer_widget_position_x">';
			_e('Widget relative position (x,y)', 'energ1zer_textdomain');
			echo '</label> ';
			echo '<input type="text" id="energ1zer_widget_position_x" name="energ1zer_widget_position_x" value="' . esc_attr( $position_x ) . '" size="5" />';
			echo '<input type="text" id="energ1zer_widget_position_y" name="energ1zer_widget_position_y" value="' . esc_attr( $position_y ) . '" size="5" />';
			echo <<<EOF
<br/>
<label for="energ1zer_widget_showtext">
Show text on hover 
</label>
<input type="text" id="energ1zer_widget_showtext" name="energ1zer_widget_showtext" value="$showtext_esc" size="15" />
<br/>
<label for="energ1zer_widget_grayscale">
Grayscale widget
</label>
<input type="checkbox" id="energ1zer_widget_grayscale" name="energ1zer_widget_grayscale" $grayscale />
EOF;
		}
		
		/**
		 * When the post is saved, saves our custom data.
		 *
		 * @param int $post_id The ID of the post being saved.
		 */
		function energ1zer_save_meta_box_data($post_id)
		{	/*
			 * We need to verify this came from our screen and with proper authorization,
			 * because the save_post action can be triggered at other times.
			 */
			// Check if our nonce is set.
			if ( ! isset( $_POST['energ1zer_meta_box_nonce'] ) )
			{	return;
			}
		
			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['energ1zer_meta_box_nonce'], 'energ1zer_meta_box' ) ) 
			{	return;
			}
		
			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			{	return;
			}
		
			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] )
			{
				if ( ! current_user_can( 'edit_page', $post_id ) ) 
				{	return;
				}
			} 
			else
			{
				if ( ! current_user_can( 'edit_post', $post_id ) ) 
				{	return;
				}
			}
		
			/* OK, it's safe for us to save the data now. */
			
			// Make sure that it is set.
			if ( ! isset( $_POST['energ1zer_widget_position_x'] ) )
			{	return;
			}
		
			// Sanitize user input.
			$position_x = sanitize_text_field( $_POST['energ1zer_widget_position_x'] );
			$position_y = sanitize_text_field( $_POST['energ1zer_widget_position_y'] );
			$showtext = sanitize_text_field( $_POST['energ1zer_widget_showtext'] );
			$grayscale = "";
			if (isset($_POST['energ1zer_widget_grayscale']))
			{	$grayscale = "yes";
			}
		
			// Update the meta field in the database.
			update_post_meta($post_id, '_energ1zer_meta_widget_position_x', $position_x );
			update_post_meta($post_id, '_energ1zer_meta_widget_position_y', $position_y );
			update_post_meta($post_id, '_energ1zer_meta_widget_showtext', $showtext );
			update_post_meta($post_id, '_energ1zer_meta_widget_grayscale', $grayscale );
		}
		add_action('save_post', 'energ1zer_save_meta_box_data');
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Energ1zer" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Energ1zer::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Energ1zer" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Energ1zer::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Page Title', $this->plugin_slug ),
			__( 'Menu Text', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
