# Troubleshooting Database Connection Error

WordPress can't connect to your database. Let's fix this step by step.

---

## Quick Diagnosis Commands

**SSH into your server:**
```bash
ssh root@YOUR_SERVER_IP
```

### Step 1: Check if MySQL is Running

```bash
systemctl status mysql
```

**Should show:** `active (running)`

**If not running:**
```bash
systemctl start mysql
systemctl enable mysql
```

---

### Step 2: Check wp-config.php Database Settings

```bash
cat /var/www/html/wp-config.php | grep -E "DB_NAME|DB_USER|DB_PASSWORD|DB_HOST"
```

**Should show:**
```php
define('DB_NAME', 'dev_accurate_surveyors');
define('DB_USER', 'dev_wp_user');
define('DB_PASSWORD', 'your_password_here');
define('DB_HOST', 'localhost');
```

**If values are wrong, fix them:**
```bash
nano /var/www/html/wp-config.php
```
Update the values, then save: `Ctrl+O`, `Enter`, `Ctrl+X`

---

### Step 3: Verify Database Exists

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

**Look for:** `dev_accurate_surveyors` (or whatever DB_NAME is in wp-config.php)

**If database doesn't exist:**
```bash
mysql -u root -p
CREATE DATABASE dev_accurate_surveyors;
EXIT;
```

---

### Step 4: Verify User Exists and Has Permissions

```bash
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User='dev_wp_user';"
```

**Should show:** Your user and `localhost`

**If user doesn't exist or lacks permissions:**
```bash
mysql -u root -p
```

Then in MySQL:
```sql
-- Create user (if doesn't exist) or update password
CREATE USER IF NOT EXISTS 'dev_wp_user'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD_HERE';

-- Grant permissions
GRANT ALL PRIVILEGES ON dev_accurate_surveyors.* TO 'dev_wp_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SHOW GRANTS FOR 'dev_wp_user'@'localhost';

EXIT;
```

---

### Step 5: Test Connection Manually

**Test if user can connect to database:**
```bash
mysql -u dev_wp_user -p dev_accurate_surveyors -e "SELECT DATABASE();"
```

**Enter the password** when prompted.

**If connection fails:**
- Password might be wrong
- User might not have permissions
- Database might not exist

**If connection works:** The credentials are correct, move to Step 6.

---

### Step 6: Check wp-config.php File Permissions

```bash
ls -la /var/www/html/wp-config.php
```

**Should show:** `-rw-------` (readable/writable by owner only)

**If permissions are wrong:**
```bash
chmod 600 /var/www/html/wp-config.php
chown www-data:www-data /var/www/html/wp-config.php
```

---

### Step 7: Check Apache Error Logs

```bash
tail -20 /var/log/apache2/error.log
```

Look for database-related errors.

---

## Common Fixes

### Fix 1: Database Credentials Don't Match

**Problem:** wp-config.php has wrong database name, user, or password.

**Solution:**
```bash
# Check what's in wp-config.php
cat /var/www/html/wp-config.php | grep DB_

# Compare with actual database
mysql -u root -p -e "SHOW DATABASES;"
mysql -u root -p -e "SELECT User, Host FROM mysql.user;"

# Update wp-config.php if needed
nano /var/www/html/wp-config.php
```

---

### Fix 2: User Doesn't Have Permissions

**Problem:** User exists but can't access the database.

**Solution:**
```bash
mysql -u root -p
```

```sql
GRANT ALL PRIVILEGES ON dev_accurate_surveyors.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### Fix 3: Database Doesn't Exist

**Problem:** Database name in wp-config.php doesn't match actual database.

**Solution - Option A: Create the database:**
```bash
mysql -u root -p
CREATE DATABASE dev_accurate_surveyors;
GRANT ALL PRIVILEGES ON dev_accurate_surveyors.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Solution - Option B: Update wp-config.php to match existing database:**
```bash
# See what databases exist
mysql -u root -p -e "SHOW DATABASES;"

# Update wp-config.php to use the correct one
nano /var/www/html/wp-config.php
```

---

### Fix 4: Wrong Password

**Problem:** Password in wp-config.php doesn't match MySQL user password.

**Solution - Reset password:**
```bash
mysql -u root -p
```

```sql
ALTER USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'NewPassword123!';
FLUSH PRIVILEGES;
EXIT;
```

**Then update wp-config.php:**
```bash
nano /var/www/html/wp-config.php
```
Update `DB_PASSWORD` to match the new password.

---

### Fix 5: MySQL Service Not Running

**Problem:** MySQL isn't running.

**Solution:**
```bash
systemctl start mysql
systemctl enable mysql
systemctl status mysql
```

---

## Complete Reset (If Nothing Works)

If all else fails, set everything up from scratch:

```bash
# 1. Connect to MySQL
mysql -u root -p

# 2. Drop and recreate database
DROP DATABASE IF EXISTS dev_accurate_surveyors;
CREATE DATABASE dev_accurate_surveyors;

# 3. Drop and recreate user
DROP USER IF EXISTS 'dev_wp_user'@'localhost';
CREATE USER 'dev_wp_user'@'localhost' IDENTIFIED BY 'YourSecurePassword123!';

# 4. Grant permissions
GRANT ALL PRIVILEGES ON dev_accurate_surveyors.* TO 'dev_wp_user'@'localhost';
FLUSH PRIVILEGES;

# 5. Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User='dev_wp_user';
SHOW GRANTS FOR 'dev_wp_user'@'localhost';

EXIT;
```

**Update wp-config.php:**
```bash
nano /var/www/html/wp-config.php
```

Make sure these match:
```php
define('DB_NAME', 'dev_accurate_surveyors');
define('DB_USER', 'dev_wp_user');
define('DB_PASSWORD', 'YourSecurePassword123!');
define('DB_HOST', 'localhost');
```

**Test connection:**
```bash
mysql -u dev_wp_user -p dev_accurate_surveyors -e "SELECT 'Connection successful!' AS Status;"
```

---

## Quick Diagnostic Script

Run this all-in-one diagnostic:

```bash
echo "=== MySQL Status ==="
systemctl status mysql | head -3

echo ""
echo "=== wp-config.php Database Settings ==="
grep -E "DB_NAME|DB_USER|DB_HOST" /var/www/html/wp-config.php

echo ""
echo "=== Databases ==="
mysql -u root -p -e "SHOW DATABASES;" 2>/dev/null || echo "Can't connect to MySQL as root"

echo ""
echo "=== Database Users ==="
mysql -u root -p -e "SELECT User, Host FROM mysql.user;" 2>/dev/null || echo "Can't connect to MySQL as root"

echo ""
echo "=== Testing User Connection ==="
DB_NAME=$(grep "DB_NAME" /var/www/html/wp-config.php | cut -d "'" -f 4)
DB_USER=$(grep "DB_USER" /var/www/html/wp-config.php | cut -d "'" -f 4)
echo "Attempting to connect as: $DB_USER to database: $DB_NAME"
mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT 'SUCCESS!'" 2>&1 || echo "Connection failed - check password in wp-config.php"
```

---

## Still Having Issues?

1. **Check error logs:**
   ```bash
   tail -50 /var/log/apache2/error.log
   ```

2. **Verify file permissions:**
   ```bash
   ls -la /var/www/html/wp-config.php
   ```

3. **Check if WordPress files are owned by www-data:**
   ```bash
   chown -R www-data:www-data /var/www/html
   ```

4. **Restart Apache:**
   ```bash
   systemctl restart apache2
   ```

---

**Start with Step 1 and work through each step. Share the output if you get stuck!**
