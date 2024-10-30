<?php if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
$data->template->set_template_data(
	array(
		'title' => esc_html__( 'Settings', 'connect-cf7-hubspot' ),
	)
)->get_template_part( 'admin/header' );

$data->template->set_template_data(
	array(
		'message' => $data->settings['message'] ?? false,
	)
)->get_template_part( 'admin/message' );
?>
    <div id="poststuff">
        <div id="post-body">
            <div class="form-wrapper postbox">
                <h3 class="hndle">
                    <label for="title">
						<?php esc_html_e( 'Access Token', 'connect-cf7-hubspot' ); ?>
                    </label>
                </h3>
                <div class="form-group inside">
                    <form method="post">
                        <div class="form-inline-flex">
                            <input class="mw-400" type="text" name="cfhs_access_token"
                                   id="cfhs_access_token"
                                   value="<?php echo esc_html( $data->settings['access_token'] ) ?? ''; ?>"
                                   required/>

							<?php wp_nonce_field( 'cf7hs_submit_form' ); ?>
                            <input type='submit' class='button button-secondary' name="connect"
                                   value="<?php esc_html_e( 'Connect', 'connect-cf7-hubspot' ); ?>"/>
                        </div>
                    </form>
                </div>
                <h3 class="hndle">
                    <label for="title"><?php echo __("To obtain a HubSpot Access Token, follow these steps:", "connect-cf7-hubspot"); ?></label>
                </h3>
                <div class="form-group inside">
                    <ol>
                        <li><?php echo __("Log in to your HubSpot account as an administrator.", "connect-cf7-hubspot"); ?></li>
                        <li><?php echo __("Go to HubSpot -> Settings -> Integrations -> Private apps.", "connect-cf7-hubspot"); ?>
                            <ol style="margin-top:5px;">
                                <li><?php echo __("Click on ‘Create a private app’ button.", "connect-cf7-hubspot"); ?></li>
                                <li><?php echo __("In the \"Basic Info\" tab, add an app name and description.", "connect-cf7-hubspot"); ?></li>
                                <li><?php echo __("In the \"Scopes\" tab, select the following \"CRM\" and \"Standard\" scopes: crm.objects.contacts, crm.objects.deals, crm.schemas.contacts, crm.schemas.deals.", "connect-cf7-hubspot"); ?></li>
                                <li><?php echo __("Under \"files,\" check the \"Request\" checkbox.", "connect-cf7-hubspot"); ?></li>
                                <li><?php echo __("Under \"forms,\" check the \"Request\" checkbox.", "connect-cf7-hubspot"); ?></li>
                            </ol>
                        </li>
                        <li><?php echo __("Click on ‘Create app’ button.", "connect-cf7-hubspot"); ?></li>
                        <li><?php echo __("Copy the generated access token for use in our plugin.", "connect-cf7-hubspot"); ?></li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <form method="post">
                <h3 scope="row"><label><?php esc_html_e( 'API Error Notification', 'connect-cf7-hubspot' ); ?></label>
                </h3>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Subject', 'connect-cf7-hubspot' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" name="cfhs_notification_subject"
                                   value="<?php echo esc_html( $data->settings['notification_subject'] ) ?? ''; ?>"/>
                            <p class="description"><?php esc_html_e( 'Enter the subject.', 'connect-cf7-hubspot' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Send To', 'connect-cf7-hubspot' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" name="cfhs_notification_send_to"
                                   value="<?php echo esc_html( $data->settings['notification_send_to'] ) ?? ''; ?>"/>
                            <p class="description"><?php esc_html_e( 'Enter the email address. For multiple email addresses, you can add email address by comma separated.', 'connect-cf7-hubspot' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label class="form-check-label"
                                   for="gridcfhs_uninstallCheck"><?php esc_html_e( 'Delete data on uninstall?', 'connect-cf7-hubspot' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" class="form-check-input" name="cfhs_uninstall"
                                   id="cfhs_uninstall"
                                   value="1"<?php echo esc_html( $data->settings['uninstall'] ) === '1' ? ' checked' : ''; ?> />
                        </td>
                    </tr>

                    </tbody>
                </table>
                <div class="submit">
					<?php wp_nonce_field( 'cf7hs_submit_form' ); ?>
                    <input type='submit' class='button-primary' name="submit"
                           value="<?php esc_html_e( 'Save Changes', 'connect-cf7-hubspot' ); ?>"/>
                </div>
            </form>
        </div>
    </div>
    </div>
<?php
$data->template->get_template_part( 'admin/footer' );


