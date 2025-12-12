# GitHub Setup & Deployment Guide

This guide shows you how to connect your repository to GitHub and deploy to DigitalOcean.

---

## Part 1: Connect Repository to GitHub

### Step 1: Create GitHub Repository

1. **Go to GitHub**: https://github.com
2. **Sign in** (or create account if needed)
3. **Click the "+" icon** (top right) → **"New repository"**
4. **Repository settings:**
   - **Name**: `accurate-surveying-mapping` (or your preferred name)
   - **Description**: "WordPress theme for Accurate Surveying & Mapping"
   - **Visibility**: Choose **Private** (recommended) or Public
   - **DO NOT** check "Initialize with README" (we already have files)
   - **DO NOT** add .gitignore or license (we already have them)
5. **Click "Create repository"**

### Step 2: Connect Local Repository to GitHub

**From your local machine** (in your project directory):

```bash
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"

# Check if remote already exists
git remote -v

# Add GitHub as remote (replace YOUR_USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR_USERNAME/accurate-surveying-mapping.git

# Or if using SSH (recommended if you have SSH keys set up):
# git remote add origin git@github.com:YOUR_USERNAME/accurate-surveying-mapping.git

# Verify remote was added
git remote -v
```

### Step 3: Push to GitHub

```bash
# Make sure you're on main branch
git branch

# Push all commits to GitHub
git push -u origin main

# If you get authentication error, GitHub will prompt for:
# - Username: your GitHub username
# - Password: use a Personal Access Token (see below)
```

