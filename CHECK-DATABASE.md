# How to Check Your Database on DigitalOcean Server

Run these commands to verify your database was created correctly.

---

## Step 1: SSH into Your Server

```bash
ssh root@YOUR_SERVER_IP
```

---

## Step 2: Check if Database Exists

### Check all databases:

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

Enter your MySQL root password when prompted.

**Look for your database name** (e.g., `dev_wordpress` or `prod_wordpress`)

### Check specific database exists:

```bash
mysql -u root -p -e "SHOW DATABASES LIKE 'dev_wordpress';"
```

Replace `dev_wordpress` with your actual database name.

**Expected output:** Should show your database name in a table.

---

## Step 3: Check if Database User Exists

### List all users:

```bash
mysql -u root -p -e "SELECT User, Host FROM mysql.user;"
```

**Look for your database user** (e.g., `dev_wp_user` or `prod_wp_user`)

### Check specific user exists:

```bash
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User='dev_wp_user';"
```

Replace `dev_wp_user` with your actual username.

**Expected output:** Should show your user and `localhost` as host.

---

## Step 4: Check User Permissions

### Check what privileges the user has:

```bash
mysql -u root -p -e "SHOW GRANTS FOR 'dev_wp_user'@'localhost';"
```

Replace `dev_wp_user` with your actual username.

**Expected output:** Should show `GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost'`

---

## Step 5: Test Connection with Your User

### Try to connect as your database user:

```bash
mysql -u dev_wp_user -p dev_wordpress
```

Replace `dev_wp_user` with your username and `dev_wordpress` with your database name.

Enter your database user password when prompted.

**If successful:** You'll see `mysql>` prompt - type `EXIT;` to leave.

**If fails:** You'll see an error like "Access denied" - user doesn't exist or wrong password.

---

## Step 6: Check if Database Has Tables (After Import)

If you've already imported your database:

```bash
mysql -u root -p -e "USE dev_wordpress; SHOW TABLES;"
```

**Expected output:** Should show a list of WordPress tables like:
- `wp_posts`
- `wp_users`
- `wp_options`
- etc.

If you see "No tables found", the database is empty (normal if you haven't imported yet).

---

## Common Issues & Fixes

### Issue 1: Database Doesn't Exist

**Error:** Database name doesn't appear in `SHOW DATABASES`

**Fix:**
```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE dev_wordpress;
EXIT;
```

---

### Issue 2: User Doesn't Exist

**Error:** User doesn't appear in `SELECT User FROM mysql.user`

**Fix:**
```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### Issue 3: User Can't Connect / Access Denied

**Error:** "Access denied for user 'dev_wp_user'@'localhost'"

**Possible causes:**
1. Wrong password
2. User doesn't have permissions
3. User was created but privileges not granted

**Fix - Reset password and permissions:**
```bash
mysql -u root -p
```

Then in MySQL:
```sql
-- Change password
ALTER USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'NEW_PASSWORD_HERE';

-- Grant privileges again
GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### Issue 4: User Exists But Has No Permissions

**Error:** User can connect but can't see database or tables

**Fix:**
```bash
mysql -u root -p
```

Then in MySQL:
```sql
GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Quick Health Check Script

Run this single command to check everything at once:

```bash
mysql -u root -p << EOF
SHOW DATABASES;
SELECT '---' AS '';
SELECT User, Host FROM mysql.user WHERE User LIKE '%wp%';
SELECT '---' AS '';
SHOW GRANTS FOR 'dev_wp_user'@'localhost';
EOF
```

Replace `dev_wp_user` with your actual username.

This will show:
1. All databases (look for yours)
2. All WordPress-related users
3. Permissions for your user

---

## Complete Database Setup (If Starting Fresh)

If you need to create everything from scratch:

```bash
mysql -u root -p
```

Then run these SQL commands (replace with your actual values):

```sql
-- Create database
CREATE DATABASE dev_wordpress;

-- Create user
CREATE USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'YourSecurePassword123!';

-- Grant privileges
GRANT ALL PRIVILEGES ON dev_wordpress.* TO 'dev_wp_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User='dev_wp_user';
SHOW GRANTS FOR 'dev_wp_user'@'localhost';

-- Exit
EXIT;
```

---

## After Verification: Update wp-config.php

Once you've confirmed everything is set up correctly, make sure your `wp-config.php` matches:

```bash
nano /var/www/html/wp-config.php
```

Verify these lines match your database:
```php
define('DB_NAME', 'dev_wordpress');
define('DB_USER', 'dev_wp_user');
define('DB_PASSWORD', 'YourSecurePassword123!');
define('DB_HOST', 'localhost');
```

Save: `Ctrl+O`, `Enter`, `Ctrl+X`

---

## Ready to Import?

Once database is verified, you can import your backup:

```bash
# Make sure your SQL file is on the server
ls -lh /root/your-backup-file.sql

# Import
mysql -u dev_wp_user -p dev_wordpress < /root/your-backup-file.sql
```

Enter your database user password when prompted.

---

**Need help? Share the output of the commands above and I can help troubleshoot!**
