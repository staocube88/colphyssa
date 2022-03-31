<?php
if ( ! class_exists( 'Adifier_Options' ) ) {

    class Adifier_Options {

        protected $_config;
        protected $_is_loaded = false;
        protected $_sections = false;
        protected $_defaults = array();
    
        protected $_opt_name = 'adifier_options';

        public static $_instance;

        public function __construct() {

            self::$_instance = $this;
            $this->_load_sections();
        }

        public function init()
        {
            $this->_set_defaults();
    
            if ( class_exists( 'Redux' ) )
            {
                $this->_load_extensions();

                $this->_set_args();
                $this->_set_sections();
                $this->_is_loaded = true;
            }
        }

        protected function _set_sections()
        {
            if( !empty( $this->_sections ) )
            {
                foreach( $this->_sections as $section )
                {
                    \Redux::set_section( $this->_opt_name, $section );
                }
            }
        }

        protected function _load_extensions()
        {
            Redux::set_extensions( $this->_opt_name, plugin_dir_path( __DIR__  ).'redux-extension' );
        }
    

        protected function get_option( $key )
        {
            if( $this->_is_loaded )
            {
                $value = \Redux::get_option( $this->_opt_name, $key );
            }
    
            if( empty( $value ) )
            {
                $value = $this->_defaults[ $key ] ?? '';
            }
    
            return $value;
        }
    
        public static function get( $key )
        {
            $obj = self::$_instance;
            return $obj->get_option( $key );
        }
    
        protected function _set_defaults()
        {
            $fields = array_column( $this->_sections, 'fields' );
            $fields = call_user_func_array('array_merge', $fields);
            foreach( $fields as $field )
            {
                $this->_defaults[ $field['id'] ] = $fields['default'] ?? '';
            }
        }
    
        protected function _set_args()
        {        
            $theme = wp_get_theme();
            $args = 
            [
                'display_name'         => $theme->get( 'Name' ),
                'display_version'      => $theme->get( 'Version' ),
                'menu_title'           => esc_html__( 'Adifier WP', 'recipifier' ),
                'page_title'           => esc_html__( 'Adifier WP', 'recipifier' ),
                'customizer'           => true,
                'show_options_object'  => false,
                'dev_mode'  => false,
            ];
        
            \Redux::set_args( $this->_opt_name, $args );
        }   

        protected function _load_sections()
        {
            /**********************************************************************
            ***********************************************************************
            OVERALL
            **********************************************************************/
            $this->_sections[] = array(
                'title'     => esc_html__('Overall', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('This is basic section where you can set up main settings for your website.', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'header_style',
                        'type'      => 'select',
                        'title'     => esc_html__('Header Style', 'adifier') ,
                        'options'   => array(
                            'header-1'      => esc_html__( 'Inline Navigation', 'adifier' ),
                            'header-2'      => esc_html__( 'Bottom Navigation', 'adifier' ),
                            'header-3'      => esc_html__( 'Transparent Navigation', 'adifier' ),
                            'header-4'      => esc_html__( 'Side Categories', 'adifier' ),
                            'header-5'      => esc_html__( 'Full Dark', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select style of the header. Logo selection is under Appearance tab', 'adifier'),
                        'default'   => 'header-1'
                    ),
                    array(
                        'id'        => 'header_banner',
                        'type'      => 'editor',
                        'title'     => esc_html__('Header Banner', 'adifier') ,
                        'desc'      => esc_html__('Input header banner code here.', 'adifier'),
                        'options'   => array(
                            'tiny'  => false
                        ),
                        'required'  => array( 'header_style', '=', 'header-2' )
                    ),
                    array(
                        'id'        => 'header_4_cats',
                        'type'      => 'taxonomy_ajax',
                        'taxonomy'  => 'advert-category',
                        'multi'     => true,
                        'sortable'  => true,
                        'title'     => esc_html__('Header Categories', 'adifier') ,
                        'desc'      => esc_html__('Select which categories to display on leave empty for main categories only.', 'adifier'),
                        'required'  => array( 'header_style', '=', 'header-4' )
                    ),  
                    array(
                        'id'        => 'header_4_cats_opened',
                        'type'      => 'select',
                        'options'   => array(
                            'yes' => esc_html__( 'Yes', 'adifier' ),
                            'no'  => esc_html__( 'No', 'adifier' ),
                        ),
                        'title'     => esc_html__('Header Categories Opened On Front', 'adifier') ,
                        'desc'      => esc_html__('Select yes if you want for cats to be opened on home page by default.', 'adifier'),
                        'required'  => array( 'header_style', '=', 'header-4' ),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'enable_sticky',
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' )
                        ),
                        'title'     => esc_html__('Sticky Menu', 'adifier'),
                        'desc'      => esc_html__('Enable or disable sticky menu', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id' => 'direction',
                        'type' => 'select',
                        'options' => array(
                            'ltr' => esc_html__('LTR', 'adifier'),
                            'rtl' => esc_html__('RTL', 'adifier')
                        ),
                        'title' => esc_html__('Choose Site Content Direction', 'adifier'),
                        'desc' => esc_html__('Choose overall website text direction which can be RTL (right to left) or LTR (left to right).', 'adifier'),
                        'default' => 'ltr'
                    ),
                    array(
                        'id'        => 'custom_css',
                        'type'      => 'ace_editor',
                        'mode'      => 'css',
                        'title'     => esc_html__('Custom CSS', 'adifier'),
                        'desc'      => esc_html__('Here you can add custom CSS.', 'adifier'),
                    ),
                    array(
                        'id'        => 'page_custom_sidebars',
                        'type'      => 'text',
                        'title'     => esc_html__('Custom Sidebars', 'adifier'),
                        'desc'      => esc_html__('Set number of custom sidebars you need.', 'adifier'),
                        'default'   => '2'
                    ),
                    array(
                        'id'        => 'google_analytics',
                        'type'      => 'textarea',
                        'mode'      => 'css',
                        'title'     => esc_html__('Google Analytics', 'adifier'),
                        'desc'      => esc_html__('Input your code for google analytics.', 'adifier'),
                    ),
                    array(
                        'id'        => 'google_adsense',
                        'type'      => 'textarea',
                        'mode'      => 'css',
                        'title'     => esc_html__('Google AdSense', 'adifier'),
                        'desc'      => esc_html__('Input your adsense code here in order to have ads after the headeron inner pages.', 'adifier'),
                    ),
                    array(
                        'id'        => 'gdpr_text',
                        'type'      => 'textarea',
                        'title'     => esc_html__('GDPR', 'adifier'),
                        'desc'      => esc_html__('Input GDPR text which will be shown in every form ( registration/contact/subscribe ).', 'adifier'),
                    ),
                    array(
                        'id'        => 'register_terms',
                        'title'     => esc_html__( 'Register Terms', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input link to terms & conditions for registering', 'adifier'),
                    ),
                    array(
                        'id'        => 'password_length',
                        'title'     => esc_html__( 'Password Length', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input required number of characters for password', 'adifier'),
                        'default'   => 6
                    ),
                    array(
                        'id'        => 'password_strength_test',
                        'title'     => esc_html__( 'Password Strength Enable', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes' => esc_html__( 'Yes', 'adifier' ),
                            'no' => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable password strength', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'max_login_attempts',
                        'title'     => esc_html__( 'Max Login Attempts', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input how many times user can try to login or leave emnpty to disable', 'adifier'),
                    ),
                    array(
                        'id'        => 'login_attempt_block_time',
                        'title'     => esc_html__( 'Login Lock Time', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input how many hours user will be blocked from login', 'adifier'),
                    ),
                )
            );

            /**********************************************************************
            ***********************************************************************
            SLUGS
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Slugs', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Only numbers and letters and - and _. Must not contain any spaces. After the change you must go to Settings -> Permalinks and click Save', 'adifier'),
                'fields'    => array(  
                    array(
                        'id'        => 'trans_advert',
                        'type'      => 'text',
                        'title'     => esc_html__('Ads Slug', 'adifier') ,
                        'desc'      => esc_html__('Input slug you want to use for the ad single page.', 'adifier'),
                        'default'   => 'advert'
                    ),
                    array(
                        'id'        => 'trans_advert-category',
                        'type'      => 'text',
                        'title'     => esc_html__('Ad Category Slug', 'adifier') ,
                        'desc'      => esc_html__('Input slug you want to use for the ad categories.', 'adifier'),
                        'default'   => 'advert-category'
                    ),
                    array(
                        'id'        => 'trans_advert-location',
                        'type'      => 'text',
                        'title'     => esc_html__('Ad Location Slug', 'adifier') ,
                        'desc'      => esc_html__('Input slug you want to use for the ad locations.', 'adifier'),
                        'default'   => 'advert-location'
                    )
                )
            ); 


            /**********************************************************************
            ***********************************************************************
            SLUGS
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Images', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Control image sizes. After the changes you must regenerate thumbnails. Regenerate Thumbnails plugin will help you with this. More on function which is being used https://developer.wordpress.org/reference/functions/add_image_size/', 'adifier'),
                'fields'    => array(  
                    array(
                        'id'        => 'grid_width',
                        'type'      => 'text',
                        'title'     => esc_html__('Adifier Grid -  Width', 'adifier') ,
                        'desc'      => esc_html__('Width for images in grid layout - number only', 'adifier'),
                        'default'   => '355'
                    ),
                    array(
                        'id'        => 'grid_height',
                        'type'      => 'text',
                        'title'     => esc_html__('Adifier Grid -  Height', 'adifier') ,
                        'desc'      => esc_html__('Height for images in grid layout - number only', 'adifier'),
                        'default'   => '250'
                    ),
                    array(
                        'id'        => 'grid_crop',
                        'type'      => 'select',
                        'options'   => array(
                            true      => esc_html__('Yes', 'adifier'),
                            false     => esc_html__('No', 'adifier'),
                        ),
                        'title'     => esc_html__('Adifier Grid -  Crop', 'adifier') ,
                        'desc'      => esc_html__('Whether to crop images to specified width and height or resize', 'adifier'),
                        'default'   => true
                    ),
                    array(
                        'id'        => 'list_width',
                        'type'      => 'text',
                        'title'     => esc_html__('Adifier List -  Width', 'adifier') ,
                        'desc'      => esc_html__('Width for images in list layout - number only', 'adifier'),
                        'default'   => '355'
                    ),
                    array(
                        'id'        => 'list_height',
                        'type'      => 'text',
                        'title'     => esc_html__('Adifier List -  Height', 'adifier') ,
                        'desc'      => esc_html__('Height for images in list layout - number only', 'adifier'),
                        'default'   => '400'
                    ),
                    array(
                        'id'        => 'list_crop',
                        'type'      => 'select',
                        'options'   => array(
                            true      => esc_html__('Yes', 'adifier'),
                            false     => esc_html__('No', 'adifier'),
                        ),
                        'title'     => esc_html__('Adifier List -  Crop', 'adifier') ,
                        'desc'      => esc_html__('Whether to crop images to specified width and height or resize', 'adifier'),
                        'default'   => true
                    ),
                    array(
                        'id'        => 'single-slider_width',
                        'type'      => 'text',
                        'title'     => esc_html__('Adifier Single Slider -  Width', 'adifier') ,
                        'desc'      => esc_html__('Width for images in list layout - number only', 'adifier'),
                        'default'   => '750'
                    ),
                    array(
                        'id'        => 'single-slider_height',
                        'type'      => 'text',
                        'title'     => esc_html__('Adifier Single Slider -  Height', 'adifier') ,
                        'desc'      => esc_html__('Height for images in list layout - number only', 'adifier'),
                        'default'   => '450'
                    ),
                    array(
                        'id'        => 'single-slider_crop',
                        'type'      => 'select',
                        'options'   => array(
                            true      => esc_html__('Yes', 'adifier'),
                            false     => esc_html__('No', 'adifier'),
                        ),
                        'title'     => esc_html__('Adifier Single Slider -  Crop', 'adifier') ,
                        'desc'      => esc_html__('Whether to crop images to specified width and height or resize', 'adifier'),
                        'default'   => true
                    ),
                )
            ); 
                

            /**********************************************************************
            ***********************************************************************
            SHARE
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Share', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Post share options.', 'adifier'),
                'fields'    => array(
                    // Enable Share
                    array(
                        'id'        => 'enable_share',
                        'type'      => 'select',
                        'title'     => esc_html__('Enable Share', 'adifier') ,
                        'desc'      => esc_html__('Enable or disable post share.', 'adifier'),
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'default'   => 'yes'
                    ),
                )
            );  

            /**********************************************************************
            ***********************************************************************
            SOCIAL LOGIN
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Social Login', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Social login options.', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'info_facebook',
                        'type'      => 'info',
                        'title'     => esc_html__('Facebook', 'adifier') ,
                        'desc'      => esc_html__('Register your application on https://developers.facebook.com/apps/', 'adifier').'<br/>'.sprintf( esc_html__( 'Callback URL: %sindex.php?adifier-callback=facebook', 'adifier' ), home_url('/') ),
                    ),
                    array(
                        'id'        => 'facebook_app_id',
                        'type'      => 'text',
                        'title'     => esc_html__('Facebook App ID', 'adifier') ,
                        'desc'      => esc_html__('Input facebook application ID.', 'adifier'),
                    ),
                    array(
                        'id'        => 'facebook_app_secret',
                        'type'      => 'text',
                        'title'     => esc_html__('Facebook App Secret', 'adifier') ,
                        'desc'      => esc_html__('Input facebook application secret.', 'adifier'),
                    ),
                    array(
                        'id'        => 'info_twitter',
                        'type'      => 'info',
                        'title'     => esc_html__('Twitter', 'adifier') ,
                        'desc'      => esc_html__('Register your application on https://apps.twitter.com/', 'adifier').'<br/>'.sprintf( esc_html__( 'Callback URL: %s', 'adifier' ), home_url('/index.php') ),
                    ),
                    array(
                        'id'        => 'twitter_app_id',
                        'type'      => 'text',
                        'title'     => esc_html__('Twitter Consumer Key (API Key)', 'adifier') ,
                        'desc'      => esc_html__('Input twitter application ID.', 'adifier'),
                    ),
                    array(
                        'id'        => 'twitter_app_secret',
                        'type'      => 'text',
                        'title'     => esc_html__('Twitter Consumer Secret (API Secret)', 'adifier') ,
                        'desc'      => esc_html__('Input twitter application secret.', 'adifier'),
                    ),
                    array(
                        'id'        => 'info_google',
                        'type'      => 'info',
                        'title'     => esc_html__('Google', 'adifier') ,
                        'desc'      => esc_html__('Register your application on https://console.developers.google.com/project', 'adifier').'<br/>'.esc_html__( 'Application Type: Web Application', 'adifier' ).'<br/>'.esc_html__( 'Authorized JavaScript origins: <YOUR SITE DOMAIN>', 'adifier' ).'<br/>'.sprintf( esc_html__( 'Callback URL: %sindex.php?adifier-callback=google', 'adifier' ), home_url('/') ),
                    ),
                    array(
                        'id'        => 'google_app_id',
                        'type'      => 'text',
                        'title'     => esc_html__('Google Client ID', 'adifier') ,
                        'desc'      => esc_html__('Input google client ID.', 'adifier'),
                    ),
                    array(
                        'id'        => 'google_app_secret',
                        'type'      => 'text',
                        'title'     => esc_html__('Google Client Secret', 'adifier') ,
                        'desc'      => esc_html__('Input google client secret.', 'adifier'),
                    ),                  
                )
            ); 

            /**********************************************************************
            ***********************************************************************
            ADVERTS
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Ads', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Ads settings.', 'adifier'),
                'fields'    => array(                   
                    array(
                        'id'        => 'map_source',
                        'title'     => esc_html__( 'Map Source', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'google'    => esc_html__( 'Google Map', 'adifier' ),
                            'mapbox'    => esc_html__( 'Mapbox', 'adifier' ),
                            'osm'       => esc_html__( 'Open Street Map', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select which maps you wish to use', 'adifier'),
                        'default'   => 'google'
                    ),   
                    array(
                        'id'        => 'google_api_key',
                        'type'      => 'text',
                        'title'     => esc_html__('Map API Key', 'adifier') ,
                        'desc'      => esc_html__('Input API key of your selected map source', 'adifier'),
                        'required'  => array( 
                            array('map_source','=',array('google', 'mapbox')),
                        ),						
                    ),
                    array(
                        'id'        => 'ad_types',
                        'title'     => esc_html__( 'Ad Types', 'adifier' ),
                        'type'      => 'checkbox',
                        'options'   => array(
                            '1' => esc_html__( 'Sell', 'adifier' ),
                            '2' => esc_html__( 'Auction', 'adifier' ),
                            '3' => esc_html__( 'Buy', 'adifier' ),
                            '4' => esc_html__( 'Exchange', 'adifier' ),
                            '5' => esc_html__( 'Gift', 'adifier' ),
                            '6' => esc_html__( 'Rent', 'adifier' ),
                            '7' => esc_html__( 'Job - Offer', 'adifier' ),
                            '8' => esc_html__( 'Job - Wanted', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select which ad types are alowed to be submitted. If none is selected all will be available', 'adifier'),
                    ),
                    array(
                        'id'        => 'rent_periods',
                        'title'     => esc_html__( 'Rent Periods', 'adifier' ),
                        'type'      => 'checkbox',
                        'options'   => adifier_get_rent_periods(),
                        'default'   => array(
                            '5' => 1,
                            '7' => 1,
                            '6' => 1,
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1
                        ),
                        'desc'      => esc_html__('Select which rent periods are available', 'adifier'),
                    ),                    
                    array(
                        'id'        => 'mandatory_fields',
                        'title'     => esc_html__( 'Mandatory Fields', 'adifier' ),
                        'type'      => 'checkbox',
                        'options'   => array(
                            'phone'         => esc_html__( 'Phone', 'adifier' ),
                            'first_name'    => esc_html__( 'First Name', 'adifier' ),
                            'last_name'     => esc_html__( 'Last Name', 'adifier' ),
                        ),
                        'default'   => array(
                            'phone'         => 1,
                            'first_name'    => 1,
                            'last_name'     => 0
                        ),
                        'desc'      => esc_html__('Select which fields are mandatory', 'adifier'),
                    ),               
                    array(
                        'id'        => 'bidding_step',
                        'title'     => esc_html__( 'Bidding Step', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input value for bidding step', 'adifier'),
                        'default'   => 5
                    ),                    
                    array(
                        'id'        => 'max_top_ads',
                        'title'     => esc_html__( 'Maximum Top Ads', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input how many top ads places are available or leave empty for unlimited', 'adifier'),
                        'default'   => '5'
                    ),
                    array(
                        'id'        => 'similar_ads',
                        'title'     => esc_html__( 'Similar Ads', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number of hom many similar ads to display on ad single page or leave empty to disable', 'adifier'),
                    ),                    
                    array(
                        'id'        => 'max_homemap_ads',
                        'title'     => esc_html__( 'Maximum Home Map Ads', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input how many home map ads pomotion places are available', 'adifier'),
                        'default'   => '100'
                    ),
                    array(
                        'id'        => 'adverts_per_page',
                        'title'     => esc_html__( 'Ads Per Page', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number of ads to show per page on search results', 'adifier'),
                        'default'   => '9'
                    ),
                    array(
                        'id'        => 'adverts_per_page_author',
                        'title'     => esc_html__( 'Ads Per Page Author', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number of ads to show per page on author listing page', 'adifier'),
                        'default'   => '8'
                    ),
                    array(
                        'id'        => 'video_thumbnail',
                        'title'     => esc_html__( 'Video Thumbnail', 'adifier' ),
                        'type'      => 'media',
                        'desc'      => esc_html__('Select image for video thumbnail on ad single page (150px x 150px)', 'adifier'),
                    ),
                    array(
                        'id'        => 'placeholder_thumbnail',
                        'title'     => esc_html__( 'Placeholder Image', 'adifier' ),
                        'type'      => 'media',
                        'desc'      => esc_html__('Select iamge which will be used if ad does not have images', 'adifier'),
                    ),
                    array(
                        'id'        => 'map_style',
                        'title'     => esc_html__( 'Map Style', 'adifier' ) ,
                        'type'      => 'textarea',
                        'desc'      => esc_html__( 'Input map style array which you can find on sites like https://snazzymaps.com/ for Google Map or link of style found here https://www.mapbox.com/designer-maps/ for mapbox', 'adifier' )
                    ),
                    array(
                        'id'        => 'google_map_lang',
                        'title'     => esc_html__( 'Map Language', 'adifier' ) ,
                        'type'      => 'text',
                        'desc'      => esc_html__( 'Input map language code, https://developers.google.com/maps/faq#languagesupport', 'adifier' ),
                        'required'  => array( 
                            array('map_source','=','google'),
                        ),
                    ),
                    array(
                        'id'        => 'mapbox_map_lang',
                        'title'     => esc_html__( 'Map Language', 'adifier' ) ,
                        'type'      => 'text',
                        'desc'      => esc_html__( 'Input map language code, https://docs.mapbox.com/vector-tiles/reference/mapbox-streets-v8/#name-text--name_lang-code-text', 'adifier' ),
                        'required'  => array( 
                            array('map_source','=','mapbox'),
                        ),
                    ),
                    array(
                        'id'        => 'mapbox_geocoder_lang',
                        'title'     => esc_html__( 'GeoCoder Language', 'adifier' ) ,
                        'type'      => 'text',
                        'desc'      => esc_html__( 'Input geocoder language code ( autocomplete box for place select ), http://www.mathguide.de/info/tools/languagecode.html', 'adifier' ),
                        'required'  => array( 
                            array('map_source','=','mapbox'),
                        ),                            
                    ),
                    array(
                        'id'        => 'osm_map_lang',
                        'title'     => esc_html__( 'Map Language', 'adifier' ) ,
                        'type'      => 'text',
                        'desc'      => esc_html__( 'Input map language code en, fr, de,...', 'adifier' ),
                        'required'  => array( 
                            array('map_source','=','osm'),
                        ),
                    ),                                        
                    array(
                        'id'        => 'enable_compare',
                        'title'     => esc_html__( 'Enable Compare', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable comparing of the product', 'adifier'),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'compare_box_autoopen',
                        'title'     => esc_html__( 'Auto Open Compare', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Open compare box after product has been added to the list', 'adifier'),
                        'default'   => 'yes',
                        'required'  => array( 
                            array('enable_compare','=',array('yes')),
                        ),	                        
                    ),                    
                    array(
                        'id'        => 'compare_max_ads',
                        'title'     => esc_html__( 'Max Compare Ads', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input maximum number of products to compare', 'adifier'),
                        'default'   => '20'
                    ),
                    array(
                        'id'        => 'default_search_listing',
                        'title'     => esc_html__( 'Default Search Listing', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'grid' => esc_html__( 'Grid', 'adifier' ),
                            'list' => esc_html__( 'List', 'adifier' ),
                            'card' => esc_html__( 'Card', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select default listing on search listing', 'adifier'),
                        'default'   => 'grid'
                    ),
                    array(
                        'id'        => 'search_more_less',
                        'title'     => esc_html__( 'More/Less Search Toggle', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number if you want to limit list of category and predefined locations on search page or leave empty to show them all', 'adifier'),
                    ),
                    array(
                        'id'        => 'enable_logout_contact',
                        'title'     => esc_html__( 'Logout Contact', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'      => esc_html__( 'Yes', 'adifier' ),
                            'no'     => esc_html__( 'No', 'adifier' )
                        ),
                        'desc'      => esc_html__('If this is set to yes then users will be able to send mail to seller while they are loggedout and at the same time they will be able to register.', 'adifier'),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'phone_visibility',
                        'title'     => esc_html__( 'Phone Visibility', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            '1' => esc_html__( 'Always Visible', 'adifier' ),
                            '2' => esc_html__( 'Visible To Logged In Users', 'adifier' ),
                            '3' => esc_html__( 'Always Hidden', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Set visibility level for phone contact on ad and profile pages', 'adifier'),
                        'default'   => '1'
                    ),
                    array(
                        'id'        => 'random_author',
                        'title'     => esc_html__( 'Other Seller Ads Number', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number if you want to show some other author ads on ad single page ( it will dispay them random ) or leave empty to disable', 'adifier'),
                    ),
                    array(
                        'id'        => 'price_filter_type',
                        'title'     => esc_html__( 'Price Filter Type', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'slider'    => esc_html__( 'Slider', 'adifier' ),
                            'inputs'    => esc_html__( 'Input Range', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select style of the price filter on search page which can be draggable slider or two inputs for min and max', 'adifier'),
                        'default'   => 'slider'
                    ),
                    array(
                        'id'        => 'deactivate_account',
                        'title'     => esc_html__( 'Can Users Deactivate Account', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable ability for users to delete their account and all what they have posted', 'adifier'),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'keep_order_on_delete',
                        'title'     => esc_html__( 'Keep Orders On Delete', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'required'  => array( 
                            array('deactivate_account','=','yes'),
                        ),
                        'desc'      => esc_html__('If set to yes order associated to account which is being deleted will be kept', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'address_order',
                        'title'     => esc_html__( 'Address Direction', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'front'       => esc_html__( 'Country, state, city, street, number', 'adifier' ),
                            'back'        => esc_html__( 'Number, street, city, state, country', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select elements order of the address', 'adifier'),
                        'default'   => 'front'
                    ),
                    array(
                        'id'        => 'reporting_ads_can',
                        'title'     => esc_html__( 'Reporting Of Ads', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            ''          => esc_html__( 'By All', 'adifier' ),
                            'logged'    => esc_html__( 'By Logged In Users', 'adifier' ),
                        ),
                        'default'   => ''
                    ),
                    array(
                        'id'        => 'search_trigger',
                        'title'     => esc_html__( 'Search Filter Trigger', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'btn'       => esc_html__( 'Clicking On A Button', 'adifier' ),
                            'change'       => esc_html__( 'Any Change On Form', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select what will trigger searching for results', 'adifier'),
                        'default'   => 'btn'
                    ),
                    array(
                        'id'        => 'category_selection',
                        'title'     => esc_html__( 'Category Selection', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'combined'   => esc_html__( 'Combined', 'adifier' ),
                            'separated' => esc_html__( 'Separated', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Combined - show all categories in single select box ( search will display radio button list ) Separated - load subcategories based on selected category ( search will display multiple select boxes. Search form element will display only top level categories )', 'adifier'),
                        'default'   => 'combined'
                    ),
                    array(
                        'id'        => 'show_username_only',
                        'title'     => esc_html__( 'Show Username Only', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select to display username instead for dispaly name ( First Name + Last Name )', 'adifier'),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'advert_comments',
                        'title'     => esc_html__( 'Show Comments On Ads', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable comments on ads', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'redirect_on_submit',
                        'title'     => esc_html__( 'Redirect After Ad Submit', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable redirect to author list of ads after ad has been submitted', 'adifier'),
                        'default'   => 'no'
                    ),
                )
            ); 

            $this->_sections[] = array(
                'title'     => esc_html__('Locations', 'adifier') ,
                'icon'      => '',
                'subsection'=> true,
                'desc'      => esc_html__('Locations settings', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'location_search',
                        'title'     => esc_html__( 'Location Search', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'geo'           => esc_html__( 'Geo Search', 'adifier' ),
                            'predefined'    => esc_html__( 'Predefined Values', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select whether you want to use map places or dropdown with predefined values for location search. If you set this option to Geo Search then make sure you have set Use Map Locations to Yes', 'adifier'),
                        'default'   => 'geo'
                    ),
                    array(
                        'id'        => 'use_google_location',
                        'title'     => esc_html__( 'Use Map Locations', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('If this is set to No then map will not be displayed at all ( ad single, profile,... )', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'use_predefined_locations',
                        'title'     => esc_html__( 'Use Predefined Locations', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('If this is set to No then users will not be forced to select predefined location from the dropdown', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'location_selection',
                        'title'     => esc_html__( 'Predefined Locations Selection', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'combined'   => esc_html__( 'Combined', 'adifier' ),
                            'separated' => esc_html__( 'Separated', 'adifier' ),
                        ),
                        'required'  => array( 
                            array('use_predefined_locations','=','yes'),
                        ),
                        'desc'      => esc_html__('Combined - show all predefined locations in single select box ( search will display radio button list ) Separated - load sublocations based on selected location ( search will display multiple select boxes. Search form element will display only top level locations )', 'adifier'),
                        'default'   => 'combined'
                    ),  
                    array(
                        'id'        => 'single_location_display',
                        'title'     => esc_html__( 'Location On Single', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'geo_value'       => esc_html__( 'Geo Location', 'adifier' ),
                            'pre_value'       => esc_html__( 'Predefined Location Value', 'adifier' ),
                        ),
                        'desc'      => esc_html__('What location to display on ad single? Geo Location means to display values from map and Predefined means to display from dropdown', 'adifier'),
                        'required'  => array( 
                            array('use_google_location','=','yes'),
                            array('use_predefined_locations','=','yes'),
                        ),
                        'default'   => 'geo_value'
                    ),
                    array(
                        'id'        => 'single_location_zoom',
                        'type'      => 'text',
                        'title'     => esc_html__('Zoom Location On Single', 'adifier') ,
                        'desc'      => esc_html__('Input zoom for the single ad page', 'adifier'),
                        'required'  => array( 
                            array('use_google_location','=','yes'),
                        ),
                    ),
                    array(
                        'id'        => 'profile_location_display',
                        'title'     => esc_html__( 'Location On Profile', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'geo_value'       => esc_html__( 'Geo Location', 'adifier' ),
                            'pre_value'       => esc_html__( 'Predefined Location Value', 'adifier' ),
                        ),
                        'desc'      => esc_html__('What location to display on profile? Geo Location means to display values from map and Predefined means to display from dropdown', 'adifier'),
                        'required'  => array( 
                            array('use_google_location','=','yes'),
                            array('use_predefined_locations','=','yes'),
                        ),
                        'default'   => 'geo_value'
                    ),
                    array(
                        'id'        => 'profile_location_zoom',
                        'type'      => 'text',
                        'title'     => esc_html__('Zoom Location On Profile', 'adifier') ,
                        'desc'      => esc_html__('Input zoom for the profile ad page', 'adifier'),
                        'required'  => array( 
                            array('use_google_location','=','yes'),
                        ),
                    ),
                    array(
                        'id'        => 'radius_units',
                        'title'     => esc_html__( 'Radius Units', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'mi'        => esc_html__( 'Miles', 'adifier' ),
                            'km'        => esc_html__( 'Kilometers', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select units for the radius location search', 'adifier'),
                        'default'   => 'mi'
                    ),
                    array(
                        'id'        => 'radius_max',
                        'title'     => esc_html__( 'Max Radius Search', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input max radius search distance', 'adifier'),
                        'default'   => '300'
                    ),
                    array(
                        'id'        => 'google_location_restriction',
                        'title'     => esc_html__( 'Restrict Location To Country', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input country code or comma separated list of country codes in which locations will be available', 'adifier'),
                    ),
                    array(
                        'id'        => 'use_google_direction',
                        'title'     => esc_html__( 'Use Google Directions', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable usage of map directions on single advert and on profile page', 'adifier'),
                        'default'   => 'yes'
                    ),                    
                )
            ); 

            $this->_sections[] = array(
                'title'     => esc_html__('Submitting', 'adifier') ,
                'icon'      => '',
                'subsection'=> true,
                'desc'      => esc_html__('Submitting settings', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'approval_method',
                        'title'     => esc_html__( 'Approval Method', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'auto'      => esc_html__( 'Auto', 'adifier' ),
                            'manual'    => esc_html__( 'Manual', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select approval method of the adverts', 'adifier'),
                        'default'   => 'auto'
                    ),                   
                    array(
                        'id'        => 'enable_conditions',
                        'title'     => esc_html__( 'Show Conditions', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'      => esc_html__( 'Yes', 'adifier' ),
                            'no'    => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable conditions', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'submit_terms',
                        'title'     => esc_html__( 'Submit Terms', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input link to terms & conditions for submitting ads', 'adifier'),
                    ),

                    array(
                        'id'        => 'regular_expires',
                        'title'     => esc_html__( 'Regular Ads Expire', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number of days how long ads last for', 'adifier'),
                        'default'   => '30'
                    ),
                    array(
                        'id'        => 'auction_expires',
                        'title'     => esc_html__( 'Auction Ads Expire', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input number of days how long ads last for', 'adifier'),
                        'default'   => '10'
                    ),
                    array(
                        'id'        => 'max_videos',
                        'title'     => esc_html__( 'Max Video', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input maximum number of videos per ad or leave empty for unlimited', 'adifier'),
                    ),
                    array(
                        'id'        => 'max_images',
                        'title'     => esc_html__( 'Max Images', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input maximum number of images per ad or leave empty for unlimited', 'adifier'),
                    ),
                    array(
                        'id'        => 'max_image_size',
                        'title'     => esc_html__( 'Max Image Size', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input maximum size of image allowed in MB', 'adifier'),
                    ),
                    array(
                        'id'        => 'bad_words',
                        'title'     => esc_html__( 'Bad Words', 'adifier' ),
                        'type'      => 'textarea',
                        'desc'      => esc_html__('Input pie separated list of bad words which will be removed for example ( word|another|another one )', 'adifier'),
                    ),
                    array(
                        'id'        => 'max_title_length',
                        'title'     => esc_html__( 'Max Title Length', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input maximum chars that can be added in title field or leave empty for unlimited', 'adifier'),
                    ),                    
                    array(
                        'id'        => 'currencies',
                        'title'     => esc_html__( 'Currencies', 'adifier' ),
                        'type'      => 'grouped_adifier',
                        'repeatable'=> true,
                        'allow_empty' => true,
                        'subfields' => array( esc_html__( 'Abbreviation', 'adifier' ), esc_html__( 'Sign', 'adifier' ), esc_html__( 'Sign Location ( F - Front, B - Back )', 'adifier' ), esc_html__( 'Rate', 'adifier' ), esc_html__( 'Thousands  Separator', 'adifier' ), esc_html__( 'Decimal Separator', 'adifier' ), esc_html__( 'Has Decimals ( Y - Yes, N - No )', 'adifier' ) ),
                        'desc'      => esc_html__('Abbreviation is 3 letters code ( i.e. USD ). Sign is representative sign of the currency. Rate is value which represents amount of base currency ( currency set in Adifier WP -> Payments ) which is equal to amount of 1 of given currency (i.e. based currency is USD and 1 EUR = 1.4 USD rate value for EUR is 1.4 ). Even if you are not using decimals populate decimal separator ( in this case you can put anything )', 'adifier')
                    )
                )
            ); 

            $this->_sections[] = array(
                'title'     => esc_html__('Phone Verification', 'adifier') ,
                'icon'      => '',
                'subsection'=> true,
                'desc'      => esc_html__('Twilio settings', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'enable_phone_verification',
                        'title'     => esc_html__( 'Enable Verification', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable phone verification', 'adifier'),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'twilio_service_sid',
                        'title'     => esc_html__( 'Service SID', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input your Twilio Service SID. To create new service visit this link https://www.twilio.com/console/verify/dashboard', 'adifier'),
                    ),                    
                    array(
                        'id'        => 'twilio_account_sid',
                        'title'     => esc_html__( 'Account SID', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input your Twilio Account SID. This info can be found here https://www.twilio.com/console/project/settings', 'adifier'),
                    ),
                    array(
                        'id'        => 'twilio_auth_token',
                        'title'     => esc_html__( 'Auth Token', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input your Twilio Auth Token. This info can be found here https://www.twilio.com/console/project/settings', 'adifier'),
                    ),
                )
            ); 

            $this->_sections[] = array(
                'title'     => esc_html__('Emails', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Email settings', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'sender_email',
                        'title'     => esc_html__( 'Sender Email', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input sender mail which will be used by system mails like registration, password recovery...', 'adifier'),
                    ),
                    array(
                        'id'        => 'sender_name',
                        'title'     => esc_html__( 'Sender Name', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input sender name which will be used by system mails like registration, password recovery...', 'adifier'),
                    ),
                    array(
                        'id'        => 'email_logo',
                        'title'     => esc_html__( 'Email Logo', 'adifier' ),
                        'type'      => 'media',
                        'desc'      => esc_html__('If your main logo is SVG you need to add png/jpg logo here since SVG logo is not supported in emails or you can use it anyway', 'adifier'),
                    ),
                )
            ); 



            /**********************************************************************
            ***********************************************************************
            PRODUCTS
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Products', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Products settings', 'adifier'),
            ); 

            $this->_sections[] = array(
                'title'     => esc_html__('Account', 'adifier') ,
                'icon'      => '',
                'subsection'=> true,
                'desc'      => esc_html__('Account products', 'adifier'),
                'fields'    => array(                    
                    array(
                        'id'        => 'account_payment',
                        'type'      => 'radio',
                        'title'     => esc_html__('Account Payments', 'adifier'),
                        'desc'      => esc_html__('Select payment way for site usage.', 'adifier'),
                        'options'   => array(
                            'packages'           => esc_html__( 'Packages', 'adifier' ),
                            'subscriptions'      => esc_html__( 'Subscription', 'adifier' ),
                            'hybrids'            => esc_html__( 'Hybrid', 'adifier' ),
                            'free'               => esc_html__( 'Free', 'adifier' )
                        ),
                        'default'   => 'free'
                    ),
                    array(
                        'id'            => 'package_free_ads',
                        'title'         => esc_html__( 'Free Ads', 'adifier' ),
                        'type'          => 'text',
                        'desc'          => esc_html__('Input number of ads which users will receive upon registration', 'adifier'),
                        'default'       => '',
                        'required'      => array( 'account_payment', '=', 'packages' )
                    ),
                    array(
                        'id'            => 'subscription_free_time',
                        'title'         => esc_html__( 'Free Time For Ads', 'adifier' ),
                        'type'          => 'text',
                        'desc'          => esc_html__('Free time for posting ads upon registration. If you are inputing days input clean number only ( For example 20 ). If you are inputing hours input it with prefix + ( For example +14 ) ', 'adifier'),
                        'default'       => '',
                        'required'      => array( 'account_payment', '=' , 'subscriptions' )
                    ),
                    array(
                        'id'            => 'hybrid_free_stuff',
                        'title'         => esc_html__( 'Free Submits For Hybrid', 'adifier' ),
                        'type'          => 'grouped_adifier',
                        'subfields'     => array( esc_html__( 'Ads', 'adifier' ), esc_html__( 'Time', 'adifier' ) ),
                        'desc'          => esc_html__('Input free ads and free time which users will receive upon registration. If you are inputing days input clean number only ( For example 20 ). If you are inputing hours input it with prefix + ( For example +14 ) ', 'adifier'),
                        'default'       => '',
                        'required'      => array( 'account_payment', '=' , 'hybrids' )
                    ),
                    array(
                        'id'            => 'packages',
                        'type'          => 'grouped_adifier',
                        'repeatable'    => true,
                        'title'         => esc_html__('Packages', 'adifier'),
                        'subfields'     => array( esc_html__( 'Name', 'adifier' ), esc_html__( 'Ads', 'adifier' ), esc_html__( 'Price', 'adifier' ) ),
                        'desc'          => esc_html__('Create packages for posting ads. Packages contain number of ads which user can post. Decimal separator is . ( dot ) no thousands separator. Price can not be 0 since packages are cumulative', 'adifier'),
                        'required'      => array( 'account_payment', '=', 'packages' )
                    ),
                    array(
                        'id'            => 'subscriptions',
                        'type'          => 'grouped_adifier',
                        'repeatable'    => true,
                        'title'         => esc_html__('Subscriptions', 'adifier'),
                        'subfields'     => array( esc_html__( 'Name', 'adifier' ), esc_html__( 'Days', 'adifier' ), esc_html__( 'Price', 'adifier' ) ),
                        'desc'          => esc_html__('Create subscriptions for posting ads. Subscription contain number of days for how long user can post ad. Decimal separator is . ( dot ) no thousands separator. Price can not be 0 since packages are cumulative', 'adifier'),
                        'required'      => array( 'account_payment', '=', 'subscriptions' )
                    ),
                    array(
                        'id'            => 'hybrids',
                        'type'          => 'grouped_adifier',
                        'repeatable'    => true,
                        'title'         => esc_html__('Hybrids', 'adifier'),
                        'subfields'     => array( esc_html__( 'Name', 'adifier' ), esc_html__( 'Ads', 'adifier' ), esc_html__( 'Days', 'adifier' ), esc_html__( 'Price', 'adifier' ) ),
                        'desc'          => esc_html__('Create hybrids for posting ads. Hybrid contain number of ads and number of days for how long user can post ad. Decimal separator is . ( dot ) no thousands separator. Price can not be 0 since packages are cumulative', 'adifier'),
                        'required'      => array( 'account_payment', '=', 'hybrids' )
                    ),
                ) 
            );


            $this->_sections[] = array(
                'title'     => esc_html__('Ads', 'adifier') ,
                'icon'      => '',
                'subsection'=> true,
                'desc'      => esc_html__('Ad products', 'adifier'),
                'fields'    => array_merge(array(
                    array(
                        'id'        => 'enable_promotions',
                        'title'     => esc_html__( 'Enable Promotions', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable promotions', 'adifier'),
                        'default'   => 'yes'
                    ),                    
                ), adifier_available_promotions())
            );                 

            /**********************************************************************
            ***********************************************************************
            PAYMENT METHODS
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Payments', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Payment settings', 'adifier'),
                'fields'    => array(                    
                    array(
                        'id'        => 'thousands_separator',
                        'title'     => esc_html__( 'Thousands Separator', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input sugn for thousands separator ( for display purposes )', 'adifier'),
                        'default'   => ','
                    ),
                    array(
                        'id'        => 'decimal_separator',
                        'title'     => esc_html__( 'Decimal Separator', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input sugn for decimal separator ( for display purposes )', 'adifier'),
                        'default'   => '.'
                    ),
                    array(
                        'id'        => 'show_decimals',
                        'title'     => esc_html__( 'Show Decimals', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Enable or disable decimal places on price', 'adifier'),
                        'default'   => 'yes'
                    ),
                    array(
                        'id'        => 'currency_location',
                        'title'     => esc_html__( 'Currency Symbol Location', 'adifier' ),
                        'type'      => 'select',
                        'options'   => array(
                            'front'     => esc_html__( 'Before Price', 'adifier' ),
                            'back'      => esc_html__( 'After Price', 'adifier' ),
                        ),
                        'desc'      => esc_html__('Select location of the symbol.', 'adifier'),
                        'default'   => 'front'
                    ),
                    array(
                        'id'        => 'currency_abbr',
                        'title'     => esc_html__( 'Currency Abbreviation', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input currency abbreviation ( USD, EUR, ... )', 'adifier'),
                        'default'   => ''
                    ),
                    array(
                        'id'        => 'payment_enviroment',
                        'type'      => 'select',
                        'options'   => array(
                            'live'       => esc_html__( 'Live', 'adifier' ),
                            'test'       => esc_html__( 'Test', 'adifier' )
                        ),
                        'title'     => esc_html__('Payment Enviroment', 'adifier') ,
                        'desc'      => esc_html__('If you want to test out payment with sandbox accounts select Test and if you want to start receiving payments select Live', 'adifier'),
                        'default'   => 'live'
                    ),
                    array(
                        'id'        => 'currency_symbol',
                        'title'     => esc_html__( 'Currency Symbol', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input price symbol.', 'adifier'),
                    ),
                    array(
                        'id'        => 'tax',
                        'title'     => esc_html__( 'Tax', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('Input tax - number only with maximum 2 decimal places', 'adifier'),
                    ),
                    array(
                        'id'        => 'tax_name',
                        'title'     => esc_html__( 'Tax Name', 'adifier' ),
                        'type'      => 'text',
                        'desc'      => esc_html__('name of the tax', 'adifier'),
                    ),
                    array(
                        'id'        => 'invoice_data',
                        'type'      => 'textarea',
                        'title'     => esc_html__('Invoice Data', 'adifier') ,
                        'desc'      => esc_html__('Input information of your company which are required to be on invoice', 'adifier'),
                    ),
                    array(
                        'id'        => 'invoice_num_start',
                        'type'      => 'text',
                        'title'     => esc_html__('Invoice Number - Start', 'adifier') ,
                        'desc'      => esc_html__('Input number which will increment with every order. Empty means that it continues to use timestamp', 'adifier'),
                    ),
                    array(
                        'id'        => 'invoice_num_prefix',
                        'type'      => 'text',
                        'title'     => esc_html__('Invoice Number - Prefix', 'adifier') ,
                        'desc'      => esc_html__('Input string to be displayed before the incremental number or leave empty to remove', 'adifier'),
                    ),
                    array(
                        'id'        => 'invoice_num_sufix',
                        'type'      => 'text',
                        'title'     => esc_html__('Invoice Number - Sufix', 'adifier') ,
                        'desc'      => esc_html__('Input string to be displayed after the incremental number or leave empty to remove', 'adifier'),
                    ),
                    array(
                        'id'        => 'strict_sequential_numbering',
                        'type'      => 'select',
                        'title'     => esc_html__('Force Sequential Paid Invoice Numbering', 'adifier') ,
                        'options'   => array(
                            'no' => esc_html__( 'No', 'adifier' ),
                            'yes' => esc_html__( 'Yes', 'adifier' ),
                        ),
                        'default'   => 'no',
                        'desc'      => esc_html__('If you are required to have sequential numbers on all paid invoices enable this option. It will add temporary number on each order and apply proper sequential number only when the order is paid.', 'adifier'),
                    ),                    
                )
            ); 

            $this->_sections = apply_filters( 'adifier_payment_options', $this->_sections );

            /**********************************************************************
            ***********************************************************************
            BLOG
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Blog', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Blog settings', 'adifier'),
                'fields'    => array(                    
                    array(
                        'id'        => 'listing_type',
                        'type'      => 'select',
                        'title'     => esc_html__('Listing Type', 'adifier'),
                        'desc'      => esc_html__('Select listing style for blog listing.', 'adifier'),
                        'options'   => array(
                            '1'         => esc_html__( 'Grid 1 & Sidebar', 'adifier' ),
                            '2'         => esc_html__( 'Grid 1', 'adifier' ),
                            '3'         => esc_html__( 'Grid 2 & Sidebar', 'adifier' ),
                            '4'         => esc_html__( 'Grid 2', 'adifier' ),
                            '5'         => esc_html__( 'Grid 3', 'adifier' ),
                        ),
                        'default'   => '1'
                    ),
                )
            );  


            /**********************************************************************
            ***********************************************************************
            SUBSCRIPTION
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Subscription', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Set up subscription API key and list ID.', 'adifier'),
                'fields'    => array(
                    // Mail Chimp API
                    array(
                        'id'        => 'mail_chimp_api',
                        'type'      => 'text',
                        'title'     => esc_html__('API Key', 'adifier') ,
                        'desc'      => esc_html__('Type your mail chimp api key.', 'adifier')
                    ) , 
                    // Mail Chimp List ID
                    array(
                        'id'        => 'mail_chimp_list_id',
                        'type'      => 'text',
                        'title'     => esc_html__('List ID', 'adifier') ,
                        'desc'      => esc_html__('Type here ID of the list on which users will subscribe.', 'adifier')
                    ) ,
                    // Mail Chimp Double Opt-In
                    array(
                        'id'        => 'mail_chimp_double_optin',
                        'type'      => 'select',
                        'options'   => array(
                            'no'   => esc_html__( 'No', 'adifier' ),
                            'yes'  => esc_html__( 'Yes', 'adifier' )
                        ),
                        'title'     => esc_html__('Double Opt-In', 'adifier') ,
                        'desc'      => esc_html__('If it is set to yes then user will first receive confirmation mail in order to give their consent.', 'adifier')
                    ) ,
                )
            );

            /***********************************************************************
            Appearance
            **********************************************************************/
            $this->_sections[] = array(
                'title'     => esc_html__('Appearance', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Set up the looks.', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'site_logo',
                        'type'      => 'media',
                        'title'     => esc_html__('Logo For Light', 'adifier') ,
                        'desc'      => esc_html__('Upload logo which is used on light  background headers. Used by Inline Navigation, Bottom Navigation, Transaprent Navigation ( on inner pages ), Side Categories', 'adifier'),
                        'required'  => array( 'header_style', '=', array( 'header-1', 'header-2', 'header-3', 'header-4' ) ),
                    ),     
                    array(
                        'id'            => 'dark_nav_logo',
                        'type'          => 'media',
                        'title'         => esc_html__('Logo For Dark', 'adifier'),
                        'desc'          => esc_html__('Upload logo which is used on dark background headers. Used by Inline Navigation, Bottom Navigation ( if sticky is enabled ), Transaprent Navigation ( on home page ), Side Categories ( if sticky is enabled ), Full Dark', 'adifier'),
                        'required'      => array( 'header_style', '=', array( 'header-2', 'header-3', 'header-4', 'header-5' ) ),
                    ),                                   
                    array(
                        'id'            => 'main_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Main Color', 'adifier'),
                        'desc'          => esc_html__('Select main color of the site.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#00a591'
                    ),
                    array(
                        'id'            => 'main_color_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Main Color On Hover', 'adifier'),
                        'desc'          => esc_html__('Select main color of the site on hover for example for button.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#008c77'
                    ),
                    array(
                        'id'            => 'main_color_font',
                        'type'          => 'color',
                        'title'         => esc_html__('Main Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the elements which have main color as their background.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#ffffff'
                    ), 
                    array(
                        'id'            => 'main_color_font_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Main Font Color On Hover', 'adifier'),
                        'desc'          => esc_html__('Select main font color of the site on hover for example for button.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'search_btn_bg',
                        'type'          => 'color',
                        'title'         => esc_html__('Search Button Background', 'adifier'),
                        'desc'          => esc_html__('Select background color of the search button on inner pages.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#ff5a5f'
                    ), 
                    array(
                        'id'            => 'search_btn_bg_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Search Button Background Hover', 'adifier'),
                        'desc'          => esc_html__('Select background color on hover of the search button on inner pages.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#d54b4f'
                    ), 
                    array(
                        'id'            => 'search_btn_font',
                        'type'          => 'color',
                        'title'         => esc_html__('Search Button Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color of the search button on inner pages.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ), 
                    array(
                        'id'            => 'search_btn_font_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Search Button Font Hover', 'adifier'),
                        'desc'          => esc_html__('Select font color on hover of the search button on inner pages.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'link_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Link Color', 'adifier'),
                        'desc'          => esc_html__('Select color for the text links.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#666'
                    ),
                    array(
                        'id'            => 'price_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Price Color', 'adifier'),
                        'desc'          => esc_html__('Select color for the priceon ad items.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#d54b4f'
                    ),
                    array(
                        'id'            => 'logo_width',
                        'type'          => 'text',
                        'title'         => esc_html__('Logo Width', 'adifier'),
                        'desc'          => esc_html__('Input logo width', 'adifier'),
                    ),
                    array(
                        'id'            => 'logo_height',
                        'type'          => 'text',
                        'title'         => esc_html__('Logo Height', 'adifier'),
                        'desc'          => esc_html__('Input logo height', 'adifier'),
                    ),
                    array(
                        'id'            => 'header_submit',
                        'type'          => 'select',
                        'options'        => array(
                            'full'  => esc_html__( 'Full Button', 'adifier' ),
                            'icon'  => esc_html__( 'Icon Only', 'adifier' )
                        ),
                        'title'         => esc_html__('Header Submit Button', 'adifier'),
                        'desc'          => esc_html__('Select style of the header submit button - full with text or bullhorn icon only', 'adifier'),
                        'default'       => 'full'
                    ),                    
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('Typography', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(

                    array(
                        'id'            => 'text_font',
                        'type'          => 'select',
                        'title'         => esc_html__('Text Font', 'adifier'),
                        'desc'          => esc_html__('Select font for the regular text.', 'adifier'),
                        'options'       => adifier_all_google_fonts(),
                        'default'       => 'Open Sans'
                    ),
                    array(
                        'id'            => 'text_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Text Font Size', 'adifier'),
                        'desc'          => esc_html__('Inuput font size for the regular text.', 'adifier'),
                        'default'       => '14px'
                    ),
                    array(
                        'id'            => 'text_font_line_height',
                        'type'          => 'text',
                        'title'         => esc_html__('Text Font Line Height', 'adifier'),
                        'desc'          => esc_html__('Input line height e for the regular text.', 'adifier'),
                        'default'       => '24px'
                    ),                            
                    array(
                        'id'            => 'text_font_weight',
                        'type'          => 'text',
                        'title'         => esc_html__('Text Font Weight', 'adifier'),
                        'desc'          => esc_html__('Select font weight for the regular text. Check out available weights on https://fonts.google.com/', 'adifier'),
                        'default'       => '400'
                    ),
                    array(
                        'id'            => 'text_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the text.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#484848'
                    ),

                    array(
                        'id'            => 'title_font',
                        'type'          => 'select',
                        'title'         => esc_html__('Title Font', 'adifier'),
                        'desc'          => esc_html__('Select font for the title text.', 'adifier'),
                        'options'       => adifier_all_google_fonts(),
                        'default'       => 'Quicksand'
                    ),
                    array(
                        'id'            => 'title_font_weight',
                        'type'          => 'text',
                        'title'         => esc_html__('Title Font Weight', 'adifier'),
                        'desc'          => esc_html__('Select font weight for the titles. Check out available weights on https://fonts.google.com/', 'adifier'),
                        'default'       => '700'
                    ),
                    array(
                        'id'            => 'title_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Title Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the titles.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#333'
                    ),
                    array(
                        'id'            => 'heading_line_height',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading Line Height', 'adifier'),
                        'desc'          => esc_html__('Input headings line height.', 'adifier'),
                        'default'       => '1.3'
                    ),                        
                    array(
                        'id'            => 'h1_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading 1 Font Size', 'adifier'),
                        'desc'          => esc_html__('Input heading 1 font size.', 'adifier'),
                        'default'       => '40px'
                    ),
                    array(
                        'id'            => 'h2_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading 2 Font Size', 'adifier'),
                        'desc'          => esc_html__('Input heading 2 font size.', 'adifier'),
                        'default'       => '35px'
                    ),
                    array(
                        'id'            => 'h3_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading 3 Font Size', 'adifier'),
                        'desc'          => esc_html__('Input heading 3 font size.', 'adifier'),
                        'default'       => '30px'
                    ),
                    array(
                        'id'            => 'h4_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading 4 Font Size', 'adifier'),
                        'desc'          => esc_html__('Input heading 4 font size.', 'adifier'),
                        'default'       => '25px'
                    ),
                    array(
                        'id'            => 'h5_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading 5 Font Size', 'adifier'),
                        'desc'          => esc_html__('Input heading 5 font size.', 'adifier'),
                        'default'       => '18px'
                    ),
                    array(
                        'id'            => 'h6_font_size',
                        'type'          => 'text',
                        'title'         => esc_html__('Heading 6 Font Size', 'adifier'),
                        'desc'          => esc_html__('Input heading 6 font size.', 'adifier'),
                        'default'       => '16px'
                    ),
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('Footer Sidebars', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'footer_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Background Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the footer sidebar.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#374252'
                    ),
                    array(
                        'id'            => 'footer_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the text.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#959ba7'
                    ),
                    array(
                        'id'            => 'footer_active_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Active Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the links.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('Breadcrumbs', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'show_breadcrumbs',
                        'type'          => 'select',
                        'options'       => array(
                            'yes'   => esc_attr__( 'Yes', 'adifier' ),
                            'no'    => esc_attr__( 'No', 'adifier' ),
                        ),
                        'title'         => esc_html__('Show Breadcrumbs', 'adifier'),
                        'desc'          => esc_html__('Enable or disable display of breadcrumbs', 'adifier'),
                        'default'       => 'no'
                    ),
                    array(
                        'id'            => 'header_search',
                        'type'          => 'select',
                        'options'       => array(
                            'yes'   => esc_attr__( 'Yes', 'adifier' ),
                            'no'    => esc_attr__( 'No', 'adifier' ),
                        ),
                        'title'         => esc_html__('Show Header Search', 'adifier'),
                        'desc'          => esc_html__('Enable or disable display of header search', 'adifier'),
                        'required'      => array( 'show_breadcrumbs', '=', 'yes' ),
                        'default'       => 'yes'
                    ),
                    array(
                        'id'            => 'breadcrumbs_style',
                        'type'          => 'select',
                        'options'       => array(
                            'normal'            => esc_attr__( 'Normal', 'adifier' ),
                            'quick-search'      => esc_attr__( 'Quick Search', 'adifier' ),
                        ),
                        'title'         => esc_html__('Breadcrumbs Style', 'adifier'),
                        'desc'          => esc_html__('Select breadcrumbs style', 'adifier'),
                        'default'       => 'normal'
                    ),
                    array(
                        'id'            => 'breadcrumbs_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Background Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the breadcrumbs', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#2a2f36'
                    ),
                    array(
                        'id'            => 'breadcrumbs_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the text.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'breadcrumbs_image_bg',
                        'type'          => 'media',
                        'title'         => esc_html__('Breadcrumbs BG Image', 'adifier'),
                        'desc'          => esc_html__('Select background iamge of breadcrumbs', 'adifier'),
                    ),
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('Price Tables', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'pt_price_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Price BG Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for price section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#374252'
                    ),
                    array(
                        'id'            => 'pt_price_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Price Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for price section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_title_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Title BG Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for title section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#2e3744'
                    ),
                    array(
                        'id'            => 'pt_title_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Title Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for title section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_btn_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Button BG Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for button.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#374252'
                    ),
                    array(
                        'id'            => 'pt_btn_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Button Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for button.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_btn_bg_color_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Button BG Color Hover', 'adifier'),
                        'desc'          => esc_html__('Select background color for button onhover.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#2e3744'
                    ),
                    array(
                        'id'            => 'pt_btn_font_color_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Button Font Color On Hover', 'adifier'),
                        'desc'          => esc_html__('Select font color for button on hover.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_ac_price_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Price BG Color ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select background color for price section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#00a591'
                    ),
                    array(
                        'id'            => 'pt_ac_price_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Price Font Color ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select font color for price section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_ac_title_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Title BG Color ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select background color for title section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#008c77'
                    ),
                    array(
                        'id'            => 'pt_ac_title_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Title Font Color ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select font color for title section.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_ac_btn_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Button BG Color ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select background color for button.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#00a591'
                    ),
                    array(
                        'id'            => 'pt_ac_btn_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Button Font Color ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select font color for button.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'pt_ac_btn_bg_color_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Button BG Color Hover ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select background color for button onhover.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#008c77'
                    ),
                    array(
                        'id'            => 'pt_ac_btn_font_color_hover',
                        'type'          => 'color',
                        'title'         => esc_html__('Button Font Color On Hover ( Active Table )', 'adifier'),
                        'desc'          => esc_html__('Select font color for button on hover.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                )
            );


            $this->_sections[] = array(
                'title'         => esc_html__('Copyrights', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'copyrights_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Background Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the footer sidebar.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#2d323e'
                    ),
                    array(
                        'id'            => 'copyrights_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the text.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#aaa'
                    ),
                    array(
                        'id'            => 'copyrights_active_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Active Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the links.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    )
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('Dark Navigation', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'dark_nav_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Background Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the dark navigation.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#374252'
                    ),
                    array(
                        'id'            => 'dark_nav_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the dark navigation.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'dark_nav_font_color_active',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color Active/Hover', 'adifier'),
                        'desc'          => esc_html__('Select font color for the dark navigation on hover or when active.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    )
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('Subscription', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'subscription_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Background Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the subscription.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'subscription_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Text Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the subscription.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#2d323e'
                    )
                )
            );

            $this->_sections[] = array(
                'title'         => esc_html__('CTAs', 'adifier') ,
                'icon'          => '',
                'subsection'    => true,
                'desc'          => esc_html__('Set up the looks.', 'adifier'),
                'fields'        => array(
                    array(
                        'id'            => 'contact_phone_icon_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Contact Phone Icon BG', 'adifier'),
                        'desc'          => esc_html__('Select background color for the icon of contact seler by phone call to action.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#d54b4f'
                    ),
                    array(
                        'id'            => 'contact_phone_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Contact Phone BG Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the contact seller by phone call to action.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#ff5a5f'
                    ),
                    array(
                        'id'            => 'contact_phone_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Contact Phone Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the contact seller by phone call to action.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                    array(
                        'id'            => 'contact_msg_icon_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Contact Message Icon BG', 'adifier'),
                        'desc'          => esc_html__('Select background color for the icon of contact seller by message call to action.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#2e3744'
                    ),
                    array(
                        'id'            => 'contact_msg_bg_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Contact Message BG Color', 'adifier'),
                        'desc'          => esc_html__('Select background color for the contact seller by message call to action.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#4b586b'
                    ),
                    array(
                        'id'            => 'contact_msg_font_color',
                        'type'          => 'color',
                        'title'         => esc_html__('Contact Message Font Color', 'adifier'),
                        'desc'          => esc_html__('Select font color for the contact seller by message call to action.', 'adifier'),
                        'transparent'   => false,
                        'default'       => '#fff'
                    ),
                )
            );

            /***********************************************************************
            CONTACT PAGE SETTINGS
            **********************************************************************/
            
            $this->_sections[] = array(
                'title'     => esc_html__('Contact Page', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Contact page settings.', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'contact_form_email',
                        'type'      => 'text',
                        'title'     => esc_html__('Email', 'adifier') ,
                        'desc'      => esc_html__('Input email where the messages should arive.', 'adifier'),
                    ),
                    array(
                        'id'        => 'markers',
                        'type'      => 'multi_text',
                        'title'     => esc_html__('Markers', 'adifier') ,
                        'desc'      => esc_html__('Input markers for contact page in form LATITUDE,LONGITUDE.', 'adifier'),
                    ),
                    array(
                        'id'        => 'marker_icon',
                        'type'      => 'media',
                        'title'     => esc_html__('Marker Icon', 'adifier') ,
                        'desc'      => esc_html__('Select marker icon for the contact page.', 'adifier'),
                    ),
                    array(
                        'id'        => 'markers_max_zoom',
                        'type'      => 'text',
                        'title'     => esc_html__('Markers Max Zoom', 'adifier') ,
                        'desc'      => esc_html__('Markers max zoom 0 - 19.', 'adifier'),
                    ),
                )
            );

            /***********************************************************************
            COPYRIGHTS
            **********************************************************************/

            $this->_sections[] = array(
                'title'     => esc_html__('Footer', 'adifier') ,
                'icon'      => '',
                'desc'      => esc_html__('Footer settings.', 'adifier'),
                'fields'    => array(
                    array(
                        'id'        => 'show_subscription_form',
                        'type'      => 'select',
                        'options'   => array(
                            'yes'        => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'title'     => esc_html__('Enable Subscription Form', 'adifier') ,
                        'desc'      => esc_html__('Enable or disable footer subscription section.', 'adifier'),
                        'default'   => 'no'
                    ),                    
                    array(
                        'id'        => 'show_footer',
                        'type'      => 'select',
                        'options'   => array(
                            'yes'        => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'title'     => esc_html__('Enable Footer', 'adifier') ,
                        'desc'      => esc_html__('Enable or disable footer section.', 'adifier'),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'copyrights',
                        'type'      => 'text',
                        'title'     => esc_html__('Copyrights', 'adifier') ,
                        'desc'      => esc_html__('Input copyrights which will be visible at the bottom of the page.', 'adifier'),
                    ),
                    array(
                        'id'        => 'tb_social',
                        'type'      => 'select',
                        'title'     => esc_html__('Enable Social', 'adifier') ,
                        'desc'      => esc_html__('Enable or disable social links in top bar.', 'adifier'),
                        'options'   => array(
                            'yes'       => esc_html__( 'Yes', 'adifier' ),
                            'no'        => esc_html__( 'No', 'adifier' ),
                        ),
                        'default'   => 'no'
                    ),
                    array(
                        'id'        => 'tb_facebook_link',
                        'type'      => 'text',
                        'title'     => esc_html__('Facebook Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your facebook page', 'adifier'),
                    ),
                    array(
                        'id'        => 'tb_twitter_link',
                        'type'      => 'text',
                        'title'     => esc_html__('Twitter Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your twitter page', 'adifier'),
                    ),
                    array(
                        'id'        => 'tb_instagram_link',
                        'type'      => 'text',
                        'title'     => esc_html__('Instagram Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your instagram page', 'adifier'),
                    ),
                    array(
                        'id'        => 'tb_youtube_link',
                        'type'      => 'text',
                        'title'     => esc_html__('YouTube Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your youtube page', 'adifier'),
                    ),
                    array(
                        'id'        => 'tb_pinterest_link',
                        'type'      => 'text',
                        'title'     => esc_html__('Pinterest Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your pinterest page', 'adifier'),
                    ),
                    array(
                        'id'        => 'tb_linkedin_link',
                        'type'      => 'text',
                        'title'     => esc_html__('LInkedin Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your linkedin page', 'adifier'),
                    ),                    
                    array(
                        'id'        => 'tb_rss_link',
                        'type'      => 'text',
                        'title'     => esc_html__('RSS Link', 'adifier') ,
                        'desc'      => esc_html__('Input link to your rss feed', 'adifier'),
                    ),
                )
            );   

        }
    }

    $adifier_opts = new Adifier_Options();
    $adifier_opts->init();
}
?>