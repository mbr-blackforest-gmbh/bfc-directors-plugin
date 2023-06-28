<div class="director">
    <h2><?php echo esc_html($director_title); ?></h2>
    <div class="content"><?php echo wp_kses_post($director_content); ?></div>
    <img src="<?php echo esc_url($director_image); ?>" alt="<?php echo esc_attr($director_title); ?>">
    <div class="contact-info">
        <p>
            <?php echo __('e-Mail:'); ?>
            <?php echo esc_html($director_email); ?>
        </p>
        <p>
            <?php echo __('Phone:'); ?>
            <?php echo esc_html($director_phone); ?>
        </p>
    </div>
</div>