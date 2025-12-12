# DigitalOcean Setup Guide - Dev & Production Servers

## Overview

Setting up two DigitalOcean droplets:
- **Development Server**: For testing and staging (e.g., `dev.yourdomain.com` or separate droplet)
- **Production Server**: Live site at `yourdomain.com` (migrating from existing hosting)

**Cost**: ~$5-6/month per droplet ($10-12/month total for both)

---

## Step 1: Create DigitalOcean Account

1. Go to https://www.digitalocean.com
2. Sign up (get $200 credit with referral link)
3. Add payment method

---

## Step 2: Development Droplet Setup

### A. Create Development Droplet

1. **Click "Create" → "Droplets"**

2. **Choose Image:**
   - **Ubuntu 22.04 LTS** (recommended)

3. **Choose Plan:**
   - **Regular Intel with SSD**
   - **Basic** plan
   - **$5/month** (1 GB RAM / 1 vCPU / 25 GB SSD) - sufficient for dev

4. **Choose Datacenter Region:**
   - Select closest to your location

5. **Authentication:**
   - **SSH keys** (recommended) - add your public key
   - Or use root password (less secure)

6. **Hostname:**
   - `dev-yourdomain` or `yourdomain-dev`

7. **Click "Create Droplet"**
   - Wait 1-2 minutes for provisioning
   - Note the IP address

### B. Initial Server Setup

1. **SSH into your droplet:**
   ```bash
   ssh root@YOUR_DROPLET_IP
   ```

2. **Run the setup script** (see `scripts/do-setup-server.sh`):
   ```bash
   # Download and run setup script
   curl -o setup.sh https://raw.githubusercontent.com/yourusername/yourrepo/main/scripts/do-setup-server.sh
   # Or upload it via SCP first
   chmod +x setup.sh
   sudo ./setup.sh
   ```

3. **Or manually set up LAMP stack:**
   ```bash
   # Update system
   apt update && apt upgrade -y
   
   # Install LAMP stack
   apt install apache2 mysql-server php php-mysql libapache2-mod-php php-xml php-mbstring php-curl php-zip unzip git -y
   
   # Secure MySQL
   mysql_secure_installation
   # Follow prompts: Set root password, remove anonymous users, etc.
   
   # Create database for WordPress
   mysql -u root -p
   # In MySQL:
   CREATE DATABASE dev_wordpress;
   CREATE USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'strong_password_here';
   GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   
   # Enable Apache modules
   a2enmod rewrite
   a2enmod headers
   systemctl restart apache2
   
   # Set firewall
   ufw allow 'Apache Full'
   ufw allow OpenSSH
   ufw enable
   ```

### C. Install WordPress on Dev

```bash
# Navigate to web root
cd /var/www/html

# Download WordPress
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rm -rf wordpress latest.tar.gz

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Create wp-config.php
cp wp-config-sample.php wp-config.php
nano wp-config.php
# Update database credentials:
# DB_NAME = 'dev_wordpress'
# DB_USER = 'dev_wp_user'
# DB_PASSWORD = 'your_password'
# DB_HOST = 'localhost'
```

### D. Configure Apache for Dev

```bash
nano /etc/apache2/sites-available/000-default.conf
```

Add/update:
```apache
<VirtualHost *:80>
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

```bash
systemctl restart apache2
```

### E. Access WordPress Setup

1. Visit: `http://YOUR_DROPLET_IP`
2. Complete WordPress installation wizard
3. Set site title: "Accurate Surveyors - Development"
4. Create admin account

### F. Deploy Theme to Dev

**Option 1: Via Git (recommended)**
```bash
cd /var/www/html/wp-content/themes/
git clone https://github.com/yourusername/yourrepo.git temp
mv temp/app/public/wp-content/themes/accurate\ surveyors\ 2025 ./
rm -rf temp
chown -R www-data:www-data accurate\ surveyors\ 2025
```

**Option 2: Via SCP (from your local machine)**
```bash
# From your local machine
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"
scp -r "app/public/wp-content/themes/accurate surveyors 2025" \
  root@YOUR_DROPLET_IP:/var/www/html/wp-content/themes/
```

