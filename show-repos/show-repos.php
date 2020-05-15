<?php
/**
 * @package Show_Repos
 * @version 1.0.0
 */
/**
 * Plugin Name: Show Repos
 * Description: Show your repo(s) on the wordpress through a simple shortcode.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 5.4
 * Author: Joytou Wu
 * Author URI: http://www.xn--irr040d121a.cn/
 * License: GPL v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: show-repos
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
 		$dir = SHOW_REPOS_MOD_DIR;
 		//Check if exists the mod directory
 		if ( !is_dir( $dir ) ) {
			self::add_settings_error(
				'myUniqueIdentifyer', 
				esc_attr( 'settings_error' ), 
				sprintf( 
					self::$errorMessage[ 'missing_requisite_file' ], 
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
					self::$errorMessage[ 'missing_requisite_file' ], 
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
							self::$errorMessage[ 'class_not_exists' ], 
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
 					self::$errorMessage[ 'empty_param_in_shortcode' ],
 					$k
 				);
 			}
 		}
 		//Get the rendered shortcode's attributes.
 		$classname = strtoupper( $atts[ 'src' ] );
 		$user = $atts[ 'user' ];
 		$repo = $atts[ 'repo' ];
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
 		 	return self::tmpl_render( $class->template_html, $class->data_format( $api ) );
 		} else {
 			$api = array( 
 				'error_code' => wp_remote_retrieve_response_code( $response ),
 				'error_msg' => wp_remote_retrieve_body( $response ),
 				'error' => 'Not found the repo <strong>'. $user . '/' . $repo . '</strong> from <strong>' . $classname . '</strong>, please ensure there has datas in ' . $api_url,
 				
 			);
 			return sprintf( 
 				self::$errorMessage[ 'not_found_repo' ],
 				$api[ 'error' ],
 				$api[ 'error_code' ],
 				$api[ 'error_msg' ]
 			);
 		}
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
 						wp_enqueue_style( 'show-repos' . $v, SHOW_REPOS_TEMPLATE_URL . $v );
 						self::debug( __LINE__ );
 					}
 				} else {
 					wp_enqueue_style( 'show-repos' . $css, SHOW_REPOS_TEMPLATE_URL . $css );
 					self::debug( __LINE__ );
 				}
 			}

 			//Only load the javascript files when $template_js has been defined.
 			if( isset( $class->template_js ) ) {
 				$js = $class->template_js;
 				//$template_js can be a string to load only one file, and can be an array string to load lots of files.
				if( is_array( $js ) ) {
 					foreach( $js as $v ) {
 						wp_enqueue_script( 'show-repos' . $v, SHOW_REPOS_TEMPLATE_URL . $v );
 						self::debug( __LINE__ );
 					}
 				} else {
 					wp_enqueue_script( 'show-repos' . $js, SHOW_REPOS_TEMPLATE_URL . $js );
 					self::debug( __LINE__ );
 				}
 			}
 			
		}
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
 
 add_action( 'plugins_loaded', array( 'SHOW_REPOS', 'init' ) );
 add_action( 'wp_head', array( 'SHOW_REPOS', 'load_static_file' ) );
 add_shortcode( 'show-repo', array( 'SHOW_REPOS', 'render_shortcode' ) );
 add_shortcode( 'show-repos', array( 'SHOW_REPOS', 'render_shortcode' ) );