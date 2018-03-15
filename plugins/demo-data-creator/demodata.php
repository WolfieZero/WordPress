<?php
/*
Plugin Name: Demo Data Creator
Plugin URI: http://www.stillbreathing.co.uk/wordpress/demo-data-creator/
Description: Demo Data Creator is a Wordpress, WPMU and BuddyPress plugin that allows a Wordpress developer to create demo users, blogs, posts, comments and blogroll links for a Wordpress site. For BuddyPress you can also create user friendships, user statuses, user wire posts, groups, group members and group wire posts. PLEASE NOTE: deleting the data created by this plugin will delete EVERYTHING (pages, posts, comments, users - everything) on your site, so DO NOT use on a production site, or one where you want to save the data.
Version: 1.3.4
Author: Chris Taylor
Author URI: http://www.stillbreathing.co.uk
*/

require_once( "plugin-register.class.php" );
$register = new Plugin_Register();
$register->file = __FILE__;
$register->slug = "demodata";
$register->name = "Demo Data Creator";
$register->version = "1.3.4";
$register->developer = "Chris Taylor";
$register->homepage = "http://www.stillbreathing.co.uk";
$register->Register();

// if the file is being called in an AJAX call
if ( isset($_GET['ajax']) && $_GET['ajax'] == "true" )
{

	// when the admin menu is built
	add_action('admin_menu', 'demodata_do_ajax');
	
}

// check for MultiSite
function demodata_is_multisite() {
	if (
		version_compare(get_bloginfo("version"), "3", ">=") 
		&& defined('MULTISITE') 
		&& MULTISITE
	) {
		return true;
	}
	return false;
}

// check for WPMU
function demodata_is_mu() {
	if (
		defined('VHOST')
		&& function_exists("is_site_admin") 
	){
		return true;
	}
	return false;
}

// when the admin menu is built
add_action('admin_menu', 'demodata_add_menu_items');

// if this is not WPMU/MultiSite
if (!demodata_is_multisite() || !demodata_is_mu()) {
	// set up the $current_site global
	global $current_site;
	if( $current_site != null ) {
		$current_site->domain = demodata_blog_domain();
	}
}

// include registration functions - DEPRECATED
//require_once(ABSPATH . WPINC . '/registration.php');

// ======================================================
// Admin functions
// ======================================================

// do an ajax request
function demodata_do_ajax()
{

	echo '
	<div id="demodata_results">
	';

	// listen for form submission
	demodata_watch_form();
	
	echo '
	</div>
	';
	
	// stop processing
	exit();
}

// create the data
function demodata_create()
{
	// listen for a form submission
	if (count($_POST) > 0 && isset($_POST["create"]))
	{
	
		// get the upgrade functions
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
	
		switch ($_POST["create"]) {
		
			// creating users
			case "users":
				demodata_create_users();
				break;
		
			// creating blogs
			case "blogs":
				if (demodata_is_multisite() || demodata_is_mu()){
					demodata_create_blogs();
				}
				break;
		
			// creating categories
			case "categories":
				demodata_create_categories();
				break;
		
			// creating posts
			case "posts":
				demodata_create_posts();
				break;
				
			// creating pages
			case "pages":
				demodata_create_pages();
				break;
				
			// creating comments
			case "comments":
				demodata_create_comments();
				break;
				
			// creating links
			case "links":
				demodata_create_links();
				break;
				
			// creating groups
			case "groups":
				demodata_create_groups();
				break;
				
			// creating wire messages
			case "wire":
				demodata_create_wire();
				break;
				
			// creating status messages
			case "status":
				demodata_create_statuses();
				break;
				
			// creating friends
			case "friends":
				demodata_create_friends();
				break;
		
		}
		
	}
}

// add the menu items to the Site Admin list
function demodata_add_menu_items()
{
	// add includes
	if (isset($_GET["page"]) && $_GET["page"] == "demodata_form") {
		add_action("admin_head", "demodata_css");
		add_action("admin_head", "demodata_js");
	}
	
	// WP3
	if (version_compare(get_bloginfo("version"), "3", ">=")) {
		// multisite
		if (demodata_is_multisite()) {
			add_submenu_page('ms-admin.php', 'Demo Data Creator', 'Demo Data Creator', 'edit_users', 'demodata_form', 'demodata_form');
		// standard
		} else {
			add_submenu_page('tools.php', 'Demo Data Creator', 'Demo Data Creator', 'edit_users', 'demodata_form', 'demodata_form');
		}
	} else {
		// MU
		if (demodata_is_mu()) {
			add_submenu_page('wpmu-admin.php', 'Demo Data Creator', 'Demo Data Creator', 'edit_users', 'demodata_form', 'demodata_form');
		// standard
		} else {
			add_submenu_page('tools.php', 'Demo Data Creator', 'Demo Data Creator', 'edit_users', 'demodata_form', 'demodata_form');
		}
	}
}

// add the CSS to the admin page head
function demodata_css()
{
	if (isset($_GET["page"]) && $_GET["page"] == "demodata_form")
	{
		echo '
		<style type="text/css">
		html body form.demodata fieldset p {
		border-width: 0 0 1px 0;
		border-color: #AAA;
		}
		html body form.demodata fieldset label {
		float: left;
		width: 32em;
		}
		html body form.demodata fieldset input {
		width: 6em;
		}
		html body form.demodata fieldset input.text {
		width: 18em;
		}
		html body div.demodatasuccess {
		padding: 0 1em 1em 1em;
		margin-top: 1em;
		background: #D2FFCF;
		border: 1px solid #188F11;
		}
		html body div.demodatapending {
		padding: 0 1em 1em 1em;
		margin-top: 1em;
		background: #FFE8BF;
		border: 1px solid #FFB93F;
		}
		html body div.demodataerror {
		padding: 0 1em 1em 1em;
		margin-top: 1em;
		background: #FFCFCF;
		border: 1px solid #BF0B0B;
		}
		</style>
		';
	}
}

// add the JavaScript to the admin page head
function demodata_js()
{
	if (isset($_GET["page"]) && $_GET["page"] == "demodata_form")
	{
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".demodatabutton").bind("click", function(e) {
				var id = jQuery(this).attr("id");
				var div = jQuery("#" + id + "output");
				var form = jQuery("#" + id + "form");
				div.html(\'<div class="demodatapending"><p>' . __("Processing ... please wait", "demodata") . '</div></p>\');
				var formdata = form.serialize();
				jQuery.ajax({
					data: formdata,
					type: "POST",';
					// WP3
					if (version_compare(get_bloginfo("version"), "3", ">=")) {
						// multisite
						if (demodata_is_multisite()) {
							echo 'url: "ms-admin.php?page=demodata_form&ajax=true",
							';
						// standard
						} else {
							echo 'url: "tools.php?page=demodata_form&ajax=true",
							';
						}
					} else {
						// MU
						if (demodata_is_mu()) {
							echo 'url: "wpmu-admin.php?page=demodata_form&ajax=true",
							';
						// standard
						} else {
							echo 'url: "tools.php?page=demodata_form&ajax=true",
							';
						}
					}
		echo '
					success: function(data) {
						div.html(data);
					},
					error: function() {
						div.html(\'<div class="demodataerror"><p>' . __("Sorry, the process failed", "demodata") . '</p></div>\');
					}
				});
				e.preventDefault();
				return false;
			});
		});
		</script>
		';
	}
}