**Option 3: Via SFTP**
- Use FileZilla or similar
- Connect to droplet IP
- Upload theme folder to `/var/www/html/wp-content/themes/`

### G. Configure Dev Domain (Optional)

If using `dev.yourdomain.com`:

1. **Create DNS A record** at your domain registrar:
   - Type: A
   - Host: `dev`
   - Points to: `YOUR_DROPLET_IP`
   - TTL: 3600

2. **Configure Apache virtual host:**
   ```bash
   nano /etc/apache2/sites-available/dev.conf
   ```
   
   ```apache
   <VirtualHost *:80>
       ServerName dev.yourdomain.com
       DocumentRoot /var/www/html
       
       <Directory /var/www/html>
           Options FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   
   ```bash
   a2ensite dev.conf
   systemctl reload apache2
   ```

---

## Step 3: Production Droplet Setup

### A. Create Production Droplet

Same steps as dev droplet, but:
- **Plan**: Consider **$6/month** (1 GB RAM) or **$12/month** (2 GB RAM) for production
- **Hostname**: `prod-yourdomain` or `yourdomain-prod`
- **Note the IP address** (you'll need it for DNS)

### B. Set Up Production Server

Run the same setup script or manual LAMP installation:
```bash
# Same as dev, but use production names:
CREATE DATABASE prod_wordpress;
CREATE USER 'prod_wp_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON prod_wordpress.* TO 'prod_wp_user'@'localhost';
```

### C. Install WordPress & Deploy Theme

Same process as dev server, but:
- Database: `prod_wordpress`
- Site title: "Accurate Surveyors & Mapping"
- **⚠️ Don't configure domain DNS yet!**

### D. Configure for Main Domain

```bash
nano /etc/apache2/sites-available/yourdomain.conf
```

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

```bash
a2ensite yourdomain.conf
a2dissite 000-default.conf
systemctl reload apache2
```

### E. Migrate Content (if needed)

**Export from old site:**
- Tools → Export → All content
- Download XML

**Import to new site:**
- Tools → Import → WordPress
- Upload XML
- Import attachments

Or use database migration:
```bash
# Export from old hosting (on old server)
mysqldump -u old_user -p old_database > backup.sql

# Import to DigitalOcean (on new server)
mysql -u prod_wp_user -p prod_wordpress < backup.sql

# Update URLs
mysql -u prod_wp_user -p prod_wordpress
UPDATE wp_options SET option_value = 'https://yourdomain.com' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = 'https://yourdomain.com' WHERE option_name = 'home';
EXIT;
```

### F. Test Production (Before DNS Switch)

**Option 1: Edit hosts file (local testing)**
```bash
# On your local machine
sudo nano /etc/hosts
# Add line:
YOUR_DROPLET_IP yourdomain.com www.yourdomain.com
```

**Option 2: Access via IP**
- Visit: `http://YOUR_DROPLET_IP`
- Test all functionality

---

## Step 4: Install SSL Certificate (Let's Encrypt)

### On Both Servers:

