# Render Deployment Guide

## Quick Deploy (5 Minutes)

### 1. Prerequisites
- [Render account](https://render.com) (free)
- GitHub repository pushed

### 2. Deploy to Render

**Option A: One-Click Deploy (Recommended)**

Click this button to deploy:

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/Kimberly-ops177/Inventory_Management_System)

**Option B: Manual Deploy**

1. **Login to Render**
   - Go to https://dashboard.render.com
   - Sign in with GitHub

2. **Create New Blueprint**
   - Click **"New +"** → **"Blueprint"**
   - Connect your GitHub repository: `Kimberly-ops177/Inventory_Management_System`
   - Render will auto-detect `render.yaml`

3. **Configure Environment**
   - Render will create:
     - Web service (inventory-system)
     - MySQL database (inventory-db)
   - Click **"Apply"**

4. **Set APP_URL**
   - After deploy, you'll get a URL like: `https://inventory-system.onrender.com`
   - Go to **Environment** tab
   - Add: `APP_URL=https://YOUR-APP-NAME.onrender.com`
   - Save changes (app will auto-redeploy)

5. **Seed Initial Data (Optional)**
   - Go to **Environment** tab
   - Add: `SEED_DATABASE=true`
   - Save (triggers rebuild)
   - After seeding, remove this variable

### 3. Access Your Application

**URL:** `https://YOUR-APP-NAME.onrender.com`

**Default Login:**
- Email: `admin@example.com`
- Password: `password`

**⚠️ IMPORTANT:** Change the default password immediately!

---

## Post-Deployment

### Update Admin Password

1. Login with default credentials
2. Go to **Settings** or **Profile**
3. Change password to something secure

### Configure Database Backups

Render free tier doesn't include automatic backups. For production:

1. Upgrade to paid plan ($7/month)
2. Enable daily backups in database settings

### Monitor Application

**Health Check:**
- URL: `https://YOUR-APP-NAME.onrender.com/health`
- Should return: `{"status":"ok"}`

**Logs:**
- Dashboard → Your service → Logs tab
- Real-time application logs

---

## Troubleshooting

### Issue: Build Failed

**Check:**
1. Build logs in Render dashboard
2. Ensure `composer.json` exists
3. Verify PHP version in `composer.json` (should be `^8.1`)

**Fix:**
```bash
# Locally test build
./build.sh
```

### Issue: Database Connection Failed

**Check:**
1. Environment variables are set correctly
2. Database service is running
3. Connection string is valid

**Fix:**
- Go to **Environment** tab
- Verify `DB_*` variables match database credentials
- Click **"Manual Deploy"** to restart

### Issue: 500 Internal Server Error

**Check:**
1. Logs in Render dashboard
2. Storage directory permissions
3. Database migrations ran

**Fix:**
```bash
# Check health endpoint
curl https://YOUR-APP-NAME.onrender.com/health

# If unhealthy, trigger rebuild
```

### Issue: Session Not Persisting

**Fix:**
- Ensure `SESSION_SECURE=true` is set
- Access app via HTTPS (not HTTP)

---

## Deployment Workflow

### Auto-Deploy

Render automatically deploys when you push to `main` branch:

```bash
# Make changes locally
git add .
git commit -m "Update feature"
git push origin main

# Render auto-deploys in ~2-3 minutes
```

### Manual Deploy

1. Go to Render Dashboard
2. Select your service
3. Click **"Manual Deploy"** → **"Deploy latest commit"**

---

## Environment Variables

Set these in **Render Dashboard → Environment**:

| Variable | Value | Notes |
|----------|-------|-------|
| `APP_ENV` | `production` | Auto-set |
| `APP_DEBUG` | `false` | Auto-set |
| `APP_URL` | Your Render URL | **Required** |
| `SESSION_SECURE` | `true` | Auto-set |
| `DB_*` | Auto-set from database | Don't change |
| `SEED_DATABASE` | `true` | Optional, only first deploy |

---

## Scaling & Performance

### Free Tier Limits
- 750 hours/month (enough for 24/7)
- Sleeps after 15 min inactivity
- 512MB RAM
- Shared CPU

### Upgrade to Paid ($7/month)
- No sleep
- More RAM/CPU
- Database backups
- Better performance

### Optimize for Free Tier
- App wakes in ~30 seconds
- First request may be slow
- Use external monitoring to keep awake (optional)

---

## Security Checklist

After deployment:

- [ ] Change default admin password
- [ ] Update `APP_URL` in environment
- [ ] Verify HTTPS is working
- [ ] Test `/health` endpoint
- [ ] Review environment variables
- [ ] Enable database backups (paid tier)
- [ ] Set up monitoring/alerts

---

## Support

**Render Issues:**
- Docs: https://render.com/docs
- Support: https://render.com/support

**Application Issues:**
- Check logs in Render dashboard
- Test locally first: `php -S localhost:8000 -t public`
- Review error logs: `/storage/logs/error.log`

---

## Rollback

If deployment fails:

1. Go to Render Dashboard
2. Select your service
3. Click **"Deploys"** tab
4. Find previous working deploy
5. Click **"Rollback"**

---

## Next Steps

After successful deployment:

1. **Test all features:**
   - Login/Logout
   - Create products, suppliers, categories
   - Create purchase/sales orders
   - Check stock movements
   - Generate reports

2. **Configure backups:**
   - Upgrade to paid tier
   - Enable automated backups

3. **Set up monitoring:**
   - Use Render's built-in metrics
   - Set up UptimeRobot (optional)

4. **Customize:**
   - Update branding
   - Add company logo
   - Configure email notifications (future)

---

## Cost Breakdown

**Free Tier:**
- Web Service: Free
- MySQL Database: Free
- Total: **$0/month**

**Paid Tier (Recommended for Production):**
- Web Service: $7/month
- MySQL Database: $7/month
- Total: **$14/month**

Includes:
- No sleep
- Database backups
- Better performance
- Support
