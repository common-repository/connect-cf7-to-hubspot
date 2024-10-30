<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$cfhs_module   = $data->form['cfhs_module'];
$cf7_fields    = $data->form['cf7_fields'];
$cfhs_fields   = $data->form['cfhs_fields'];
$fields        = $data->form['fields'];
$hubspot_forms = $data->form['hubspot_forms'];
$object_types  = $data->form['object_types'];

$data->template->set_template_data(
	array(
		'title' => esc_html( $data->form['title'] ),
	)
)->get_template_part( 'admin/header' );
$data->template->set_template_data(
	array(
		'message' => $data->form['message'] ?? false,
	)
)->get_template_part( 'admin/message' );
?>
    <form method="post">
        <div class="form-wrapper postbox">
            <div class="form-group inside">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label class="form-check-label"
                                   for="cfhs"><?php esc_html_e( 'Turn on integration on form', 'connect-cf7-hubspot' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" class="form-check-input" name="cfhs_active"
                                   value="1"<?php echo '1' === $data->form['cfhs_active'] ? ' checked' : ''; ?> />
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="cfhs__form_row">
                    <strong>
                        <label for="cfhs_module">
							<?php esc_html_e( 'Select Module', 'connect-cf7-hubspot' ); ?>
                        </label>
                    </strong>
                </div>
                <div class="cfhs__form_row form-inline-flex">
                    <select class="form-control" id="cfhs_module" name="cfhs_module">
                        <option value=""><?php esc_html_e( 'Select an module', 'connect-cf7-hubspot' ); ?></option>
						<?php
						foreach ( $data->form['modules'] as $key => $value ) {
							$selected = $key === $cfhs_module ? ' selected="selected"' : '';
							?>
                            <option value="<?php echo esc_html( $key ); ?>"<?php echo esc_html( $selected ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						}

						if ( null !== $hubspot_forms ) {
							?>
                            <optgroup label="<?php esc_html_e( 'Forms', 'connect-cf7-hubspot' ); ?>">
								<?php
								foreach ( $hubspot_forms as $hubspot_form ) {
									$selected = $hubspot_form->id === $cfhs_module ? ' selected="selected"' : '';
									?>
                                    <option value="<?php echo esc_html( $hubspot_form->id ); ?>"<?php echo esc_html( $selected ); ?>><?php echo esc_html( $hubspot_form->name ); ?></option>
									<?php
								}
								?>
                            </optgroup>
							<?php
						}
						?>
                    </select>
                    <button type="submit" name="filter"
                            class='button-secondary'><?php esc_html_e( 'Filter', 'connect-cf7-hubspot' ); ?></button>
                </div>

				<?php
				if ( 'contacts' === $cfhs_module ) {
					?>
                    <div class="cfhs__form_row">
                        <div class="form-check">
                            <strong><label><?php esc_html_e( 'Actions: ', 'connect-cf7-hubspot' ); ?></label></strong>
                            <fieldset>
                                <label>
                                    <input type="radio" name="cfhs_action"
                                           value="create"<?php echo 'create' === esc_html( $data->form['action'] ) ? ' checked="checked"' : ''; ?> />
									<?php esc_html_e( 'Create Module Record', 'connect-cf7-hubspot' ); ?>
                                </label>&nbsp;&nbsp;
                                <label>
                                    <input type="radio" name="cfhs_action"
                                           value="create_or_update"<?php echo 'create_or_update' === esc_html( $data->form['action'] ) ? ' checked="checked"' : ''; ?> />
									<?php esc_html_e( 'Create/Update Module Record', 'connect-cf7-hubspot' ); ?>
                                </label>
                            </fieldset>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
        <div class="form-wrapper">
            <div class="form-group">
				<?php
				if ( $data->form['_form'] ) {
				if ( $cf7_fields ) {
				?>
                <table class="widefat striped">
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'CF7 Form Field', 'connect-cf7-hubspot' ); ?></th>
                        <th><?php esc_html_e( 'HubSpot Field', 'connect-cf7-hubspot' ); ?></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th><?php esc_html_e( 'CF7 Form Field', 'connect-cf7-hubspot' ); ?></th>
                        <th><?php esc_html_e( 'Hubspot Field', 'connect-cf7-hubspot' ); ?></th>
                    </tr>
                    </tfoot>
                    <tbody>
					<?php
					foreach ( $cf7_fields as $cf7_field_key => $cf7_field_value ) {
						?>
                        <tr>
                            <td><?php echo esc_html( $cf7_field_key ); ?></td>
                            <td>
                                <select name="cfhs_fields[<?php echo esc_html( $cf7_field_key ); ?>][key]">
                                    <option value=""><?php esc_html_e( 'Select a field', 'connect-cf7-hubspot' ); ?></option>
									<?php
									$_type        = '';
									$_object_type = '';
									if ( null !== $fields ) {

										foreach ( $fields as $field_key => $field_value ) {

											$selected = '';
											if ( isset( $cfhs_fields[ $cf7_field_key ]['key'] ) && $cfhs_fields[ $cf7_field_key ]['key'] === $field_key ) {
												$selected     = ' selected="selected"';
												$_type        = $field_value['type'];
												$_object_type = $field_value['object_type'];
											}
											?>
                                            <option value="<?php echo esc_html( $field_key ); ?>"<?php echo esc_html( $selected ); ?>><?php echo esc_html( $field_value['label'] ); ?>
                                                (
												<?php
												if ( array_key_exists( 'object_type', $field_value ) ) {
													echo 'Type: ' . esc_html( $object_types[ $field_value['object_type'] ] );
													echo ', ' . esc_html( $field_value['type'] );
												} else {
													echo  esc_html( $field_value['type'] );
												}

												echo 0 !== $field_value['required'] ? esc_html__( 'Required', 'connect-cf7-hubspot' ) : '';
												?>
                                                )
                                            </option>
											<?php
										}
									}
									?>
                                </select>
                                <input type="hidden"
                                       name="cfhs_fields[<?php echo esc_html( $cf7_field_key ); ?>][type]"
                                       value="<?php echo esc_html( $_type ); ?>"/>
                                <input type="hidden"
                                       name="cfhs_fields[<?php echo esc_html( $cf7_field_key ); ?>][object_type]"
                                       value="<?php echo esc_html( $_object_type ); ?>"/>
                            </td>
                        </tr>
						<?php
					}
					?>
                    </tbody>
                </table>
            </div>
        </div>
		<?php
		}
		}
		?>
        <div class="submit">
			<?php wp_nonce_field( 'cf7hs_submit_form' ); ?>
            <input type='submit' class='button-primary' name="submit"
                   value="<?php esc_html_e( 'Save Changes', 'connect-cf7-hubspot' ); ?>"/>
        </div>
    </form>
<?php
$data->template->get_template_part( 'admin/footer' );