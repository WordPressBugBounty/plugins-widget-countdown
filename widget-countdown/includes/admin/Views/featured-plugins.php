<?php
defined( 'ABSPATH' ) || exit;

$plugins = array(
	array(
		'image' => 'gallery-album-icon.png',
		'url'   => 'https://wpdevart.com/wordpress-gallery-plugin',
		'title' => 'WordPress Gallery plugin',
		'desc'  => 'Gallery plugin is an useful tool that will help you to create Galleries and Albums. Try our nice Gallery views and awesome animations.',
	),
	array(
		'image' => 'coming_soon.png',
		'url'   => 'https://wpdevart.com/wordpress-coming-soon-plugin/',
		'title' => 'Coming soon and Maintenance mode',
		'desc'  => 'Coming soon and Maintenance mode plugin is an awesome tool to show your visitors that you are working on your website to make it better.',
	),
	array(
		'image' => 'contact_forms.png',
		'url'   => 'https://wpdevart.com/wordpress-contact-form-plugin/',
		'title' => 'Contact Form Builder',
		'desc'  => 'Contact Form Builder plugin is an handy tool for creating different types of contact forms on your WordPress websites.',
	),
	array(
		'image' => 'Booking_calendar_featured.png',
		'url'   => 'https://wpdevart.com/wordpress-booking-calendar-plugin/',
		'title' => 'WordPress Booking Calendar',
		'desc'  => 'WordPress Booking Calendar plugin is an awesome tool to create a booking system for your website. Create booking calendars in a few minutes.',
	),
	array(
		'image' => 'Pricing-table.png',
		'url'   => 'https://wpdevart.com/wordpress-pricing-table-plugin/',
		'title' => 'WordPress Pricing Table',
		'desc'  => 'WordPress Pricing Table plugin is a nice tool for creating beautiful pricing tables. Use WpDevArt pricing table themes and create tables just in a few minutes.',
	),
	array(
		'image' => 'chart-featured.png',
		'url'   => 'https://wpdevart.com/wordpress-organization-chart-plugin/',
		'title' => 'WordPress Organization Chart',
		'desc'  => 'WordPress organization chart plugin is a great tool for adding organizational charts to your WordPress websites.',
	),
	array(
		'image' => 'youtube.png',
		'url'   => 'https://wpdevart.com/wordpress-youtube-embed-plugin',
		'title' => 'WordPress YouTube Embed',
		'desc'  => 'YouTube Embed plugin is an convenient tool for adding videos to your website. Use YouTube Embed plugin for adding YouTube videos in posts/pages, widgets.',
	),
	array(
		'image' => 'facebook-comments-icon.png',
		'url'   => 'https://wpdevart.com/wordpress-facebook-comments-plugin/',
		'title' => 'Wpdevart Social comments',
		'desc'  => 'WordPress Facebook comments plugin will help you to display Facebook Comments on your website. You can use Facebook Comments on your pages/posts.',
	),
	array(
		'image' => 'countdown.jpg',
		'url'   => 'https://wpdevart.com/wordpress-countdown-plugin/',
		'title' => 'WordPress Countdown plugin',
		'desc'  => 'WordPress Countdown plugin is an nice tool for creating countdown timers for your website posts/pages and widgets.',
	),
	array(
		'image' => 'lightbox.png',
		'url'   => 'https://wpdevart.com/wordpress-lightbox-plugin',
		'title' => 'WordPress Lightbox plugin',
		'desc'  => 'WordPress Lightbox Popup is an high customizable and responsive plugin for displaying images and videos in popup.',
	),
	array(
		'image' => 'facebook.png',
		'url'   => 'https://wpdevart.com/wordpress-facebook-like-box-plugin',
		'title' => 'Social Like Box',
		'desc'  => 'Facebook like box plugin will help you to display Facebook like box on your website, just add Facebook Like box widget to sidebar or insert it into posts/pages and use it.',
	),
	array(
		'image' => 'poll.png',
		'url'   => 'https://wpdevart.com/wordpress-polls-plugin',
		'title' => 'WordPress Polls system',
		'desc'  => 'WordPress Polls system is an handy tool for creating polls and survey forms for your visitors. You can use our polls on widgets, posts and pages.',
	),
);

$img_base = wpda_countdown_plugin_url . 'includes/admin/images/featured_plugins/';
?>
<style>
.featured_plugin_main{background-color:#fff;box-sizing:border-box;float:left;margin-right:30px;margin-bottom:30px;width:calc((100% - 90px)/3);border-radius:15px;box-shadow:1px 1px 7px rgba(0,0,0,0.04);padding:20px 25px;text-align:center;transition:transform 0.3s;transform:translateY(0);min-height:344px}
.featured_plugin_main:hover{transform:translateY(-2px)}
.featured_plugin_image{max-width:128px;margin:0 auto}
.featured_plugin_image img{max-width:100%}
.featured_plugin_image a{display:inline-block}
.featured_plugin_title{color:#7052fb;font-size:18px;display:inline-block}
.featured_plugin_title a{text-decoration:none;font-size:19px;line-height:22px;color:#7052fb}
.featured_plugin_title h4{margin:0;margin-top:20px;min-height:44px}
.featured_plugin_description{font-size:14px;min-height:63px}
.blue_button{display:inline-block;font-size:15px;text-decoration:none;border-radius:5px;color:#fff;font-weight:400;opacity:1;transition:opacity 0.3s;background-color:#7052fb;padding:10px 22px;text-transform:uppercase}
.blue_button:hover,.blue_button:focus{color:#fff;box-shadow:none;outline:none}
@media screen and (max-width:1460px){.featured_plugin_main{margin-right:20px;margin-bottom:20px;width:calc((100% - 60px)/3);padding:20px 10px}.featured_plugin_description{font-size:13px;min-height:63px}}
@media screen and (max-width:1279px){.featured_plugin_main{width:calc((100% - 60px)/2);padding:20px;min-height:363px}}
@media screen and (max-width:768px){.featured_plugin_main{width:calc(100% - 30px);padding:20px;min-height:auto;margin:0 auto 20px;float:none}.featured_plugin_title h4{min-height:auto}.featured_plugin_description{min-height:auto;font-size:14px}}
</style>

<h1 style="text-align:center;font-size:50px;font-weight:700;color:#2b2350;margin:20px auto 25px;line-height:1.2;">Featured Plugins</h1>
<?php foreach ( $plugins as $plugin ) : ?>
<div class="featured_plugin_main">
	<div class="featured_plugin_image">
		<a target="_blank" href="<?php echo esc_url( $plugin['url'] ); ?>">
			<img src="<?php echo esc_url( $img_base . $plugin['image'] ); ?>">
		</a>
	</div>
	<div class="featured_plugin_information">
		<div class="featured_plugin_title">
			<h4><a target="_blank" href="<?php echo esc_url( $plugin['url'] ); ?>"><?php echo esc_html( $plugin['title'] ); ?></a></h4>
		</div>
		<p class="featured_plugin_description"><?php echo esc_html( $plugin['desc'] ); ?></p>
		<a target="_blank" href="<?php echo esc_url( $plugin['url'] ); ?>" class="blue_button">Check The Plugin</a>
	</div>
	<div style="clear:both"></div>
</div>
<?php endforeach; ?>
