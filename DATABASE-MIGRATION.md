# Database Migration Guide

This guide explains how to migrate your local WordPress database to your DigitalOcean server.

---

## Option 1: Complete Migration (Recommended for First-Time Setup)

This method exports your entire local database and imports it to the server, then updates URLs.

### Step 1: Export Database from Local

#### Using Local by Flywheel / LocalWP (Easiest)

1. **Open Local by Flywheel**
2. **Select your site**: "accurate-surveying-mapping"
3. **Click "Database" tab** (or "Open Database" button)
4. This opens **phpMyAdmin** in your browser
5. **Select your database** from the left sidebar
6. Click **"Export"** tab at the top
7. Choose **"Quick"** export method
8. Format: **SQL**
9. Click **"Go"**
10. **Save the file** (e.g., `local-site-backup.sql`)

#### Using Command Line (Alternative)

**From your local machine:**
```bash
# Navigate to your site directory
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/app/public"

# Find your database name in wp-config.php first:
grep "DB_NAME" wp-config.php

# Export (adjust credentials if needed)
mysqldump -u root -p --databases your_local_db_name > ~/Desktop/local-site-backup.sql
# Enter your MySQL password when prompted
```

**Note**: Local by Flywheel typically uses:
- Username: `root`
- Password: `root` (or check Local settings)
- Database name: usually matches your site name (check wp-config.php)

---

### Step 2: Upload Database to Server

**Option A: Using SCP (Secure Copy)**

```bash
# From your local machine
scp ~/Desktop/local-site-backup.sql root@YOUR_SERVER_IP:/root/
```

**Option B: Using SFTP Client**

1. Use FileZilla, Cyberduck, or similar
2. Connect via SFTP to your server
3. Upload `local-site-backup.sql` to `/root/`

---

### Step 3: Import Database on Server

**SSH into your server:**
```bash
ssh root@YOUR_SERVER_IP
```

**Import the database:**
```bash
# Import into your WordPress database
mysql -u dev_wp_user -p dev_wordpress < /root/local-site-backup.sql
# Enter the password you set for dev_wp_user

# OR if importing to production:
mysql -u prod_wp_user -p prod_wordpress < /root/local-site-backup.sql
```

---

### Step 4: Update URLs in Database

Your local site uses URLs like `http://accurate-surveying-mapping.local` but your server needs different URLs.

**Option A: Using WP-CLI (Best Method - Recommended)**

**Install WP-CLI on server first:**
```bash
# On server (via SSH)
cd /var/www/html
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
wp --info  # Verify installation
```

**Update URLs (choose based on your setup):**

**For Development (using IP):**
```bash
cd /var/www/html
wp search-replace 'http://accurate-surveying-mapping.local' 'http://YOUR_DEV_IP' --allow-root
wp search-replace 'https://accurate-surveying-mapping.local' 'http://YOUR_DEV_IP' --allow-root
```

**For Development (using subdomain):**
```bash
cd /var/www/html
wp search-replace 'http://accurate-surveying-mapping.local' 'http://dev.yourdomain.com' --allow-root
wp search-replace 'https://accurate-surveying-mapping.local' 'https://dev.yourdomain.com' --allow-root
```

**For Production:**
```bash
cd /var/www/html
wp search-replace 'http://accurate-surveying-mapping.local' 'https://yourdomain.com' --allow-root
wp search-replace 'https://accurate-surveying-mapping.local' 'https://yourdomain.com' --allow-root
wp search-replace 'http://yourdomain.com' 'https://yourdomain.com' --allow-root  # Force HTTPS
```

**Option B: Using Better Search Replace Plugin**

1. **Install plugin** on server:
   - Log into WordPress admin on server
   - Plugins → Add New
   - Search "Better Search Replace"
   - Install and Activate

2. **Run search-replace:**
   - Tools → Better Search Replace
   - Search: `http://accurate-surveying-mapping.local`
   - Replace: `http://YOUR_DEV_IP` (or your domain)
   - Select all tables
   - **Check "Run as dry run?" first** to preview changes
   - Click "Run Search/Replace"
   - Review results, then **uncheck dry run** and run again

3. **Deactivate and delete plugin** after migration (for security)

**Option C: Manual SQL (Not Recommended - Use Only If Needed)**

```bash
# SSH into server
ssh root@YOUR_SERVER_IP

# Login to MySQL
mysql -u dev_wp_user -p dev_wordpress

# In MySQL prompt:
UPDATE wp_options SET option_value = replace(option_value, 'http://accurate-surveying-mapping.local', 'http://YOUR_DEV_IP') WHERE option_name = 'home';
UPDATE wp_options SET option_value = replace(option_value, 'http://accurate-surveying-mapping.local', 'http://YOUR_DEV_IP') WHERE option_name = 'siteurl';
UPDATE wp_posts SET post_content = replace(post_content, 'http://accurate-surveying-mapping.local', 'http://YOUR_DEV_IP');
UPDATE wp_posts SET guid = replace(guid, 'http://accurate-surveying-mapping.local', 'http://YOUR_DEV_IP');
UPDATE wp_postmeta SET meta_value = replace(meta_value, 'http://accurate-surveying-mapping.local', 'http://YOUR_DEV_IP');
EXIT;
```

