<div style="margin-top:20px;">
    <h1 style="float: left; width: 100%;">PageBuilder API — Documentation</h1>
    <p>Secure REST API to create pages in bulk. Authenticated by API keys generated in the WordPress admin. This doc describes endpoints, authentication, examples, and admin features (key management, logs, created pages).</p>

    <h2>Base URL</h2>
    <p>Replace <code>{{SITE_URL}}</code> with your site root (for local dev use <code>http://localhost/page-builder</code>).</p>
    <pre><code>https://{{SITE_URL}}/wp-json/pagebuilder/v1</code></pre>

    <h2>Primary Endpoint</h2>
    <p><strong>Create pages (bulk)</strong></p>
    <pre><code>POST https://{{SITE_URL}}/wp-json/pagebuilder/v1/create-pages</code></pre>

    <h2>Authentication</h2>
    <p>This API uses API keys generated in the WordPress admin. Important security details:</p>
    <ul>
        <li>The plain API key is shown <strong>only once</strong> immediately after generation — copy it then. The plugin stores a hashed copy (using WordPress hashing).</li>
        <li>To authenticate, send the API key either as a request header <code>api-key</code> or as a query parameter <code>?api_key=...</code>.</li>
        <li>On each request the plugin verifies the plain key by checking it against the stored hashed keys (via <code>wp_check_password()</code>-style verification).</li>
        <li>Keys can be <em>Active</em> or <em>Revoked</em>, and may optionally have an expiration date.</li>
    </ul>

    <h3>Header Example</h3>
    <pre><code>api-key: YOUR_GENERATED_API_KEY_GOES_HERE</code></pre>

    <h3>Query parameter Example</h3>
    <pre><code>?api_key=YOUR_GENERATED_API_KEY_GOES_HERE</code></pre>

    <h2>Request: Create Pages (Bulk)</h2>
    <p>Send JSON body with <code>pages</code> array. Each object should contain at least a <code>title</code>. <code>content</code> is optional.</p>

    <h3>Request headers</h3>
    <pre><code>Content-Type: application/json
    api-key: YOUR_GENERATED_API_KEY_GOES_HERE</code></pre>

    <h3>Request body (example)</h3>
    <pre><code>{
    "pages": [
        {
        "title": "About Us (API)",
        "content": "AboutCreated via API"
        },
        {
        "title": "Contact (API)",
        "content": "Contact email: info@example.com"
        }
    ]
    }</code></pre>

    <h2>Responses</h2>

    <h3>Success (HTTP 200)</h3>
    <pre><code>{
    "success": true,
    "created_count": 2,
    "failed_count": 0,
    "created_pages": [
        {
            "title": "About Us",
            "page_id": 20,
            "link": "http://localhost/assessment/index.php/about-us-7/"
        },
        {
            "title": "Contact",
            "page_id": 21,
            "link": "http://localhost/assessment/index.php/contact-7/"
        }
    ],
    "failed_pages": []
    }</code></pre>

    <h3>Common error responses</h3>
    <table>
        <thead><tr><th>Status</th><th>Code</th><th>Message (example)</th></tr></thead>
        <tbody>
        <tr><td>401</td><td><code>unauthorized</code></td><td>Invalid or missing API key</td></tr>
        <tr><td>403</td><td><code>unauthorized</code></td><td>API key revoked / expired / no permission</td></tr>
        <tr><td>400</td><td><code>invalid_input</code></td><td>Expected an array of pages</td></tr>
        <tr><td>500</td><td><code>server_error</code></td><td>Internal error (PHP / DB)</td></tr>
        </tbody>
    </table>

    <h2>cURL Examples</h2>

    <h3>1) Create pages — send API key in header</h3>
    <pre><code>curl -X POST "https://{{SITE_URL}}/wp-json/pagebuilder/v1/create-pages" \
    -H "Content-Type: application/json" \
    -H "api-key: YOUR_GENERATED_API_KEY_GOES_HERE" \
    -d '{
        "pages":[
        {"title":"API Page 1","content":"Hello 1<"},
        {"title":"API Page 2","content":"Hello 2"}
        ]
    }'</code></pre>

    <h3>2) Create pages — send API key as query param</h3>
    <pre><code>curl -X POST "https://{{SITE_URL}}/wp-json/pagebuilder/v1/create-pages?api_key=YOUR_GENERATED_API_KEY_GOES_HERE" \
    -H "Content-Type: application/json" \
    -d '{
        "pages":[ {"title":"API Page A"} ]
    }'</code></pre>

    <h2>Admin: Generate & Manage API Keys</h2>
    <p>In WP Admin area (the plugin provides a page, e.g.):</p>
    <ul>
        <li><strong>Generate API Key</strong> — provide a name (e.g., <code>Production Server</code>), optional expiration date. The plain key is shown once immediately after generation — copy it now.</li>
        <li><strong>Status</strong> — keys can be <em>Active</em> or <em>Revoked</em> or <em>Deleted</em>.</li>
    </ul>


    <h2>Admin: Logs & Created Pages</h2>
    <p>The plugin logs each API request and each created page. Available admin tabs (examples):</p>

    <div class="grid">
        <div class="box">
        <h3>API Activity Log</h3>
        <p>Shows recent requests with:</p>
        <ul>
            <li>Timestamp</li>
            <li>API Key (preview, e.g., 8 chars)</li>
            <li>Endpoint</li>
            <li>Status (success / failed)</li>
            <li>Pages Created</li>
            <li>Response Time</li>
            <li>IP Address</li>
        </ul>
        <p>Filters: <strong>Status</strong>, <strong>Date Range</strong>, <strong>API Key</strong>. Export logs as CSV.</p>
        </div>
        <div class="box">
        <h3>Created Pages</h3>
        <p>Shows pages created by the API with:</p>
        <ul>
            <li>Page Title</li>
            <li>URL (clickable)</li>
            <li>Created Date</li>
            <li>Created By (API Key name + preview)</li>
        </ul>
        <p>Supports date range and API key filters and CSV export.</p>
        </div>
    </div>


</div>