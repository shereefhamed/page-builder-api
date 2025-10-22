<?php
global $wpdb;
//$table_name = $wpdb->prefix . CREATED_PAGES_TABLE;

// Filters
$api_key_filter = isset($_GET['api_key']) ? sanitize_text_field($_GET['api_key']) : '';
$date_from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '';
$date_to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '';

$where = 'WHERE 1=1';
if ($api_key_filter) $where .= $wpdb->prepare(' AND api_key_preview LIKE %s', '%' . $wpdb->esc_like($api_key_filter) . '%');
if ($date_from && $date_to) $where .= $wpdb->prepare(' AND created_date BETWEEN %s AND %s', $date_from, $date_to);

$pages = $wpdb->get_results("SELECT * FROM {$this->pages_table} $where ORDER BY created_date DESC LIMIT 100");

?>

<div style="margin-top:20px;">
    <h1 style="float: left; width: 100%;">Created Pages (via API)</h1>
    <form method="get">
        <input type="hidden" name="page" value="page_builder" />
        <input type="hidden" name="tab" value="created-page" />

        <label>API Key:</label>
        <input type="text" name="api_key" value="<?php echo esc_attr($api_key_filter); ?>" />

        <label>From:</label>
        <input type="date" name="from" value="<?php echo esc_attr($date_from); ?>" />
        <label>To:</label>
        <input type="date" name="to" value="<?php echo esc_attr($date_to); ?>" />

        <input type="submit" class="button" value="Filter" />
        <!-- <a href="<?php echo admin_url('tools.php?page=page_builder&tab=created-pages&export=1'); ?>" class="button">Export CSV</a> -->
        <a href="#" class="button" id="export">Export CSV</a>
    </form>

    <table class="widefat fixed striped dataTable" style="margin-top:15px;">
        <thead>
            <tr>
                <th>Page Title</th>
                <th>URL</th>
                <th>Created Date</th>
                <th>Created By (API Key)</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($pages) : ?>
            <?php foreach ($pages as $page) : ?>
                <tr>
                    <td><?php echo esc_html($page->page_title); ?></td>
                    <td><a href="<?php echo esc_url($page->page_url); ?>" target="_blank"><?php echo esc_html($page->page_url); ?></a></td>
                    <td><?php echo esc_html($page->created_date); ?></td>
                    <td><?php echo esc_html($page->api_key_name . ' (' . $page->api_key_preview . ')'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="4">No pages found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>