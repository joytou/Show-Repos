<?php
/**
 * @package Show_Repos
 * @version 1.1.2
 */
/**
 * Plugin Name: Show Repos
 * Description: Show your repo(s) on the wordpress through a simple shortcode.
 * Version: 1.1.2
 * Requires at least: 5.0
 * Requires PHP: 5.4
 * Author: Joytou Wu
 * Author URI: http://www.xn--irr040d121a.cn/
 * License: GPL v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: show-repos
 * Domain Path: /languages
 * 
 * Show Repos is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * Show Repos is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Show Repos. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */
 
 /**
  * Please keep it false instead of true in debug mode, 
  * unless you are a developer or do you want to debug/develop this plugin.
  */
 defined( 'SHOW_REPOS_IN_DEBUG') or define( 'SHOW_REPOS_IN_DEBUG', false );
 
 /**
  * Define the infomation about this plugin.
  */
 defined( 'SHOW_REPOS_NAME' ) or define( 'SHOW_REPOS_NAME', 'Show Repos' );
 defined( 'SHOW_REPOS_VERSION' ) or define( 'SHOW_REPOS_VERSION', '1.0.0' );
 defined( 'SHOW_REPOS_MOD_PREFIX' ) or define( 'SHOW_REPOS_MOD_PREFIX', 'SHOW_REPOS_MOD_' );
 defined( 'SHOW_REPOS_SLUG' ) or define( 'SHOW_REPOS_SLUG', 'show-repos' );
 defined( 'SHOW_REPOS_SLUG_' ) or define( 'SHOW_REPOS_SLUG_', 'show_repos' );
 defined( 'SHOW_REPOS_OPTION_NAME' ) or define( 'SHOW_REPOS_OPTION_NAME', SHOW_REPOS_SLUG_ . '_options' );

 /**
  * Define the some uri of directory that running in this plugin.
  */
 defined( 'SHOW_REPOS_DIR' ) or define( 'SHOW_REPOS_DIR', plugin_dir_path( __FILE__ ) );
 defined( 'SHOW_REPOS_URL' ) or define( 'SHOW_REPOS_URL', plugin_dir_url( __FILE__ ) );
 defined( 'SHOW_REPOS_MOD_DIR' ) or define( 'SHOW_REPOS_MOD_DIR', SHOW_REPOS_DIR . 'mod/' );
 defined( 'SHOW_REPOS_MOD_URL' ) or define( 'SHOW_REPOS_MOD_URL', SHOW_REPOS_URL . 'mod/' );
 defined( 'SHOW_REPOS_TEMPLATE_DIR' ) or define( 'SHOW_REPOS_TEMPLATE_DIR', SHOW_REPOS_MOD_DIR . 'template/' );
 defined( 'SHOW_REPOS_TEMPLATE_URL' ) or define( 'SHOW_REPOS_TEMPLATE_URL', SHOW_REPOS_MOD_URL . 'template/' );

 /**
 * Class for WordPress plugin Show Repos
 * @access public
 * @author Joytou
 * @version 1.0.0
 * @license GPL v2
 */
 class SHOW_REPOS {
 	
 	const OPTION_FIELDS = [
 		[
 			'id' => 'expire_time',
 			'name' => 'Expire Time',
 			'default_value' => '600',
 			'type' => 'number',
 			'desc' => 'The time of the cache data of the repo api to live. Set to 0 to keep it always be the latest.',
 			'required' => true,
 			'attrs' => [
 			],
 		],
 	];
 	
 	/**
 	 * Storage the instance of repo class.
 	 * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
 	 */
 	static $instances = array();
 	
 	static $errorMessage = array(
 		'missing_requisite_file' => '<strong>%s</strong>: There are lack necessary file: <strong>%s</strong>',
 		'class_not_exists' => '<strong>%s</strong>: Class <strong>%s</strong> does not exists in the file <em>%s</em>',
 		'not_found_repo' => '<div>%s</div><br/><div>Error code: %d</div><br/><div>Error body: %s</div>',
 		'empty_param_in_shortcode' => '<div>Please give a value to the parameter <strong>%s</strong>, instead of leave it blank</div>',
 	);
 	
 	/**
 	 * Initialize the plugin.
 	 * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
 	 */
 	static function init() {
 		//Initialize the option datas.
 		foreach(self::OPTION_FIELDS as $option ) {
            
            //Set to the default value if not exist.
            if( !isset( get_option( SHOW_REPOS_OPTION_NAME )[$option['id']] ) ) {
                $default = get_option( SHOW_REPOS_OPTION_NAME );
                $default[$option['id']] = $option['default_value'];
                update_option( SHOW_REPOS_OPTION_NAME, $default );
            }
        }
        if( !isset( get_option( SHOW_REPOS_OPTION_NAME )['list'] ) ) {
        	$default = get_option( SHOW_REPOS_OPTION_NAME );
			$default['list'] = array();
			update_option( SHOW_REPOS_OPTION_NAME, $default );
        }
 		
 		//Deal the form submit action.
 		if( isset( $_POST[SHOW_REPOS_OPTION_NAME] ) && isset( $_POST['action'] ) ){
 			 $options = get_option( SHOW_REPOS_OPTION_NAME );
			switch( $_POST['action'] ){
 				case 'submit':
 					foreach( self::OPTION_FIELDS as $v ) {
 						$options[$v['id']] = sanitize_text_field( $_POST[SHOW_REPOS_OPTION_NAME][$v['id']] );
 					}
				case 'clear_transient':
 					foreach( $options['list'] as $k=>$v ) {
 						delete_transient( $v );
 					}
 					$options['list'] = array();
 					break;
 			}
 			update_option( SHOW_REPOS_OPTION_NAME, $options );
 		}
 		
 		//Load i18n translations.
        load_plugin_textdomain( 
            SHOW_REPOS_SLUG, 
            false, 
            dirname( plugin_basename( __FILE__ ) ) . '/languages/' 
        );
 		
 		$dir = SHOW_REPOS_MOD_DIR;
 		//Check if exists the mod directory
 		if ( !is_dir( $dir ) ) {
			self::add_settings_error(
				'myUniqueIdentifyer', 
				esc_attr( 'settings_error' ), 
				sprintf( 
					__( self::$errorMessage[ 'missing_requisite_file' ] ), 
					SHOW_REPOS_NAME, 
					$dir 
				),
				'error'
			);
 		}
 		$dir = SHOW_REPOS_TEMPLATE_DIR;
 		//Check if exists the template directory
 		if ( !is_dir( $dir ) ) {
			self::add_settings_error(
				'myUniqueIdentifyer', 
				esc_attr( 'settings_error' ), 
				sprintf( 
					__( self::$errorMessage[ 'missing_requisite_file' ] ), 
					SHOW_REPOS_NAME, 
					$dir 
				),
				'error'
			);
 		}
 		self::debug( __LINE__ );
 		$dir = SHOW_REPOS_MOD_DIR;
 		//All the repo class storage in mod directory
 		if ( $dh = opendir( SHOW_REPOS_MOD_DIR ) ) {
 			while ( ( $file = readdir( $dh ) ) !== false ){
 				self::debug( __LINE__ );
 				if( $file === '.' || $file==='..' || !is_file( $dir . $file ) ) {
 					continue;
 				}
 				self::debug( __LINE__ );
				require_once $dir . $file;
 				$class = SHOW_REPOS_MOD_PREFIX . strtoupper( str_replace( '.php', '', $file ) );
 				if( class_exists( $class ) ) {
 					self::debug( __LINE__ );
					$instance = new $class;
 					self::$instances[ $class ] = $instance;
 					self::debug( __LINE__ );
 				}else{
 					self::debug( __LINE__ );
 					self::add_settings_error(
						'myUniqueIdentifyer', 
						esc_attr( 'settings_error' ), 
						sprintf( 
							__( self::$errorMessage[ 'class_not_exists' ] ), 
							SHOW_REPOS_NAME, 
							$class,
							$dir . $file
						),
						'error'
					);
 				}
			}
			closedir( $dh );
		}
 	}
 	
 	/**
 	 * Handler for activing the plugin.
 	 * @static
 	 * @author Joytou Wu <joytou.wu@qq.com>
 	 * @since 1.1.1
 	 */
 	static function active() {
 		if( !current_user_can( 'activate_plugins' ) ) {
 			return;
 		}
 		$options = get_option( SHOW_REPOS_OPTION_NAME );
 		if( empty( $options ) ) {
 			$options = array();
 			$options['expire_time'] = '3600';
 			$options['list'] = array();
 			update_option( SHOW_REPOS_OPTION_NAME, $options );
 		}
 	}

 	/**
 	 * Handler for deactiving the plugin.
 	 * @static
 	 * @author Joytou Wu <joytou.wu@qq.com>
 	 * @since 1.1.1
 	 */
 	static function deactive() {
 		if( !current_user_can( 'activate_plugins' ) ) {
 			return;
 		}
 		$options = get_option( SHOW_REPOS_OPTION_NAME );
 		foreach( $options['list'] as $k=>$v ) {
 			delete_transient( $v );
 		}
 		$options['list'] = array();
 		update_option( SHOW_REPOS_OPTION_NAME, $options );
 	}

 	/**
 	 * Handler for uninstalling the plugin.
 	 * @static
 	 * @author Joytou Wu <joytou.wu@qq.com>
 	 * @since 1.1.1
 	 */
 	static function uninstall() {
 		if( !current_user_can( 'activate_plugins' ) || !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
 			return;
 		}
 		delete_option( SHOW_REPOS_OPTION_NAME );
 		delete_site_option( SHOW_REPOS_OPTION_NAME );
 	}
 	
 	/**
 	 * Render the shortcode show-repo.
 	 * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
 	 */
 	static function render_shortcode( $atts = [], $content = null, $tag = '' ) {
 		//Initialize the shortcode's attributes, and set the default value.
 		$atts = array_change_key_case( ( array )$atts, CASE_LOWER );
 		$atts = shortcode_atts(
 			[
 				'src' => 'github',
 				'user' => '',
 				'repo' => '',
 			], 
 			$atts, 
 			$tag
 		);
 		foreach( $atts as $k => $v ) {
 			if( empty( $v ) ) {
 				return sprintf(
 					__( self::$errorMessage[ 'empty_param_in_shortcode' ] ),
 					$k
 				);
 			}
 		}
 		//Get the rendered shortcode's attributes.
 		$classname = strtoupper( $atts[ 'src' ] );
 		$user = $atts[ 'user' ];
 		$repo = $atts[ 'repo' ];
 		/*//Get the current shortcode's class, and get the api data from the url that defined by repo class.
 		$class = self::$instances[ SHOW_REPOS_MOD_PREFIX . $classname ];
 		$api_url = str_replace( array( '{:user}', '{:repo}' ), array( $user, $repo ), $class->api_url );
 		//Simulating user normally access the site, some will return 403 forbidden with no header.
 		$response_header = array(
 			'method' => 'GET',
 			'user-agent' => $_SERVER['HTTP_USER_AGENT'],
 			'header' => array(
 				'Content-Type' => 'application/json;charset=UTF-8',
 			),
 		);*/
 		
		$repo_data = get_transient( SHOW_REPOS_SLUG_ . "_{$classname}_{$user}_{$repo}" );
 		if( $repo_data === false ){
 			//Get the current shortcode's class, and get the api data from the url that defined by repo class.
 			$class = self::$instances[ SHOW_REPOS_MOD_PREFIX . $classname ];
 			$api_url = str_replace( array( '{:user}', '{:repo}' ), array( $user, $repo ), $class->api_url );
 			//Simulating user normally access the site, some will return 403 forbidden with no header.
 			$response_header = array(
 				'method' => 'GET',
 				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
 				'header' => array(
 					'Content-Type' => 'application/json;charset=UTF-8',
 				),
 			);
 			$response = wp_remote_get( $api_url, $response_header );
 			//Check can get api data, or return error message.
 			if( wp_remote_retrieve_response_code( $response ) === 200 ) {
 				$api = json_decode( wp_remote_retrieve_body( $response ), true );
 				$options = get_option( SHOW_REPOS_OPTION_NAME );
				$current_transient_name = SHOW_REPOS_SLUG_ . "_{$classname}_{$user}_{$repo}";
 				set_transient( $current_transient_name, $api, $options['expire_time'] );
 				if( !in_array( $current_transient_name, $options['list'] ) ){
 					array_push( $options['list'], $current_transient_name );
 				}
 				update_option( SHOW_REPOS_OPTION_NAME, $options );
 			} else {
 				$api = array( 
 					'error_code' => wp_remote_retrieve_response_code( $response ),
 					'error_msg' => wp_remote_retrieve_body( $response ),
 					'error' => 'Not found the repo <strong>'. $user . '/' . $repo . '</strong> from <strong>' . $classname . '</strong>, please ensure there has datas in ' . $api_url,
 				);
 				return sprintf( 
 					__( self::$errorMessage[ 'not_found_repo' ] ),
 					$api[ 'error' ],
 					$api[ 'error_code' ],
 					$api[ 'error_msg' ]
 				);
 			}
 		}
 		return self::tmpl_render( $class->template_html, $class->data_format( $repo_data ) );
 	}
 	
 	/**
 	 * Render the shortcode's html.
 	 * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
 	 */
 	static function tmpl_render( $tmpl, $data=[] ) {
 		//Get the template file from the repo class.
 		$file = fopen( SHOW_REPOS_TEMPLATE_DIR . $tmpl, 'r' );
		$contents = fread( $file, filesize( SHOW_REPOS_TEMPLATE_DIR . $tmpl ) );
		fclose( $file );
		//Design a simple template engine. It just replace the word {{data}} if exist in the $data.
 		$ret = preg_replace_callback(
 			"/{{(.*?)}}/", 
 			function( $match ) use( $data ) {
 				return isset( $data[ $match[ 1 ] ] ) ? $data[ $match[ 1 ] ] : $match[ 1 ];
 			},
 			$contents
 		);
 		return $ret;
 	}
 	
 	/**
 	 * Load the shortcode's needed static files from classes.
 	 * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
 	 */
 	static function load_static_file() {
 		foreach ( self::$instances as $class ) {
 			self::debug( __LINE__ );
 			//Only load the css files when $template_css has been defined.
 			if( isset($class->template_css ) ) {
 				$css = $class->template_css;
 				//$template_css can be a string to load only one file, and can be an array string to load lots of files.
 				if( is_array( $css ) ) {
 					foreach( $css as $v ) {
 						wp_enqueue_style( SHOW_REPOS_SLUG . $v, SHOW_REPOS_TEMPLATE_URL . $v );
 						self::debug( __LINE__ );
 					}
 				} else {
 					wp_enqueue_style( SHOW_REPOS_SLUG . $css, SHOW_REPOS_TEMPLATE_URL . $css );
 					self::debug( __LINE__ );
 				}
 			}

 			//Only load the javascript files when $template_js has been defined.
 			if( isset( $class->template_js ) ) {
 				$js = $class->template_js;
 				//$template_js can be a string to load only one file, and can be an array string to load lots of files.
				if( is_array( $js ) ) {
 					foreach( $js as $v ) {
 						wp_enqueue_script( SHOW_REPOS_SLUG . $v, SHOW_REPOS_TEMPLATE_URL . $v );
 						self::debug( __LINE__ );
 					}
 				} else {
 					wp_enqueue_script( SHOW_REPOS_SLUG . $js, SHOW_REPOS_TEMPLATE_URL . $js );
 					self::debug( __LINE__ );
 				}
 			}
 			
		}
 	}
 	
    /**
     * Handler for adding setting page.
     * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
     */
    static function add_setting_page(){
    	add_options_page( 
    	    esc_html__( SHOW_REPOS_NAME . ' Setting', SHOW_REPOS_SLUG ), 
    	    esc_html__( SHOW_REPOS_NAME, SHOW_REPOS_SLUG ), 
    	    'manage_options', 
    	    SHOW_REPOS_SLUG, 
    	    array( SHOW_REPOS_SLUG_, 'render_setting_page' ) 
	    );
    }
    
    /**
     * Handler for rendering setting page.
     * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
     */
    static function render_setting_page(){
    	?>
    	<h2><?php esc_html_e( SHOW_REPOS_NAME, SHOW_REPOS_SLUG );?></h2>
    	<form action="?page=<?php echo SHOW_REPOS_SLUG;?>&action=submit" method="post">
    		<?php
                settings_fields( SHOW_REPOS_OPTION_NAME );
                do_settings_sections( SHOW_REPOS_SLUG_ );
    		?>
    		<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save', SHOW_REPOS_SLUG ); ?>" />
    	</form>
    	<form action="?page=<?php echo SHOW_REPOS_SLUG;?>&action=clear_transient" method="post">
    		<input name="submit" class="button button-secondly" type="submit" value="<?php esc_attr_e( 'Clear Cache', SHOW_REPOS_SLUG ); ?>" />
    	</form>
    	<?php 
    }
    
    /**
     * Handler for registing settings and adding settings fields.
     * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
     */
    static function register_settings(){
        
        $required_field_html = '<span class="required">&nbsp;*</span>';
        
        register_setting( 
            SHOW_REPOS_SLUG_, 
            SHOW_REPOS_OPTION_NAME, 
            array( SHOW_REPOS_SLUG_, 'options_validate' ) 
        );
    	
        add_settings_section( 
            'api_settings', 
            esc_html__( SHOW_REPOS_NAME . ' Setting', SHOW_REPOS_SLUG ), 
            array( SHOW_REPOS_SLUG_, 'setting_section_text' ), 
            SHOW_REPOS_SLUG_
        );

    	foreach ( self::OPTION_FIELDS as $item ) {
    	    add_settings_field(
    	        SHOW_REPOS_SLUG_ . '_' . $item['id'], 
    	        esc_html__( $item['name'] , SHOW_REPOS_SLUG ) . ( $item['required'] ? $required_field_html : '' ),
    	        array(SHOW_REPOS_SLUG_, 'settings_field' ),
    	        SHOW_REPOS_SLUG_,
    	        'api_settings',
    	        $item
	        );
    	}    	
    }
    
    /**
     * Handler for validating post options' data.
     * @static
     * @param Array $input Array for filtering and valitading.
     * @return Array
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
     */
    static function options_validate( $input ){
    	//Valitaded the fields.
    	$output = array();
    	foreach ( $input as $k => $v ) {
    	    //Protect the input datas.
    	    $output[$k] = sanitize_text_field( $v );
    	}
    	
    	return $output;
    }
    
    /**
     * Handler for rendering subtitle for setting page.
     * @static
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
     */
    static function setting_section_text(){
    	//Form second title.
        esc_html_e( SHOW_REPOS_NAME, SHOW_REPOS_SLUG );
    }
    
    /**
     * Handler for rendering setting field.
     * @static
     * @param Array $args Array for rendering the setting fields. 
     * @author Joytou Wu <joytou.wu@qq.com>
     * @since 1.0.0
     */
    static function settings_field( $args ) {
    	$key_value_binding = '';
        foreach($args['attrs'] as $k=>$v){
            $key_value_binding .= " " . esc_attr( $k ) . "=\"" . esc_attr( $v ) . "\"";
        }
        
        $options = get_option( SHOW_REPOS_OPTION_NAME );
        echo "<input id=\"" . SHOW_REPOS_SLUG_ . "_" . esc_attr( $args['id'] ) ."\" name=\"" . esc_attr( SHOW_REPOS_OPTION_NAME ) . "[". esc_attr( $args['id'] ) ."]\" type=\"". ( $args['type'] ? esc_attr( $args['type'] ) : 'text' ) ."\" value=\"" . ( isset( $options[$args['id']] ) ? esc_attr( $options[$args['id']] ) : esc_attr( $args['default_value'] ) ) . "\" aria-describedby=\"" . esc_attr( $args['id'] ) . "-description\"" . $key_value_binding . "/>";
    	
    	if(isset( $args['type'] ) && esc_attr( $args['type'] ) === 'range' ){
    	    echo "<span id=\"" . SHOW_REPOS_SLUG_ . "_" . esc_attr( $args['id'] ) ."-value\">" . ( isset( $options[$args['id']] ) ? esc_html( $options[$args['id']] ) : esc_html( $args['default_value'] ) ) . "</span>";
    	}
    	/**
    	 * It will not run as expect unless use several echo() for outputing.
    	 * Expect: <p class="description" id="{$id}-description">{$desc}</p>
    	 * The fact if use only one echo(): {$desc}<p class="description" id="{$id}-description"></p>
    	 */
    	echo "<p class=\"description\" id=\"" . esc_attr( $args['id'] ) . "-description\">";
    	echo ( $args['desc'] ? esc_html__( $args['desc'], SHOW_REPOS_SLUG ) : '' );
    	echo "</p>";
    }
 	
 	private static function debug( $line ) {
 		if( SHOW_REPOS_IN_DEBUG )
 			var_dump( sprintf( '&#x0009;<p>Line %d be asked to display</p>', $line ) );
 	}
 	
 	private static function add_settings_error( $setting, $code, $message, $type = 'error' ) {
        global $wp_settings_errors;
        
        $wp_settings_errors[] = array(
            'setting' => $setting,
            'code'	=> $code,
            'message' => $message,
            'type'	=> $type
        );
    }
 }
 
 register_activation_hook( __FILE__, array( 'SHOW_REPOS', 'activate' ) );
 register_deactivation_hook( __FILE__, array( 'SHOW_REPOS', 'deactivate' ) );
 register_uninstall_hook( __FILE__, array( 'SHOW_REPOS', 'uninstall' ) );
 add_action( 'plugins_loaded', array( 'SHOW_REPOS', 'init' ) );
 add_action( 'wp_head', array( 'SHOW_REPOS', 'load_static_file' ) );
 add_shortcode( 'show-repo', array( 'SHOW_REPOS', 'render_shortcode' ) );
 add_shortcode( 'show-repos', array( 'SHOW_REPOS', 'render_shortcode' ) );
 if ( is_admin() ) {
    add_action( 'admin_menu', array( 'SHOW_REPOS', 'add_setting_page' ) );
    add_action( 'admin_init', array( 'SHOW_REPOS', 'register_settings' ) );
 }