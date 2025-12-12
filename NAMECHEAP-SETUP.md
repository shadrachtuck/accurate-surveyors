# Namecheap Setup Guide - Dev & Production Servers

## Overview

Setting up two environments:
- **Development Server**: For testing and staging (e.g., `dev.yourdomain.com` or `staging.yourdomain.com`)
- **Production Server**: Live site at `yourdomain.com` (migrating from existing hosting)

---

## Step 1: Purchase Namecheap Hosting

### Development Server:
- **Option A**: Subdomain on same account (free subdomain)
  - Use: `dev.yourdomain.com` or `staging.yourdomain.com`
  - Hosting: Namecheap Stellar ($1.58/month)
  
- **Option B**: Separate hosting account
  - Separate domain: `yourdomain-dev.com` or `devyourdomain.com`
  - Hosting: Namecheap Stellar ($1.58/month)

### Production Server:
- Use client's existing domain: `yourdomain.com`
- Hosting: Namecheap Stellar Plus (recommended) or Stellar ($1.58-$4.88/month)
- Will replace existing website

---

## Step 2: Development Server Setup

### A. Create Development Subdomain/Account

1. **If using subdomain**:
   - Log into Namecheap cPanel
   - Go to **Subdomains**
   - Create: `dev` (creates `dev.yourdomain.com`)

2. **If using separate domain**:
   - Purchase separate hosting or add second hosting package
   - Point new domain to hosting

### B. Install WordPress on Dev Server

**Via cPanel Softaculous (easiest):**
1. Log into cPanel
2. Find **Softaculous Apps Installer**
3. Click **WordPress**
4. Click **Install Now**
5. Configure:
   - Choose Domain: `dev.yourdomain.com` (or dev domain)
   - In Directory: Leave blank (root)
   - Database Name: `dev_wp` (or auto-generated)
   - Table Prefix: `wp_` (or `wp_dev_`)
   - Site Name: "Accurate Surveyors - Development"
   - Admin Username: (choose secure username)
   - Admin Password: (strong password)
   - Admin Email: (your email)

6. Click **Install**
7. Note the database credentials shown

### C. Deploy Theme to Dev Server

**Option 1: Via cPanel File Manager**
1. Go to **File Manager** in cPanel
2. Navigate to: `public_html/wp-content/themes/` (or `dev/public_html/wp-content/themes/` for subdomain)
3. Upload your theme folder: `accurate surveyors 2025`
   - Compress locally: `zip -r accurate-surveyors-2025.zip "accurate surveyors 2025"`
   - Upload ZIP in File Manager
   - Extract in cPanel

**Option 2: Via FTP/SFTP**
```bash
# Connect via FTP (credentials from cPanel)
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"
scp -r "app/public/wp-content/themes/accurate surveyors 2025" \
  username@dev.yourdomain.com:/public_html/wp-content/themes/
```

**Option 3: Via Git (if available)**
```bash
# SSH into server (if SSH enabled)
ssh username@dev.yourdomain.com
cd public_html/wp-content/themes/
git clone https://github.com/yourusername/yourrepo.git
# Move child theme to correct location
```

### D. Activate Theme
1. Go to `dev.yourdomain.com/wp-admin`
2. Appearance → Themes
3. Activate "Accurate Surveyors 2025"

---

## Step 3: Production Server Setup

### A. Domain DNS Preparation

⚠️ **IMPORTANT**: Before switching DNS, do these in order:

1. **Backup current website** (from existing hosting)
   - Export WordPress database
   - Download all files via FTP
   - Document current configuration

