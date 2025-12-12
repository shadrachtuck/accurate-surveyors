# DigitalOcean Quick Start Guide

## 🎯 Setup Overview

- **Development Server**: `$5/month` droplet for testing
- **Production Server**: `$6-12/month` droplet for live site
- **Total Cost**: ~$11-17/month

---

## 📋 Quick Setup Steps

### 1. Create Development Droplet

1. **Sign up**: https://www.digitalocean.com (get $200 free credit)
2. **Create Droplet**:
   - Image: **Ubuntu 22.04 LTS**
   - Plan: **$5/month** (1 GB RAM)
   - Region: Choose closest
   - Authentication: Add SSH key (recommended)
   - Hostname: `dev-yourdomain`
   - Click **Create Droplet**

3. **SSH into server**:
   ```bash
   ssh root@YOUR_DEV_IP
   ```

4. **Run setup script**:
   ```bash
   # Upload setup script first
   scp scripts/do-setup-server.sh root@YOUR_DEV_IP:/root/
   ssh root@YOUR_DEV_IP
   chmod +x do-setup-server.sh
   ./do-setup-server.sh
   ```

5. **Create WordPress database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE dev_wordpress;
   CREATE USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

6. **Install WordPress**:
   ```bash
   cd /var/www/html
   wget https://wordpress.org/latest.tar.gz
   tar -xzf latest.tar.gz
   mv wordpress/* .
   rm -rf wordpress latest.tar.gz
   chown -R www-data:www-data /var/www/html
   
   # Configure wp-config.php
   cp wp-config-sample.php wp-config.php
   nano wp-config.php
   # Update: DB_NAME, DB_USER, DB_PASSWORD
   ```

7. **Access WordPress**: Visit `http://YOUR_DEV_IP` and complete installation

8. **Deploy theme**:
   ```bash
   # Update deploy script with your IP, then:
   ./scripts/deploy-do-dev.sh
   ```

---

### 2. Create Production Droplet

**Repeat steps above**, but:
- Use **$6-12/month** plan for production
- Database: `prod_wordpress`
- User: `prod_wp_user`
- Hostname: `prod-yourdomain`

**Important**: Don't configure domain DNS yet!

---

### 3. Configure Domain & DNS

#### A. Add Domain to DigitalOcean

1. Go to **Networking** → **Domains**
2. Add domain: `yourdomain.com`
3. Note nameservers:
   - `ns1.digitalocean.com`
   - `ns2.digitalocean.com`
   - `ns3.digitalocean.com`

#### B. Update Nameservers at Registrar

At your domain registrar (GoDaddy, Namecheap, etc.):
- Update nameservers to DigitalOcean ones above

#### C. Add DNS Records

In DigitalOcean → Domains → `yourdomain.com`:

```
Type    Hostname    Will Direct To
A       @           [Production Droplet IP]
A       www         [Production Droplet IP]
A       dev         [Development Droplet IP]  (optional)
```

#### D. Configure Apache for Domain

On production server:
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
</VirtualHost>
```

```bash
a2ensite yourdomain.conf
a2dissite 000-default.conf
systemctl reload apache2
```

---

### 4. Install SSL Certificate

**After DNS propagates** (check: https://www.whatsmydns.net):

```bash
# On production server
certbot --apache -d yourdomain.com -d www.yourdomain.com

# On dev server (if using subdomain)
certbot --apache -d dev.yourdomain.com
```

---

### 5. Deploy Theme to Production

```bash
# Update PROD_IP in script first, then:
./scripts/deploy-do-prod.sh
```

---

## 🔧 Useful Commands

### SSH Access
```bash
ssh root@DEV_IP      # Dev server
ssh root@PROD_IP     # Prod server
```

### Check Services
```bash
systemctl status apache2
systemctl status mysql
systemctl restart apache2
```

### View Logs
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

### Database Backup
```bash
mysqldump -u wp_user -p wordpress_db > backup.sql
```

### File Permissions
```bash
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod 600 /var/www/html/wp-config.php
```

---

## 📝 Pre-Deployment Checklist

### Before DNS Switch:
- [ ] Dev server fully tested
- [ ] Production site set up
- [ ] Theme deployed to production
- [ ] Content migrated (if applicable)
- [ ] Production tested via IP or hosts file
- [ ] Backup of old site completed

### After DNS Switch:
- [ ] DNS propagated (check whatsmydns.net)
- [ ] SSL certificate installed
- [ ] Site accessible at domain
- [ ] All pages working
- [ ] Forms tested
- [ ] Mobile responsive verified

---

## 🆘 Troubleshooting

### Can't SSH into droplet?
- Check firewall settings
- Verify SSH key added
- Try password auth (if enabled)

### WordPress not loading?
- Check Apache: `systemctl status apache2`
- Check file permissions
- View error logs: `tail -f /var/log/apache2/error.log`

### Database connection error?
- Verify credentials in wp-config.php
- Check MySQL running: `systemctl status mysql`
- Test connection: `mysql -u wp_user -p`

### SSL not working?
- Wait 10-15 min after certbot
- Check DNS propagated
- Verify ports 80/443 open in firewall

---

## 📚 Full Documentation

- **Detailed Setup**: See `DIGITALOCEAN-SETUP.md`
- **Deployment Scripts**: `scripts/` directory
- **General Info**: See `README.md`

---

## 💰 Cost Breakdown

- Development: $5/month
- Production: $6-12/month
- Snapshots: $0.06/GB/month
- **Total**: ~$11-17/month

**Get $200 free credit** with referral link!
