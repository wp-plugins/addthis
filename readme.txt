=== AddThis ===
Contributors: _mjk_
Tags: share, addthis, social, bookmark, sharing, bookmarking, widget
Requires at least: 2.3
Tested up to: 2.9.2
Stable tag: 1.6.5

The AddThis Social Bookmarking Widget allows any visitor to bookmark and share your site easily with many popular services. 

== Description ==
Get more traffic back to your site by installing the AddThis WordPress plugin. With AddThis, your users can promote your content by sharing to 295 of the most popular social networking and bookmarking sites (like Facebook, Twitter, Digg, StumbleUpon and MySpace). Our button is small, unobtrusive, quick to load and recognized all over the web.

Optionally, sign up for a free AddThis.com account to see how your visitors are sharing your content: which services they're using for sharing, which content is shared the most, and more.

<a href="http://www.addthis.com/blog">AddThis Blog</a> | <a href="http://www.addthis.com/privacy">Privacy Policy</a>

== Installation ==

1. Upload `addthis_social_widget.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. (Optional) Customize the plugin in the Settings > AddThis menu

Note: due to confusion, there are no longer separate versions for PHP4 and PHP5.

== Frequently Asked Questions ==

= Is AddThis free? =

Yep! The features you see today on AddThis will always be free. 

= Do I need to create an account? =

No. You only need to create an account if you want to see how your users are sharing your blog; the sharing itself works the same either way.

= Is JavaScript required? =

If you've turned on the drop-down menu (which is recommended, as it's been shown to increase sharing/bookmarking rates), JavaScript must be enabled. We load the actual interface via JavaScript at run-time, which allows us to upgrade the core functionality of the menu itself automatically everywhere. 

= Why use AddThis? =
1. Ease of use. AddThis is easy to install, customize and localize. We've worked hard to make it the simplest, most recognized sharing tool on the internet.
1. Performance. The AddThis menu code is tiny and fast. We constantly optimize its behavior and design to make sharing a snap.
1. Peace of mind. AddThis gathers the best services on the internet so you don't have to, and backs them up with industrial strength analytics, code caching, active tech support and a thriving developer community.
1. Flexibility. AddThis can be customized via API, and served securely via SSL. You can roll your own sharing toolbars with our toolbox. Share just about anything, anywhere -- your way.
1. Global reach. AddThis sends content to 295+ sharing services 60+ languages, to over half a billion unique users in countries all over the world.
1. It's free!

= Who else uses AddThis? =
Over 1,200,000 sites have installed AddThis. With over a billion unique users, AddThis is helping share content all over the world, in more than sixty languages. You might be surprised who's sharing their website using AddThis--<a href="http://www.addthis.com/features#partners">here are just a few</a>.

= What services does AddThis support? =
We currently support over 295 services, from email and blogging platforms to social networks and news aggregators, and we add new services every month. Want to know if your favorite service is supported? This list is accurate up to the minute: <a href="http://www.addthis.com/services">http://www.addthis.com/services</a>.

== Screenshots ==

1. The button on a sample post.
2. The open menu on a sample post.
3. The settings interface on WordPress 2.7.
4. A sample sharing trend report.
5. A sample service usage report.

== PHP Version ==

PHP 5+ is preferred; PHP 4 is supported.

== Changelog ==

= 1.6.5 =
* Added support for arbitrary URL and title in template tag as optional parameters
 * i.e., <?php do_action( 'addthis_widget', $url, $title); ?>
 * Can be called, for example, with get_permalink() and the_title() within a post loop, or some other URL if necessary

= 1.6.4 =
* Fixed parse bug with "static" menu option
* Fixed regression of brand option

= 1.6.3 = 
* Added template tags. &lt;?php do_action( 'addthis_widget' ); ?&gt; in your template will print an AddThis button or toolbox, per your configuration.
* Added <a href="http://addthis.com/blog/2010/03/11/clickback-analytics-measure-traffic-back-to-your-site-from-addthis/">clickback</a> tracking.
* Added "Automatic" language option. We'll auto-translate the AddThis button and menu into our supported languages depending on your users' settings.
* Fixed script showing up in some trackback summaries. 

= 1.6.2 =
Fixed name conflict with get_wp_version() (renamed to addthis_get_wp_version()), affecting users with the k2 theme.

= 1.6.1 =
Fixed nondeterministic bug with the_title(), causing the title to occasionally appear in posts.

= 1.6.0 =
* Added <a href="http://addthis.com/toolbox">toolbox</a> support
* Added WPMU support
* For WP 2.7+ only:
 * Added support for displaying basic sharing metrics in the WordPress dashboard
 * Updated settings management to use nonces

