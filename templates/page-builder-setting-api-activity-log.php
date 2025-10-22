<?php
/* if (isset($_GET['export']) && $_GET['export'] == 1) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'page_builder_api_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY timestamp DESC", ARRAY_A);

    // Clean any output buffer
    if (ob_get_length()) ob_end_clean();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="api_logs.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Timestamp', 'API Key', 'Endpoint', 'Status', 'Pages Created', 'Response Time', 'IP Address']);

    foreach ($logs as $log) {
        fputcsv($output, [
            $log['timestamp'],
            $log['api_key_preview'],
            $log['endpoint'],
            $log['status'],
            $log['pages_created'],
            $log['response_time'],
            $log['ip_address'],
        ]);
    }
    fclose($output);
    exit;
} */ 
global $wpdb;
//$table_name = $wpdb->prefix . 'page_builder_api_logs';

$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$api_key_filter = isset($_GET['api_key']) ? sanitize_text_field($_GET['api_key']) : '';
$date_from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '';
$date_to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '';

$where = 'WHERE 1=1';
if ($status_filter) $where .= $wpdb->prepare(' AND status = %s', $status_filter);
if ($api_key_filter) $where .= $wpdb->prepare(' AND api_key_preview LIKE %s', '%' . $wpdb->esc_like($api_key_filter) . '%');
if ($date_from && $date_to) $where .= $wpdb->prepare(' AND timestamp BETWEEN %s AND %s', $date_from, $date_to); 

$logs = $wpdb->get_results("SELECT * FROM {$this->logs_table} $where ORDER BY timestamp DESC LIMIT 100");
?>
<div style="margin-top:20px;">
    <h1 style="float: left; width: 100%;">Recent API Requests</h1>
    <form method="get">
        <input type="hidden" name="page" value="page_builder" />
        <input type="hidden" name="tab" value="api-activity-log" />
        <label>Status:</label>
        <select name="status">
            <option value="">All</option>
            <option value="success" <?php echo selected($status_filter, 'success', false) ?>>Success</option>
            <option value="failed" <?php echo selected($status_filter, 'failed', false) ?>>Failed</option>
        </select>
        <label>API Key:</label> <input type="text" name="api_key" value="<?php echo esc_attr($api_key_filter) ?>" />
        <label>From:</label> <input type="date" name="from" value="<?php echo esc_attr($date_from) ?>" />
        <label>To:</label> <input type="date" name="to" value="<?php echo esc_attr($date_to) ?>" />
        <input type="submit" class="button" value="Filter" />
        <!-- <a href="<?php echo admin_url('tools.php?page=page_builder&tab=api-activity-log&export=1')?>" class="button">Export CSV</a> -->
        <a href="#" class="button" id="export">Export CSV</a>
    </form>
    <table class="widefat fixed striped dataTable">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>API Key</th>
                <th>Endpoint</th>
                <th>Status</th>
                <th>Pages Created</th>
                <th>Response Time (s)</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log->timestamp); ?></td>
                        <td><code><?php echo esc_html($log->api_key_preview); ?></code></td>
                        <td><?php echo esc_html($log->endpoint); ?></td>
                        <td><?php echo $log->status === 'success'
                            ? '<span style="color:green;font-weight:bold;">Success</span>'
                            : '<span style="color:red;font-weight:bold;">Failed</span>'; ?></td>
                        <td><?php echo esc_html($log->pages_created); ?></td>
                        <td><?php echo esc_html(number_format($log->response_time, 3)); ?></td>
                        <td><?php echo esc_html($log->ip_address); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No logs found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>