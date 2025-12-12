# DigitalOcean Setup - Step-by-Step Guide

Follow these steps in order to set up your WordPress site on DigitalOcean.

---

## Phase 1: Create Development Droplet

### Step 1.1: Sign Up / Log In
- Go to: https://www.digitalocean.com
- Sign up (get $200 free credit) or log in

### Step 1.2: Create Development Droplet
1. Click **"Create"** → **"Droplets"**
2. **Choose Image**: Ubuntu 22.04 LTS
3. **Choose Plan**: 
   - Regular Intel with SSD
   - Basic plan
   - **$5/month** (1 GB RAM / 1 vCPU / 25 GB SSD)
4. **Choose Datacenter**: Select closest to your location
5. **Authentication**: 
   - Option A: Add SSH key (recommended - more secure)
   - Option B: Use root password (less secure)
6. **Hostname**: `dev-yourdomain` or `yourdomain-dev`
7. Click **"Create Droplet"**
8. **Wait 1-2 minutes** for provisioning
9. **Note the IP address** (e.g., `134.122.89.123`)

### Step 1.3: SSH into Your Droplet

**From your local machine:**
```bash
ssh root@YOUR_DEV_IP
```
Replace `YOUR_DEV_IP` with the IP address from Step 1.2.

If using password auth, enter the password when prompted.

### Step 1.4: Run Server Setup Script

**From your local machine** (in the project directory):
```bash
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"
scp scripts/do-setup-server.sh root@YOUR_DEV_IP:/root/
```

**Then SSH into server and run:**
```bash
ssh root@YOUR_DEV_IP
chmod +x /root/do-setup-server.sh
/root/do-setup-server.sh
```

**OR manually set up** (if you prefer):
```bash
# Update system
apt update && apt upgrade -y

# Install LAMP stack
apt install -y apache2 mysql-server php php-mysql libapache2-mod-php php-xml php-mbstring php-curl php-zip php-gd php-imagick unzip git certbot python3-certbot-apache ufw

# Secure MySQL
mysql_secure_installation
# Follow prompts to set root password and secure installation

# Enable Apache modules
a2enmod rewrite
a2enmod headers
a2enmod ssl
systemctl restart apache2

# Configure firewall
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Apache Full'
ufw --force enable
```

### Step 1.5: Create WordPress Database

**On the server (via SSH):**
```bash
mysql -u root -p
# Enter the root password you set during mysql_secure_installation
```

**In MySQL prompt:**
```sql
CREATE DATABASE dev_wordpress;
CREATE USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Note down:**
- Database name: `dev_wordpress`
- Database user: `dev_wp_user`
- Database password: (the one you just set)

### Step 1.6: Install WordPress

**On the server (via SSH):**
```bash
cd /var/www/html

# Download WordPress
wget https://wordpress.org/latest.tar.gz

# Extract
tar -xzf latest.tar.gz

