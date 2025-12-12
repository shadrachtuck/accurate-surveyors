# How to Export Database from Local by Flywheel

## Step-by-Step with Screenshots Guide

### Option 1: Using Local's Built-in Database Tools

1. **Open the Local App**
   - Look for the Local icon in your Applications folder or Dock
   - This is NOT WordPress admin - it's the Local by Flywheel desktop app

2. **Select Your Site**
   - In the left sidebar of Local, click on **"accurate-surveying-mapping"**
   - Your site should highlight/select

3. **Find Database Options**
   - Look for tabs or buttons at the top or in the main panel:
     - **"Database"** tab
     - **"Open Database"** button
     - **"Adminer"** button
     - **"phpMyAdmin"** button
   - Any of these will open a database management tool

4. **If You See "Open Database" or Similar Button:**
   - Click it - it will open your default browser
   - You'll see phpMyAdmin or Adminer interface

5. **Export in phpMyAdmin/Adminer:**
   - **Select database** from left sidebar (usually named `local` or similar)
   - Click **"Export"** tab at the top
   - Choose **"Quick"** method
   - Format: **SQL**
   - Click **"Go"** or **"Export"**
   - File downloads automatically

---

### Option 2: Using Terminal (Command Line)

If you can't find the Database tab/button in Local:

```bash
# Step 1: Navigate to your site directory
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"

# Step 2: Find MySQL connection info
# Local stores this in different places depending on version
# Check for a .env file or config:
cat .env 2>/dev/null || cat conf/mysql/my.cnf 2>/dev/null

# Step 3: Export using mysqldump
# Common Local by Flywheel defaults:
# Username: root
# Password: root (or check Local settings)
# Database name: usually 'local' or matches site folder name

mysqldump -u root -proot local > ~/Desktop/local-site-backup.sql

# If password is different, use:
mysqldump -u root -p local > ~/Desktop/local-site-backup.sql
# Then enter password when prompted
```

---

### Option 3: Use WordPress Export (Alternative - Less Complete)

If database export isn't working, you can use WordPress's built-in export:

1. **Open WordPress Admin** in your browser:
   - Go to: `http://accurate-surveying-mapping.local/wp-admin`
   - Log in

2. **Go to Tools → Export**
   - Left sidebar: **Tools** → **Export**

3. **Choose what to export:**
   - Select **"All content"**
   - Click **"Download Export File"**

**Note:** This exports content (posts, pages, etc.) but NOT:
- Plugin settings
- Theme customizations
- User accounts
- Database structure

For full migration, database export is better, but this works for content.

---

### Option 4: Direct File Access (If Using SQLite)

Some Local setups use SQLite instead of MySQL:

```bash
# Check if using SQLite
ls -la "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/conf/database.sqlite"

# If file exists, copy it:
cp "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/conf/database.sqlite" ~/Desktop/local-database.sqlite

# Note: SQLite won't work directly with MySQL on DigitalOcean
# You'd need to convert it or use different migration method
```

---

### Finding Local Database Connection Info

If you need to find your database credentials:

**In Local App:**
1. Select your site
2. Look for **"Connection Info"** or **"Database Info"** section
3. Or check **"Environment"** tab

**Via File System:**
```bash
# Check Local's configuration
cat "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/.env" 2>/dev/null

# Or check wp-config.php
cat "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/app/public/wp-config.php" | grep DB_
```

This will show:
- `DB_NAME` = database name
- `DB_USER` = username (usually `root`)
- `DB_PASSWORD` = password
- `DB_HOST` = host (usually `localhost`)

---

### Quick Test: Can You Connect?

Test if you can connect to the database:

```bash
# Try connecting (replace password if different):
mysql -u root -proot -e "SHOW DATABASES;"

# If that works, list tables:
mysql -u root -proot -D local -e "SHOW TABLES;"

# Then you can export:
mysqldump -u root -proot local > ~/Desktop/local-site-backup.sql
```

---

### Still Stuck?

**Try these:**

1. **Check Local version:**
   - Local → About (or Help → About)
   - Newer versions have different UI

2. **Look for "Database" in different places:**
   - Top menu bar
   - Right-click site name → context menu
   - Site settings/configuration

3. **Use WordPress Admin method** (Option 3 above) as temporary solution

4. **Check Local documentation:**
   - https://localwp.com/help-docs/

5. **Alternative: Use phpMyAdmin directly:**
   - If Local runs MySQL, you might be able to access it via:
   - `http://localhost/phpmyadmin` or
   - The port Local assigns (check Local → Site → Database → View Connection Info)

---

**Once you have the SQL file, continue with Step 2 in `DATABASE-MIGRATION.md`!**