2. **Set up production server FIRST** (don't switch DNS yet)
   - Install WordPress on Namecheap
   - Deploy theme
   - Import content/migrate data
   - Test everything

3. **Only switch DNS after production is ready**

### B. Install WordPress on Production

Same process as dev server, but:
- Domain: `yourdomain.com`
- Database: `prod_wp` or `wp_production`
- Site Name: "Accurate Surveyors & Mapping"

### C. Deploy Theme to Production

Same methods as dev server (FTP, File Manager, or Git)

### D. Migrate Content (if needed)

**If migrating from existing WordPress site:**

1. **Export from old site:**
   - Tools → Export → All content
   - Download XML file

2. **Import to new site:**
   - Tools → Import → WordPress
   - Upload XML file
   - Check "Download and import file attachments"

3. **Import database** (advanced):
   ```sql
   # Export from old hosting
   mysqldump -u old_user -p old_database > backup.sql
   
   # Import to Namecheap
   mysql -u new_user -p new_database < backup.sql
   ```

4. **Update URLs** (if domain changed):
   - Use "Better Search Replace" plugin
   - Replace: `old-domain.com` → `yourdomain.com`

---

## Step 4: Point Domain to Namecheap (DNS Migration)

### A. Get Namecheap DNS/Nameservers

1. Log into Namecheap account
2. Go to **Domain List**
3. Find your domain
4. Click **Manage**
5. Note the **Nameservers** (usually):
   ```
   dns1.registrar-servers.com
   dns2.registrar-servers.com
   ```
   Or if using Namecheap hosting:
   ```
   dns1.namecheaphosting.com
   dns2.namecheaphosting.com
   ```

### B. Update Nameservers at Current Registrar

**If domain is registered elsewhere** (GoDaddy, Network Solutions, etc.):
1. Log into current domain registrar
2. Find DNS/Nameserver settings
3. Update to Namecheap nameservers
4. Save changes

**If domain is already at Namecheap:**
1. In Namecheap Domain List → Manage
2. Under **Nameservers**, select **Namecheap Web Hosting DNS**
3. Save

### C. Configure DNS Records

In Namecheap cPanel → **Advanced DNS**:

```
Type    Host    Value                      TTL
A       @       [Namecheap server IP]       Automatic
A       www     [Namecheap server IP]       Automatic
CNAME   dev     dev.yourdomain.com.         Automatic (if using subdomain)
```

**To find Namecheap server IP:**
- Check welcome email
- Or ask Namecheap support
- Usually shown in cPanel → **Account Information**

### D. DNS Propagation

- Can take 24-48 hours (usually 1-4 hours)
- Check propagation: https://www.whatsmydns.net
- Test locally: `ping yourdomain.com` (should resolve to new IP)

---

## Step 5: SSL Certificates (HTTPS)

Namecheap includes free SSL via Let's Encrypt:

1. In cPanel → **SSL/TLS Status**
2. Select domains
3. Click **Run AutoSSL**
4. Wait 10-15 minutes
5. Test: `https://yourdomain.com`

---

## Step 6: Environment-Specific Configurations

### Development Server wp-config.php

```php
define('WP_ENVIRONMENT_TYPE', 'development');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

### Production Server wp-config.php

```php
define('WP_ENVIRONMENT_TYPE', 'production');
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);

// Security
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
```

---

## Step 7: Git Workflow for Deployments

### Branch Strategy:
- `main` branch → Production server
- `develop` branch → Development server

### Deployment Commands:

```bash
# Deploy to development
git checkout develop
git merge main  # or merge feature branch
# Then FTP/upload to dev server

# Deploy to production
git checkout main
git merge develop  # or hotfix
# Then FTP/upload to prod server
```

---

## Checklist

### Development Server:
- [ ] Namecheap hosting purchased
- [ ] Subdomain/second domain created
- [ ] WordPress installed
- [ ] Theme uploaded and activated
- [ ] Site accessible at dev URL
- [ ] SSL certificate installed
- [ ] Basic content added for testing

### Production Server:
- [ ] Namecheap hosting purchased
- [ ] WordPress installed
- [ ] Theme uploaded and activated
- [ ] Content migrated/imported
- [ ] Site fully tested
- [ ] Backup of old site created
- [ ] DNS nameservers updated
- [ ] DNS propagation complete
- [ ] SSL certificate installed
- [ ] Final testing on live domain
- [ ] Old hosting canceled (after confirming new site works)

---

## Important Notes

1. **DNS Timing**: Switch DNS only after production site is fully ready
2. **Backup First**: Always backup old site before DNS switch
3. **Test Thoroughly**: Test dev server before deploying to production
4. **SSL**: Essential for production, install immediately
5. **Email**: If client uses email on current hosting, set up email on Namecheap or migrate separately
6. **DNS Records**: If using services (email, API), keep those DNS records

---

## Support Resources

- Namecheap Knowledge Base: https://www.namecheap.com/support/
- Namecheap Live Chat: Available 24/7
- WordPress Codex: https://codex.wordpress.org/
