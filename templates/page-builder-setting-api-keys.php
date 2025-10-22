<?php
global $wpdb;
$api_key= null;

if (isset($_POST['generate_api_key'])) {
    check_admin_referer('generate_api_key_nonce');

    $key_name = sanitize_text_field($_POST['key_name']);
    $expires = !empty($_POST['expires_at']) ? sanitize_text_field($_POST['expires_at']) : null;

    $api_key = wp_generate_password(64, false, false);

    $api_key = bin2hex(random_bytes(32)); 
    $api_key_hash = wp_hash_password($api_key);

    $wpdb->insert(
        $this->api_keys_table, 
        [
            'api_key' => $api_key_hash,
            'key_name' => $key_name,
            'expires_at' => $expires,
            'permissions' => 'create_pages',
            'status' => 'active'
        ]
    );

}

// Handle revoke
 if (isset($_GET['revoke_key']) && check_admin_referer('revoke_api_key_' . intval($_GET['revoke_key']))) {
    $wpdb->update(
        $this->api_keys_table, 
        ['status' => 'revoked'],
        ['id' => intval($_GET['revoke_key'])]
    );
    echo '<div class="notice notice-warning"><p>API Key revoked.</p></div>';
}

// Handle delete
if (isset($_GET['delete_key']) && check_admin_referer('delete_api_key_' . intval($_GET['delete_key']))) {
    $wpdb->delete(
        $this->api_keys_table,
        ['id' => intval($_GET['delete_key'])]
    );
    echo '<div class="notice notice-success"><p>API Key deleted.</p></div>';
}

// Fetch all keys
$keys = $wpdb->get_results("SELECT * FROM {$this->api_keys_table} ORDER BY created_at DESC");
?>

<div style="margin-top:20px;">
     <h1 style="float: left; width: 100%;">Page Builder API Keys</h1>
    <form method="post" style="margin-top:20px;">
        <?php if($api_key!=null): ?>
            <div class="notice notice-success">
                <p>API Key generated successfully, Save this key securely, you won't see it again</p>
                <p><span id="generated-api-key"><?php echo $api_key; ?></span> <span class="dashicons dashicons-media-default copy-api-key"></span></p>
            </div>
        <?php endif; ?>
        <?php wp_nonce_field('generate_api_key_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="key_name">Key Name</label></th>
                <td><input type="text" name="key_name" id="key_name" style="width: 300px;" placeholder="e.g. Production Server" required></td>
            </tr>
            <tr>
                <th><label for="expires_at">Expiration Date</label></th>
                <td><input type="date" name="expires_at" id="expires_at"></td>
            </tr>
        </table>
        <p><input type="submit" name="generate_api_key" class="button button-primary" value="Generate New API Key"></p>
    </form>

    <h2 style="margin-top:40px;">Existing Keys</h2>
    <table class="widefat striped" style="margin-top:10px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Key Name</th>
                <th>API Key</th>
                <th>Status</th>
                <th>Created</th>
                <th>Expires</th>
                <th>Last Used</th>
                <th>Requests</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($keys): ?>
                <?php foreach ($keys as $key): ?>
                    <tr>
                        <td><?php echo esc_html($key->id); ?></td>
                        <td><?php echo esc_html($key->key_name); ?></td>
                        <td><code><?php echo substr($key->api_key, 0, 8) . '***'; ?></code></td>
                        <td>
                            <?php if ($key->status === 'active'): ?>
                                <span style="color:green;">Active</span>
                            <?php else: ?>
                                <span style="color:red;">Revoked</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($key->created_at); ?></td>
                        <td><?php echo esc_html($key->expires_at ?: 'Never'); ?></td>
                        <td><?php echo esc_html($key->last_used ?: '—'); ?></td>
                        <td><?php echo esc_html($key->request_count); ?></td>
                        <td>
                            <?php if ($key->status === 'active'): ?>
                                <a href="<?php echo wp_nonce_url(admin_url('tools.php?page=page_builder&tab=api-keys&revoke_key=' . $key->id), 'revoke_api_key_' . $key->id); ?>" class="button button-small">Revoke</a>
                            <?php endif; ?>
                                <a href="<?php echo wp_nonce_url(admin_url('tools.php?page=page_builder&tab=api-keys&delete_key=' . $key->id), 'delete_api_key_' . $key->id); ?>" class="button button-small">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10">No API keys found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>