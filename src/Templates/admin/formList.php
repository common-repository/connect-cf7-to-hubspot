<?php if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
$data->template->set_template_data(
	array(
		'title' => esc_html__( 'CF7 - HubSpot Connection', 'connect-cf7-hubspot' ),
	)
)->get_template_part( 'admin/header' );
?>
    <table class="widefat striped">
        <thead>
        <tr>
            <th><?php esc_html_e( 'CF7 Form', 'connect-cf7-hubspot' ); ?></th>
            <th><?php esc_html_e( 'Edit', 'connect-cf7-hubspot' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		if ( $data->forms ) {
			foreach ( $data->forms as $form ) {
				?>
                <tr class="<?php echo '1' === $form['status'] ? 'active' : 'inactive'; ?>">
                    <td><?php echo esc_html( $form['title'] ); ?></td>
                    <td>
                        <a href="<?php echo esc_html( $form['link'] ); ?>"><span
                                    class="dashicons dashicons-edit"></span></a>
                    </td>
                </tr>
				<?php
			}
		} else {
			?>
            <tr>
                <td colspan="3"><?php esc_html_e( 'No forms found.', 'connect-cf7-hubspot' ); ?></td>
            </tr>
			<?php
		}
		?>
        </tbody>
    </table>
<?php
$data->template->get_template_part( 'admin/footer' );