// create demo statuses
function demodata_create_statuses()
{
	global $wpdb;
	global $current_site;
	
	// get the users settings
	$statuses = @$_POST["maxstatus"] == "" ? 50 : (int)$_POST["maxstatus"];
	
	// check all the settings
	if (
		$statuses != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		// get users
		$sql = "select id from " . $wpdb->users . " order by id;";
		$users = $wpdb->get_results($sql);

		$success = true;
		$statusx = 0;
		$errorx = 0;
		
		// loop users
		foreach ($users as $user)
		{
		
			// generate a random number of statuses
			$statusnum = rand(0, $statuses);
			
			// for the required number of status messages
			for($s = 0; $s < $statusnum; $s++)
			{

				// add the status
				if (!demodata_create_status( $user->id ))
				{
					$success = false;
					$error .= "<li>Error creating status " . $statusx . " for user ID " . $user->id . '</li>';
					$statusx--;
					$errorx++;
					// break out of this loop
					break;
				} else {
					$statusx++;
				}
			
			}
		
		}
		
		if ($success)
		{
		
			echo '
			<div class="demodatasuccess">
			<p>' . $statusx . " " . __("demo statuses created", "demodata") . '</p>
			</div>
			';
			
		} else {
		
			echo '
			<div class="demodataerror">
			<p>' . $statusx . ' ' . __("demo statuses created", "demodata") . '</p>
			<p>' . __("Errors encountered:", "demodata") . '</p>
			<ul>
			' . $error . '
			</ul>
			</div>
			';
			
		}
	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo users
function demodata_create_users()
{
	global $wpdb;
	global $current_site;
	$domain = '';
	if( is_object( $current_site ) ) {
		$domain = $current_site->domain;
	}
	else {
		$domain = demodata_blog_domain();
	}
	if ( $domain == '' ) {
		$domain = $_SERVER['SERVER_NAME'];
	}
	// if the domain does not have a suffix (for example "localhost" put a .com at the end to pass WordPress email checking
	if (strpos($domain, ".") === false) $domain .= ".com";
	
	// get the users settings
	$users = @$_POST["users"] == "" ? 100 : (int)$_POST["users"];
	$users = $users > 1000 ? $users = 1000 : $users = $users;
	$useremailtemplate = @$_POST["useremailtemplate"] == "" ? "demouser[x]@" . $domain : $_POST["useremailtemplate"];		
	
	// check all the settings
	if (
		$users != "" &&
		$useremailtemplate != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		// detect BuddyPress
		$buddypress = defined( 'BP_ROOT_BLOG' );
	
		// turn off new registration notifications for WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
			$registrationnotification = get_site_option("registrationnotification");
			update_site_option("registrationnotification", "no");
		}

		$userx = $wpdb->get_var("select count(ID) from ".$wpdb->users.";");
		$created = 0;
		
		// loop the number of required users
		for($u = 0; $u < $users; $u++)
		{
			$userx++;
			
			// generate the details for this user
			// get a random name
			$firstname = demodata_firstname();
			$lastname = demodata_lastname();
			$username = $firstname . $lastname;
			$email = str_replace("[x]", $userx, $useremailtemplate);
			$random_password = wp_generate_password( 12, false );
			
			if ( email_exists( $email ) ) {
			
				$error .= "<li>Email exists: " . $email . "</li>";
			
			} else {
			
				// check the user can be created
				//$id = wp_create_user($username, $random_password, $email);

				// check the user can be created
				$id = wp_insert_user(array(
					'user_login' => $username
					,'first_name' => $firstname
					,'last_name' => $lastname
					,'user_pass' => $random_password
					,'user_email' => $email
					,'nickname' => $username
					,'display_name' => $firstname
				)) ;


				if ($id == 0 || is_array($id))
				{
					$error .= "<li>Error creating user " . $userx;
					if (is_array($id)) {
						$error .= ": " . $id[0][0];
					}
					$error .= "</li>";
					$userx--;
					// break out of this loop
					break;
				} else {
					$created++;
					$userdetails["ID"] = $id;
					if ($buddypress)
					{
						$userdetails["user_nicename"] = strtolower($firstname . $lastname);
						$userdetails["display_name"] = ucfirst($firstname) . " " . ucfirst($lastname);
						// set XProfile full name
						xprofile_set_field_data(1, $id, ucfirst($firstname) . " " . ucfirst($lastname));
					} else {
						$userdetails["user_nicename"] = ucfirst($firstname) . ucfirst($lastname);
						$userdetails["display_name"] = ucfirst($firstname);
					}
					wp_update_user($userdetails);
				}
			}
		}
		
		$success = false;
		if ($created == $users) {
			$success = true;
		}
		
		// turn registration notification back on for WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
			update_site_option("registrationnotification", $registrationnotification);
		}
	
		if ($success)
		{
		
			echo '
			<div class="demodatasuccess">
			<p>' . $created . " " . __("demo users created", "demodata") . '</p>
			</div>
			';
			
		} else {
		
			echo '
			<div class="demodataerror">
			<p>' . $created . ' ' . __("demo users created", "demodata") . '</p>
			<p>' . __("Errors encountered:", "demodata") . '</p>
			<ul>
			' . $error . '
			</ul>
			</div>
			';
			
		}
	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo blogs
function demodata_create_blogs()
{
	// if this is WPMU/MultiSite
	if (demodata_is_multisite() || demodata_is_mu()) {

		global $wpdb;
		global $current_site;
		
		// get the blog settings
		$bloguserstoprocess = @$_POST["bloguserstoprocess"] == "" ? 1 : (int)$_POST["bloguserstoprocess"];
		$membershiptype = @$_POST["membershiptype"] == "" ? 1 : (int)$_POST["membershiptype"];
		$maxblogsperuser = @$_POST["maxblogsperuser"] == "" ? 1 : (int)$_POST["maxblogsperuser"];
		$maxblogsperuser = $maxblogsperuser > 5 ? $maxblogsperuser = 5 : $maxblogsperuser = $maxblogsperuser;
		$blogpath = @$_POST["blogpath"] == "" ? "/" : $_POST["blogpath"];
		
		// check all the settings
		if (
			$bloguserstoprocess != "" &&
			$membershiptype != "" && 
			$maxblogsperuser != "" &&
			$blogpath != ""
		)
		{
			$go = true;
		} else {
			$go = false;
		}
		
		// if the settings are OK
		if ($go)
		{
			
			// turn off new registration notifications
			$registrationnotification = get_site_option("registrationnotification");
			update_site_option("registrationnotification", "no");
		
			$success = true;
			$blogx = 0;
			$usersx = 0;
			$userids = "";
					
			// set the minimum number of blogs
			if ($membershiptype == 1)
			{
				$minblogs = 1;
			} else {
				$minblogs = 0;
			}
			
			// get users
			$sql = "select id from " . $wpdb->users . " where id > 0;";
			$users = $wpdb->get_results($sql);
			
			// get highest blog id
			$sql = "select max(blog_id) from " . $wpdb->blogs . ";";
			$blogid = (int)$wpdb->get_var($sql) + 1;

			// loop users
			foreach($users as $user)
			{
			
				// get the current blogs for this user
				$userblogs = get_blogs_of_user($user->id);

				// if the user has no blogs, or is just a member of the main blog
				if (count($userblogs) == 0 || (count($userblogs) == 1 && $userblogs[1]->userblog_id == "1"))
				{
			
					// get a random number of blogs
					$blogs = rand($minblogs, $maxblogsperuser);
					
					// loop the number of required blogs
					for($b = 0; $b < $blogs; $b++)
					{
					
						$blogx++;
						
						// get a random blogname
						$blogname = demodata_blogname();
						$blogdomain = "demoblog" . $blogid;

						// check the blog can be created
						if (!demodata_create_blog($blogid, $blogdomain, $blogpath, $blogname, $user->id))
						{
							$success = false;
							$error .= "<li>Error creating blog " . $blogid . '</li>';
							$blogx--;
							// break out of this loop
							break;
						}
						
						$blogid++;
						
					}
					
					$userids .= $user->id . ", ";
					
					$usersx++;
					
					if ($bloguserstoprocess == $usersx)
					{
						break;
					}
				}
			}
			
			// turn registration notification back on
			update_site_option("registrationnotification", $registrationnotification);
		
			if ($success)
			{
			
				echo '
				<div class="demodatasuccess">
				<p>' . $blogx . " " . __("demo blogs created", "demodata") . '</p>
				<p>' . __("Blogs created for User IDs:", "demodata") . " " . trim(trim($userids), ",") . '</p>
				</div>
				';
				
			} else {
			
				echo '
				<div class="demodataerror">
				<p>' . $blogx . ' ' . __("demo blogs created", "demodata") . '</p>
				<p>' . __("Errors encountered:", "demodata") . '</p>
				<ul>
				' . $error . '
				</ul>
				</div>
				';
				
			}
		} else {
		
			echo '
			<div class="demodataerror">
			<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
			</div>
			';
		
		}
	} else {
		echo '
		<div class="demodataerror">
		<p>' . __("This site does not support multiple blogs.", "demodata") . '</p>
		</div>
		';
	}
}

// create demo categories
function demodata_create_categories()
{
	global $wpdb;
	global $current_site;
	
	// get the categories and tags settings
	$maxblogcategories = @$_POST["maxblogcategories"] == "" ? 10 : (int)$_POST["maxblogcategories"];
	$maxblogcategories = $maxblogcategories > 25 ? $maxblogcategories = 25 : $maxblogcategories = $maxblogcategories;
	
	// check all the settings
	if (
		$maxblogcategories != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		$categoryx = 0;
		
		// if this is WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
		
			// get blogs
			$sql = "select blog_id, domain from " . $wpdb->blogs . ";";
			$blogs = $wpdb->get_results($sql);
			
			// loop blogs
			foreach($blogs as $blog)
			{
			
				// switch to this blog
				switch_to_blog($blog->blog_id);
			
				// get a random number of blog categories
				$categories = rand(0, $maxblogcategories);
				
				// loop the number of required categories
				for($c = 0; $c < $categories; $c++)
				{
				
					$categoryx++;

					// see if the category can be inserted
					if (!demodata_create_category($blog->domain, $categoryx))
					{
						$categoryx--;
					}				
				}
				
				// switch back to the main blog
				restore_current_blog();
			}
		} else {
			
			// get a random number of blog categories
			$categories = rand(0, $maxblogcategories);
			
			$domain = '';
			if( is_object( $current_site ) ) {
				$domain = $current_site->domain;
			}
			else {
				$domain = demodata_blog_domain();
			}
			if ( $domain == '' ) {
				$domain = $_SERVER['SERVER_NAME'];
			}
			
			// loop the number of required categories
			for($c = 0; $c < $categories; $c++)
			{
			
				$categoryx++;

				// see if the category can be inserted
				if (!demodata_create_category($domain, $categoryx))
				{
					$categoryx--;
				}				
			}
			
		}
	
		echo '
		<div class="demodatasuccess">
		<p>' . $categoryx . " " . __("demo categories created", "demodata") . '</p>
		</div>
		';

	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo posts
function demodata_create_posts()
{
	global $wpdb;
	global $current_site;
	
	// get the post settings
	$maxblogposts = @$_POST["maxblogposts"] == "" ? 50 : (int)$_POST["maxblogposts"];
	$maxblogposts = $maxblogposts > 100 ? $maxblogposts = 100 : $maxblogposts = $maxblogposts;
	$maxpostlength = @$_POST["maxpostlength"] == "" ? 10 : (int)$_POST["maxpostlength"];
	$maxpostlength = $maxpostlength > 50 ? $maxpostlength = 50 : $maxpostlength = $maxpostlength;
	$maxpostlength = $maxpostlength < 1 ? $maxpostlength = 1 : $maxpostlength = $maxpostlength;
	
	// check all the settings
	if (
		$maxblogposts != "" &&
		$maxpostlength != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		$postx = 0;
		
		// if this is WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
		
			// get blogs
			$sql = "select blog_id, domain from " . $wpdb->blogs . ";";
			$blogs = $wpdb->get_results($sql);
			
			// loop blogs
			foreach($blogs as $blog)
			{
			
				// switch to this blog
				switch_to_blog($blog->blog_id);
			
				// get a random number of blog posts
				$posts = rand(0, $maxblogposts);
				
				// loop the number of required posts
				for($p = 0; $p < $posts; $p++)
				{
				
					$postx++;

					// see if the post can be inserted
					if (!demodata_create_post($blog->domain, $maxpostlength, $postx))
					{
						$postx--;
					}				
				}
				
				// switch back to the main blog
				restore_current_blog();
			}
		} else {
		
			// get a random number of blog posts
			$posts = rand(0, $maxblogposts);
			
			// loop the number of required posts
			for($p = 0; $p < $posts; $p++)
			{
			
				$postx++;

				$domain = '';
				if( is_object( $current_site ) ) {
					$domain = $current_site->domain;
				}
				else {
					$domain = demodata_blog_domain();
				}
				if ( $domain == '' ) {
					$domain = $_SERVER['SERVER_NAME'];
				}
				// see if the post can be inserted
				if (!demodata_create_post($domain, $maxpostlength, $postx))
				{
					$postx--;
				}				
			}
		
		}
		
		echo '
		<div class="demodatasuccess">
		<p>' . $postx . " " . __("demo posts created", "demodata") . '</p>
		</div>
		';

	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo pages
function demodata_create_pages()
{
	global $wpdb;
	global $current_site;

	// get the pages settings
	$maxpages = @$_POST["maxpages"] == "" ? 25 : (int)$_POST["maxpages"];
	$maxpages = $maxpages > 25 ? $maxpages = 25 : $maxpages = $maxpages;
	$maxtoppages = @$_POST["maxtoppages"] == "" ? 5 : (int)$_POST["maxtoppages"];
	$maxtoppages = $maxtoppages > 5 ? $maxtoppages = 5 : $maxtoppages = $maxtoppages;
	$maxpageslevels = @$_POST["maxpageslevels"] == "" ? 3 : (int)$_POST["maxpageslevels"];
	$maxpageslevels = $maxpageslevels > 3 ? $maxpageslevels = 3 : $maxpageslevels = $maxpageslevels;
	$maxpagelength = @$_POST["maxpagelength"] == "" ? 10 : (int)$_POST["maxpagelength"];
	$maxpagelength = $maxpagelength > 10 ? $maxpagelength = 10 : $maxpagelength = $maxpagelength;
	
	// check all the settings
	if (
		$maxpages != ""
		&& $maxtoppages != ""
		&& $maxpageslevels != ""
		&& $maxpagelength != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		$pagex = 0;
		
		// if this is WPMU
		if (demodata_is_multisite() || demodata_is_mu()) {
		
			// get blogs
			$sql = "select blog_id, domain from " . $wpdb->blogs . ";";
			$blogs = $wpdb->get_results($sql);
			
			// loop blogs
			foreach($blogs as $blog)
			{
			
				// switch to this blog
				switch_to_blog($blog->blog_id);
				
				// get a random number of top pages
				$toppages = rand(1, $maxtoppages);
				
				// loop the number of top pages
				for($p = 0; $p < $toppages; $p++)
				{
					$pagex++;
					
					$id = demodata_create_page($blog->domain, 0, $maxpagelength, $pagex);
					
					if (!$id)
					{
						$pagex--;
					} else {
						// add the page id to the array
						$pageids[0][] = $id;
					}
				}
				
				// get random number of sublevels
				$levels = rand(1, $maxpageslevels);
				
				// if the levels is greater than 1
				if ($levels > 1 && $pageids[0] && count($pageids[0]) > 0)
				{
					// loop the top level pages
					foreach($pageids[0] as $pageid)
					{
						$pagex++;
					
						$id = demodata_create_page($blog->domain, $pageid, $maxpagelength, $pagex);
						
						if (!$id)
						{
							$pagex--;
						} else {
							// add the page id to the array
							$pageids[1][] = $id;
						}
					}
				}
				
				// if the levels is greater than 2
				if ($levels > 2 && $pageids[1] && count($pageids[1]) > 0)
				{
					// loop the level 1 pages
					foreach($pageids[1] as $pageid)
					{
						$pagex++;
					
						$id = demodata_create_page($blog->domain, $pageid, $maxpagelength, $pagex);
						
						if (!$id)
						{
							$pagex--;
						}
					}
				}
				
				// switch back to the main blog
				restore_current_blog();
			}
		} else {
		
			// get a random number of top pages
			$toppages = rand(1, $maxtoppages);
			
			$domain = '';
			if( is_object( $current_site ) ) {
				$domain = $current_site->domain;
			}
			else {
				$domain = demodata_blog_domain();
			}
			if ( $domain == '' ) {
				$domain = $_SERVER['SERVER_NAME'];
			}


			// loop the number of top pages
			for($p = 0; $p < $toppages; $p++)
			{
				$pagex++;

				$id = demodata_create_page($domain, 0, $maxpagelength, $pagex);
				
				if (!$id)
				{
					$pagex--;
				} else {
					// add the page id to the array
					$pageids[0][] = $id;
				}
			}
			
			// get random number of sublevels
			$levels = rand(1, $maxpageslevels);
			
			// if the levels is greater than 1
			if ($levels > 1 && $pageids[0] && count($pageids[0]) > 0)
			{
				// loop the top level pages
				foreach($pageids[0] as $pageid)
				{
					$pagex++;
				
					$id = demodata_create_page($domain, $pageid, $maxpagelength, $pagex);
					
					if (!$id)
					{
						$pagex--;
					} else {
						// add the page id to the array
						$pageids[1][] = $id;
					}
				}
			}

			// if the levels is greater than 2
			if ($levels > 2 && $pageids[1] && count($pageids[1]) > 0)
			{
				// loop the level 1 pages
				foreach($pageids[1] as $pageid)
				{
					$pagex++;

					$id = demodata_create_page($domain, $pageid, $maxpagelength, $pagex);
					
					if (!$id)
					{
						$pagex--;
					}
				}
			}
		
		}
		
		echo '
		<div class="demodatasuccess">
		<p>' . $pagex . " " . __("demo pages created", "demodata") . '</p>
		</div>
		';
	
	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo comments
function demodata_create_comments()
{
	global $wpdb;
	global $current_site;
	
	// get the comments settings
	$maxcomments = @$_POST["maxcomments"] == "" ? 50 : (int)$_POST["maxcomments"];
	$maxcomments = $maxcomments > 50 ? $maxcomments = 50 : $maxcomments = $maxcomments;
	
	// check all the settings
	if (
		$maxcomments != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		$commentx = 0;
		
		// if this is WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
		
			// get blogs
			$sql = "select blog_id, domain from " . $wpdb->blogs . ";";
			$blogs = $wpdb->get_results($sql);
			
			// loop blogs
			foreach($blogs as $blog)
			{
			
				// switch to this blog
				switch_to_blog($blog->blog_id);
			
				// get posts
				$sql = "select id from " . $wpdb->posts . ";";
				$posts = $wpdb->get_results($sql);
				
				// loop posts
				foreach($posts as $post)
				{
				
					// get a random number of comments
					$comments = rand(0, $maxcomments);
					
					// loop the number of required comments
					for($c = 0; $c < $comments; $c++)
					{
						$commentx++;
						
						// see if the comment can be inserted
						if (!demodata_create_comment($blog->domain, $post->id, $commentx))
						{
							// continue
							$commentx--;
						}
					}
					
				}
				
				// switch back to the main blog
				restore_current_blog();
			}
		} else {
			
			// get posts
			$sql = "select id from " . $wpdb->posts . ";";
			$posts = $wpdb->get_results($sql);
			
			// loop posts
			foreach($posts as $post)
			{
			
				// get a random number of comments
				$comments = rand(0, $maxcomments);
				
				// loop the number of required comments
				for($c = 0; $c < $comments; $c++)
				{
					$commentx++;
					
					$domain = '';
					if( is_object( $current_site ) ) {
						$domain = $current_site->domain;
					}
					else {
						$domain = demodata_blog_domain();
					}
					if ( $domain == '' ) {
						$domain = $_SERVER['SERVER_NAME'];
					}
					
					// see if the comment can be inserted
					if (!demodata_create_comment($domain, $post->id, $commentx))
					{
						// continue
						$commentx--;
					}
				}
				
			}
			
		}
		
		echo '
		<div class="demodatasuccess">
		<p>' . $commentx . ' ' . __("demo comments created", "demodata") . '</p>
		</div>
		';

	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo links
function demodata_create_links()
{
	global $wpdb;
	global $current_site;
	
	// get the links settings
	$maxbloglinks = @$_POST["maxbloglinks"] == "" ? 25 : (int)$_POST["maxbloglinks"];
	$maxbloglinks = $maxbloglinks > 100 ? $maxbloglinks = 100 : $maxbloglinks = $maxbloglinks;
	
	// check all the settings
	if (
		$maxbloglinks != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
	
		$linkx = 0;
		
		// if this is WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
		
			// get blogs
			$sql = "select blog_id, domain from " . $wpdb->blogs . ";";
			$blogs = $wpdb->get_results($sql);
			
			// loop blogs
			foreach($blogs as $blog)
			{
			
				// switch to this blog
				switch_to_blog($blog->blog_id);
			
				// get a random number of bookmarks
				$links = rand(0, $maxbloglinks);
				
				// loop the number of required bookmarks
				for($l = 0; $l < $links; $l++)
				{
					$linkx++;
					if (!demodata_create_link($blog->domain, $linkx))
					{
						// continue
						$linkx--;
					}
				}
				
				// switch back to the main blog
				restore_current_blog();
			}

		} else {
		
			// get a random number of bookmarks
			$links = rand(0, $maxbloglinks);
			
			$domain = '';
			if( is_object( $current_site ) ) {
				$domain = $current_site->domain;
			}
			else {
				$domain = demodata_blog_domain();
			}
			if ( $domain == '' ) {
				$domain = $_SERVER['SERVER_NAME'];
			}
			
			// loop the number of required bookmarks
			for($l = 0; $l < $links; $l++)
			{
				$linkx++;
				if (!demodata_create_link($domain, $linkx))
				{
					// continue
					$linkx--;
				}
			}
		
		}
		
		echo '
		<div class="demodatasuccess">
		<p>' . $linkx . " " . __("demo links created", "demodata") . '</p>
		</div>
		';

	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo groups
function demodata_create_groups()
{
	// detect BuddyPress
	$buddypress = defined( 'BP_ROOT_BLOG' );
	
	if ($buddypress)
	{

		global $wpdb;
		global $current_site;
		
		// get groups settings
		$maxgroups = @$_POST["maxgroups"] == "" ? 250 : (int)$_POST["maxgroups"];
		$maxgroups = $maxgroups > 500 ? $maxgroups = 500 : $maxgroups = $maxgroups;
		$maxgroupmembership = @$_POST["maxgroupmembership"] == "" ? 25 : (int)$_POST["maxgroupmembership"];
		$maxgroupmembership = $maxgroupmembership > 50 ? $maxgroupmembership = 50 : $maxgroupmembership = $maxgroupmembership;
		$maxgroupwire = @$_POST["maxgroupwire"] == "" ? 10 : (int)$_POST["maxgroupwire"];
		$maxgroupwire = $maxgroupwire > 25 ? $maxgroupwire = 25 : $maxgroupwire = $maxgroupwire;
		
		// check all the settings
		if (
			$maxgroups != "" &&
			$maxgroupmembership != ""
		)
		{
			$go = true;
		} else {
			$go = false;
		}
		
		// if the settings are OK
		if ($go)
		{
		
			$groupx = 0;
			$memberx = 0;
			$postsx = 0;
			$success = true;
			
			// get a random number of groups
			$groups = rand(1, $maxgroups);
		
			// loop the number of required groups
			for($g = 0; $g < $groups; $g++)
			{
				$groupx++;
				
				// get a random group name
				$groupname = demodata_groupname();
				
				$newgroupid = demodata_create_group( $groupname, $groupsx );
				
				// save the group
				if ( !$newgroupid )
				{
				
					$success = false;
					$error .= "<li>Error creating group " . $groupx . '</li>';
					$groupx--;
					// break out of this loop
					break;
					
				} else {
				
					$memberx++;

					// get a random number of group members
					$members = rand(1, $maxgroupmembership);
					
					$admin = demodata_create_group_member( $newgroupid, 1, $memberx );
					
					// if the admin member could not be saved
					if ( !$admin )
					{
					
						$success = false;
						$error .= "<li>Error creating group member " . $memberx . '</li>';
						$memberx--;
						// break out of this loop
						break;
						
					} else {
					
						// loop the number of required members of this group
						for($m = 0; $m < $members; $m++)
						{		
					
							$memberx++;
							
							// if the member could not be saved
							if ( !demodata_create_group_member( $newgroupid, 0, $membersx ) )
							{
								$success = false;
								$error .= "<li>Error creating group member " . $memberx . '</li>';
								$memberx--;
								// break out of this loop
								break;
							}
						}
					}
					
					$current_id = $bp->loggedin_user->id;
					
					// loop the number of required group wire messages for this group
					for($w = 0; $w < $maxgroupwire; $w++)
					{		
				
						$postsx++;
						
						// if the member could not be saved
						if ( !demodata_create_group_wire_message( $newgroupid ) )
						{
							$success = false;
							$error .= "<li>Error creating group wire post for group ID " . $newgroupid . '</li>';
							$postsx--;
							// break out of this loop
							break;
						}
					}
					
					$bp->loggedin_user->id = $current_id;
				}
			}
		
			if ($success)
			{
			
				echo '
				<div class="demodatasuccess">
				<p>' . $groupx . " " . __("demo groups created", "demodata") . '</p>
				<p>' . $memberx . ' ' . __("demo group members created", "demodata") . '</p>
				<p>' . $postsx . ' ' . __("demo group wire posts created", "demodata") . '</p>
				</div>
				';
				
			} else {
			
				echo '
				<div class="demodataerror">
				<p>' . $groupx . ' ' . __("demo groups created", "demodata") . '</p>
				<p>' . $memberx . ' ' . __("demo group members created", "demodata") . '</p>
				<p>' . $postsx . ' ' . __("demo group wire posts created", "demodata") . '</p>
				<p>' . __("Errors encountered:", "demodata") . '</p>
				<ul>
				' . $error . '
				</ul>
				</div>
				';
				
			}
		} else {
		
			echo '
			<div class="demodataerror">
			<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
			</div>
			';
		
		}
	} else {
		
		echo '
		<div class="demodataerror">
		<p>' . __("BuddyPress not found.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo friends
function demodata_create_friends()
{

	// detect BuddyPress
	$buddypress = defined( 'BP_ROOT_BLOG' );
	
	if ($buddypress)
	{

		global $wpdb;
		global $current_site;
		
		// get friends settings
		$maxfriends = @$_POST["maxfriends"] == "" ? 50 : (int)$_POST["maxfriends"];
		$maxfriends = $maxfriends > 100 ? $maxfriends = 100 : $maxfriends = $maxfriends;
		
		// check all the settings
		if (
			$maxfriends != ""
		)
		{
			$go = true;
		} else {
			$go = false;
		}
		
		// if the settings are OK
		if ($go)
		{
		
			$friendsx = 0;
			$success = true;
			
			// get users
			$sql = "select id from " . $wpdb->users . " order by id;";
			$users = $wpdb->get_results($sql);

			// loop users
			foreach ($users as $user)
			{
			
				// get a random mumber of friends
				$friends = rand(1, $maxfriends);
				
				// loop the required number of friends
				for ($f = 0; $f < $friends; $f++)
				{
					$friendx++;
				
					// create the friendship
					if (!demodata_create_friendship($user->id))
					{
						$success = false;
						$error .= "<li>Error creating friendship " . $friendx . '</li>';
						$friendx--;
						break;
					}
				
				}
				
			}
		
			if ($success)
			{
			
				echo '
				<div class="demodatasuccess">
				<p>' . $friendx . " " . __("demo friendships created", "demodata") . '</p>
				</div>
				';
				
			} else {
			
				echo '
				<div class="demodataerror">
				<p>' . $friendx . ' ' . __("demo friendships created", "demodata") . '</p>
				<p>' . __("Errors encountered:", "demodata") . '</p>
				<ul>
				' . $error . '
				</ul>
				</div>
				';
				
			}
		} else {
		
			echo '
			<div class="demodataerror">
			<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
			</div>
			';
		
		}
	} else {
		
		echo '
		<div class="demodataerror">
		<p>' . __("BuddyPress not found.", "demodata") . '</p>
		</div>
		';
	
	}
}

// create demo wire messages
function demodata_create_wire()
{
	// detect BuddyPress
	$buddypress = defined( 'BP_ROOT_BLOG' );
	
	if ($buddypress)
	{

		global $wpdb;
		global $current_site;
		
		// get wire settings
		$maxwire = @$_POST["maxwire"] == "" ? 25 : (int)$_POST["maxwire"];
		$maxwire = $maxwire > 50 ? $maxwire = 50 : $maxwire = $maxwire;
		
		// check all the settings
		if (
			$maxwire != ""
		)
		{
			$go = true;
		} else {
			$go = false;
		}
		
		// if the settings are OK
		if ($go)
		{
		
			$wirex = 0;
			
			// get users
			$sql = "select id from " . $wpdb->users . " order by id;";
			$users = $wpdb->get_results($sql);
			
			$current_id = $bp->loggedin_user->id;
			
			// loop users
			foreach ($users as $user)
			{
			
				// get a random number of wire messages
				$wires = rand(0, $maxwire);
				
				// loop the number of required wires
				for($w = 0; $w < $wires; $w++)
				{
					$wirex++;
					// see if the wire can be created
					if (!demodata_create_wire_message($user->id))
					{
						// continue
						$wirex--;
					}
				}
				
			}
			
			$bp->loggedin_user->id = $current_id;
			
			echo '
			<div class="demodatasuccess">
			<p>' . $wirex . " " . __("demo wire messages created", "demodata") . '</p>
			</div>
			';

		} else {
		
			echo '
			<div class="demodataerror">
			<p>' . __("Some of your settings were not valid. Please check all the settings below.", "demodata") . '</p>
			</div>
			';
		
		}
	} else {
		
		echo '
		<div class="demodataerror">
		<p>' . __("BuddyPress not found.", "demodata") . '</p>
		</div>
		';
	
	}
}

// ======================================================
// Data creation functions
// ======================================================

// get the domain for this blog
function demodata_blog_domain()
{
	$u = get_bloginfo("wpurl");
	$u = str_replace("http://", "", $u);
	$u = str_replace("https://", "", $u);
	$parts = explode("/", $u);
	$domain = $parts[0];
	return $domain;
}

// create a bookmark
function demodata_create_link($blogdomain, $linkx)
{
	$link = array(
		'link_id' => 0,
		'link_name' => 'Bookmark ' . $linkx,
		'link_url' => 'http://' . $blogdomain . '/#bookmark' . $linkx,
		'link_rating' => 0
	);
	return wp_insert_link($link);
}

// create a comments
function demodata_create_comment($blogdomain, $postid, $commentx)
{
	$commentcontent = demodata_generate_random_text(1000);
	$time = current_time('mysql', $gmt = 0); 
	$comment = array(
	    'comment_post_ID' => $postid,
	    'comment_author' => 'Commenter '.$commentx,
	    'comment_author_email' => 'commenter@' . $blogdomain,
	    'comment_author_url' => 'http://commenter.url',
	    'comment_content' => $commentcontent,
	    'comment_type' => '',
	    'comment_parent' => 0,
	    'user_ID' => 0,
	    'comment_author_IP' => '127.0.0.1',
	    'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
	    'comment_date' => $time,
	    'comment_date_gmt' => $time,
	    'comment_approved' => 1,
	);
	return wp_insert_comment($comment);
}

// create a category
function demodata_create_category($blogdomain, $categoryx)
{
	$cat = ucfirst(strtolower(demodata_generate_random_text(20)));
	return wp_create_category( $cat );
}

// create a post
function demodata_create_post($blogdomain, $maxpostlength, $postx)
{
	// get a random user for the current blog
	$userid = demodata_random_blog_user_id();
	// generate the random post data
	$postcontent = demodata_generate_html(rand(1, $maxpostlength));
	// generate random date (thanks to derscheinwelt for this: http://wordpress.org/support/topic/plugin-demo-data-creator-random-creation-date)
	$randdate = date('Y-m-d H:i:s', strtotime( mt_rand(-1095,10).' days' . mt_rand(0,1440). 'minutes' ));
	// generate array of category ids
	$cats = demodata_random_categories($blogdomain);
	$post = array('post_status' => 'live',
	'post_date' => $randdate,
	'post_type' => 'post',
	'post_author' => $userid,
	'ping_status' => 'open',
	'post_parent' => 0,
	'menu_order' => 0,
	'to_ping' => '',
	'pinged' => '',
	'post_password' => '',
	'guid' => 'http://' . $blogdomain . '/post' . $postx,
	'post_content_filtered' => '',
	'post_excerpt' => '',
	'import_id' => 0,
	'post_status' => 'publish',
	'post_title' => 'Demo post ' . $postx,
	'post_content' => $postcontent,
	'post_excerpt' => '',
	'post_category' => $cats);
	return wp_insert_post($post);
}

// create a page
function demodata_create_page($blogdomain, $parentid, $maxpagelength, $pagex)
{
	$pagecontent = demodata_generate_html(rand(1, $maxpagelength));
	$page = array('post_status' => 'live',
	'post_type' => 'page',
	'post_author' => 1,
	'ping_status' => 'open',
	'post_parent' => $parentid,
	'menu_order' => 0,
	'to_ping' => '',
	'pinged' => '',
	'post_password' => '',
	'guid' => 'http://' . $blogdomain . '/page' . $pagex,
	'post_content_filtered' => '',
	'post_excerpt' => '',
	'import_id' => 0,
	'post_status' => 'publish',
	'post_title' => 'Demo page ' . $pagex,
	'post_content' => $pagecontent,
	'post_excerpt' => '',
	'post_category' => '');
	return wp_insert_post( $page );
}

// create a blog (taken from /wp-admin/wpmu-edit.php)
function demodata_create_blog($newid, $blogdomain, $blogpath, $blogname, $user_id)
{
	// if this is WPMU
	if ((demodata_is_multisite() || demodata_is_mu()) && $newid > 1)
	{
		global $current_site;
		global $current_user;
		global $wpdb;
		global $wp_queries;
		
		$wp_queries = str_replace($wpdb->base_prefix . "1_", $wpdb->base_prefix . $newid . "_", $wp_queries);
		$wp_queries = str_replace($wpdb->base_prefix . ($newid - 1) . "_", $wpdb->base_prefix . $newid . "_", $wp_queries);
		
		$base = $blogpath;
		
		if( strtolower( constant('VHOST') ) == 'yes' ) {
			$newdomain = $blogdomain.".".$current_site->domain;
			$path = $base;
		} else {
			$newdomain = $current_site->domain;
			$path = $base.$blogdomain;
		}
		
		// install this blog
		$meta = apply_filters('signup_create_blog_meta', array ('lang_id' => 1, 'public' => 1));
		$id = wpmu_create_blog($newdomain, $path, $blogname, $user_id , $meta, $current_site->id);

		// in case the tables haven't been created, create them
		$post_table = $wpdb->base_prefix . $id . "_posts";
		$post_table_exists = $wpdb->get_results( "show tables like '" . $post_table . "';" );
		if ( !$post_table_exists ) {
			print "<p>Table <code>" . $post_table . "</code> was not created ... attempting to create blog tables manually</p>";
			global $wp_queries;
			$wp_queries = str_replace( $wpdb->base_prefix, $wpdb->base_prefix . $id . "_", $wp_queries );
			dbDelta( $wp_queries );
			switch_to_blog($id);
			populate_options();
			restore_current_blog();
		}
		
		if( !is_wp_error($id) ) {
			if( get_user_option( $user_id, 'primary_blog' ) == 1 )
				update_user_option( $user_id, 'primary_blog', $id, true );
				
			// add the user to the blog
			add_user_to_blog($id, $user_id, 'administrator');
				
			return $id;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

// ======================================================
// BuddyPress functions
// ======================================================

// create a wire message
function demodata_create_wire_message($userid)
{
	global $bp;
	
	$message = demodata_generate_random_text(100);
	
	$bp->loggedin_user->id = demodata_random_user_id();
	
	if ( function_exists( "bp_wire_new_post" ) ) {
		return bp_wire_new_post( $userid, $message, "profile" );
	} else {
		return bp_activity_add( array(
			"action"=>"New Demo Profile Wire Message",
			"content"=>$message,
			"component"=>$bp->profile->id,
			"type"=>"activity_update",
			"item_id"=>$userid
		) );
	}
}

// create a group wire message
function demodata_create_group_wire_message($groupid)
{
	global $bp;
	
	$message = demodata_generate_random_text(100);
	
	$bp->loggedin_user->id = demodata_random_user_id();
	
	if ( function_exists( "groups_new_wire_post" ) ) {
		return groups_new_wire_post( $groupid, $message );
	} else {
		return bp_activity_add( array(
			"action"=>"New Demo Group Wire Message",
			"content"=>$message,
			"component"=>$bp->groups->id,
			"type"=>"activity_update",
			"item_id"=>$groupid
		) );
	}
}

// create a BuddyPress group
function demodata_create_group($groupname, $num)
{
	// set up the new group
	$group_obj = new BP_Groups_Group();
	$group_obj->name = $groupname;
	$group_obj->slug = sanitize_title( $groupname );
	$group_obj->description = "Demo group " . $num . " created by Demo Data Creator plugin.";
	$group_obj->status = 'public';
	$group_obj->is_invitation_only = 0;
	$group_obj->enable_wire = 1;
	$group_obj->enable_forum = 1;
	$group_obj->enable_photos = 1;
	$group_obj->photos_admin_only = 0;
	$group_obj->date_created = time();
	
	// save the group
	$saved = $group_obj->save();
	
	if ( $saved )
	{
		return $group_obj->id;
	} else {
		return false;
	}
}

// create a BuddyPress group member
function demodata_create_group_member($group_id, $admin, $num)
{	
	$user_id = demodata_random_user_id();
	return groups_join_group( $group_id, $user_id );
}

// create a BuddyPress friendship
function demodata_create_friendship($userid)
{
	return friends_add_friend($userid, demodata_random_user_id($userid), true);
}

// create a BuddyPress status update
function demodata_create_status($userid)
{
	global $bp;

	// get random status text
	$content = demodata_generate_random_text(140);

	// generate a random time between 1 year ago and now
	$time = rand(time() - (60 * 60 * 24 * 365), time());

	if ( function_exists( "bp_status_add_status" ) ) {
		return bp_status_add_status( $userid, $content, $time );
	} else {
		return bp_activity_add( array(
			"action"=>"New Demo User Status",
			"content"=>$content,
			"component"=>$bp->profile->id,
			"type"=>"profile_updated",
			"item_id"=>$userid
		) );
	}
}

// ======================================================
// Helper functions
// ======================================================

// get a random user id
function demodata_random_user_id($id = 0)
{
	global $wpdb;
	return $wpdb->get_var("select id from " . $wpdb->users . " where " . $wpdb->escape($id) . " = 0 or id <> " . $wpdb->escape($id) . " order by rand() limit 1;");
}

// get a random user id for the current blog
function demodata_random_blog_user_id($id = 0)
{
	$user_fields = array( 'ID' );
	$wp_user_query = new WP_User_Query( array( 'fields' => $user_fields ) );
	$rs = $wp_user_query->results;
	$x = array_rand($rs);
	return $rs[$x]->ID;
}

// delete demo data
function demodata_delete()
{
	global $wpdb;
	global $current_site;

	echo '
	<div class="demodatasuccess">
	<h2>' . __("Deleting demo data...", "demodata") . '</h2>
	<ul>
	';
	
	$i = 0;
	
	// delete all pages except page ID 1
	$sql = "delete from " . $wpdb->posts . " where id > 1;";
	$posts = $wpdb->query($sql);
	if ($posts === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// delete user meta
	$sql = "delete from " . $wpdb->usermeta . " where user_id > 1;";
	$users = $wpdb->query($sql);
	if ($users === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// count users
	$sql = "select count(id) from " . $wpdb->users . " where id > 1;";
	$usercount = $wpdb->get_var($sql);
	
	$i = 0;
	
	// if this is WPMU/MultiSite
	if (demodata_is_multisite() || demodata_is_mu()) {
	
		// count blogs
		$sql = "select count(blog_id) from " . $wpdb->blogs . " where blog_id > 1;";
		$blogcount = $wpdb->get_var($sql);
		
		// delete blogs
		$sql = "select blog_id from " . $wpdb->blogs . " where blog_id > 1;";
		$blogs = $wpdb->get_results($sql);
		foreach($blogs as $blog)
		{
			if (@wpmu_delete_blog($blog->blog_id, true))
			{
				$i++;
			}
		}
	
	}
	
	// delete blog 1 comments
	$sql = "delete from " . $wpdb->comments . ";";
	$comments = $wpdb->query($sql);
	if ($comments === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// delete blog 1 links
	$sql = "delete from " . $wpdb->links . ";";
	$links = $wpdb->query($sql);
	if ($links === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// delete blog 1 terms
	$sql = "delete from " . $wpdb->terms . " where term_id > 2;";
	$terms = $wpdb->query($sql);
	if ($terms === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// delete blog 1 term taxonomy
	$sql = "delete from " . $wpdb->term_taxonomy . " where term_id > 2;";
	$term_taxonomy = $wpdb->query($sql);
	if ($term_taxonomy === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// delete blog 1 term relationships
	$sql = "delete from " . $wpdb->term_relationships . " where term_taxonomy_id > 2;";
	$term_relationships = $wpdb->query($sql);
	if ($term_relationships === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// delete registration log (if the table exists)
	if ($wpdb->registration_log != "") {
		$sql = "delete from " . $wpdb->registration_log . ";";
		$registration_log = $wpdb->query($sql);
		if ($registration_log === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
	}
	
	// delete site categories (if the table exists)
	if ($wpdb->sitecategories != "") {
		$sql = "delete from " . $wpdb->sitecategories . " where cat_ID > 2;";
		$sitecategories = $wpdb->query($sql);
		if ($sitecategories === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
	}
	
	// if this is WPMU/MultiSite
	if (demodata_is_multisite() || demodata_is_mu()) {
	
		// alter auto integer
		$sql = "ALTER TABLE " . $wpdb->blogs . " AUTO_INCREMENT = 2;";
		$users = $wpdb->query($sql);

		echo '<li>' . $blogcount . ' ' . __("blogs deleted", "demodata") . '</li>
		';
	
	} else {
	
		echo '<li>' . __("Blog data deleted", "demodata") . '</li>
		';
	
	}
	
	$i = 0;
	
	// detect BuddyPress
	$buddypress = defined( 'BP_ROOT_BLOG' );
	
	if ($buddypress)
	{
		// delete activity
		$sql = "delete from " . $wpdb->base_prefix. "bp_activity;";
		$activity = $wpdb->query($sql);
		if ($activity === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete activity meta
		$sql = "delete from " . $wpdb->base_prefix. "bp_activity_meta;";
		$activity_meta = $wpdb->query($sql);
		if ($activity_meta === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}

		// delete user xprofile data
		$sql = "delete from " . $wpdb->base_prefix. "bp_xprofile_data where user_id > 1;";
		$xprofile_data = $wpdb->query($sql);
		if ($xprofile_data === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
	
		// delete group meta
		$sql = "delete from " . $wpdb->base_prefix. "bp_groups_groupmeta;";
		$groups_groupmeta = $wpdb->query($sql);
		if ($groups_groupmeta === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete group membership
		$sql = "delete from " . $wpdb->base_prefix. "bp_groups_members;";
		$groups_members = $wpdb->query($sql);
		if ($groups_members === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
	
		// delete groups
		$sql = "delete from " . $wpdb->base_prefix. "bp_groups;";
		$groups = $wpdb->query($sql);
		if ($groups === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete friendships
		$sql = "delete from " . $wpdb->base_prefix. "bp_friends;";
		$friends = $wpdb->query($sql);
		if ($friends === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete notifications
		$sql = "delete from " . $wpdb->base_prefix. "bp_notifications;";
		$notifications = $wpdb->query($sql);
		if ($notifications === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete messages recipients
		$sql = "delete from " . $wpdb->base_prefix. "bp_messages_recipients;";
		$messages_recipients = $wpdb->query($sql);
		if ($messages_recipients === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete messages notices
		$sql = "delete from " . $wpdb->base_prefix. "bp_messages_notices;";
		$messages_notices = $wpdb->query($sql);
		if ($messages_notices === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		// delete messages
		$sql = "delete from " . $wpdb->base_prefix. "bp_messages_messages;";
		$messages_messages = $wpdb->query($sql);
		if ($messages_messages === false) {
			echo '<li>Error with SQL: ' . $sql . '</li>';
		}
		
		echo '<li>' . __("BuddyPress data deleted", "demodata") . '</li>
		';
	}
	
	// delete users
	$sql = "delete from " . $wpdb->users . " where id > 1;";
	$users = $wpdb->query($sql);
	if ($users === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}
	
	// alter auto integer
	$sql = "ALTER TABLE " . $wpdb->users . " AUTO_INCREMENT = 2;";
	$users = $wpdb->query($sql);
	if ($users === false) {
		echo '<li>Error with SQL: ' . $sql . '</li>';
	}

	echo '<li>' . $usercount . ' ' . ("users deleted") . '</li>
	';
	
	echo '
	</ul>
	</div>
	';
}

// watch for a form action
function demodata_watch_form()
{
	// if submitting form
	if (is_array($_POST) && count($_POST) > 0 && isset($_POST["action"]))
	{	
		set_time_limit(300);
	
		if ($_POST["action"] == "create")
		{
			demodata_create();
		} else {
			echo '
			<!-- No POST action of "CREATE" -->
			';
		}
		
		if ($_POST["action"] == "delete")
		{
			demodata_delete();
		}
	} else {
		echo '
		<!-- No POST action -->
		';
	}
}

// ======================================================
// Admin forms
// ======================================================

// write out the form
function demodata_form()
{
	global $current_site;
	global $wpdb;

	// detect BuddyPress
	$buddypress = defined( 'BP_ROOT_BLOG' );

	echo '
	<div class="wrap">
	';
	
	demodata_wp_plugin_standard_header( "GBP", "Demo Data Creator", "Chris Taylor", "chris@stillbreathing.co.uk", "http://wordpress.org/extend/plugins/demo-data-creator/" );
	
	demodata_watch_form();
	
	$formpage = "tools";
	// WP3
	if (demodata_is_multisite()) {
		$formpage = "ms-admin";
	} else if (demodata_is_mu()) {
		$formpage = "wpmu-admin";
	}

	$domain = '';
	if( is_object( $current_site ) ) {
		$domain = $current_site->domain;
	}
	else {
		$domain = demodata_blog_domain();
	}
	if ( $domain == '' ) {
		$domain = $_SERVER['SERVER_NAME'];
	}

	echo '
	
		<h2>' . __("Create demo data", "demodata") . '</h2>
		<p>' . __("Use the form below to create multiple test users and blog in this WPMU system. Warning: this may take some time if you are creating a lot of data.", "demodata") . '</p>

		<form action="' . $formpage . '.php?page=demodata_form&amp;create=users" method="post" class="demodata" id="createusersform">
		<fieldset>

			<h4>' . __("Users", "demodata") . '</h4>
			
			<p><label for="users">' . __("Number of users (max 1000)", "demodata") . '</label>
			<input type="text" name="users" id="users" value="100" /></p>
			
			<p><label for="useremailtemplate">' . __("User email template (with [x] for the user ID)", "demodata") . '</label>
			<input type="text" name="useremailtemplate" id="useremailtemplate" value="demouser[x]@' . $domain . '" class="text" /></p>
			
			<p><label for="createusers">' . __("Create users", "demodata") . '</label>
			<input type="hidden" name="create" value="users" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createusers">' . __("Create users", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createusersoutput"></div>
		';
		
		$perblog = "";
		
		// if this is WPMU/MultiSite
		if (demodata_is_multisite() || demodata_is_mu()) {
		
		$perblog = __(". This is per blog.", "demodata");
		
		echo '
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=users" method="post" class="demodata" id="createblogsform">
		<fieldset>
		
			<h4>' . __("Blogs", "demodata") . '</h4>
			
			<p>' . __("Adding blogs takes a lot of processing and is prone to errors. This function will not add any blogs for users who already have blogs, so I recommend you add blogs in batches of ten using the 'Number of users to process' option below. Running this function repeatedly will create blogs for the next ten users in the system who do not have any blogs yet.", "demodata") . '</p>
			
			<p><label for="bloguserstoprocess">' . __("Number of users to process", "demodata") . '</label>
			<input type="text" name="bloguserstoprocess" id="bloguserstoprocess" value="10" /></p>
			
			<p><label for="maxblogsperuser">' . __("Maximum number of blogs per user (max 5)", "demodata") . '</label>
			<input type="text" name="maxblogsperuser" id="maxblogsperuser" value="1" /></p>
			
			<p><label for="membershiptype1">' . __("All users must have at least one blog", "demodata") . '</label>
			<input type="radio" name="membershiptype" id="membershiptype1" value="1" checked="checked" /></p>
			
			<p><label for="membershiptype2">' . __("Users may have zero or more blogs", "demodata") . '</label>
			<input type="radio" name="membershiptype" id="membershiptype2" value="2" /></p>
			
			<p><label for="blogpath">' . __("Default path", "demodata") . '</label>
			<input type="text" name="blogpath" id="blogpath" value="/" /></p>
			
			<p><label for="createblogs">' . __("Create blogs", "demodata") . '</label>
			<input type="hidden" name="create" value="blogs" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createblogs">' . __("Create blogs", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createblogsoutput"></div>
		';
		
		}
		
		echo '
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=categories" method="post" class="demodata" id="createcategoriesform">
		<fieldset>
		
			<h4>' . __("Categories", "demodata") . '</h4>
			
			<p><label for="maxblogcategories">' . __("Maximum number of categories (max 25)", "demodata") . $perblog . '</label>
			<input type="text" name="maxblogcategories" id="maxblogcategories" value="10" /></p>
			
			<p><label for="createcategories">' . __("Create categories", "demodata") . '</label>
			<input type="hidden" name="create" value="categories" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createcategories">' . __("Create categories", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createcategoriesoutput"></div>
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=posts" method="post" class="demodata" id="createpostsform">
		<fieldset>
		
			<h4>' . __("Posts", "demodata") . '</h4>
			
			<p><label for="maxblogposts">' . __("Maximum number of posts (max 100)", "demodata") . $perblog . '</label>
			<input type="text" name="maxblogposts" id="maxblogposts" value="50" /></p>
			
			<p><label for="maxpostlength">' . __("Maximum number of blog post paragraphs (min 1, max 50)", "demodata") . '</label>
			<input type="text" name="maxpostlength" id="maxpostlength" value="10" /></p>
			
			<p><label for="createposts">' . __("Create posts", "demodata") . '</label>
			<input type="hidden" name="create" value="posts" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createposts">' . __("Create posts", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createpostsoutput"></div>
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=pages" method="post" class="demodata" id="createpagesform">
		<fieldset>
		
			<h4>' . __("Pages", "demodata") . '</h4>
			
			<p><label for="maxpages">' . __("Maximum number of pages (max 50)", "demodata") . $perblog . '</label>
			<input type="text" name="maxpages" id="maxpages" value="25" /></p>
			
			<p><label for="maxtoppages">' . __("Maximum number of top-level pages (max 10)", "demodata") . $perblog . '</label>
			<input type="text" name="maxtoppages" id="maxtoppages" value="5" /></p>
			
			<p><label for="maxpageslevels">' . __("Maximum number of level to nest pages (max 5)", "demodata") . '</label>
			<input type="text" name="maxpageslevels" id="maxpageslevels" value="3" /></p>
			
			<p><label for="maxpagelength">' . __("Maximum number of blog page paragraphs (min 1, max 50)", "demodata") . '</label>
			<input type="text" name="maxpagelength" id="maxpagelength" value="10" /></p>
			
			<p><label for="createpages">' . __("Create pages", "demodata") . '</label>
			<input type="hidden" name="create" value="pages" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createpages">' . __("Create pages", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createpagesoutput"></div>
			
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=comments" method="post" class="demodata" id="createcommentsform">
		<fieldset>
		
				<h4>' . __("Comments", "demodata") . '</h4>
			
			<p><label for="maxcomments">' . __("Maximum number of comments per post (max 50)", "demodata") . '</label>
			<input type="text" name="maxcomments" id="maxcomments" value="10" /></p>
			
			<p><label for="createcomments">' . __("Create comments", "demodata") . '</label>
			<input type="hidden" name="create" value="comments" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createcomments">' . __("Create comments", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createcommentsoutput"></div>
			
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=links" method="post" class="demodata" id="createlinksform">
		<fieldset>
		
			<h4>' . __("Links", "demodata") . '</h4>
			
			<p><label for="maxbloglinks">' . __("Maximum number of links in blogroll (max 100)", "demodata") . '</label>
			<input type="text" name="maxbloglinks" id="maxbloglinks" value="25" /></p>
			
			<p><label for="createlinks">' . __("Create links", "demodata") . '</label>
			<input type="hidden" name="create" value="links" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createlinks">' . __("Create links", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createlinksoutput"></div>
		';
		
		// if Buddypress has been detected
		if ($buddypress)
		{
		echo '	
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=groups" method="post" class="demodata" id="creategroupsform">
		<fieldset>
		
			<h4>' . __("BuddyPress Groups", "demodata") . '</h4>
			
			<p><label for="maxgroups">' . __("Number of groups (maximum 500)", "demodata") . '</label>
			<input type="text" name="maxgroups" id="maxgroups" value="100" /></p>
			
			<p><label for="maxgroupmembership">' . __("Max. number of groups per user (maximum 50)", "demodata") . '</label>
			<input type="text" name="maxgroupmembership" id="maxgroupmembership" value="25" /></p>
			
			<p><label for="maxgroupwire">' . __("Max. number of wire messages per group (maximum 25)", "demodata") . '</label>
			<input type="text" name="maxgroupwire" id="maxgroupwire" value="10" /></p>
			
			<p><label for="creategroups">' . __("Create groups", "demodata") . '</label>
			<input type="hidden" name="create" value="groups" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="creategroups">' . __("Create groups", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="creategroupsoutput"></div>
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=wire" method="post" class="demodata" id="createwireform">
		<fieldset>
		
			<h4>' . __("BuddyPress Wire Messages", "demodata") . '</h4>
			
			<p><label for="maxwire">' . __("Number of wire messages per user (maximum 50)", "demodata") . '</label>
			<input type="text" name="maxwire" id="maxwire" value="25" /></p>
			
			<p><label for="createwire">' . __("Create wire messages", "demodata") . '</label>
			<input type="hidden" name="create" value="wire" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createwire">' . __("Create wire", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createwireoutput"></div>
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=status" method="post" class="demodata" id="createstatusform">
		<fieldset>
		
			<h4>' . __("BuddyPress Member Statuses", "demodata") . '</h4>
			
			<p><label for="maxstatus">' . __("Number of status messages per user (maximum 50)", "demodata") . '</label>
			<input type="text" name="maxstatus" id="maxstatus" value="25" /></p>
			
			<p><label for="createstatus">' . __("Create status messages", "demodata") . '</label>
			<input type="hidden" name="create" value="status" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createstatus">' . __("Create statuses", "demodata") . '</button></p>
			
		</fieldset>
		</form>
		
		<div id="createstatusoutput"></div>
		
		<form action="' . $formpage . '.php?page=demodata_form&amp;create=friends" method="post" class="demodata" id="createfriendsform">
		<fieldset>
			
			<h4>' . __("BuddyPress Friends", "demodata") . '</h4>
			
			<p><label for="maxfriends">' . __("Number of friends per user (maximum 100)", "demodata") . '</label>
			<input type="text" name="maxfriends" id="maxfriends" value="50" /></p>
		
			<p>' . __("Creating this demo data may take several minutes. Please be patient, and only click the button below once.", "demodata") . '</p>
			
			<p><label for="createfriends">' . __("Create friends", "demodata") . '</label>
			<input type="hidden" name="create" value="friends" />
			<input type="hidden" name="action" value="create" />
			<button type="submit" class="button demodatabutton" id="createfriends">' . __("Create friends", "demodata") . '</button></p>
		
		</fieldset>
		</form>
		
		<div id="createfriendsoutput"></div>
		
		';
		}
		
		echo '		
		<h3>Delete demo data</h3>
		
		<form action="' . $formpage . '.php?page=demodata_form" method="post" class="demodata" id="deleteform">
		<fieldset>
		
		';
		if ($buddypress)
		{
			echo '
			<p>Delete all user and blog data in your database except for information and tables for blog ID 1 and user ID 1. This will also delete all Buddypress groups and friend relationships.</p>
			<p><strong>WARNING: This will delete ALL data, making your site as it was when you first installed WordPress and BuddyPress. This will also delete all Buddypress groups and friend relationships.</strong></p>
			<p><strong>WARNING: PLEASE READ THIS</strong> Deleting demo data will RESET YOUR ENTIRE WORDPRESS DATABASE, deleting all your posts, pages, comments, users etc. DO NOT DO THIS IF YOU WANT TO SAVE ANY OF THE DATA IN THIS SITE.</p>
			';
		} else {
			echo '
			<p>Delete all users and blog data in your database.</p>
			<p><strong>WARNING: This will delete ALL data.</strong></p>
			<p><strong>WARNING: PLEASE READ THIS</strong> Deleting demo data will RESET YOUR ENTIRE WORDPRESS DATABASE, deleting all your posts, pages, comments, users etc. DO NOT DO THIS IF YOU WANT TO SAVE ANY OF THE DATA IN THIS SITE.</p>
			';
		}
		echo '
			<p style="border:4px solid #c00;padding:2em;font-weight:bold;color:#c00">THIS WILL DELETE ALL YOUR DATA, INCLUDING POSTS, PAGES, USERS, COMMENTS ETC. ARE YOU SURE YOU WANT TO PROCEED?</p>
			<p><label for="delete">Delete demo data</label>
			<input type="hidden" name="action" value="delete" />
			<button type="submit" class="button demodatabutton" name="delete" id="delete">Delete</button></p>
		
		</fieldset>
		</form>
		
		<div id="deleteoutput"></div>
	';
	
	demodata_wp_plugin_standard_footer( "GBP", "Demo Data Creator", "Chris Taylor", "chris@stillbreathing.co.uk", "http://wordpress.org/extend/plugins/demo-data-creator/" );
	
	echo '
	</div>
	';
}

// ======================================================
// Content functions
// ======================================================
 
// return a random array of category ids
function demodata_random_categories()
{
	$limit = rand(1, 6);
	global $wpdb;
	return $wpdb->get_col("select term_id from ".$wpdb->terms." order by rand() limit ".$limit.";");
}

// return a random contact first name
function demodata_firstname()
{
	$firstnames = explode(",", "Alan,Albert,Allen,Amy,Andrew,Angela,Anita,Ann,Anne,Annette,Anthony,Arthur,Barbara,Barry,Beth,Betty,Beverly,Bill,Billy,Bobby,Bonnie,Bradley,Brenda,Brian,Bruce,Bryan,Carl,Carol,Carolyn,Catherine,Cathy,Charles,Cheryl,Chris,Christine,Christopher,Cindy,Connie,Craig,Curtis,Cynthia,Dale,Daniel,Danny,Darlene,Darryl,David,Dawn,Dean,Debbie,Deborah,Debra,Denise,Dennis,Diana,Diane,Donald,Donna,Dorothy,Douglas,Edward,Elizabeth,Ellen,Eric,Frank,Gail,Gary,George,Gerald,Glenn,Gloria,Greg,Gregory,Harold,Henry,Jack,Jacqueline,James,Jane,Janet,Janice,Jay,Jean,Jeff,Jeffery,Jeffrey,Jennifer,Jerry,Jill,Jim,Jimmy,Joan,Joanne,Joe,John,Johnny,Jon,Joseph,Joyce,Judith,Judy,Julie,Karen,Katharine,Kathleen,Kathryn,Kathy,Keith,Kelly,Kenneth,Kevin,Kim,Kimberly,Larry,Laura,Laurie,Lawrence,Leslie,Linda,Lisa,Lori,Lynn,Margaret,Maria,Mark,Martha,Martin,Mary,Matthew,Michael,Michele,Michelle,Mike,Nancy,Pamela,Patricia,Patrick,Paul,Paula,Peggy,Peter,Philip,Phillip,Ralph,Randall,Randy,Raymond,Rebecca,Renee,Rhonda,Richard,Rick,Ricky,Rita,Robert,Robin,Rodney,Roger,Ronald,Ronnie,Rose,Roy,Russell,Ruth,Samuel,Sandra,Scott,Sharon,Sheila,Sherry,Shirley,Stephanie,Stephen,Steve,Steven,Susan,Suzanne,Tammy,Teresa,Terri,Terry,Theresa,Thomas,Tim,Timothy,Tina,Todd,Tom,Tony,Tracy,Valerie,Vicki,Vickie,Vincent,Walter,Wanda,Wayne,Wendy,William,Willie");
	return $firstnames[ array_rand( $firstnames, 1 ) ];
}

// return a random contact last name
function demodata_lastname()
{
	$lastnames = explode(",", "Smith,Johnson,Williams,Jones,Brown,Davis,Miller,Wilson,Moore,Taylor,Anderson,Thomas,Jackson,White,Harris,Martin,Thompson,Garcia,Robinson,Clark,Lewis,Lee,Walker,Hall,Allen,Young,King,Wright,Lopez,Hill,Scott,Green,Adams,Baker,Nelson,Carter,Mitchell,Perez,Roberts,Turner,Phillips,Campbell,Parker,Evans,Edwards,Collins,Stewart,Morris,Rogers,Reed,Cook,Morgan,Bell,Murphy,Bailey,Rivera,Cooper,Richardson,Cox,Howard,Ward,Peterson,Gray,James,Watson,Brooks,Kelly,Sanders,Price,Bennett,Wood,Barnes,Ross,Henderson,Coleman,Jenkins,Perry,Powell,Long,Patterson,Hughes,Flores,Washington,Butler,Simmons,Foster,Bryant,Alexander,Russell,Griffin,Hayes,Myers,Ford,Hamilton,Graham,Sullivan,Wallace,Woods,Cole,West,Jordan,Owens,Reynolds,Fisher,Ellis,Harrison,Gibson,Mcdonald,Cruz,Marshall,Gomez,Murray,Freeman,Wells,Webb,Simpson,Stevens,Tucker,Porter,Hunter,Hicks,Crawford,Henry,Boyd,Mason,Morales,Kennedy,Warren,Dixon,Ramos,Reyes,Burns,Gordon,Shaw,Holmes,Rice,Robertson,Hunt,Black,Daniels,Palmer,Mills,Nichols,Grant,Knight,Ferguson,Rose,Stone,Hawkins,Dunn,Perkins,Hudson,Spencer,Gardner,Stephens,Payne,Pierce,Berry,Matthews,Arnold,Wagner,Willis,Ray,Watkins,Olson,Carroll,Duncan,Snyder,Hart,Cunningham,Bradley,Lane,Andrews,Ruiz,Harper,Fox,Riley,Armstrong,Carpenter,Weaver,Greene,Lawrence,Elliott,Chavez,Sims,Austin,Peters,Kelley,Franklin,Lawson,Fields,Ryan,Schmidt,Carr,Castillo,Wheeler,Chapman,Oliver,Montgomery,Richards,Williamson,Johnston,Banks,Meyer,Bishop,Mccoy,Howell,Morrison,Hansen,Garza,Harvey,Little,Burton,Stanley,Nguyen,George,Jacobs,Reid,Kim,Fuller,Lynch,Dean,Gilbert,Garrett,Welch,Larson,Frazier,Burke,Hanson,Day,Moreno,Bowman,Fowler");
	return $lastnames[ array_rand( $lastnames, 1 ) ];
}

// return a random blog name
function demodata_blogname()
{
	$names1 = explode(",", "View from,All about,Here is,About,Writings on,See my,Blog about,I love,Do you like,Unlimited,,,");
	$names2 = explode(",", "Photos,Food,History,Music,Sport,Football,Rugby,Golf,Tennis,Cricket,Hockey,Jazz,Folk,Rock,Metal,Victoriana,Elizabethan,Stamp,Model,Flying Astronomy,Robotics,Bird watching,Calligraphy,Drawing,Dollhouses,Knitting,Antiques,Book,Animation,Gardening,Literature");
	return $names1[ array_rand( $names1, 1 ) ] . ' ' . $names2[ array_rand( $names2, 1 ) ];
}

// return a random group name
function demodata_groupname()
{
	$names1 = explode(",", "Photos,Food,History,Music,Sport,Football,Rugby,Golf,Tennis,Cricket,Hockey,Jazz,Folk,Rock,Metal,Victoriana,Elizabethan,Stamp,Model,Flying Astronomy,Robotics,Bird watching,Calligraphy,Drawing,Dollhouses,Knitting,Antiques,Book,Animation,Gardening,Literature");
	$names2 = explode(",", "Professionals,Lovers,Heroes,Fans,Appreciators,Association,Society,Community,World,Group,Order,Gang,Guild,,,");
	return $names1[ array_rand( $names1, 1 ) ] . ' ' . $names2[ array_rand( $names2, 1 ) ];
}

// generate random html content
function demodata_generate_html($maxblocks = 4)
{
	$head = "<h1>HTML Ipsum Presents (" . $maxblocks . " blocks)</h1>";
	$htmlstr = '	       
<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>
<!--break-->
<h2>Header Level 2</h2>
	       
<ol>
   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
   <li>Aliquam tincidunt mauris eu risus.</li>
</ol>
<!--break-->
<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>
<!--break-->
<img src="http://lorempixel.com/300/200" alt="Random image courtesy of LoremPixel.com" />
<!--break-->
<img src="http://lorempixel.com/100/100" alt="Random image courtesy of LoremPixel.com" />
<img src="http://lorempixel.com/100/100" alt="Random image courtesy of LoremPixel.com" />
<img src="http://lorempixel.com/100/100" alt="Random image courtesy of LoremPixel.com" />
<img src="http://lorempixel.com/100/100" alt="Random image courtesy of LoremPixel.com" />
<img src="http://lorempixel.com/100/100" alt="Random image courtesy of LoremPixel.com" />
<img src="http://lorempixel.com/100/100" alt="Random image courtesy of LoremPixel.com" />
<!--break-->
<h3>Header Level 3</h3>

<ul>
   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
   <li>Aliquam tincidunt mauris eu risus.</li>
</ul>
<!--break-->
<pre><code>
#header h1 a { 
	display: block; 
	width: 300px; 
	height: 80px; 
}
</code></pre>
<!--break-->
<table summary="Table summary">
   <caption>Table Caption</caption>
   <thead>
      <tr>
         <th>Header</th>
         <th>Header</th>
         <th>Header</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>Content</td>
         <td>1</td>
         <td>a</td>
      </tr>
      <tr>
         <td>Content</td>
         <td>2</td>
         <td>b</td>
      </tr>
   </tbody>
</table><table summary="Table summary">
   <caption>Table Caption</caption>
   <thead>
      <tr>
         <th>Header</th>
         <th>Header</th>
         <th>Header</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>Content</td>
         <td>1</td>
         <td>a</td>
      </tr>
      <tr>
         <td>Content</td>
         <td>2</td>
         <td>b</td>
      </tr>
   </tbody>
</table>
<!--break-->
<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>
<!--break-->
<dl>
   <dt>Definition list</dt>
   <dd>Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna 
aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea 
commodo consequat.</dd>
   <dt>Lorem ipsum dolor sit amet</dt>
   <dd>Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna 
aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea 
commodo consequat.</dd>
</dl>
<!--break-->
<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>';

	$htmlstr = explode("<!--break-->", $htmlstr);
	$blocks = count($htmlstr)-1;
	
	if ($maxblocks > count($htmlstr)) { $maxblocks = $blocks+1; }

	$out = "";
	
	for($x = 0; $x < $maxblocks; $x++)
	{
		$out .= $htmlstr[rand(0, $blocks)] . "\n\n";
	}
	
	return $out;
}

// generate random text content
function demodata_generate_random_text($maxlength, $randomstart = true)
{
	$len = rand(0, $maxlength);

	$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec tincidunt dignissim risus. Sed mattis quam non ante. Nullam condimentum fringilla nibh. Quisque non lectus. Phasellus est orci, fermentum sit amet, vulputate sit amet, facilisis varius, nulla. In aliquet. Sed justo lectus, tempor eleifend, vulputate nec, auctor ac, nibh. Cras sem turpis, adipiscing quis, ultricies et, blandit vitae, justo. Suspendisse potenti. Quisque dapibus neque. Nulla purus sapien, interdum ac, lobortis quis, tempus quis, tellus. Cras semper odio id purus. Nunc eros. In massa. Curabitur viverra, felis eget sagittis consectetur, neque neque fringilla lectus, quis faucibus risus tellus consequat nunc. In nec lectus. Aliquam erat volutpat. Ut vel odio. Quisque lacus. Etiam consectetur rutrum justo.

Duis facilisis. Aliquam sagittis. Proin consectetur egestas metus. Curabitur pellentesque posuere arcu. Integer lorem nulla, congue a, rhoncus nec, vestibulum a, ante. Mauris lobortis iaculis erat. Maecenas faucibus tincidunt dui. Cras accumsan vestibulum ligula. Morbi dapibus, lorem nec euismod pharetra, augue risus congue augue, molestie eleifend lacus magna nec magna. Morbi ac lorem.

Aliquam id ipsum. Sed mauris. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin ac ipsum. Nunc vitae sapien sit amet tellus eleifend hendrerit. Phasellus orci nunc, fermentum eu, ultrices quis, dapibus vitae, lectus. Donec blandit ligula vitae justo. Quisque rutrum ante ac nisi. Sed at mi. Donec eget odio in est tempus malesuada. Donec sem. Donec tortor. In auctor purus a lectus. Nulla a leo. Curabitur velit. In et lorem. Quisque congue, lorem sed cursus ullamcorper, mauris urna adipiscing enim, eget pulvinar est lacus ut augue.

Aliquam erat volutpat. Proin scelerisque lectus non purus. Donec quis tellus. Vestibulum dictum imperdiet lacus. Integer magna libero, feugiat id, tincidunt eu, venenatis vel, mi. Cras mi. Vestibulum est. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed ut magna quis felis congue commodo. Fusce dictum hendrerit dolor. Donec commodo euismod dolor. Curabitur nibh. Vestibulum ut risus. Integer laoreet, ipsum congue congue suscipit, ipsum est molestie dolor, consectetur tempus justo lectus ac mauris.

Integer vulputate molestie quam. Vestibulum et nisi. Nullam in magna quis libero posuere vestibulum. Donec mi leo, elementum ut, tincidunt at, condimentum eu, est. Donec sed nisl ac justo consectetur viverra. Nulla volutpat est vitae nisl. Nullam aliquam ipsum a arcu facilisis adipiscing. Pellentesque cursus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nulla gravida viverra urna.

Aenean condimentum, arcu sit amet volutpat ullamcorper, erat sapien tempor dolor, ac consectetur diam ante at nibh. Proin erat lectus, vehicula id, consequat quis, semper ac, felis. Sed in lectus. Duis nec massa. Nullam augue. Duis dolor felis, porta et, molestie vitae, imperdiet eget, purus. Mauris iaculis. In cursus, neque eu sollicitudin ullamcorper, odio mauris tempus odio, id tincidunt metus leo vel ipsum. Quisque suscipit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam sed libero. Nunc sollicitudin diam ac dui. Nunc faucibus auctor tortor. Quisque ipsum sem, hendrerit accumsan, congue ut, porttitor ut, turpis. Sed sollicitudin, leo et condimentum tempus, massa augue dictum est, iaculis posuere nulla felis quis tortor. Pellentesque ac nisl vitae nunc porta pretium. Praesent id erat. Cras leo. Quisque eleifend metus nec lorem.

Sed sed quam non mauris aliquam rhoncus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Praesent quam turpis, dignissim et, consequat in, porttitor eget, orci. Aenean pretium, orci vel ultrices dapibus, ipsum metus lobortis ante, ac tincidunt urna erat et tortor. Mauris nisl eros, dapibus et, tristique eget, dignissim non, lacus. Fusce aliquam, turpis quis varius blandit, purus elit sollicitudin leo, vitae posuere odio odio id dui. Fusce adipiscing. Maecenas a enim eu sem accumsan laoreet. Donec sem eros, egestas ornare, fermentum quis, malesuada sed, risus. Sed eleifend faucibus magna. Fusce malesuada ante eget massa. Donec consectetur dolor vitae erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas elit risus, pellentesque sed, imperdiet quis, pulvinar in, odio.

Duis nulla diam, fringilla at, feugiat et, tincidunt consectetur, magna. Duis id est ac neque mollis ullamcorper. Nulla quis urna. Ut vitae nisi sit amet lectus blandit ultrices. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus consectetur nunc at sapien. Ut pellentesque laoreet diam. Morbi arcu neque, congue rhoncus, sollicitudin ut, condimentum id, lectus. Nulla facilisi. Praesent neque lacus, pretium eu, molestie at, laoreet convallis, metus. Fusce leo nisi, ornare ut, ullamcorper ac, molestie et, quam. In vehicula arcu eu risus. Fusce ultrices lectus sit amet diam. Maecenas commodo risus eu diam. Maecenas at nibh. Nullam mattis pharetra dolor. Donec eleifend leo sit amet diam. Quisque ante. Aliquam erat volutpat.

Maecenas vel lectus. Maecenas convallis lorem at risus. Nullam facilisis tortor. Quisque quis turpis. Suspendisse consectetur nisl. Integer facilisis, massa consectetur mattis sollicitudin, lectus elit pharetra eros, ut dignissim sapien nunc eu nisi. Nunc tortor. Vestibulum imperdiet. Aliquam erat volutpat. Integer fermentum tincidunt nisl. Vivamus fringilla, augue vel consectetur convallis, justo leo rutrum dui, et dapibus ipsum nulla sit amet est. Curabitur augue. Sed nec magna vel est auctor porttitor. Integer at lorem. Sed et turpis nec lorem consectetur volutpat. Donec vestibulum cursus mauris. Nullam blandit urna quis nibh. Proin sollicitudin elementum tellus. Suspendisse mauris enim, ultricies ut, rhoncus non, sodales at, arcu.

Nam eu tortor. Nam venenatis congue nibh. Donec posuere lacinia neque. Pellentesque vehicula. Nam eleifend ipsum. Vestibulum lectus diam, viverra vitae, tempor sit amet, eleifend eu, quam. Aenean aliquam ornare tellus. Donec vel ligula ut mauris pellentesque mattis. In neque leo, porta non, rutrum vel, sollicitudin a, libero. Pellentesque ornare mauris id odio. Aliquam erat volutpat. Aenean eget ante eget nunc feugiat lacinia. Duis ullamcorper consequat risus. Nunc at magna.

Aliquam in elit vitae dui gravida venenatis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam bibendum risus id nunc. Nullam tempus molestie eros. Maecenas molestie pharetra augue. In sagittis enim vitae libero. Praesent feugiat blandit lacus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas erat lacus, sollicitudin placerat, facilisis nec, tempor nec, elit. Pellentesque iaculis urna ut leo mattis sagittis. Cras urna. Vestibulum malesuada orci eget leo.

Etiam egestas auctor sapien. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent feugiat. Donec vehicula, dui nec adipiscing ultricies, lorem urna eleifend arcu, eu congue libero lectus ac lacus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent pulvinar pellentesque lacus. Suspendisse urna orci, rutrum a, placerat a, suscipit a, est. Aliquam ac odio. Fusce arcu lorem, sodales et, condimentum ultrices, fringilla ut, tellus. Quisque velit justo, molestie ac, imperdiet et, vulputate semper, dolor. Phasellus laoreet, sapien in molestie gravida, velit quam consequat enim, sit amet malesuada arcu justo vitae turpis. Aliquam orci lorem, ornare ut, congue nec, bibendum at, erat. Donec vitae nisl id risus euismod viverra. Pellentesque eleifend risus sit amet diam. Mauris pharetra ornare tellus. Pellentesque ante elit, vestibulum eget, laoreet dictum, pulvinar vitae, velit. Mauris vel augue quis enim ornare imperdiet.

Phasellus sed tellus eget lacus molestie laoreet. Phasellus commodo euismod mauris. Sed facilisis nulla a est. Proin sit amet lectus. Morbi suscipit libero a nisl. Sed quam ipsum, ullamcorper non, congue vitae, ornare non, nisl. Nunc ultrices. Aliquam imperdiet velit sit amet nulla. Sed a neque. Fusce tempus tortor ut diam. Quisque sagittis lacus eget velit. Quisque augue magna, commodo in, molestie nec, convallis at, tortor. Etiam blandit ultrices tortor. Aliquam nisi risus, lobortis vitae, elementum pulvinar, viverra vel, dolor. Donec metus urna, faucibus aliquet, egestas in, adipiscing sit amet, risus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin nisi. Nunc hendrerit nisi lobortis purus.

Vivamus turpis ante, ultrices scelerisque, elementum id, tempor at, quam. Cras eu mauris eu nulla congue convallis. Fusce ornare, nibh sit amet porta rhoncus, nibh metus tincidunt magna, quis gravida dui neque pharetra risus. Etiam a felis. Fusce sed dolor. Mauris lectus mi, fringilla tempor, varius sit amet, placerat a, velit. Suspendisse justo. In vehicula urna fringilla neque. Suspendisse cursus, magna a imperdiet pellentesque, lacus velit dignissim urna, vel suscipit massa enim ac nunc. Proin porta aliquet eros. Curabitur ut erat. Quisque vitae tortor.

Duis imperdiet, mi eget euismod fermentum, odio nisl posuere quam, sit amet tristique urna diam at lacus. Duis congue lacus non ipsum. Donec felis tortor, lacinia at, rhoncus id, scelerisque ornare, nisi. Mauris felis ligula, pharetra vitae, posuere eget, tincidunt at, turpis. Donec eget ligula. Praesent fermentum dictum nisl. Phasellus enim. Nam placerat. Ut dignissim est nec lorem. Aliquam eros augue, rutrum et, placerat in, euismod in, libero. Morbi venenatis, eros non gravida rhoncus, leo metus venenatis augue, vitae dignissim lorem sapien eget diam. Ut nisl. Fusce quis lorem. Etiam mollis risus. Integer luctus. In quis quam. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas metus.';

	if (!$randomstart)
	{

		$out = trim(substr($str, 0, $len));
	
	} else {
	
		$out = trim(substr($str, rand(0 + $len, strlen($str) - $len), $len));
	
	}

	return $out;
}

// a standard header for your plugins, offers a PayPal donate button and link to a support page
function demodata_wp_plugin_standard_header( $currency = "", $plugin_name = "", $author_name = "", $paypal_address = "", $bugs_page ) {
	$r = "";
	$option = get_option( $plugin_name . " header" );
	if ( ( isset( $_GET[ "header" ] ) &&  $_GET[ "header" ] != "" ) || ( isset( $_GET["thankyou"] ) && $_GET["thankyou"] == "true" ) ) {
		update_option( $plugin_name . " header", "hide" );
		$option = "hide";
	}
	if ( isset( $_GET["thankyou"] ) && $_GET["thankyou"] == "true" ) {
		$r .= '<div class="updated"><p>' . __( "Thank you for donating" ) . '</p></div>';
	}
	if ( $currency != "" && $plugin_name != "" && isset( $_GET[ "header" ] ) && $_GET[ "header" ] != "hide" && $option != "hide" )
	{
		$r .= '<div class="updated">';
		$pageURL = 'http';
		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) { $pageURL .= "s"; }
		$pageURL .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		if ( strpos( $pageURL, "?") === false ) {
			$pageURL .= "?";
		} else {
			$pageURL .= "&";
		}
		$pageURL = htmlspecialchars( $pageURL );
		if ( $bugs_page != "" ) {
			$r .= '<p>' . sprintf ( __( 'To report bugs please visit <a href="%s">%s</a>.' ), $bugs_page, $bugs_page ) . '</p>';
		}
		if ( $paypal_address != "" && is_email( $paypal_address ) ) {
			$r .= '
			<form id="wp_plugin_standard_header_donate_form" action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_donations" />
			<input type="hidden" name="item_name" value="Donation: ' . $plugin_name . '" />
			<input type="hidden" name="business" value="' . $paypal_address . '" />
			<input type="hidden" name="no_note" value="1" />
			<input type="hidden" name="no_shipping" value="1" />
			<input type="hidden" name="rm" value="1" />
			<input type="hidden" name="currency_code" value="' . $currency . '" />
			<input type="hidden" name="return" value="' . $pageURL . 'thankyou=true" />
			<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted" />
			<p>';
			if ( $author_name != "" ) {
				$r .= sprintf( __( 'If you found %1$s useful please consider donating to help %2$s to continue writing free Wordpress plugins.' ), $plugin_name, $author_name );
			} else {
				$r .= sprintf( __( 'If you found %s useful please consider donating.' ), $plugin_name );
			}
			$r .= '
			<p><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="" /></p>
			</form>
			';
		}
		$r .= '<p><a href="' . $pageURL . 'header=hide" class="button">' . __( "Hide this") . '</a></p>';
		$r .= '</div>';
	}
	print $r;
}
function demodata_wp_plugin_standard_footer( $currency = "", $plugin_name = "", $author_name = "", $paypal_address = "", $bugs_page ) {
	$r = "";
	if ( $currency != "" && $plugin_name != "" )
	{
		$r .= '<form id="wp_plugin_standard_footer_donate_form" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="clear:both;padding-top:50px;"><p>';
		$pageURL = 'http';
		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) { $pageURL .= "s"; }
		$pageURL .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		if ( strpos( $pageURL, "?") === false ) {
			$pageURL .= "?";
		} else {
			$pageURL .= "&";
		}
		$pageURL = htmlspecialchars( $pageURL );
		if ( $bugs_page != "" ) {
			$r .= sprintf ( __( '<a href="%s">Bugs</a>' ), $bugs_page );
		}
		if ( $paypal_address != "" && is_email( $paypal_address ) ) {
			$r .= '
			<input type="hidden" name="cmd" value="_donations" />
			<input type="hidden" name="item_name" value="Donation: ' . $plugin_name . '" />
			<input type="hidden" name="business" value="' . $paypal_address . '" />
			<input type="hidden" name="no_note" value="1" />
			<input type="hidden" name="no_shipping" value="1" />
			<input type="hidden" name="rm" value="1" />
			<input type="hidden" name="currency_code" value="' . $currency . '" />
			<input type="hidden" name="return" value="' . $pageURL . 'thankyou=true" />
			<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted" />
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="' . __( "Donate" ) . ' ' . $plugin_name . '" />
			';
		}
		$r .= '</p></form>';
	}
	print $r;
}
?>