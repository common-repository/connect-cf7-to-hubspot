<?php if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if ( $data->message ) { ?>
    <div class="notice <?php echo esc_html( $data->message['success'] ) ? 'notice-success' : 'notice-error'; ?> is-dismissible">
        <p><?php echo esc_html( $data->message['text'] ) ?? ''; ?></p>
    </div>
<?php } ?>