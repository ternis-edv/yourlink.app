# Custom Domain Support Plan: yourlink.app

## 1. Concept
Users can add their own domains (e.g., `links.mybrand.com`) to shorten links. Instead of `yourlink.app/hash`, the link becomes `links.mybrand.com/hash`.

## 2. Infrastructure (The "Magic" Part)
To support this at scale with automatic SSL, we recommend using **Caddy** as a reverse proxy.

### Flow:
1.  **DNS:** User creates a CNAME record pointing `links.mybrand.com` to `cname.yourlink.app`.
2.  **Caddy:** Listens for incoming traffic. When a new domain hits Caddy, it uses **On-Demand TLS** to issue a Let's Encrypt certificate on the fly.
3.  **Application:** Laravel receives the request. We check the `Host` header.
    *   If `Host` is `yourlink.app`, we use standard routing.
    *   If `Host` is anything else, we look up the domain in the `domains` table.

## 3. Database Schema (Already Implemented)
*   **`domains` table:** Stores `host`, `is_verified`, and `user_id`.
*   **`links` table:** Has a `domain_id` column. If null, it defaults to `yourlink.app`.

## 4. Implementation Steps
1.  **Domain Verification:** Ask user to add a TXT record to verify ownership.
2.  **Middleware:** A global middleware to detect the domain and set the context.
3.  **Redirection Logic:** Update the redirection route to filter links by `domain_id` if a custom domain is used.

## 5. Security
*   Blacklist common domains (google.com, etc.).
*   Rate limiting per domain.
*   SSL termination at the edge.
