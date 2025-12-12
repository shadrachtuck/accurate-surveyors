# Verify WordPress Setup After Database Connection

Great! Your database connection is working. Now let's make sure WordPress can use it.

---

## Step 1: Verify wp-config.php Has Correct Password

**Check your wp-config.php:**
```bash
cat /var/www/html/wp-config.php | grep -E "DB_NAME|DB_USER|DB_PASSWORD|DB_HOST"
```

**Should show:**
```php
define('DB_NAME', 'dev_accurate_surveyors');
define('DB_USER', 'dev_wp_user');
define('DB_PASSWORD', 'toiruasfn32465');
define('DB_HOST', 'localhost');
```

**If password is wrong, update it:**
```bash
nano /var/www/html/wp-config.php
```

Make sure the password line looks exactly like:
```php
define('DB_PASSWORD', 'toiruasfn32465');
```

Save: `Ctrl+O`, `Enter`, `Ctrl+X`

---

## Step 2: Check if Database Has Tables

**If you've already imported your database:**
```bash
mysql -u dev_wp_user -p dev_accurate_surveyors -e "SHOW TABLES;"
```

Enter password: `toiruasfn32465`

**Expected:** List of WordPress tables like `wp_posts`, `wp_users`, `wp_options`, etc.

**If you see "Empty set":** Database is empty - you need to import your database or run WordPress installation.

---

## Step 3: Visit Your Site

**Open in browser:**
- `http://YOUR_SERVER_IP`

**Expected results:**

**✅ If database has tables (imported):**
- Site should load (or might need URL updates - see Step 4)
- If you see "Error establishing database connection" - check wp-config.php password matches

**✅ If database is empty:**
- WordPress installation screen should appear
- Fill in site details and complete installation

**❌ If you still see "Error establishing database connection":**
- Double-check wp-config.php password matches `toiruasfn32465`
- Check file permissions: `chmod 600 /var/www/html/wp-config.php`
- Restart Apache: `systemctl restart apache2`

---

## Step 4: If You Imported Database - Update URLs

If you imported your local database, you'll need to update URLs from your local site to your server IP.

**Option A: Using WP-CLI (Recommended)**

**Install WP-CLI first:**
```bash
cd /var/www/html
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
wp --info  # Verify installation
```

**Update URLs:**
```bash
cd /var/www/html
wp search-replace 'http://accurate-surveying-mapping.local' 'http://YOUR_SERVER_IP' --allow-root
wp search-replace 'https://accurate-surveying-mapping.local' 'http://YOUR_SERVER_IP' --allow-root
```

**Option B: Using Better Search Replace Plugin**

1. Log into WordPress admin (if you can access it)
2. Install "Better Search Replace" plugin
3. Tools → Better Search Replace
4. Search: `http://accurate-surveying-mapping.local`
5. Replace: `http://YOUR_SERVER_IP`
6. Run search/replace

---

## Step 5: Check File Permissions

Make sure WordPress files have correct permissions:

```bash
# Set ownership
chown -R www-data:www-data /var/www/html

# Set permissions
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;

# Secure wp-config.php
chmod 600 /var/www/html/wp-config.php
chown www-data:www-data /var/www/html/wp-config.php
```

---

## Quick Checklist

- [ ] Database connection works ✅ (You've done this!)
- [ ] wp-config.php has correct password: `toiruasfn32465`
- [ ] Database has tables (if imported) or is ready for installation
- [ ] Site loads in browser
- [ ] URLs updated (if database was imported)
- [ ] File permissions correct
- [ ] Apache is running: `systemctl status apache2`

---

## Troubleshooting

### Still seeing database error?

1. **Verify wp-config.php:**
   ```bash
   cat /var/www/html/wp-config.php | grep DB_PASSWORD
   ```

2. **Test connection again:**
   ```bash
   mysql -u dev_wp_user -p dev_accurate_surveyors -e "SELECT 1;"
   ```

3. **Check Apache error logs:**
   ```bash
   tail -20 /var/log/apache2/error.log
   ```

4. **Restart services:**
   ```bash
   systemctl restart apache2
   systemctl restart mysql
   ```

---

## Next Steps

Once WordPress is loading:

1. **Deploy your theme** (if not already done):
   ```bash
   # From your local machine
   ./scripts/deploy-do-dev.sh
   ```

2. **Activate theme** in WordPress admin:
   - Appearance → Themes → Activate "Accurate Surveyors 2025"

3. **Import content** (if needed):
   - Tools → Import → WordPress (if you used export, not database import)

4. **Test everything:**
   - Homepage
   - Pages
   - Navigation
   - Forms
   - Theme functionality

---

**You're almost there! Visit your site in the browser and let me know what you see!**
