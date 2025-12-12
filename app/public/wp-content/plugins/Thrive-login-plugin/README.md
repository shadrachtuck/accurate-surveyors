# Thrive Login Plugin

A secure and easy-to-integrate WordPress login plugin powered by GitHub releases for automatic updates.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![WordPress](https://img.shields.io/badge/compatible-WordPress-blue)
![Version](https://img.shields.io/github/v/release/your-username/thrive-login-plugin)

---

## 🚀 Features

- ✅ GitHub-based auto-update support
- 🔐 Secure and customizable login system
- 🔄 Version-controlled plugin lifecycle
- 📦 Easy installation and distribution
- 🧠 Developer-friendly structure

---

## 🔁 Plugin Update Instructions

If you want to update this plugin and publish a new release, follow these steps:

### ✅ Step 1: Clone the Repository

```bash
git clone https://github.com/glossyit/LoginWithThrives_plugin.git
cd thrive-login-plugin
```
### ✅ Step 2: Make Your Changes
Update the plugin code as needed to fix bugs, add features, or make improvements.
### ✅ Step 3:Update Plugin Version
Open the main plugin file thrive-login.php, and update the version constant:

```bash
define('THRIVE_LOGIN_VERSION', '1.0.X'); // Replace with the new version
```
Ensure the version matches the one you'll use in the release tag (e.g., v1.0.7).
### ✅ Step 4: Commit and Push Changes
```bash
git add .
git commit -m "Update: Describe your changes"
git push origin main
```
### 🏷️ Step 5: Create a GitHub Release

1. Go to your repo’s [Releases](https://github.com/glossyit/LoginWithThrives_plugin/releases) tab.
2. Click **"Draft a new release"**.
3. Fill out the release form:
    - **Tag version**: `v1.0.X`  
      👉 Must match the version in `define('THRIVE_LOGIN_VERSION', '1.0.X');`
    - **Release title**: `v1.0.X` (e.g., `v1.0.7`)
    - **Description / Release notes**: (Optional) Add a summary of changes or a changelog.
    - ✅ **Check** the box for **"Set as the latest release"**
4. Click **"Publish release"**