```bash
# Install Certbot
apt install certbot python3-certbot-apache -y

# For dev server
certbot --apache -d dev.yourdomain.com

# For production server (wait until DNS is configured)
certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Certbot will:
- Automatically configure SSL
- Set up auto-renewal
- Configure HTTPS redirect

---

## Step 5: Point Domain to DigitalOcean (DNS Migration)

### A. Get DigitalOcean DNS Nameservers

1. Go to DigitalOcean → **Networking** → **Domains**
2. Add domain: `yourdomain.com`
3. DigitalOcean will provide nameservers:
   - `ns1.digitalocean.com`
   - `ns2.digitalocean.com`
   - `ns3.digitalocean.com`

### B. Update Nameservers at Domain Registrar

**At your current registrar** (GoDaddy, Namecheap, etc.):
1. Log into registrar account
2. Find DNS/Nameserver settings
3. Update to DigitalOcean nameservers:
   - `ns1.digitalocean.com`
   - `ns2.digitalocean.com`
   - `ns3.digitalocean.com`
4. Save changes

### C. Configure DNS Records in DigitalOcean

1. Go to DigitalOcean → **Networking** → **Domains** → `yourdomain.com`

2. **Add A Records:**
   ```
   Type: A
   Hostname: @
   Will Direct To: [Production Droplet IP]
   TTL: 3600
   
   Type: A
   Hostname: www
   Will Direct To: [Production Droplet IP]
   TTL: 3600
   ```

3. **Add Dev A Record (if using subdomain):**
   ```
   Type: A
   Hostname: dev
   Will Direct To: [Development Droplet IP]
   TTL: 3600
   ```

### D. Wait for DNS Propagation

- Check: https://www.whatsmydns.net
- Usually 1-4 hours (can take up to 48 hours)
- Domain will start resolving to DigitalOcean

### E. Install SSL on Production

Once DNS propagates:
```bash
# SSH into production droplet
ssh root@PRODUCTION_IP

# Install SSL
certbot --apache -d yourdomain.com -d www.yourdomain.com
```

---

## Step 6: Security Hardening

### Firewall Configuration (Both Servers)

```bash
# Allow only necessary ports
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Apache Full'
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

### WordPress Security (Production)

1. **Install security plugin:**
   - Wordfence or Sucuri Security

2. **Update wp-config.php:**
   ```php
   // Change database prefix (if fresh install)
   $table_prefix = 'wp_rand123_';
   
   // Security keys (generate at https://api.wordpress.org/secret-key/1.1/salt/)
   // Add unique keys
   
   // Disable file editing
   define('DISALLOW_FILE_EDIT', true);
   
   // Force SSL
   define('FORCE_SSL_ADMIN', true);
   if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
       $_SERVER['HTTPS']='on';
   ```

3. **Set proper file permissions:**
   ```bash
   # On production server
   find /var/www/html -type d -exec chmod 755 {} \;
   find /var/www/html -type f -exec chmod 644 {} \;
   chmod 600 /var/www/html/wp-config.php
   ```

### Fail2Ban (Recommended)

```bash
apt install fail2ban -y
systemctl enable fail2ban
systemctl start fail2ban
```

---

## Step 7: Automated Backups

### DigitalOcean Snapshots (Recommended)

1. **Manual snapshot:**
   - Droplet → Snapshots → Take Snapshot
   - Can restore entire server

2. **Automatic snapshots:**
   - Go to droplet settings
   - Enable weekly snapshots

### Database Backups (Daily)

Create backup script:
```bash
nano /root/backup-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="prod_wordpress"
DB_USER="prod_wp_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete
```

```bash
chmod +x /root/backup-db.sh

# Add to crontab (daily at 2 AM)
crontab -e
# Add line:
0 2 * * * /root/backup-db.sh
```

---

## Quick Reference Commands

### SSH into servers
```bash
# Dev
ssh root@DEV_DROPLET_IP

# Prod
ssh root@PROD_DROPLET_IP
```

### Restart services
```bash
systemctl restart apache2
systemctl restart mysql
systemctl status apache2
```

### Check logs
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
journalctl -u apache2 -f
```

### Update WordPress
```bash
cd /var/www/html
wp core update  # If WP-CLI installed
```

---

## Cost Breakdown

- **Development Droplet**: $5/month (1 GB RAM)
- **Production Droplet**: $6-12/month (1-2 GB RAM)
- **Total**: ~$11-17/month
- **Snapshots**: $0.06/GB/month
- **DNS**: Free (included)

**First Month**: Get $200 free credit with referral!

---

## Support Resources

- **DigitalOcean Docs**: https://docs.digitalocean.com/
- **Community Tutorials**: https://www.digitalocean.com/community/tags/wordpress
- **Support Tickets**: Available in dashboard

---

## Checklist

See `DEPLOYMENT-CHECKLIST.md` for detailed checklist, adapted for DigitalOcean.