---

### Step 5: Update wp-config.php on Server

**SSH into server:**
```bash
ssh root@YOUR_SERVER_IP
nano /var/www/html/wp-config.php
```

**Verify/Update these lines match your server database:**
```php
define('DB_NAME', 'dev_wordpress');  // or 'prod_wordpress' for production
define('DB_USER', 'dev_wp_user');    // or 'prod_wp_user' for production
define('DB_PASSWORD', 'YOUR_PASSWORD_HERE');
define('DB_HOST', 'localhost');
```

**Add these lines (if not present) for production:**
```php
// Force SSL in admin (optional, for production)
define('FORCE_SSL_ADMIN', true);

// Update URLs without accessing database
define('WP_HOME','https://yourdomain.com');
define('WP_SITEURL','https://yourdomain.com');
```

**Save**: `Ctrl+O`, `Enter`, `Ctrl+X`

---

### Step 6: Migrate Media Files (Uploads Folder)

**Option A: Using SCP (Recommended)**

**From your local machine:**
```bash
# Copy entire wp-content/uploads directory
scp -r "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/app/public/wp-content/uploads" root@YOUR_SERVER_IP:/var/www/html/wp-content/

# Set correct permissions on server
ssh root@YOUR_SERVER_IP
chown -R www-data:www-data /var/www/html/wp-content/uploads
chmod -R 755 /var/www/html/wp-content/uploads
```

**Option B: Using SFTP Client**

1. Connect via SFTP to your server
2. Navigate to `/var/www/html/wp-content/`
3. Upload entire `uploads` folder from local

**Option C: Using WordPress Import/Export (For Small Sites)**

1. **Export from local**: Tools → Export → All content
2. **Import on server**: Tools → Import → WordPress

---

### Step 7: Test Your Site

1. **Visit your site**: `http://YOUR_DEV_IP` (or your domain)
2. **Log in**: Use your **local WordPress admin credentials**
3. **Check**:
   - Homepage loads correctly
   - All pages accessible
   - Images/media displaying
   - Navigation working
   - Forms functional
   - Theme displaying properly

---

## Option 2: Fresh Install + Manual Content Migration

If you prefer to start fresh and only migrate specific content:

1. **Install WordPress on server** (fresh installation)
2. **Migrate content manually**:
   - Copy/paste page content
   - Upload images as needed
   - Recreate menus
   - Reconfigure theme settings

**Pros**: Clean database, no old data
**Cons**: Time-consuming, need to reconfigure everything

---

## Troubleshooting

### "Error establishing database connection"

**Check:**
- Database credentials in `wp-config.php` match server
- MySQL service is running: `systemctl status mysql`
- Database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- User has permissions: `mysql -u root -p -e "SHOW GRANTS FOR 'dev_wp_user'@'localhost';"`

### Images Not Displaying

**Check:**
- Media files uploaded: `ls -la /var/www/html/wp-content/uploads`
- File permissions: `chown -R www-data:www-data /var/www/html/wp-content/uploads`
- URLs updated in database (use WP-CLI search-replace)

### "This site can't be reached" or Redirect Loop

**Fix:**
- Update `WP_HOME` and `WP_SITEURL` in `wp-config.php`
- Or run WP-CLI search-replace again
- Clear browser cache

### Mixed Content Warnings (HTTP/HTTPS)

**Fix:**
```bash
# Force all URLs to HTTPS
wp search-replace 'http://yourdomain.com' 'https://yourdomain.com' --allow-root
```

Or install "Really Simple SSL" plugin temporarily to fix SSL issues.

---

## Quick Migration Checklist

- [ ] Exported database from local
- [ ] Created database on server
- [ ] Uploaded SQL file to server
- [ ] Imported database on server
- [ ] Updated URLs in database (search-replace)
- [ ] Updated wp-config.php with correct credentials
- [ ] Migrated uploads folder
- [ ] Set correct file permissions
- [ ] Tested site accessibility
- [ ] Tested admin login
- [ ] Verified images/media loading
- [ ] Checked all pages working
- [ ] Tested forms/functionality

---

## Database Backup Before Migration

**Always backup before making changes:**

```bash
# On server - create backup before any changes
mysqldump -u dev_wp_user -p dev_wordpress > backup-before-migration-$(date +%Y%m%d).sql
```

---

## Need to Start Over?

If something goes wrong:

```bash
# On server - drop and recreate database
mysql -u root -p
DROP DATABASE dev_wordpress;
CREATE DATABASE dev_wordpress;
GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Then start from Step 3 (Import)
```

---

**Ready to migrate? Start with Step 1 (Export from Local)!**