# Move files to root
mv wordpress/* .
rm -rf wordpress latest.tar.gz

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Configure WordPress
cp wp-config-sample.php wp-config.php
nano wp-config.php
```

**In nano, update these lines:**
```php
define('DB_NAME', 'dev_wordpress');
define('DB_USER', 'dev_wp_user');
define('DB_PASSWORD', 'YOUR_STRONG_PASSWORD_HERE');
define('DB_HOST', 'localhost');
```

**Save**: `Ctrl+O`, `Enter`, `Ctrl+X`

**Set secure file permissions:**
```bash
chmod 600 /var/www/html/wp-config.php
```

### Step 1.7: Complete WordPress Installation

**Option A: Fresh WordPress Install**

1. **Open browser**: `http://YOUR_DEV_IP`
2. **Select language**
3. **Fill in WordPress installation form**:
   - Site Title: "Accurate Surveyors - Development"
   - Username: (choose admin username)
   - Password: (strong password)
   - Email: (your email)
4. **Click "Install WordPress"**
5. **Log in** with your credentials

**Option B: Migrate Existing Local Database (Recommended)**

If you want to use your existing local site's data:

1. **Skip the WordPress installation** (or install it fresh first)
2. **Follow the Database Migration Guide**: See `DATABASE-MIGRATION.md` for detailed instructions
3. **Quick steps**:
   - Export database from Local by Flywheel (phpMyAdmin → Export)
   - Upload to server: `scp backup.sql root@YOUR_DEV_IP:/root/`
   - Import: `mysql -u dev_wp_user -p dev_wordpress < /root/backup.sql`
   - Update URLs: Use WP-CLI or Better Search Replace plugin
   - Migrate uploads folder via SCP

### Step 1.7a: Migrate Database (If Using Existing Data)

**See `DATABASE-MIGRATION.md` for complete instructions.**

Quick version:
1. Export from Local by Flywheel (Database tab → Export)
2. Upload: `scp backup.sql root@YOUR_DEV_IP:/root/`
3. Import: `mysql -u dev_wp_user -p dev_wordpress < /root/backup.sql`
4. Update URLs using WP-CLI or plugin (see migration guide)

### Step 1.8: Deploy Your Theme

**Option 1: Using deployment script** (from your local machine):
```bash
# Edit the script first to add your IP
nano scripts/deploy-do-dev.sh
# Update: DEV_IP="YOUR_DEV_IP"

# Then run:
./scripts/deploy-do-dev.sh
```

**Option 2: Manual upload via SCP**:
```bash
# From your local machine
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"
scp -r "app/public/wp-content/themes/accurate surveyors 2025" \
  root@YOUR_DEV_IP:/var/www/html/wp-content/themes/
```

**Option 3: Via Git** (if you've pushed to GitHub):
```bash
# On server (via SSH)
cd /var/www/html/wp-content/themes/
git clone https://github.com/yourusername/yourrepo.git temp
mv temp/app/public/wp-content/themes/accurate\ surveyors\ 2025 ./
rm -rf temp
chown -R www-data:www-data accurate\ surveyors\ 2025
```

**After theme is uploaded:**
1. Go to WordPress admin: `http://YOUR_DEV_IP/wp-admin`
2. **Appearance** → **Themes**
3. Activate **"Accurate Surveyors 2025"**

### Step 1.9: Install Parent Theme

The child theme requires the parent "blockchain" theme. Upload it the same way you uploaded the child theme.

**Or download from source** (if parent theme is from a theme shop):
```bash
# On server
cd /var/www/html/wp-content/themes/
# Download and extract parent theme
# Then ensure it's named "blockchain"
```

---

## Phase 2: Create Production Droplet

### Step 2.1: Create Production Droplet

**Repeat Phase 1, Steps 1.2-1.8**, but with these differences:

- **Plan**: **$6-12/month** (1-2 GB RAM) for production
- **Hostname**: `prod-yourdomain`
- **Database name**: `prod_wordpress`
- **Database user**: `prod_wp_user`
- **Site Title**: "Accurate Surveyors & Mapping"
- **Theme deployment**: Use `scripts/deploy-do-prod.sh`

**⚠️ Important**: Don't configure domain DNS yet!

---

## Phase 3: Test Production Site (Before DNS Switch)

### Step 3.1: Test via IP Address

1. Visit: `http://YOUR_PROD_IP`
2. Verify WordPress is working
3. Activate theme
4. Test all pages and functionality

### Step 3.2: Test via Local Hosts File (Optional)

**On your local machine** (Mac):
```bash
sudo nano /etc/hosts
# Add line:
YOUR_PROD_IP yourdomain.com www.yourdomain.com
# Save: Ctrl+O, Enter, Ctrl+X
```

Now you can test the production site at `http://yourdomain.com` locally before DNS switch.

---

## Phase 4: Backup Current Site

### Step 4.1: Backup from Current Hosting

1. **Export WordPress database** from current hosting
2. **Download all files** via FTP
3. **Document current configuration**
4. **Store backups safely**

---

## Phase 5: Configure Domain DNS

### Step 5.1: Add Domain to DigitalOcean

1. In DigitalOcean dashboard: **Networking** → **Domains**
2. Click **"Add Domain"**
3. Enter: `yourdomain.com`
4. Click **"Add Domain"**
5. **Note the nameservers** shown:
   - `ns1.digitalocean.com`
   - `ns2.digitalocean.com`
   - `ns3.digitalocean.com`

### Step 5.2: Update Nameservers at Domain Registrar

**At your domain registrar** (where you purchased the domain):

1. Log into your account
2. Find **DNS Settings** or **Nameserver Settings**
3. Change from current nameservers to:
   - `ns1.digitalocean.com`
   - `ns2.digitalocean.com`
   - `ns3.digitalocean.com`
4. Save changes

### Step 5.3: Add DNS Records in DigitalOcean

**In DigitalOcean** → **Networking** → **Domains** → `yourdomain.com`:

Click **"Add Record"** for each:

1. **A Record** (for main domain):
   - Type: `A`
   - Hostname: `@`
   - Will Direct To: `YOUR_PROD_DROPLET_IP`
   - TTL: `3600`

2. **A Record** (for www):
   - Type: `A`
   - Hostname: `www`
   - Will Direct To: `YOUR_PROD_DROPLET_IP`
   - TTL: `3600`

3. **A Record** (for dev - optional):
   - Type: `A`
   - Hostname: `dev`
   - Will Direct To: `YOUR_DEV_DROPLET_IP`
   - TTL: `3600`

### Step 5.4: Configure Apache Virtual Host for Domain

**On production server (via SSH):**
```bash
nano /etc/apache2/sites-available/yourdomain.conf
```

**Add this configuration:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/yourdomain-error.log
    CustomLog ${APACHE_LOG_DIR}/yourdomain-access.log combined
</VirtualHost>
```

**Save and activate:**
```bash
a2ensite yourdomain.conf
a2dissite 000-default.conf
systemctl reload apache2
```

### Step 5.5: Wait for DNS Propagation

1. **Check propagation**: https://www.whatsmydns.net
2. **Enter your domain**: `yourdomain.com`
3. **Select A record**
4. Wait until all locations show your production IP
5. **Usually takes 1-4 hours** (can take up to 48 hours)

---

## Phase 6: Install SSL Certificate

### Step 6.1: Install Certbot (if not already installed)

**On production server:**
```bash
apt install certbot python3-certbot-apache -y
```

### Step 6.2: Request SSL Certificate

**Wait until DNS has propagated**, then:

```bash
certbot --apache -d yourdomain.com -d www.yourdomain.com
```

**Follow prompts:**
- Enter email address
- Agree to terms
- Choose redirect HTTP to HTTPS (recommended: option 2)

**Wait 10-15 minutes** for certificate to activate.

### Step 6.3: Test SSL

1. Visit: `https://yourdomain.com`
2. Verify the padlock icon appears
3. Test: `https://www.yourdomain.com`

### Step 6.4: Set Up Auto-Renewal

Certbot sets this up automatically, but verify:
```bash
certbot renew --dry-run
```

---

## Phase 7: Final Testing & Go Live

### Step 7.1: Final Production Testing

- [ ] Site loads at `https://yourdomain.com`
- [ ] All pages accessible
- [ ] Navigation works
- [ ] Contact forms working
- [ ] Images loading correctly
- [ ] Theme displaying properly
- [ ] Mobile responsive
- [ ] SSL certificate active

### Step 7.2: Monitor for 24-48 Hours

- Monitor site performance
- Check error logs: `tail -f /var/log/apache2/error.log`
- Verify all functionality

### Step 7.3: Cancel Old Hosting (After Confirming Success)

Only after confirming new site works perfectly:
- Download any final backups from old hosting
- Cancel old hosting subscription

---

## Quick Command Reference

### SSH Access
```bash
ssh root@DEV_IP
ssh root@PROD_IP
```

### Restart Services
```bash
systemctl restart apache2
systemctl restart mysql
systemctl status apache2
```

### View Logs
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
journalctl -u apache2 -f
```

### Database Backup
```bash
mysqldump -u wp_user -p wordpress_db > backup-$(date +%Y%m%d).sql
```

### File Permissions
```bash
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod 600 /var/www/html/wp-config.php
```

---

## Need Help?

- **DigitalOcean Docs**: https://docs.digitalocean.com/
- **WordPress Codex**: https://codex.wordpress.org/
- **DigitalOcean Support**: Available in dashboard

---

## Current Status Checklist

Use this to track your progress:

- [ ] DigitalOcean account created
- [ ] Development droplet created
- [ ] Dev server setup complete
- [ ] WordPress installed on dev
- [ ] Theme deployed to dev
- [ ] Dev site tested and working
- [ ] Production droplet created
- [ ] Prod server setup complete
- [ ] WordPress installed on prod
- [ ] Theme deployed to prod
- [ ] Old site backed up
- [ ] Domain added to DigitalOcean
- [ ] Nameservers updated at registrar
- [ ] DNS records configured
- [ ] Apache virtual host configured
- [ ] DNS propagated
- [ ] SSL certificate installed
- [ ] Production site tested
- [ ] Site live and working

---

**Ready to start? Begin with Phase 1, Step 1.1!**

