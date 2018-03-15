=== Demo Data Creator ===
Contributors: mrwiblog
Donate link: http://www.stillbreathing.co.uk/donate/
Tags: wordpress, wpmu, buddypress, demo, data, example, dummy, users, blogs, sample
Requires at least: 2.7
Tested up to: 4.7.2
Stable tag: 1.3.4

Demo Data Creator is a Wordpress and BuddyPress plugin that allows a Wordpress developer to create demo users, blogs, posts, comments and more. DO NOT USE ON A PRODUCTION SITE.

== Description ==

DO NOT USE THIS PLUGIN ON A PRODUCTION SITE, OR ON A SITE WHERE YOU NEED TO KEEP THE DATA.

When deleting demo data your ENTIRE WordPress database will be reset, deleting ALL your posts, pages, comments and users. ONLY USE THIS PLUGIN ON A SITE WHERE YOU KNOW THE DATABASE CAN BE WIPED.

If you develop Wordpress websites it's useful to have some demo data in your system while it's being built. This allows you to check that lists of things are displaying as they should, and that themes are working when they get data in them.

Historically it's been a pain to add that data in. Either you need to take a backup of another site and use that data, or you need to tediously create multiple users and blogs yourself. No more, not now my Demo Data Creator is in town!

This Wordpress, WPMU/MultiSite and BuddyPress plugin gives you a new admin screen where you can enter some parameters, click a button and (after a short wait) random demo data will be created. The parameter options include:

    * Number of users to create
    * Number of blogs per user (for WPMU/MultiSite)
	* Whether users must have a blog
	* Number of categories in each blog
    * Number of posts in each blog
	* Number of paragraphs in each blog post
	* Number of pages in each blog
	* Number of top-level pages
	* Number of levels to nest pages
    * Number of comments per post for each blog
    * Number of links in blogroll for each blog
	
For BuddyPress you also have:

	* Number of groups
	* Number of members per group
	* Number of wire posts for each group
	* Number of friends per user
	* Number of statuses for each user
	* Number of wire posts for each user

Post content and comment text is automatically generated from Lorem ipsum text, for post content it's even HTML-formatted.

Thanks to derscheinwelt for the suggestion and code to create random dates for posts, and Steve at http://slipfire.com/ for the wp_insert_user() code.

== Installation ==

DO NOT USE THIS PLUGIN ON A PRODUCTION SITE, OR ON A SITE WHERE YOU NEED TO KEEP THE DATA.

When deleting demo data your ENTIRE WordPress database will be reset, deleting ALL your posts, pages, comments and users. ONLY USE THIS PLUGIN ON A SITE WHERE YOU KNOW THE DATABASE CAN BE WIPED.

Install from the WordPress plugin repository using the installer, or put all the files into a /wp-content/plugins/demodata/ directory. Activate from the plugin admin screen.

You can find the Demo Data Creator admin menu option in the "Tools" menu. If you are using WordPress MultiSite (version 3.0 or above) you can find the Demo Data Creator admin menu option in the "Super Admin" menu.

If using WPMU (NOT Wordpress 3.0 MultiSite): The plugin should be placed in your /wp-content/mu-plugins/ directory (*not* /wp-content/plugins/) and requires no activation. So the path to the file should be /wp-content/mu-plugins/demodata.php. Access the form from the "Site Admin" menu in the Dashboard.

== Frequently Asked Questions ==

= The plugin deleted all my data! Why? =

I have been as clear as I can about the use for this plugin. It creates demo data (posts, pages, comments, users etc) but when you choose to delete that data IT WILL WIPE YOUR ENTIRE WORDPRESS DATABASE.

DO NOT USE THIS PLUGIN ON A PRODUCTION SITE, OR ON A SITE WHERE YOU NEED TO KEEP THE DATA.

When deleting demo data your ENTIRE WordPress database will be reset, deleting ALL your posts, pages, comments and users. ONLY USE THIS PLUGIN ON A SITE WHERE YOU KNOW THE DATABASE CAN BE WIPED.

