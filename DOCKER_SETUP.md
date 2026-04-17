# Membix - Docker Setup Guide

## Local Development

### Prerequisites
- Docker & Docker Compose installed
- `.env.docker` file configured

### Quick Start

```bash
# Copy environment variables
cp .env.docker .env

# Generate Laravel app key (if not already set)
docker-compose exec backend php artisan key:generate

# Build and start services
docker-compose up -d

# Run migrations
docker-compose exec backend php artisan migrate

# Seed database (optional)
docker-compose exec backend php artisan db:seed
```

### Services

- **Backend**: http://localhost/api (Laravel API)
- **Frontend**: http://localhost:3000 (Next.js)
- **Nginx**: http://localhost (reverse proxy)
- **Database**: PostgreSQL on localhost:5432
- **Redis**: localhost:6379

### Useful Commands

```bash
# View logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Execute artisan commands
docker-compose exec backend php artisan tinker

# Stop all services
docker-compose down

# Rebuild images
docker-compose build --no-cache
```

## VPS Deployment

### Prerequisites on VPS

1. **Install Docker & Docker Compose**
   ```bash
   curl -fsSL https://get.docker.com -o get-docker.sh
   sudo sh get-docker.sh
   sudo usermod -aG docker $USER
   sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
   sudo chmod +x /usr/local/bin/docker-compose
   ```

2. **Create deployment directory**
   ```bash
   mkdir -p /var/www/membix
   cd /var/www/membix
   git clone <your-repo-url> .
   ```

3. **Set up environment file**
   ```bash
   cp .env.docker .env
   # Edit .env with production values
   ```

### GitHub Actions Setup

1. **Generate SSH key** (on your VPS):
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/github-deploy
   cat ~/.ssh/github-deploy.pub >> ~/.ssh/authorized_keys
   cat ~/.ssh/github-deploy
   ```

2. **Add GitHub Secrets** (in your repository settings):
   - `VPS_HOST`: Your VPS IP/domain
   - `VPS_USER`: SSH username (usually `root` or your user)
   - `VPS_PORT`: SSH port (default 22)
   - `VPS_SSH_KEY`: Contents of `~/.ssh/github-deploy` (private key)
   - `VPS_APP_PATH`: Path to your app on VPS (e.g., `/var/www/membix`)

3. **Commit and push** to main branch to trigger deployment

### Production Environment Variables

Update `.env` on your VPS with production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=membix_prod
DB_USERNAME=membix_prod
DB_PASSWORD=<strong-password>

REDIS_HOST=redis

# Frontend
NEXT_PUBLIC_API_URL=https://yourdomain.com/api
```

### SSL/HTTPS Setup (recommended)

Add to your nginx.conf or use a reverse proxy (Caddy, Traefik):

```bash
# Using Certbot
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --standalone -d yourdomain.com
```

Update docker-compose.yml to mount SSL certs:

```yaml
nginx:
  volumes:
    - /etc/letsencrypt/live/yourdomain.com:/etc/nginx/certs:ro
```

## Architecture Overview

```
┌─────────────────┐
│   GitHub Push   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────┐
│   GitHub Actions Workflow           │
│  • Build Backend & Frontend Images  │
│  • Push to Container Registry       │
└────────┬────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────┐
│   SSH Deploy to VPS                    │
│  • Pull Latest Code                    │
│  • Pull Latest Images                  │
│  • Run docker-compose up               │
│  • Run Migrations                      │
└────────────────────────────────────────┘
```

## Troubleshooting

- **Container won't start**: `docker-compose logs <service-name>`
- **Permission denied**: Check `docker-compose exec -T` (non-TTY mode)
- **Database connection error**: Ensure `db` service is healthy
- **Port conflicts**: Change ports in docker-compose.yml
