<?php
if (isset($_POST['pagebuilder_settings_save'])) {
    check_admin_referer('pagebuilder_settings_nonce');

    $webhook_url = sanitize_text_field($_POST['webhook_url']);
    $rate_limit = intval($_POST['rate_limit']);
    $api_enabled = isset($_POST['api_enabled']) ? 1 : 0;
    $default_expiration = sanitize_text_field($_POST['default_expiration']);

    update_option('pagebuilder_webhook_url', $webhook_url);
    update_option('pagebuilder_rate_limit', $rate_limit);
    update_option('pagebuilder_api_enabled', $api_enabled);
    update_option('pagebuilder_default_expiration', $default_expiration);

    echo '<div class="updated notice"><p>Settings saved successfully.</p></div>';
}


$webhook_url = get_option('pagebuilder_webhook_url', '');
$rate_limit = get_option('pagebuilder_rate_limit', 100); 
$api_enabled = get_option('pagebuilder_api_enabled', 1);
$default_expiration = get_option('pagebuilder_default_expiration', 'never');
?>

<div style="margin-top:20px;">
    <h1 style="float: left; width: 100%;">Page Builder Settings</h1>
    <form method="post" action="">
        <?php wp_nonce_field('pagebuilder_settings_nonce'); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <!-- Webhook URL -->
                <tr>
                    <th scope="row"><label for="webhook_url">Default Webhook URL</label></th>
                    <td>
                        <input type="url" name="webhook_url" id="webhook_url" class="regular-text" value="<?php echo esc_attr($webhook_url); ?>" placeholder="https://example.com/webhook-endpoint" />
                        <p class="description">When pages are created via API, the plugin will send a POST request to this URL (if set).</p>
                    </td>
                </tr>

                <!-- Rate Limit -->
                <tr>
                    <th scope="row"><label for="rate_limit">Rate Limit (Requests per Hour per API Key)</label></th>
                    <td>
                        <input type="number" name="rate_limit" id="rate_limit" class="small-text" value="<?php echo esc_attr($rate_limit); ?>" min="1" />
                        <p class="description">Maximum number of API requests allowed per hour for each API key.</p>
                    </td>
                </tr>

                <!-- API Enable/Disable -->
                <tr>
                    <th scope="row">Enable API Access</th>
                    <td>
                        <label>
                            <input type="checkbox" name="api_enabled" value="1" <?php checked($api_enabled, 1); ?> />
                            Enable global API access
                        </label>
                        <p class="description">Uncheck to temporarily disable all API endpoints globally.</p>
                    </td>
                </tr>

                <!-- Default API Key Expiration -->
                <tr>
                    <th scope="row"><label for="default_expiration">Default API Key Expiration</label></th>
                    <td>
                        <select name="default_expiration" id="default_expiration">
                            <option value="30" <?php selected($default_expiration, '30'); ?>>30 days</option>
                            <option value="60" <?php selected($default_expiration, '60'); ?>>60 days</option>
                            <option value="90" <?php selected($default_expiration, '90'); ?>>90 days</option>
                            <option value="never" <?php selected($default_expiration, 'never'); ?>>Never expire</option>
                        </select>
                        <p class="description">When creating a new API key, this will be the default expiration period.</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" name="pagebuilder_settings_save" class="button-primary">Save Changes</button>
        </p>
    </form>
</div>