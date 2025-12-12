# Database Cleanup & Verification

You have the right setup! Here's what to verify and clean up.

---

## Your Current Setup

✅ **Database:** `dev_accurate_surveyors` (this is the one you want)  
⚠️ **Database:** `dev_wordpress` (you can delete this if not needed)  
✅ **User:** `dev_wp_user` (this is your WordPress database user)  

**System databases (DO NOT DELETE):**
- `information_schema` - MySQL system database
- `mysql` - MySQL system database  
- `performance_schema` - MySQL system database
- `sys` - MySQL system database

---

## Step 1: Verify User Has Access to Correct Database

Check if `dev_wp_user` has permissions on `dev_accurate_surveyors`:

```bash
mysql -u root -p -e "SHOW GRANTS FOR 'dev_wp_user'@'localhost';"
```

**Expected output should include:**
```
GRANT ALL PRIVILEGES ON `dev_accurate_surveyors`.* TO 'dev_wp_user'@'localhost'
```

---

## Step 2: Grant Permissions (If Needed)

If the user doesn't have access to `dev_accurate_surveyors`, grant it:

```bash
mysql -u root -p
```

Then in MySQL:
```sql
GRANT ALL PRIVILEGES ON dev_accurate_surveyors.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Step 3: Verify wp-config.php Points to Correct Database

Check your WordPress configuration:

```bash
cat /var/www/html/wp-config.php | grep -E "DB_NAME|DB_USER"
```

**Should show:**
```php
define('DB_NAME', 'dev_accurate_surveyors');
define('DB_USER', 'dev_wp_user');
```

**If it shows `dev_wordpress`, update it:**

```bash
nano /var/www/html/wp-config.php
```

Find and change:
```php
define('DB_NAME', 'dev_accurate_surveyors');  // Changed from dev_wordpress
define('DB_USER', 'dev_wp_user');
```

Save: `Ctrl+O`, `Enter`, `Ctrl+X`

---

## Step 4: Test Connection to Correct Database

Test that the user can connect to `dev_accurate_surveyors`:

```bash
mysql -u dev_wp_user -p dev_accurate_surveyors -e "SHOW TABLES;"
```

Enter your `dev_wp_user` password when prompted.

**If successful:** You'll see a list of tables (or "Empty set" if database is empty but connection works).

**If fails:** You'll get an access error - run Step 2 to grant permissions.

---

## Step 5: Clean Up Unused Database (Optional)

If you don't need `dev_wordpress` database, you can delete it:

⚠️ **WARNING:** Only delete if you're sure you don't need it!

```bash
mysql -u root -p
```

Then in MySQL:
```sql
-- First, check if it has any tables
USE dev_wordpress;
SHOW TABLES;

-- If it's empty or you don't need it, drop it:
DROP DATABASE dev_wordpress;

-- Verify it's gone
SHOW DATABASES;

EXIT;
```

---

## Step 6: Final Verification

Run this to verify everything is set up correctly:

```bash
mysql -u root -p << EOF
-- Show databases
SHOW DATABASES;
SELECT '---' AS '';

-- Show user permissions
SHOW GRANTS FOR 'dev_wp_user'@'localhost';
SELECT '---' AS '';

-- Test connection as user (you'll need password)
-- mysql -u dev_wp_user -p dev_accurate_surveyors -e "SELECT DATABASE();"
EOF
```

---

## Quick Commands Summary

**Check user permissions:**
```bash
mysql -u root -p -e "SHOW GRANTS FOR 'dev_wp_user'@'localhost';"
```

**Grant permissions (if needed):**
```bash
mysql -u root -p -e "GRANT ALL PRIVILEGES ON dev_accurate_surveyors.* TO 'dev_wp_user'@'localhost'; FLUSH PRIVILEGES;"
```

**Test connection:**
```bash
mysql -u dev_wp_user -p dev_accurate_surveyors -e "SHOW TABLES;"
```

**Check wp-config.php:**
```bash
grep -E "DB_NAME|DB_USER" /var/www/html/wp-config.php
```

**Delete unused database (if needed):**
```bash
mysql -u root -p -e "DROP DATABASE dev_wordpress;"
```

---

## After Verification: Ready to Import

Once everything is verified:

1. ✅ Database `dev_accurate_surveyors` exists
2. ✅ User `dev_wp_user` has permissions
3. ✅ `wp-config.php` points to correct database
4. ✅ Connection test works

You can now import your database:

```bash
mysql -u dev_wp_user -p dev_accurate_surveyors < /root/your-backup-file.sql
```

---

**Need help? Share the output of the verification commands above!**
