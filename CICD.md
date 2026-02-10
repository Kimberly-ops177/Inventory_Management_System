# CI/CD Pipeline Documentation

## Overview

The Inventory Management System includes comprehensive CI/CD pipelines for automated testing and deployment using **GitHub Actions** and **GitLab CI**.

## Features

### Continuous Integration (CI)
- ✅ **Automated Testing** - Syntax checks, migrations, health checks
- ✅ **Security Scanning** - Dependency vulnerability audits
- ✅ **Code Quality** - PHP syntax validation
- ✅ **Database Testing** - Migration validation with MySQL
- ✅ **Health Checks** - Automated endpoint testing

### Continuous Deployment (CD)
- ✅ **Staging Deployment** - Auto-deploy from `develop` branch
- ✅ **Production Deployment** - Manual approval for `main` branch
- ✅ **Automated Backups** - Database backup before production deploy
- ✅ **Health Monitoring** - Post-deployment health checks
- ✅ **Rollback Support** - Automatic rollback on failure

---

## GitHub Actions Setup

### 1. Enable GitHub Actions

GitHub Actions is automatically available for all GitHub repositories.

### 2. Configure Secrets

Go to **Settings → Secrets and variables → Actions** and add:

#### Staging Secrets
```
STAGING_SSH_KEY         # Private SSH key for staging server
STAGING_HOST            # staging.inventory.example.com
STAGING_USER            # ubuntu or deployment user
STAGING_PATH            # /var/www/inventory
```

#### Production Secrets
```
PRODUCTION_SSH_KEY      # Private SSH key for production server
PRODUCTION_HOST         # inventory.example.com
PRODUCTION_USER         # ubuntu or deployment user
PRODUCTION_PATH         # /var/www/inventory
```

### 3. Generate SSH Keys

```bash
# Generate SSH key pair
ssh-keygen -t ed25519 -C "github-actions@inventory.example.com" -f deploy_key

# Copy public key to server
ssh-copy-id -i deploy_key.pub user@server

# Add private key to GitHub secrets
cat deploy_key | pbcopy  # Or use `cat deploy_key`
```

### 4. Workflow Triggers

**Automatic Triggers:**
- Push to `main` → Run tests + Deploy to production (manual approval)
- Push to `develop` → Run tests + Auto-deploy to staging
- Pull requests → Run tests only

### 5. Manual Deployment

Go to **Actions → CI/CD Pipeline → Run workflow**

---

## GitLab CI Setup

### 1. Enable GitLab CI

GitLab CI is automatically enabled for all GitLab projects.

### 2. Configure Variables

Go to **Settings → CI/CD → Variables** and add:

#### Staging Variables
```
STAGING_SSH_PRIVATE_KEY     # Private SSH key (masked, not protected)
STAGING_HOST                # staging.inventory.example.com
STAGING_USER                # ubuntu
STAGING_PATH                # /var/www/inventory
```

#### Production Variables
```
PRODUCTION_SSH_PRIVATE_KEY  # Private SSH key (masked, protected)
PRODUCTION_HOST             # inventory.example.com
PRODUCTION_USER             # ubuntu
PRODUCTION_PATH             # /var/www/inventory
```

**Important Settings:**
- Mark SSH keys as **Masked** (hides in logs)
- Mark production keys as **Protected** (only on main branch)

### 3. Pipeline Stages

```
build → test → security → deploy-staging → deploy-production
```

### 4. Manual Production Deployment

Production deployments require manual approval:
1. Go to **CI/CD → Pipelines**
2. Find the pipeline for `main` branch
3. Click **Play** button on `deploy:production` job

---

## Server Setup

### 1. Prepare Deployment User

```bash
# Create deployment user
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG www-data deploy

# Allow deployment user to reload PHP-FPM without password
sudo visudo
# Add: deploy ALL=(ALL) NOPASSWD: /bin/systemctl reload php-fpm
```

### 2. Configure SSH Access

```bash
# On the server
sudo su - deploy
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Add the public key from GitHub/GitLab
nano ~/.ssh/authorized_keys
# Paste the public key
chmod 600 ~/.ssh/authorized_keys
```

### 3. Clone Repository

```bash
# As deployment user
cd /var/www
sudo mkdir inventory
sudo chown deploy:www-data inventory
git clone git@github.com:youruser/inventory.git inventory
cd inventory

# Initial setup
cp .env.example .env
nano .env  # Configure environment
composer install --no-dev
php database/migrate.php
chmod -R 755 storage database
```

### 4. Configure Web Server

Point your web server to `/var/www/inventory/public`

---

## Pipeline Workflow

### For Feature Development

```bash
# 1. Create feature branch
git checkout -b feature/new-feature

# 2. Make changes and commit
git add .
git commit -m "Add new feature"

# 3. Push to repository
git push origin feature/new-feature

# 4. Create pull request to develop
# → Pipeline runs tests automatically

# 5. After approval, merge to develop
# → Auto-deploys to staging
```

### For Production Release

```bash
# 1. Ensure staging is stable

# 2. Create pull request from develop to main

# 3. After approval, merge to main
# → Pipeline runs tests

# 4. Manually approve production deployment
# → Creates backup, deploys, runs health check
```

