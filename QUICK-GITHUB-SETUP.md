# Quick GitHub Setup Guide

Follow these steps to connect your repository to GitHub and deploy.

---

## Step 1: Create GitHub Repository

1. Go to: https://github.com/new
2. **Repository name**: `accurate-surveying-mapping`
3. **Description**: "WordPress theme for Accurate Surveying & Mapping"
4. **Visibility**: Choose **Private** (recommended)
5. **DO NOT** check "Initialize with README" (we already have files)
6. Click **"Create repository"**

---

## Step 2: Connect and Push

**Copy the commands** from GitHub's "push an existing repository" section, or use these:

```bash
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"

# Add GitHub remote (replace YOUR_USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR_USERNAME/accurate-surveying-mapping.git

# Verify it was added
git remote -v

# Push to GitHub
git push -u origin main
```

**If prompted for credentials:**
- **Username**: Your GitHub username
- **Password**: Use a **Personal Access Token** (not your GitHub password)

**To create Personal Access Token:**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token (classic)
3. Select scope: `repo`
4. Generate and copy the token
5. Use it as your password when pushing

---

## Step 3: Verify on GitHub

1. Go to your repository on GitHub
2. You should see all your files:
   - Theme files
   - Documentation (`.md` files)
   - Deployment scripts
   - `.gitignore`

---

## Step 4: Deploy to Server

**Option A: Using deployment script (easiest)**

1. **Update the script** with your server IP:
   ```bash
   nano scripts/deploy-do-dev.sh
   # Change: DEV_IP="YOUR_DEV_DROPLET_IP" to your actual IP
   ```

2. **Run deployment:**
   ```bash
   ./scripts/deploy-do-dev.sh
   ```

**Option B: Manual deployment**

```bash
scp -r "app/public/wp-content/themes/accurate surveyors 2025" root@YOUR_DEV_IP:/var/www/html/wp-content/themes/
```

---

## Future Workflow

1. **Make changes** to your theme locally
2. **Commit changes:**
   ```bash
   git add .
   git commit -m "Description of what you changed"
   ```
3. **Push to GitHub:**
   ```bash
   git push origin main
   ```
4. **Deploy to server:**
   ```bash
   ./scripts/deploy-do-dev.sh
   ```

---

## That's It!

Your code is now:
- ✅ Version controlled with Git
- ✅ Backed up on GitHub
- ✅ Ready to deploy

See `GITHUB-DEPLOYMENT.md` for advanced deployment options (GitHub Actions, automated deployments, etc.)
