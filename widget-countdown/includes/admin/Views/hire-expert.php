<?php
defined( 'ABSPATH' ) || exit;

$services = array(
	array( 'image' => '1.png', 'title' => 'Custom WordPress Development',              'desc' => 'Hire a WordPress expert and make any custom development for your WordPress website.' ),
	array( 'image' => '2.png', 'title' => 'WordPress Plugin Development',              'desc' => 'Our developers can create any WordPress plugin from zero. Also, they can customize any plugin and add any functionality.' ),
	array( 'image' => '3.png', 'title' => 'WordPress Theme Development',               'desc' => 'If you need an unique theme or any customizations for a ready theme, then our developers are ready.' ),
	array( 'image' => '4.png', 'title' => 'WordPress Theme Installation and Customization', 'desc' => 'If you need a theme installation and configuration, then just let us know, our experts configure it.' ),
	array( 'image' => '5.png', 'title' => 'General WordPress Support',                 'desc' => 'Our developers can provide general support. If you have any problem with your website, then our experts are ready to help.' ),
	array( 'image' => '6.png', 'title' => 'WordPress Speed Optimization',              'desc' => 'Hire an expert from WpDevArt and let him take care of your website speed optimization.' ),
	array( 'image' => '7.png', 'title' => 'WordPress Migration Services',              'desc' => 'Our developers can migrate websites from any platform to WordPress.' ),
	array( 'image' => '8.png', 'title' => 'WordPress On-Page SEO',                     'desc' => 'On-page SEO is an important part of any website. Hire an expert and they will organize the on-page SEO for your website.' ),
);

$hire_url = 'https://wpdevart.com/hire-wordpress-developer-dedicated-experts-are-ready-to-help';
$img_base = wpda_countdown_plugin_url . 'includes/admin/images/hire_expert/';
?>
<h1 class="wpdev_hire_exp_h1">Hire an Expert from WpDevArt</h1>
<div class="hire_expert_main">
	<?php foreach ( $services as $svc ) : ?>
	<div class="wpdevart_hire_main">
		<a target="_blank" class="wpdev_hire_buklet" href="<?php echo esc_url( $hire_url ); ?>">
			<div class="wpdevart_hire_image">
				<img src="<?php echo esc_url( $img_base . $svc['image'] ); ?>">
			</div>
			<div class="wpdevart_hire_information">
				<div class="wpdevart_hire_title"><?php echo esc_html( $svc['title'] ); ?></div>
				<p class="wpdevart_hire_description"><?php echo esc_html( $svc['desc'] ); ?></p>
			</div>
		</a>
	</div>
	<?php endforeach; ?>
	<div>
		<a target="_blank" class="wpdev_hire_button" href="<?php echo esc_url( $hire_url ); ?>">Hire an Expert</a>
	</div>
</div>