= Why did you write this plugin? =

To scratch my own itch when developing sites. Hopefully this plugin helps other developers too.

= What about BuddyPress support =

The DemoData plugin now supports BuddyPress.

== Screenshots ==

1. The demo data admin page

== Changelog ==

= 1.3.4 (2017/01/31) =

Fixed deprecation error messages. Fixed lorempixel.com random image links. Tested up to 4.7.2.

= 1.3.3 (2015/11/17) =

Added even more warnings about data loss. DO NOT USE THIS PLUGIN ON A PRODUCTION SITE, OR ON A SITE WHERE YOU NEED TO KEEP THE DATA.

= 1.3.2 (2014/06/19) =

Added more checking for empty $current_site variable. Thanks to Juliette RF for contributing a patch.

= 1.3.1 (2014/03/09) =

Added catch for empty $current_site variable reported at http://wordpress.org/support/topic/little-bug-on-line-60-demodataphp by http://wordpress.org/support/profile/templaries

= 1.3 (2013/09/18) =

Updated user creation to use wp_insert_user(). Many thanks to Steve at http://slipfire.com/ for the code.

= 1.2 (2013/01/05) =

Fixed incorrect function and class calls.

= 1.1 (2012/09/18) =

Added support for creating random post dates (thanks to derscheinwelt for the suggestion and code).

= 1.0 (2012/01/24) =

Added support for random images in pages and posts, thanks to LoremPixum.com. Made delete data warning even more obvious.

= 0.9.8 (2011/09/26) =

Fixed bug that stopped author pages working for automatically created users. Thanks to Zolt&aacute;n J&aacute;nos J&aacute;nosi for the fix.

= 0.9.7.7 (2011/03/05) =

Fixed bug when creating users on a site with no domain suffix (for example "http://localhost")

= 0.9.7.6 (2010/12/03) =

Fixed bug in Plugin Register caused by latest version of WordPress

= 0.9.7.5 (2010/10/16) =

Made the "Delete all data" option clearer to understand

= 0.9.7.4 (2010/09/09) =

Fixed bugs with different version of WordPress (standard and MultiSite) and with BuddyPress

= 0.9.7.3 (2010/09/02) =

Fixed bug when creating users in BuddyPress. This was related to a bug in wp_create_user() which returned an incorrect error for invalid email addresses.

= 0.9.7.2 (2010/07/10) =

Fixed bug where menu option not appearing. Thanks to Lisa Drew from http://lyricalbiz.com/ for help fixing this bug.

= 0.9.7.1 (2010/07/04) =

Updated documentation

= 0.9.7 (2010/06/23) =

Fixed bug with menu in standard WP

= 0.9.6 (2010/06/18) =

Fixed bug caused by with WordPress 3.0 not creating blog tables

= 0.9.5 (2010/06/11) =

Made plugin compatible with WordPress 3.0

= 0.9.4 (2010/05/14) =

Updated plugin URI

= 0.9.3 (2010/04/20) =

Implemented new Plugin Register version

= 0.9.2 (2010/04/12) =

Fixed bug with non-deletion of activity streams

= 0.9.1 (2010/04/01) =

Added Plugin Register code

= 0.9 (2010/03/23) =

Made plugin compatible with standard Wordpress

= 0.8 =

Added compatibility with BuddyPress 1.2. Converted form to use AJAX where possible.

= 0.7 =

Added support link and donate button

= 0.6 =

Fixed bug which stopped demo blog tables being created

= 0.5 =

Fixed bugs with user wire posts, added support for group wire posts, cleaned up code

= 0.4 =

Prepared code for proper translation support, fixed bug with BuddyPress XProfile data, added support for BuddyPress status messages

= 0.3 =

Cleaned up code

= 0.2 =

Added categories, pages for normal Wordpress MU sites. Added BuddyPress groups, group members and user friends BuddyPress-enabled sites.

= 0.1 =

Initial version added to Wordpress plugin repository