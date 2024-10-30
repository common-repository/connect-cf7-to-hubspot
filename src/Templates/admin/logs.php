<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$data->template->set_template_data(
	array(
		'title' => 'Logs',
	)
)
                     ->get_template_part( 'admin/header' ); ?>
    <div class="form-wrapper postbox">
        <div class="form-group inside">
            <pre style="overflow-y: scroll;"><?php print_r( esc_textarea( $data->file_data ) ); ?></pre>
        </div>
    </div>
<?php
$data->template->get_template_part( 'admin/footer' );