**If you need to create a Personal Access Token:**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Give it a name: "DigitalOcean Deployment"
4. Select scopes: `repo` (full control)
5. Click "Generate token"
6. **Copy the token** (you'll only see it once)
7. Use this token as your password when pushing

---

## Part 2: Deployment Options

You have several options for deploying to DigitalOcean:

### Option A: Manual Deployment (Simplest - Recommended for now)

**Deploy theme files directly via SCP:**

```bash
# From your local machine
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"

# Deploy to development server
./scripts/deploy-do-dev.sh

# Or deploy to production
./scripts/deploy-do-prod.sh
```

**This is what you've been doing** - works great for smaller sites!

---

### Option B: GitHub Actions (Automated Deployment)

Automatically deploy when you push to GitHub.

#### Step 1: Create GitHub Actions Workflow

Create the workflow file:

```bash
mkdir -p .github/workflows
```

Create `.github/workflows/deploy-dev.yml`:

```yaml
name: Deploy to Development Server

on:
  push:
    branches:
      - main
    paths:
      - 'app/public/wp-content/themes/accurate surveyors 2025/**'
      - '.github/workflows/deploy-dev.yml'

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      
    - name: Deploy to DigitalOcean
      uses: appleboy/scp-action@master
      with:
        host: ${{ secrets.DO_DEV_IP }}
        username: ${{ secrets.DO_DEV_USER }}
        key: ${{ secrets.DO_DEV_SSH_KEY }}
        source: "app/public/wp-content/themes/accurate surveyors 2025"
        target: "/var/www/html/wp-content/themes/"
        rm: false
```

#### Step 2: Set Up GitHub Secrets

1. **Go to your GitHub repository**
2. **Settings** → **Secrets and variables** → **Actions**
3. **Click "New repository secret"** for each:

   - **Name**: `DO_DEV_IP`
     - **Value**: Your development server IP address
   
   - **Name**: `DO_DEV_USER`
     - **Value**: `root` (or your SSH user)
   
   - **Name**: `DO_DEV_SSH_KEY`
     - **Value**: Your private SSH key content
     - To get it: `cat ~/.ssh/id_rsa` (or your key path)
     - **Don't share this!**

#### Step 3: Create SSH Key Pair (If You Don't Have One)

```bash
# Generate SSH key (if you don't have one)
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

# Copy public key to server
ssh-copy-id root@YOUR_DEV_IP

# Test connection (should work without password)
ssh root@YOUR_DEV_IP
```

---

### Option C: Git-Based Deployment on Server

Clone your GitHub repo directly on the server and pull updates.

#### Step 1: Set Up on Server

**SSH into your server:**
```bash
ssh root@YOUR_DEV_IP
```

**Set up deployment directory:**
```bash
# Backup current theme (if it exists)
cp -r /var/www/html/wp-content/themes/"accurate surveyors 2025" /var/www/html/wp-content/themes/"accurate surveyors 2025.backup"

# Clone repository (shallow clone, just latest)
cd /var/www/html/wp-content/themes/
git clone --depth 1 https://github.com/YOUR_USERNAME/accurate-surveying-mapping.git temp-repo

# Copy theme from cloned repo
cp -r temp-repo/"app/public/wp-content/themes/accurate surveyors 2025" ./

# Set permissions
chown -R www-data:www-data "accurate surveyors 2025"
chmod -R 755 "accurate surveyors 2025"

# Clean up
rm -rf temp-repo
```

#### Step 2: Update Script (Create on Server)

Create `/root/update-theme.sh` on server:

```bash
#!/bin/bash
cd /var/www/html/wp-content/themes/
git clone --depth 1 https://github.com/YOUR_USERNAME/accurate-surveying-mapping.git temp-repo
cp -r temp-repo/"app/public/wp-content/themes/accurate surveyors 2025" ./
chown -R www-data:www-data "accurate surveyors 2025"
chmod -R 755 "accurate surveyors 2025"
rm -rf temp-repo
echo "Theme updated successfully!"
```

Make it executable:
```bash
chmod +x /root/update-theme.sh
```

**To update theme later:**
```bash
ssh root@YOUR_DEV_IP '/root/update-theme.sh'
```

---

## Part 3: Recommended Workflow

### For Development (Now):

1. **Make changes locally**
2. **Commit changes:**
   ```bash
   git add .
   git commit -m "Description of changes"
   ```
3. **Push to GitHub:**
   ```bash
   git push origin main
   ```
4. **Deploy manually:**
   ```bash
   ./scripts/deploy-do-dev.sh
   ```

### For Production (After Testing):

1. **Test everything on dev server**
2. **When ready, deploy to production:**
   ```bash
   ./scripts/deploy-do-prod.sh
   ```

---

## Part 4: Useful Git Commands

```bash
# Check status
git status

# Add all changes
git add .

# Commit changes
git commit -m "Your commit message"

# Push to GitHub
git push origin main

# Pull latest from GitHub
git pull origin main

# Check remote
git remote -v

# View commit history
git log --oneline

# Create a new branch
git checkout -b feature/new-feature

# Switch back to main
git checkout main
```

---

## Part 5: .gitignore Already Configured

Your `.gitignore` should already exclude:
- WordPress core files (not needed in repo)
- Node modules
- Log files
- Sensitive config files

**What SHOULD be in your repo:**
- Theme files (`app/public/wp-content/themes/accurate surveyors 2025/`)
- Deployment scripts (`scripts/`)
- Documentation (`.md` files)
- `.gitignore`

**What should NOT be in your repo:**
- `wp-config.php` (contains database passwords)
- WordPress core files
- Uploaded media (can be large)
- `.env` files with secrets

---

## Troubleshooting

### Authentication Failed

**If you get "permission denied" when pushing:**
1. Check if using HTTPS or SSH
2. For HTTPS: Use Personal Access Token as password
3. For SSH: Set up SSH keys in GitHub Settings → SSH and GPG keys

### SSH Key Issues

**Generate new SSH key:**
```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
cat ~/.ssh/id_ed25519.pub
```

**Copy output and add to GitHub:**
- GitHub → Settings → SSH and GPG keys → New SSH key
- Paste your public key
- Test: `ssh -T git@github.com`

### Large File Issues

**If repository is too large:**
- Check `.gitignore` is working
- Don't commit `wp-content/uploads` folder
- Use Git LFS for large files if needed

---

## Quick Start Checklist

- [ ] Created GitHub repository
- [ ] Added remote to local repo (`git remote add origin`)
- [ ] Pushed code to GitHub (`git push -u origin main`)
- [ ] Tested deployment script (`./scripts/deploy-do-dev.sh`)
- [ ] Verified theme works on server
- [ ] Set up deployment workflow (manual or automated)

---

**Ready to start? Begin with Part 1, Step 1!**
