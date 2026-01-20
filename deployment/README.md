# Comprehensive Deployment Guide: Laravel to AWS EC2

This guide covers the **entire process** from your local machine to a live, SSL-secured production server on AWS.

## Phase 1: Local Preparation (Do this NOW)

Before touching AWS, we need to save your deployment configuration to GitHub.

1.  **Commit your Deployment Files**:
    Run this in your terminal to save the Docker, Nginx, and DB configs we just created:
    ```bash
    git add deployment/ docker-compose.prod.yml
    git commit -m "Add production deployment configuration"
    git push origin main
    ```
    *(Note: Do NOT commit your `.env` file. We will create that on the server securely.)*

---

## Phase 2: AWS Infrastructure (Manual Setup)

1.  **Launch EC2 Instance**:
    *   **OS**: Ubuntu 24.04 LTS (64-bit Arm).
    *   **Type**: `t4g.small` (ARM64) is highly recommended. It is faster and cheaper than the x86 `t3` series.
    *   **Architecture**: Ensure you select **64-bit (Arm)** when launching the instance.
    *   **Note**: Docker (and the images we use like PHP, Node, MySQL) automatically supports ARM64, so no code changes are needed!
    *   **Elastic IP**: Go to EC2 Dashboard -> "Elastic IPs", allocate one, and associate it with your new instance. This gives you a permanent IP address.

2.  **Configure Security Group**:
    Go to "Security Groups" in EC2 and ensure these **Inbound Rules** are set:
    *   **SSH (22)**: Source: My IP (Strictly restrict this!).
    *   **HTTP (80)**: Source: Anywhere (0.0.0.0/0).
    *   **HTTPS (443)**: Source: Anywhere (0.0.0.0/0).

3.  **DNS Configuration**:
    *   Go to your domain registrar (GoDaddy, Namecheap, Route53, etc.).
    *   Create an **A Record** for your domain (e.g., `api.yourdomain.com`).
    *   Point it to your **Elastic IP**.

---

## Phase 3: Server Setup

Connect to your server: `ssh -i "key.pem" ubuntu@<elastic-ip>`

### 1. Install Docker & Utilities
These commands install Docker and Docker Compose on Ubuntu.
```bash
# Update System
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Git & Unzip
sudo apt install -y git unzip

# Add User to Docker Group (avoids typing sudo for every docker command)
sudo usermod -aG docker ${USER}
```
*Logout and log back in for the user permission to take effect.*

### 2. Clone Repository
```bash
git clone <your-github-repo-url>
cd wateranalytics-australia-api
```

---

## Phase 4: Application Configuration

### 1. Create Production .env
We need to create the secrets file manually on the server.
```bash
cp .env.example .env
nano .env
```
**Critical changes to make inside nano**:
*   `APP_ENV=production`
*   `APP_DEBUG=false`
*   `APP_URL=https://<your-domain.com>`
*   `DB_CONNECTION=mysql`
*   `DB_HOST=db` (IMPORTANT: Must match the service name in docker-compose!)
*   `DB_PORT=3306`
*   `DB_DATABASE=laravel` (Or whatever you want your DB name to be)
*   `DB_USERNAME=laravel_user`
*   `DB_PASSWORD=YOUR_STRONG_PASSWORD`

### 2. Update Nginx & Certbot Domain
Edit the `nginx.conf` to use your real domain.
```bash
nano deployment/nginx.conf
```
*   Change `server_name yourdomain.com;` to your real domain in BOTH server blocks.
*   Change the ssl_certificate paths to match your real domain.

---

## Phase 5: The "First Run" (SSL Setup)

Nginx will fail to start if SSL certificates are missing. We need to generate them first.

1.  **Generate Certificates** (Replace `yourdomain.com` with your real domain):
    ```bash
    docker run --rm -it \
      -v $(pwd)/certbot/conf:/etc/letsencrypt \
      -v $(pwd)/certbot/www:/var/www/certbot \
      certbot/certbot certonly --webroot \
      -w /var/www/certbot \
      -d yourdomain.com
    ```
    *Select "Webroot" or follow the prompts.*

2.  **Start Production Containers**:
    Now that certificates exist, we can start everything.
    ```bash
    docker compose -f docker-compose.prod.yml up -d --build
    ```

---

## Phase 6: Post-Launch Optimization

Your app is running, but the database is empty and caches are cold.

1.  **Run Migrations**:
    ```bash
    docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
    ```

2.  **Optimize Caches**:
    ```bash
    docker compose -f docker-compose.prod.yml exec app php artisan config:cache
    docker compose -f docker-compose.prod.yml exec app php artisan route:cache
    docker compose -f docker-compose.prod.yml exec app php artisan view:cache
    ```

3.  **Setup Scheduler (Cron)**:
    If your app has scheduled commands (like warranty calculations), add this to the server's crontab (`crontab -e`):
    ```bash
    * * * * * docker compose -f /home/ubuntu/wateranalytics-australia-api/docker-compose.prod.yml exec app php artisan schedule:run >> /dev/null 2>&1
    ```

---

## Maintenance & Updates

### How to Deploy New Code
Because we use **Opcache** for performance, code changes are ignored until you rebuild.
```bash
# 1. Pull latest code
git pull origin main

# 2. Rebuild the app container (Critical!)
docker compose -f docker-compose.prod.yml up -d --build

# 3. Run migrations (if needed)
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### Viewing Logs
```bash

---

## Phase 7: Troubleshooting & Monitoring

*   **Logs**: `docker compose -f docker-compose.prod.yml logs -f app`
*   **Asset Issues**: The Dockerfile now builds assets automatically. If you see style issues, force a rebuild: `docker compose -f docker-compose.prod.yml up -d --build --force-recreate`
```
