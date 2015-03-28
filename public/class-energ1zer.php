<?php
/**
 * Plugin Name.
 *
 * @package   Energ1zer
 * @author    Francois Oligny-Lemieux <frank.quebec@gmail.com>
 * @license   GPL-2.0+
 * @link      http://oligny.com
 * @copyright 2014 Francois Oligny-Lemieux
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-energ1zer-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Energ1zer
 * @author  Your Name <email@example.com>
 */
class Energ1zer
{
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @since   1.0.0
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "energ1zer" to the name your your plugin
	 * Unique identifier for your plugin.
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'energ1zer';

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @since     1.0.0
	 */
	private function __construct()
	{
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
	
		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );
			
		// TODO write post about this	
		// This function will remove automatic addition of <br/> and <p> </p> by wordpress on pages
		function the_content_handler($content)
		{
			global $post;
			if ($post->post_type === "page")
			{
				// prevent wordpress from inserting <p> </p> 
				// this is executed only on request !!!! For example on a precise element-rich page				
				remove_filter ('the_content',  'wpautop');
				remove_filter ('comment_text', 'wpautop');
			}
	        return $content;
		}
		add_filter('the_content', 'the_content_handler'); 
			
		function namespace_add_custom_types( $query )
		{
			if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) 
			{	$query->set( 'post_type', array( 'post', 'nav_menu_item', 'page' ));
				return $query;			
			}
		}
		add_filter( 'pre_get_posts', 'namespace_add_custom_types' );// - See more at: http://dineshkarki.com.np/forums/topic/causes-main-menu-to-disappear#sthash.maCYBMXv.dpuf
		
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) 
		{			
			function energ1zer_modify_gallery_filter($content, $post_id, $size, $permalink)
			{
	 			if (! $permalink) 
				{					
					$image = wp_get_attachment_image_src($post_id, 'large');
					$new_content = preg_replace('/href=\'(.*?)\'/', 'href=\'' . $image[0] . '\'', $content );
					return $new_content;
				}
				else
				{	return $content;
				}
			}
			add_filter('wp_get_attachment_link', 'energ1zer_modify_gallery_filter', 10, 4);
			
			/**
			 * Get one image from a specified post in the following order:
			 * Featured Image, first attached image, first image from the_content HTML
			 * @param int $id, The post ID to check
			 * @param string $size, The image size to return
			 */
			function get_article_image($id, $size = 'home-post') 
			{
				$thumb = '';
	
				if ( '' != get_the_post_thumbnail( $id ) ) 
				{	$thumb = get_the_post_thumbnail( $id, $size, array( 'title' => esc_attr( strip_tags( get_the_title() ) ) ) );
				}
				else
				{
					$args = array(
								'post_type'		 => 'attachment',
								'fields'    	 => 'ids',
								'numberposts'	 => 1,
								'post_status'	 => null,
								'post_mime_type' => 'image',
								'post_parent'	 => $id,
							);
			
					$first_attachment = get_posts( $args );	
					if ( $first_attachment )
					{	
						/* Get the first image attachment */
						foreach ( $first_attachment as $attachment ) 
						{	$thumb = wp_get_attachment_image( $attachment, $size, false, array( 'title' => esc_attr( strip_tags( get_the_title() ) ) ) );
						}
					}
					else if ( class_exists( 'Jetpack_PostImages' ) )
					{	
						/* Get the first image directly from HTML content */
						$getimage = new Jetpack_PostImages();
						$image = $getimage->from_html( $id );	
						if ( $image )
						{	$thumb = '<img src="' . $image[0]['src'] . '" title="' . esc_attr( strip_tags( get_the_title() ) ) . '" class="attachment-' . $size . ' wp-post-image" />';
						}
					}
				}
	
				return $thumb;
			}
	
			//[shortcode] generic
			function energ1zer_generic_shortcode_func($atts)
			{
				$string = "";
				$additionalStyles = "";
				
				if (isset($atts["marginbottom"])) 
				{	$additionalStyles .= "margin-bottom:".$atts["marginbottom"];
					if (substr($additionalStyles,-2)!=="px") 
					{	$additionalStyles .= "px";
					}
				}				
				if (isset($atts["margin"])) 
				{	$additionalStyles .= " margin:".$atts["margin"].";"; // FRANK FIXME SANITIZE INPUT
				}
				if (isset($atts["break"])) 
				{
					$additionalStyles .= "clear:both;";
				}
				
				if (isset($atts["spacer"]))
				{
					$height = $atts["spacer"] . "px";
					if (strstr($height,"px")===FALSE) 
					{	$height .= "px";
					}
					$string .= "<div style=\"line-height:$height; height:$height; $additionalStyles\"></div>";
				}
								
				if (isset($atts["liner"]))
				{					
					if (isset($atts["liner"]) && strlen($atts["liner"])>0 ) 
					{	$height = $atts["liner"];
						if (strstr($height,"px")===FALSE) 
						{	$height .= "px";
						}
						$additionalStyles .= "line-height:$height; height:$height; ";
					}
										
					if (isset($atts["color"])) 
					{	
						$color = $atts["color"];
						$additionalStyles .= "background-color:$color; ";
					}
					
					if (isset($atts["width"])) 
					{	$width = $atts["width"];
						if (strstr($width,"px")===FALSE) 
						{	$width .= "px";
						}
						$additionalStyles .= "width:$width; ";
					}
					
					// liner only
					$string .= "<div class=\"energ1zer liner\" style=\"clear:both; $additionalStyles\"></div>";
				}
				
				return $string;
			}
			
			//[shortcode] div
			function energ1zer_div_shortcode_func($atts, $content = null)
			{
				$string = "";
				
				if (isset($atts["float"]) && $atts["float"] == "remainingLeft")
				{
				}
				
				return $string;
			}
			
			//[shortcode] bubble
			function energ1zer_bubble_shortcode_func($atts)
			{
				$string = "";
				$additionalStyles = "";
				
				add_action('wp_enqueue_scripts', 'energ1zer_add_stylesheet');
				if (isset($atts["postid"]))
				{
					$post_id = sanitize_text_field($atts["postid"]);
					$post_image = get_article_image($post_id, "thumbnail");
					$position_x = get_post_meta($post_id, '_energ1zer_meta_widget_position_x', true);
					$position_y = get_post_meta($post_id, '_energ1zer_meta_widget_position_y', true);
					$showtext = get_post_meta($post_id, '_energ1zer_meta_widget_showtext', true);
					$grayscale = get_post_meta($post_id, '_energ1zer_meta_widget_grayscale', true);
					
					$width = "100";
					$height = "100";
					$success = preg_match("/width=\"(\d+)\"/", $post_image, $matches);
					if ($success) 
					{	$width = $matches[1];
					}
					$success = preg_match("/height=\"(\d+)\"/", $post_image, $matches);
					if ($success) 
					{	$height = $matches[1];
					}
					
					$bubble_classes = "";
					$showtext_html = "";
					if (strlen($showtext) >= 1)
					{	$bubble_classes .= "showtext ";
						$showtext_html = <<<EOF
<div class="showtext" style="width:{$width}px; height:{$height}px; line-height:{$height}px;">$showtext</div>
EOF;
					}
					
					if ($grayscale === "yes") 
					{	$bubble_classes .= "grayscale ";
					}
					
					if (isset($atts["float"]))
					{
						if ($atts["float"] === "left") 
						{	$bubble_classes .= "floatLeft ";
						}
						else if ($atts["float"] === "right") 
						{	$bubble_classes .= "floatRight ";
						}
						else
						{	$bubble_classes .= "positionAbsolute ";
						}
					}	
					else
					{	$bubble_classes .= "positionAbsolute ";
					}
					
					
					if (isset($atts["margin"])) 
					{	$additionalStyles .= " margin:".$atts["margin"].";"; // FRANK FIXME SANITIZE INPUT
					}
					
					$permalink = get_permalink($post_id);
					$title = esc_attr(get_the_title($post_id));
					$string .= <<<EOF
					<div class="energ1zer bubble $bubble_classes" style="left:{$position_x}px; top:{$position_y}px; width:{$width}px; height:{$height}px; $additionalStyles"><a href="$permalink" title="$title"><div class="image">{$post_image}</div>{$showtext_html}</a></div>
EOF;
				}
				else
				{
					$string .= "NO POST ID";
				}
				return $string;
			}

			function energ1zer_br_shortcode_func($atts)
			{
				return "<br/>\n";
			}
						
			add_shortcode('br', 'energ1zer_br_shortcode_func');
			add_shortcode('energ1zer_bubble', 'energ1zer_bubble_shortcode_func');
			add_shortcode('energ1zer-bubble', 'energ1zer_bubble_shortcode_func');
			add_shortcode('energ1zer_div', 'energ1zer_div_shortcode_func');
			add_shortcode('energ1zer-div', 'energ1zer_div_shortcode_func');
			add_shortcode('energ1zer', 'energ1zer_generic_shortcode_func');
		}

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() 
	{
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() 
	{
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance )
		{
			self::$instance = new self;
		}
				
		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide )
	{
		if ( function_exists( 'is_multisite' ) && is_multisite() ) 
		{
			if ( $network_wide  )
			{
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id )
				{
					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			}
			else
			{
				self::single_activate();
			}
		} 
		else
		{
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide )
	{
		if ( function_exists( 'is_multisite' ) && is_multisite() )
		{
			if ( $network_wide ) 
			{
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) 
				{
					switch_to_blog( $blog_id );
					self::single_deactivate();
				}

				restore_current_blog();
			}
			else
			{
				self::single_deactivate();
			}
		} 
		else
		{
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) 
	{
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) 
		{	return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids()
	{
		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() 
	{
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() 
	{
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() 
	{
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
	{
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() 
	{
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() 
	{
		// @TODO: Define your filter hook callback here
	}

}