---

## Monitoring & Debugging

### View Pipeline Status

**GitHub:**
- Go to **Actions** tab
- Click on workflow run
- View logs for each step

**GitLab:**
- Go to **CI/CD → Pipelines**
- Click on pipeline
- View job logs

### Common Issues

#### 1. SSH Connection Failed

**Solution:**
```bash
# Test SSH connection manually
ssh -i ~/.ssh/deploy_key user@server

# Check SSH key permissions
chmod 600 ~/.ssh/deploy_key

# Verify known_hosts
ssh-keyscan server-ip >> ~/.ssh/known_hosts
```

#### 2. Migration Failed

**Solution:**
```bash
# SSH to server
ssh user@server
cd /var/www/inventory

# Check database connection
mysql -u user -p database_name

# Run migration manually
php database/migrate.php
```

#### 3. Health Check Failed

**Solution:**
```bash
# Check application logs
tail -f storage/logs/error.log

# Test health endpoint manually
curl -v http://localhost:8000/health

# Check web server status
sudo systemctl status nginx
sudo systemctl status php-fpm
```

#### 4. Permission Denied

**Solution:**
```bash
# Fix storage permissions
chmod -R 755 storage database
chown -R www-data:www-data storage

# Verify .env permissions
chmod 644 .env
```

---

## Rollback Procedure

### Automatic Rollback (On Failure)

GitHub Actions automatically rolls back on health check failure:
```bash
git reset --hard HEAD~1
./deploy.sh production
```

### Manual Rollback

```bash
# SSH to server
ssh user@server
cd /var/www/inventory

# Restore from backup
gunzip -c backups/backup_inventory_db_TIMESTAMP.sql.gz | \
  mysql -u user -p database_name

# Reset to previous commit
git reset --hard PREVIOUS_COMMIT_HASH
./deploy.sh production

# Or checkout specific version
git checkout tags/v1.2.3
./deploy.sh production
```

---

## Environment-Specific Configurations

### Staging Environment

**Purpose:** Testing before production
**URL:** https://staging.inventory.example.com
**Database:** inventory_staging
**Auto-Deploy:** Yes (on develop branch)
**Debug Mode:** Enabled

**Configuration:**
```bash
cp .env.staging.example .env
```

### Production Environment

**Purpose:** Live system
**URL:** https://inventory.example.com
**Database:** inventory_production
**Auto-Deploy:** No (manual approval required)
**Debug Mode:** Disabled

**Configuration:**
```bash
cp .env.production.example .env
```

---

## Best Practices

### 1. Branch Strategy

```
main (production)
  ↑
develop (staging)
  ↑
feature/xyz (local dev)
```

### 2. Commit Messages

```bash
# Good commit messages
git commit -m "Add product search feature"
git commit -m "Fix stock calculation bug"
git commit -m "Update deployment script"

# Bad commit messages
git commit -m "Update"
git commit -m "Fix"
```

### 3. Pull Requests

- Always create PR for code review
- Ensure all checks pass before merging
- Get at least one approval
- Squash commits when merging

### 4. Testing

```bash
# Test locally before pushing
composer install
php database/migrate.php
php -S localhost:8000 -t public

# Test health endpoint
curl http://localhost:8000/health
```

### 5. Security

- Never commit `.env` files
- Rotate SSH keys regularly
- Use strong passwords
- Enable 2FA on GitHub/GitLab
- Review dependency security alerts

---

## Monitoring Post-Deployment

### 1. Check Deployment Success

```bash
# View recent deployments
curl https://inventory.example.com/health

# Check application logs
ssh user@server
tail -f /var/www/inventory/storage/logs/error.log
```

### 2. Monitor Performance

```bash
# Check response time
curl -w "@curl-format.txt" -o /dev/null -s https://inventory.example.com

# Monitor server resources
htop
df -h
```

### 3. Database Health

```bash
# Check database size
mysql -u user -p -e "
SELECT table_schema AS 'Database',
ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'inventory_production'
GROUP BY table_schema;
"
```

---

## Troubleshooting Guide

### Pipeline Fails on Test Stage

1. Check test logs
2. Run tests locally: `composer test`
3. Verify MySQL service is running
4. Check database credentials in `.env`

### Pipeline Fails on Deploy Stage

1. Verify SSH connection
2. Check server disk space: `df -h`
3. Verify deployment user permissions
4. Check application logs

### Health Check Fails

1. Check web server status
2. Verify PHP-FPM is running
3. Check database connectivity
4. Review error logs

---

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitLab CI Documentation](https://docs.gitlab.com/ee/ci/)
- [SSH Key Management](https://docs.github.com/en/authentication)
- [Deployment Best Practices](https://www.php.net/manual/en/install.php)

---

## Support

For CI/CD issues:
1. Check pipeline logs
2. Review this documentation
3. Test deployment script locally
4. Contact DevOps team

**Emergency Rollback:**
```bash
ssh user@production-server
cd /var/www/inventory
git reset --hard LAST_WORKING_COMMIT
./deploy.sh production
```
