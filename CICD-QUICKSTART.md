# CI/CD Quick Start Guide

## 🚀 5-Minute Setup

### For GitHub Actions

**1. Add these secrets to your repository:**
```
Settings → Secrets → New repository secret
```

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `STAGING_SSH_KEY` | Private SSH key | For staging server |
| `STAGING_HOST` | staging.example.com | Staging hostname |
| `STAGING_USER` | ubuntu | SSH user |
| `STAGING_PATH` | /var/www/inventory | App path |
| `PRODUCTION_SSH_KEY` | Private SSH key | For production server |
| `PRODUCTION_HOST` | inventory.example.com | Production hostname |
| `PRODUCTION_USER` | ubuntu | SSH user |
| `PRODUCTION_PATH` | /var/www/inventory | App path |

**2. Enable Actions:**
- Go to **Actions** tab
- Click **"I understand my workflows"**
- Done! Pipeline will run on next push

**3. Push to trigger:**
```bash
git push origin develop  # Auto-deploys to staging
git push origin main     # Deploys to production (manual approval)
```

---

### For GitLab CI

**1. Add these variables:**
```
Settings → CI/CD → Variables → Add variable
```

| Variable Name | Value | Flags |
|---------------|-------|-------|
| `STAGING_SSH_PRIVATE_KEY` | Private SSH key | Masked |
| `STAGING_HOST` | staging.example.com | - |
| `STAGING_USER` | ubuntu | - |
| `STAGING_PATH` | /var/www/inventory | - |
| `PRODUCTION_SSH_PRIVATE_KEY` | Private SSH key | Masked, Protected |
| `PRODUCTION_HOST` | inventory.example.com | Protected |
| `PRODUCTION_USER` | ubuntu | Protected |
| `PRODUCTION_PATH` | /var/www/inventory | Protected |

**2. Pipeline auto-starts:**
- Push to `develop` → Auto-deploy to staging
- Push to `main` → Tests only (manual deploy)

**3. Manual production deploy:**
- Go to **CI/CD → Pipelines**
- Click **Play** on `deploy:production` job

---

## 📝 Generate SSH Keys

```bash
# Generate key pair
ssh-keygen -t ed25519 -C "ci-cd@inventory" -f ci_deploy_key

# Copy to server
ssh-copy-id -i ci_deploy_key.pub user@server

# Get private key for secrets
cat ci_deploy_key
# Copy output and paste into GitHub/GitLab secrets
```

---

## 🔄 Workflow Overview

### Development Flow
```
1. Feature branch → develop
   └─> Runs tests → Auto-deploys to staging

2. develop → main (via PR)
   └─> Runs tests → Manual deploy to production
```

### What Runs Automatically

**On every push:**
- ✅ PHP syntax check
- ✅ Composer validation
- ✅ Database migrations test
- ✅ Health check test
- ✅ Security audit

**On develop push:**
- ✅ All tests above
- ✅ Auto-deploy to staging
- ✅ Staging health check

**On main push:**
- ✅ All tests above
- ⏸️ Wait for manual approval
- ✅ Backup database
- ✅ Deploy to production
- ✅ Production health check
- 🔄 Auto-rollback if health check fails

---

## 🎯 Common Tasks

### Deploy to Staging
```bash
git checkout develop
git pull
# Make changes
git add .
git commit -m "Update feature"
git push origin develop
# ✅ Auto-deploys!
```

### Deploy to Production
```bash
# 1. Merge develop to main
git checkout main
git merge develop
git push origin main

# 2. Go to GitHub Actions or GitLab CI

# 3. Approve production deployment

# ✅ Done!
```

### Rollback Production
```bash
# SSH to server
ssh user@production-server
cd /var/www/inventory

# Option 1: Previous commit
git reset --hard HEAD~1
./deploy.sh production

# Option 2: Specific commit
git reset --hard abc1234
./deploy.sh production

# Option 3: Restore backup
gunzip -c backups/backup_*.sql.gz | mysql -u user -p database
```

---

## 🐛 Troubleshooting

### Pipeline Fails: "Permission denied (publickey)"
**Fix:**
```bash
# Regenerate SSH key
ssh-keygen -t ed25519 -f new_key

# Copy to server
ssh-copy-id -i new_key.pub user@server

# Update secret in GitHub/GitLab
cat new_key
```

### Pipeline Fails: "Migration error"
**Fix:**
```bash
# SSH to server and run manually
ssh user@server
cd /var/www/inventory
php database/migrate.php
```

### Health Check Fails
**Fix:**
```bash
# Check logs
ssh user@server
tail -f /var/www/inventory/storage/logs/error.log

# Test health endpoint
curl http://localhost:8000/health

# Restart services
sudo systemctl restart php-fpm nginx
```

---

## 📊 Pipeline Status Badges

### GitHub Actions
Add to your README.md:
```markdown
![CI/CD](https://github.com/username/inventory/workflows/CI%2FCD%20Pipeline/badge.svg)
```

### GitLab CI
Add to your README.md:
```markdown
![pipeline](https://gitlab.com/username/inventory/badges/main/pipeline.svg)
```

---

## ✅ Pre-Deployment Checklist

Before first deployment:

**Server Setup:**
- [ ] Web server configured (Apache/Nginx)
- [ ] PHP 8.1+ installed
- [ ] MySQL/MariaDB running
- [ ] SSH access configured
- [ ] Deployment user created
- [ ] Repository cloned to server

**Configuration:**
- [ ] `.env` file configured
- [ ] Database created
- [ ] Migrations run
- [ ] Permissions set (755 storage, 644 .env)

**CI/CD:**
- [ ] SSH keys generated
- [ ] SSH keys added to server
- [ ] Secrets configured in GitHub/GitLab
- [ ] Pipeline file committed

**Security:**
- [ ] HTTPS enabled
- [ ] Firewall configured
- [ ] Strong passwords set
- [ ] Backup cron job set up

---

## 📚 Full Documentation

For detailed information, see [CICD.md](CICD.md)

## 🆘 Emergency Contacts

**Pipeline Issues:** Check [CICD.md](CICD.md) troubleshooting section
**Server Issues:** SSH to server and check logs
**Database Issues:** Restore from backup
**Critical:** Manual rollback procedure in [CICD.md](CICD.md)
