<?php
defined('ABSPATH') || exit;
?>
<div class="wpda_wrap wrap wpda_main_admin">
    <?php Wpda_Countdown_Admin_Fields::render_pro_banner(); ?>
    <h2><?php echo esc_html( $params['name'] ); ?>
        <a href="<?php echo esc_url( $params['add_new_link'] ); ?>" class="add-new-h2">Add New</a>
    </h2>
    <div class="wpda_table_container" id="wpda_table_container"></div>
</div>
