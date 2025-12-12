#!/bin/bash
# DigitalOcean Server Setup Script
# Installs LAMP stack and configures for WordPress
# Usage: Run as root on fresh Ubuntu droplet

set -e

echo "==================================="
echo "DigitalOcean WordPress Server Setup"
echo "==================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root (use sudo)"
    exit 1
fi

echo -e "${YELLOW}Step 1: Updating system packages...${NC}"
apt update && apt upgrade -y

echo -e "${YELLOW}Step 2: Installing LAMP stack...${NC}"
apt install -y \
    apache2 \
    mysql-server \
    php \
    php-mysql \
    libapache2-mod-php \
    php-xml \
    php-mbstring \
    php-curl \
    php-zip \
    php-gd \
    php-imagick \
    unzip \
    git \
    certbot \
    python3-certbot-apache \
    ufw

echo -e "${YELLOW}Step 3: Configuring MySQL...${NC}"
systemctl start mysql
systemctl enable mysql

echo ""
echo -e "${GREEN}MySQL secure installation:${NC}"
echo "You'll be prompted to:"
echo "  - Set root password"
echo "  - Remove anonymous users (yes)"
echo "  - Disallow root login remotely (yes)"
echo "  - Remove test database (yes)"
echo "  - Reload privilege tables (yes)"
echo ""
read -p "Press Enter to continue with mysql_secure_installation..."
mysql_secure_installation

echo -e "${YELLOW}Step 4: Enabling Apache modules...${NC}"
a2enmod rewrite
a2enmod headers
a2enmod ssl
systemctl restart apache2

echo -e "${YELLOW}Step 5: Configuring firewall...${NC}"
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Apache Full'
ufw --force enable

echo -e "${YELLOW}Step 6: Setting up web directory...${NC}"
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo -e "${YELLOW}Step 7: Configuring Apache for WordPress...${NC}"
cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

systemctl restart apache2

echo ""
echo -e "${GREEN}===================================${NC}"
echo -e "${GREEN}Server setup complete!${NC}"
echo -e "${GREEN}===================================${NC}"
echo ""
echo "Next steps:"
echo "1. Create WordPress database:"
echo "   mysql -u root -p"
echo "   CREATE DATABASE wordpress_db;"
echo "   CREATE USER 'wp_user'@'localhost' IDENTIFIED BY 'strong_password';"
echo "   GRANT ALL PRIVILEGES ON wordpress_db.* TO 'wp_user'@'localhost';"
echo "   FLUSH PRIVILEGES;"
echo "   EXIT;"
echo ""
echo "2. Install WordPress:"
echo "   cd /var/www/html"
echo "   wget https://wordpress.org/latest.tar.gz"
echo "   tar -xzf latest.tar.gz"
echo "   mv wordpress/* ."
echo "   chown -R www-data:www-data /var/www/html"
echo ""
echo "3. Configure wp-config.php with your database credentials"
echo ""
echo "4. Visit your server IP to complete WordPress installation"
echo ""
echo "5. Install SSL certificate (after DNS is configured):"
echo "   certbot --apache -d yourdomain.com"
echo ""

