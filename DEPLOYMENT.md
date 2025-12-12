# Deployment Guide - Cheap WordPress Hosting Options

## Option 1: Shared Hosting (Cheapest - $1-5/month)

### Recommended Providers:
- **Namecheap Stellar** - $1.58/month (first year, then ~$3.48/month)
- **SiteGround StartUp** - $2.99/month (first year, then ~$14.99/month)
- **Bluehost Basic** - $2.95/month (first year, then ~$10.99/month)

### Steps for Shared Hosting:

1. **Purchase hosting** and get FTP/cPanel credentials
2. **Upload files via FTP** or File Manager:
   ```bash
   # Using SFTP/SCP
   scp -r app/public/* username@yourdomain.com:/public_html/
   ```

3. **Create database** via cPanel:
   - Create MySQL database
   - Create database user
   - Grant privileges

4. **Configure wp-config.php**:
   - Upload or create on server
   - Update database credentials
   - Set `WP_DEBUG` to `false` for production

5. **Run WordPress install** or import database

---

## Option 2: DigitalOcean Droplet (Best Value - $5-6/month)

### Setup Steps:

1. **Create account** at digitalocean.com ($5 credit free)

2. **Create Droplet**:
   - Choose Ubuntu 22.04
   - $5-6/month plan (1GB RAM sufficient for dev)
   - Add SSH key

3. **Initial Server Setup** (SSH into server):
   ```bash
   ssh root@your_server_ip
   
   # Update system
   apt update && apt upgrade -y
   
   # Install LAMP stack
   apt install apache2 mysql-server php php-mysql libapache2-mod-php php-xml php-mbstring -y
   
   # Secure MySQL
   mysql_secure_installation
   ```

4. **Configure Apache**:
   ```bash
   # Enable rewrite
   a2enmod rewrite
   
   # Set up virtual host
   nano /etc/apache2/sites-available/000-default.conf
   # Add: DocumentRoot /var/www/html
   # Add: <Directory /var/www/html> AllowOverride All </Directory>
   
   systemctl restart apache2
   ```

5. **Install WordPress**:
   ```bash
   cd /var/www/html
   wget https://wordpress.org/latest.tar.gz
   tar -xzf latest.tar.gz
   mv wordpress/* .
   chown -R www-data:www-data /var/www/html
   ```

6. **Deploy Your Theme**:
   ```bash
   # Upload your theme via git or SFTP
   cd /var/www/html/wp-content/themes/
   git clone your-repo-url
   # Or manually upload via SFTP
   ```

---

## Option 3: Railway (Easiest Dev Server - Free tier, then pay-as-you-go)

### Setup Steps:

1. **Create account** at railway.app

2. **Create New Project** → Deploy from GitHub

3. **Configure Environment Variables**:
   - `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`
   - Add MySQL service in Railway

4. **Deploy**:
   - Connect GitHub repo
   - Railway auto-deploys on push

---

## Quick Setup Script for DigitalOcean

Save this as `setup-wordpress.sh`:

```bash
#!/bin/bash
# WordPress + Your Theme Setup Script

# Update system
apt update && apt upgrade -y

# Install LAMP
apt install apache2 mysql-server php php-mysql libapache2-mod-php php-xml php-mbstring php-curl php-zip unzip git -y

# Configure MySQL
mysql -e "CREATE DATABASE wordpress_db;"
mysql -e "CREATE USER 'wpuser'@'localhost' IDENTIFIED BY 'your_secure_password';"
mysql -e "GRANT ALL PRIVILEGES ON wordpress_db.* TO 'wpuser'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Download WordPress
cd /var/www/html
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rm -rf wordpress latest.tar.gz

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Enable Apache modules
a2enmod rewrite
a2enmod headers

# Configure Apache
cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Restart Apache
systemctl restart apache2
systemctl enable apache2

echo "WordPress installed! Visit http://your_server_ip to complete setup."
```

---

## Recommended: Start with Shared Hosting

For the **cheapest and simplest** option, go with **Namecheap Stellar hosting**:
1. Easy cPanel interface
2. One-click WordPress install
3. FTP/File Manager for theme uploads
4. Includes email, SSL, and domain management
5. Only ~$1.58/month first year

Then you can deploy your theme via:
- **FTP/SFTP**: Upload theme folder
- **Git**: If hosting supports git (some do)
- **File Manager**: cPanel file browser

---

## Next Steps

1. Choose a hosting provider
2. Purchase and set up hosting
3. Create database
4. Upload WordPress (or use one-click install)
5. Upload your theme via FTP/File Manager
6. Activate theme in WordPress admin

Need help with any specific step? Let me know!
