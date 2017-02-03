<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Visual_Composer_Lightbox {

	/**
	 * The single instance of Visual_Composer_Lightbox.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'visual_composer_lightbox';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Visual_Composer_Lightbox_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
        
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 10 );
        
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()
    
    public function get_settings () {
        error_log('get_settings');
        return $this->settings;
    }
    
	/**
	 * Run after all plugins are loaded.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function plugins_loaded () {
        add_filter( 'vc_gitem_add_link_param', array( $this, 'vc_gitem_add_link_param' ) );
        
		add_filter( 'vc_gitem_zone_image_block_link', array( $this, 'vc_gitem_zone_image_block_link'), 10, 3 );
        
		add_filter( 'vc_gitem_post_data_get_link_link', array( $this, 'vc_gitem_post_data_get_link_link'), 10, 3 );
	} // End plugins_loaded ()
    
	/**
	 * Modify the vc-zone-link anchor for lightbox links.
	 * @access  public
	 * @since   1.0.0
	 * @return $param
	 */
	public function vc_gitem_zone_image_block_link ( $image_block, $link, $css_class ) {
        error_log('vc_gitem_zone_image_block_link');
        if ( 'lightbox' === $link ) {
            $link_attribute_value = get_option($this->settings->base . 'link_attribute_value');
            
            // Append link_attribute_value to existing classes
            $css_class .= ' ' . $link_attribute_value;

            return '<a href="{{ post_link_url }}" class="' . esc_attr( $css_class ) . '" data-rel="' . $link_attribute_value . '"' . '></a>';
        }

        return $image_block;
    }// End vc_gitem_zone_image_block_link ()
    
	/**
	 * Modify the post_title anchor for lightbox links.
	 * @access  public
	 * @since   1.0.0
	 * @return $link
	 */
	public function vc_gitem_post_data_get_link_link ( $link, $atts, $css_class = '' ) {
        error_log('vc_gitem_post_data_get_link_link');
        if ( isset( $atts['link'] ) && 'lightbox' === $atts['link'] ) {
            $link_attribute_value = get_option($this->settings->base . 'link_attribute_value');
            
            // Append link_attribute_value to existing classes
            $css_class .= ' ' . $link_attribute_value;

            $link = 'a href="{{ post_link_url }}" class="' . esc_attr( $css_class ) . '" data-rel="' . $link_attribute_value . '"';
        }
        
        return $link;
    } // End vc_gitem_post_data_get_link_link ()
    
	/**
	 * Append a Lightbox option to the Visual Composer Add link select.
	 * @access  public
	 * @since   1.0.0
	 * @return $param
	 */
	public function vc_gitem_add_link_param ( $param ) {
        $param['value'][ __( 'Lightbox', 'js_composer' ) ] = 'lightbox';

        return $param;
	} // End vc_gitem_add_link_param ()

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'visual-composer-lightbox', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'visual-composer-lightbox';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Visual_Composer_Lightbox Instance
	 *
	 * Ensures only one instance of Visual_Composer_Lightbox is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Visual_Composer_Lightbox()
	 * @return Main Visual_Composer_Lightbox instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}