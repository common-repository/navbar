<?php

// pluginname NavBar for WordPress
// shortname NavBar
// dashname navbar

/*
Plugin Name: NavBar
Version: 1.3.1
Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/navbar
Author: Vladimir Prelovac
Author URI: http://www.prelovac.com/vladimir
Description: Adds a handy navigation bar to organize your WordPress blog shortcuts.

*/


global $wp_version;

$exit_msg = 'NavBar requires WordPress 2.7 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';

if (version_compare($wp_version, "2.7", "<"))
{
    exit($exit_msg);
}

require_once (ABSPATH . WPINC . '/pluggable.php'); 




// Avoid name collisions.
if (!class_exists('NavBar')):

    class NavBar
    {
        var $DB_option = 'NavBar_options';
        var $plugin_url;
        
      function get_keyword($refer)
			{			
				$keyword='';		
				
				if (eregi('google.',$refer))
					preg_match('/(\?|&|&amp;|;)(q)=([^\&\|]+)/',$refer,$qmatches);
				else
					preg_match('/(\?|&|&amp;|;)(q|p|query|t|s|search|as_q|wd|string|Keywords)=([^\&\|]+)/',$refer,$qmatches);
					
				$keyword=trim(urldecode($qmatches[3]));
				if ($keyword)
				{				
					if (strpos($keyword, "cache:")!==FALSE || strpos($keyword, "http://")!==FALSE || strpos($keyword, "invocationType")!==FALSE || is_numeric($keyword))
						$keyword='';
					else
					{
						$keyword=mb_strtolower(($keyword), "UTF-8");											
					}
				}
				$keyword=str_replace('+', '', $keyword);
				$keyword=str_replace('"', ' ', $keyword);			
				$keyword=str_replace("'", " ", $keyword);
				$keyword=str_replace("  ", " ", $keyword);
				
				$keyword=trim($keyword);
					
				return $keyword;
			}


        function NavBar()
        {
            $this->plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' .
                dirname(plugin_basename(__file__));

            add_action('admin_menu', array(&$this, 'admin_menu'));
             
            
            if (current_user_can('level_8') && !$_REQUEST['navbar_noshow'])
            	add_action('wp_print_scripts', array(&$this, 'print_scripts'));
            
            
        }

        function admin_menu()
        {
            add_options_page('NavBar Options', 'NavBar', 8, basename(__file__), array(&$this,
                'handle_options'));
        }

        function print_scripts()
        {
        		global $wpdb;
        		
            $options = $this->get_options();
            $nonce = wp_create_nonce('navbar-nonce');
            
            $navbar_info='';

            //wp_enqueue_script('navbar-jquery', $this->plugin_url . '/js/jquery.js', null, "1.3.2");
            wp_enqueue_script('jquery');
            /*wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('jquery-ui-selectable');
            wp_enqueue_script('jquery-ui-resizable');            
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('jquery-ui-sortable');*/
                       
            wp_enqueue_script('navbar-jquery-ui', $this->plugin_url . '/js/jquery-ui.js', null, "1.7.2");
            wp_enqueue_script('navbar-jquery-hotkeys', $this->plugin_url . '/js/jquery-hotkeys.js');
            wp_enqueue_script('navbar-jquery-colorpicker', $this->plugin_url . '/js/colorpicker.js');
            wp_enqueue_script('navbar', $this->plugin_url . '/js/navbar.js');

            echo '<link type="text/css" rel="stylesheet" href="' . $this->plugin_url .
                '/css/ui-lightness/jquery-ui.css" />' . "\n";

            echo '<link type="text/css" rel="stylesheet" href="' . $this->plugin_url .
                '/css/navbar.css" />' . "\n";
                
            echo '<link type="text/css" rel="stylesheet" href="' . $this->plugin_url .
                '/css/colorpicker/colorpicker.css" />' . "\n";                
            
            if (isset($_POST['submitted']))
            {
                check_admin_referer('navbar-nonce');

                $options['position'] = $_POST['position'];
                $options['hotkey'] = $_POST['hotkey'];
                $options['width'] = $_POST['width'];
                $options['bgcolor'] = $_POST['bgcolor'];                
                $options['hide'] = false;
            }
            
            if ($options['navinfo'])
            	$navbar_info.='Posts: '.wp_count_posts('post')->publish.'<br />Comments: '.wp_count_comments()->total_comments.'<br /><br />';
            
            if ($options['navcomment'])
            {
	            $comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT 1"); 
	            $comment=$comments[0];
	            
	            if ($comment)
	            	$navbar_info.=$comment->comment_author.' commented on <a href="' . ( get_comment_link($comment->comment_ID) ) . '">'.get_the_title($comment->comment_post_ID).'</a><br /><br />';
	            	
            }
            if ($options['navvisitor'])
            	$navbar_info.=get_option('navbar-latest');
            	
            $navbar_info.='<br />';	
            
            if ($options['navfilter'])
            	$navbar_info=apply_filters('navbar_info', $navbar_info);
            	
            $data = array(
                'url' =>           $this->plugin_url,
                'nonce' =>         $nonce, 
                'links' =>         $options['links'],
                'position' =>      $options['position'],
                'hotkey' =>        $options['hotkey'],
                'width' =>         $options['width'],
                'hide' =>          $options['hide'],
                'bgcolor' =>       $options['bgcolor'],
                'blog_name' =>     get_bloginfo('name'),                
                'info' => $navbar_info
            );
            
            $js_object = $this->array_to_js_object($data, "NavBarSettings");
            
            echo "<script type='text/javascript'>\n";
            echo "/* <![CDATA[ */\n";
            echo $js_object;
            echo "/* ]]> */\n";
            echo "</script>\n";
        }

        function array_to_js_object($array, $varname, $sub = false)
        {
            $jsarray = $sub ? $varname . "{\n" : $varname . " = {\n";
            $varname = "\t$varname";
            reset($array);
            
            // Loop through each element of the array
            while (list($key, $value) = each($array))
            {
                $jskey = "\t$key : ";

                if (is_array($value))
                {
                    // Multi Dimensional Array
                    $temp[] = $this->array_to_js_object($value, $jskey, true);
                }
                else
                {
                    if (is_numeric($value))
                    {
                        $jskey .= "$value";
                    } 
                    elseif (is_bool($value))
                    {
                        $jskey .= ($value ? 'true' : 'false') . "";
                    } 
                    elseif ($value === null)
                    {
                        $jskey .= "null";
                    }
                    else
                    {
                        static $pattern = array("\\", "'", "\r", "\n");
                        static $replace = array('\\', '\\\'', '\r', '\n');
                        $jskey .= "'" . str_replace($pattern, $replace, $value) . "'";
                    }
                    $temp[] = $jskey;
                }
            }
            $jsarray .= implode(",\n", $temp);

            $jsarray .= "\n}";
            return $jsarray;
        }

        function get_options()
        {
            $options = array(
                'position' => "left", 
                'hotkey' => "e",
                'width' => 150,
                'hide' => false,
                'bgcolor' => "#ffffff",
                'links' => array(
                    'name' => array(
                        'Dashboard', 
                        'Add New Post', 
                        'Comments',
                        'Themes',
                        'Plugins',
                        'hr',
                        'NavBar Options'
                    ), 
                    'url' => array(
                        get_bloginfo('wpurl') . "/wp-admin/index.php",
                        get_bloginfo('wpurl') . "/wp-admin/post-new.php",
                        get_bloginfo('wpurl') . "/wp-admin/edit-comments.php", 
                        get_bloginfo('wpurl') . "/wp-admin/themes.php", 
                        get_bloginfo('wpurl') . "/wp-admin/plugins.php", 
                        '#',
                        get_bloginfo('wpurl') . "/wp-admin/options-general.php?page=navbar.php", 
                    ),
                   
                ),
                'navinfo' => 'on',
                'navcomment' => 'on',
                'navvisitor' => 'on',
                'navfilter' => 'on'
            );

            $saved = get_option($this->DB_option);

            if (!empty($saved))
            {
                foreach ($saved as $key => $option)
                    $options[$key] = $option;
            }

            if ($saved != $options)
                update_option($this->DB_option, $options);

            return $options;
        }

        function install()
        {
            $this->get_options();
        }

        function handle_options()
        {
            $options = $this->get_options();

            if (isset($_POST['submitted']))
            {
                check_admin_referer('navbar-nonce');

                $options['position'] = $_POST['position'];
                $options['hotkey'] = $_POST['hotkey'];
                $options['width'] = $_POST['width'];
                $options['bgcolor'] = $_POST['bgcolor'];
                $options['hide'] = false;
                $options['navinfo'] = $_POST['navinfo'];
                $options['navcomment'] = $_POST['navcomment'];
                $options['navvisitor'] = $_POST['navvisitor'];
                $options['navfilter'] = $_POST['navfilter'];
                

                update_option($this->DB_option, $options);
                echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
            }

            $action_url = $_SERVER['REQUEST_URI'];
            $imgpath = $this->plugin_url . '/images';

						$navinfo=$options['navinfo']=='on'?'checked':''; 
						$navcomment=$options['navcomment']=='on'?'checked':''; 
						$navvisitor=$options['navvisitor']=='on'?'checked':''; 
						$navfilter=$options['navfilter']=='on'?'checked':''; 
						
            include ('navbar-options.php');
        }

    }

endif;



if (class_exists('NavBar')):

    $NavBar = new NavBar();
    if (isset($NavBar))
    {
        register_activation_hook(__file__, array(&$NavBar, 'install'));
    }
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && !current_user_can('level_8')) {
    	$from=$_SERVER['HTTP_REFERER'];
    	$u1=parse_url(get_option('siteurl'));
			$u2=parse_url($from);
		
    	if($u1['host']!=$u2['host']) {
    		$keyword=$NavBar->get_keyword($from);
    		$host=str_replace('www.','',$u2['host']);
    		if ($keyword)
    			$saved='Visitor from <a href="'.$from.'">'.$host.'</a> for '.$keyword.' [<a href="'.$_SERVER['REQUEST_URI'].'">to</a>]<br />';
    		else
    			$saved='Visitor from <a href="'.$from.'">'.$host.'</a> [<a href="'.$_SERVER['REQUEST_URI'].'">to</a>]<br />';
	    	update_option('navbar-latest',$saved );
	    }
    }
    	
endif;
?>