# Simple Page Builder – Custom REST API Page Creator

**Simple Page Builder** is a WordPress plugin that provides a secure REST API endpoint for creating multiple WordPress pages in bulk.  
It includes a full API key management system, activity logs, webhook notifications, and security controls — ideal for headless CMS setups, integrations, or automated deployments.

---

## Features

- ✅ **Create pages via REST API** (`POST /wp-json/pagebuilder/v1/create-pages`)
- 🔑 **API Key Management** (generate, revoke, expire, and monitor keys)
- 📊 **Activity Logs** (track all API usage and requests)
- 📄 **Created Pages Log** (view all pages created through API)
- 🌐 **Webhook Support** (notify external systems when pages are created)
- ⚙️ **Settings for:**
  - Global API access toggle  
  - Default webhook URL  
  - Rate limit per key  
  - Default key expiration  

---

## 🧠 API Overview

### **Base URL**
```
https://yourdomain.com/wp-json/pagebuilder/v1/
```

### **Endpoint**
```
POST /create-pages
```

### **Authentication**
Each request **must** include a valid `api_key` (either in query params or request headers).

#### Example (Query Param)
```
POST /wp-json/pagebuilder/v1/create-pages?api_key=YOUR_SECRET_API_KEY
```

#### Example (Header)
```
api_key: YOUR_SECRET_API_KEY
```

---

## 📦 Example cURL Request

```bash
curl -X POST "https://yourdomain.com/wp-json/pagebuilder/v1/create-pages?api_key=YOUR_SECRET_API_KEY" -H "Content-Type: application/json" -d '{
  "pages": [
    {
      "title": "About Us",
      "content": "<p>Welcome to our company!</p>"
    },
    {
      "title": "Contact",
      "content": "<p>Contact us at info@example.com</p>"
    }
  ]
}'
```

### ✅ Example Successful Response

```json
{
  "status": "success",
  "message": "3 pages created successfully",
  "created_pages": [
    {
      "id": 123,
      "title": "About Us",
      "url": "https://yourdomain.com/about-us"
    },
    {
      "id": 124,
      "title": "Contact",
      "url": "https://yourdomain.com/contact"
    }
  ]
}
```

### ❌ Example Error Response

```json
{
  "status": "error",
  "message": "Invalid API key or insufficient permissions."
}
```

---

## 🔐 API Key Management

Each key has:

| Field | Description |
|--------|--------------|
| **API Key** | Securely hashed and stored key (shown once on creation) |
| **Key Name** | Human-readable identifier (e.g., "Mobile App", "Production Server") |
| **Status** | Active / Revoked |
| **Created Date** | Timestamp of key creation |
| **Expiration Date** | Optional expiration (or Never) |
| **Last Used** | Updated on each request |
| **Request Count** | Total requests made |

---

## 📡 Webhook Notifications

When pages are created, the plugin automatically sends a POST request to the configured **Webhook URL** (set in Settings).

### **Webhook Payload Example**

```json
{
  "event": "pages_created",
  "timestamp": "2025-10-07T14:30:00Z",
  "request_id": "req_abc123xyz",
  "api_key_name": "Production Server",
  "total_pages": 3,
  "pages": [
    {
      "id": 123,
      "title": "About Us",
      "url": "http://site.com/about"
    },
    {
      "id": 124,
      "title": "Contact",
      "url": "http://site.com/contact"
    }
  ]
}
```


## 🧾 Admin Interface

### **Tabs**

#### 🔑 API Keys
Generate, revoke, or manage API keys.

#### ⚙️ Settings
- Default webhook URL  
- Rate limit (requests/hour/key)  
- Enable or disable all API access  
- Default expiration (30, 60, 90 days, or never)

#### 📜 Activity Log
View and filter recent API requests:
- Timestamp  
- API Key (previewed)  
- Endpoint  
- Status (success/failed)  
- Pages created  
- Response time  
- IP address  
Filter by **Date Range**, **Status**, or **API Key**  
✅ Export as **CSV**

#### 📄 Created Pages
List of all pages created via API:
- Title (linked)  
- URL  
- Created date  
- Created by (API Key name)

---

## ⚙️ Installation

1. Upload the plugin folder to:
   ```
   /wp-content/plugins/simple-page-builder/
   ```
2. Activate **Simple Page Builder** in **Plugins → Installed Plugins**.
3. Go to **Tools → Page Builder** to manage API keys and settings.

---


