# Quick Start Guide - Namecheap Dev & Prod Setup

## 🎯 Goal
- Development server at `dev.yourdomain.com` (or separate dev domain)
- Production server at `yourdomain.com` (client's existing domain)
- Point existing domain from old hosting to Namecheap

---

## 📋 Step-by-Step Quick Reference

### Phase 1: Development Server Setup (Do First)

1. **Purchase Namecheap Hosting** ($1.58/month)
   - Go to: https://www.namecheap.com/hosting/
   - Choose: **Stellar** plan
   - Create subdomain `dev` or purchase separate dev domain

2. **Install WordPress on Dev**
   - cPanel → Softaculous → WordPress → Install
   - Domain: `dev.yourdomain.com`
   - Database: Create new (auto or manual)
   - Admin credentials: Set secure username/password

3. **Upload Theme to Dev**
   ```bash
   # Option A: Via cPanel File Manager
   # 1. Go to File Manager → public_html/wp-content/themes/
   # 2. Upload: "accurate surveyors 2025" folder
   
   # Option B: Via FTP (if you have FTP client)
   # Upload theme folder to: /public_html/wp-content/themes/
   
   # Option C: Use deployment script
   ./scripts/deploy-dev.sh
   ```

4. **Activate Theme**
   - Go to `dev.yourdomain.com/wp-admin`
   - Appearance → Themes → Activate "Accurate Surveyors 2025"

5. **Test Everything on Dev**
   - Add content, test all pages
   - Verify styling works
   - Fix any issues

---

### Phase 2: Production Server Setup (After Dev is Ready)

1. **Purchase Production Hosting** ($1.58-$4.88/month)
   - Same Namecheap account or separate
   - Choose: **Stellar** or **Stellar Plus** plan

2. **Install WordPress on Prod**
   - cPanel → Softaculous → WordPress → Install
   - Domain: `yourdomain.com` (client's domain)
   - Database: Create new
   - **⚠️ Don't change DNS yet!**

3. **Deploy Theme to Prod**
   - Same methods as dev (File Manager, FTP, or script)
   - Use: `./scripts/deploy-prod.sh`

4. **Migrate Content** (if needed)
   - Export from old site: Tools → Export
   - Import to new site: Tools → Import → WordPress
   - Or use database migration tools

5. **Test Production Site** (before DNS switch)
   - Access via temporary URL (Namecheap provides this)
   - Or edit `/etc/hosts` file to test locally
   - Verify everything works

---

### Phase 3: DNS Migration (Switch Domain to Namecheap)

⚠️ **Only do this after production site is fully ready and tested!**

1. **Backup Old Site**
   - Export WordPress database
   - Download all files via FTP
   - Document current configuration

2. **Get Namecheap Nameservers**
   - Namecheap Domain List → Manage → Nameservers
   - Usually: `dns1.registrar-servers.com` and `dns2.registrar-servers.com`

3. **Update Nameservers at Current Registrar**
   - If domain at GoDaddy/Network Solutions/etc:
     - Log into registrar
     - Find DNS/Nameserver settings
     - Update to Namecheap nameservers
   - If domain already at Namecheap:
     - Change to "Namecheap Web Hosting DNS"

4. **Wait for DNS Propagation** (1-24 hours, usually 1-4)
   - Check: https://www.whatsmydns.net
   - Domain will start resolving to Namecheap

5. **Install SSL Certificate**
   - cPanel → SSL/TLS Status → Run AutoSSL
   - Wait 10-15 minutes
   - Test: `https://yourdomain.com`

---

## 🔄 Git Workflow

### Development Workflow:
```bash
# Work on features
git checkout develop
git add .
git commit -m "New feature"
git push origin develop

# Deploy to dev server
# Then test on dev.yourdomain.com
```

### Production Deployment:
```bash
# Merge to main
git checkout main
git merge develop
git push origin main

# Deploy to production
# Test thoroughly before DNS switch
```

---

## 📁 Deployment Scripts

### Development:
```bash
./scripts/deploy-dev.sh
```
Creates ZIP file for easy upload via cPanel File Manager

### Production:
```bash
./scripts/deploy-prod.sh
```
Creates ZIP file (with extra confirmation prompts)

---

## 🔐 Important Security Settings

### Production wp-config.php additions:
```php
// Add to production wp-config.php (before "That's all, stop editing!")

// Environment
define('WP_ENVIRONMENT_TYPE', 'production');

// Debugging OFF
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Security
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);

// Database credentials (set via cPanel)
// Don't hardcode - use environment variables if possible
```

---

## 📞 Namecheap Support

- **Live Chat**: 24/7 available in Namecheap account
- **Knowledge Base**: https://www.namecheap.com/support/
- **Ticket System**: Submit ticket for complex issues

---

## ✅ Final Checklist

### Before DNS Switch:
- [ ] Dev server fully tested and working
- [ ] Production site set up and tested via temp URL
- [ ] Theme deployed to production
- [ ] Content migrated (if applicable)
- [ ] SSL certificate installed on production
- [ ] Backup of old site completed
- [ ] All plugins updated and compatible
- [ ] Final testing on production (via temp URL)

### After DNS Switch:
- [ ] Verify site loads at `yourdomain.com`
- [ ] Test HTTPS/SSL working
- [ ] Test all pages and functionality
- [ ] Verify forms/contact forms work
- [ ] Check email (if applicable)
- [ ] Monitor for 24-48 hours
- [ ] Cancel old hosting (after confirming new site works)

---

## 🆘 Troubleshooting

### Site not loading after DNS switch?
1. Check DNS propagation: https://www.whatsmydns.net
2. Clear browser cache
3. Try different device/network
4. Check Namecheap cPanel for errors
5. Contact Namecheap support

### SSL not working?
1. Wait 10-15 minutes after AutoSSL
2. Clear browser cache
3. Try incognito/private browsing
4. Check SSL/TLS Status in cPanel
5. Contact Namecheap if still not working

### Theme not showing?
1. Verify theme uploaded to correct location
2. Check file permissions (should be 755)
3. Activate theme in WordPress admin
4. Clear WordPress cache
5. Check for PHP errors in cPanel error logs

---

## 📚 Full Documentation

- **Detailed Setup**: See `NAMECHEAP-SETUP.md`
- **Deployment Options**: See `DEPLOYMENT.md`
- **General Info**: See `README.md`
