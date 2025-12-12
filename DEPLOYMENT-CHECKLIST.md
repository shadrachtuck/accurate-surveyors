# Deployment Checklist - Namecheap Dev & Prod

Print this checklist and check off items as you complete them.

---

## 📦 DEVELOPMENT SERVER SETUP

### Preparation
- [ ] Namecheap account created
- [ ] Stellar hosting plan purchased ($1.58/month)
- [ ] Decided on dev subdomain: `dev.yourdomain.com` OR separate domain

### WordPress Installation
- [ ] Logged into Namecheap cPanel
- [ ] Created subdomain (if using subdomain approach)
- [ ] Installed WordPress via Softaculous
- [ ] Set secure admin username and password
- [ ] Noted database credentials
- [ ] WordPress accessible at dev URL

### Theme Deployment
- [ ] Theme folder uploaded to `/wp-content/themes/`
- [ ] Theme activated in WordPress admin
- [ ] Theme displays correctly on dev site
- [ ] All pages tested on dev server
- [ ] Styling verified

### Dev Server Complete
- [ ] SSL certificate installed (optional for dev)
- [ ] Dev site fully functional
- [ ] Ready for testing

---

## 🚀 PRODUCTION SERVER SETUP

### Preparation
- [ ] Production hosting plan purchased
- [ ] WordPress installed on production server
- [ ] Production database created
- [ ] **⚠️ DNS NOT switched yet!**

### Theme Deployment
- [ ] Theme uploaded to production server
- [ ] Theme activated
- [ ] Content migrated (if applicable)
- [ ] Plugins installed and configured

### Pre-DNS Testing
- [ ] Accessed production site via temp URL
- [ ] All pages tested
- [ ] Forms tested
- [ ] All functionality verified
- [ ] Mobile responsive checked

### Backup
- [ ] Old site database exported
- [ ] Old site files downloaded
- [ ] Backup stored safely
- [ ] Old site configuration documented

---

## 🔄 DNS MIGRATION (Do This Last!)

### DNS Configuration
- [ ] Namecheap nameservers noted:
  - Primary: `________________________`
  - Secondary: `________________________`
- [ ] Old site backup completed
- [ ] Production site fully tested and ready

### DNS Update
- [ ] Logged into domain registrar
- [ ] Updated nameservers to Namecheap
- [ ] Nameservers saved/confirmed
- [ ] DNS change request submitted

### Post-DNS Setup
- [ ] Waited for DNS propagation (1-24 hours)
- [ ] Checked DNS propagation: https://www.whatsmydns.net
- [ ] SSL certificate installed on production
- [ ] Site accessible at `https://yourdomain.com`
- [ ] HTTPS working correctly

---

## ✅ POST-DEPLOYMENT VERIFICATION

### Production Site Testing
- [ ] Homepage loads correctly
- [ ] All pages accessible
- [ ] Navigation works
- [ ] Contact forms working
- [ ] Images loading
- [ ] Styling correct
- [ ] Mobile responsive
- [ ] Search functionality (if applicable)
- [ ] Blog/posts working (if applicable)

### Security & Performance
- [ ] SSL certificate active
- [ ] HTTPS redirect working
- [ ] Admin login secure
- [ ] File permissions correct
- [ ] Debug mode OFF in production
- [ ] Caching configured (if needed)

### Email & Services
- [ ] Email configured (if applicable)
- [ ] Contact forms sending emails
- [ ] Newsletter signup working (if applicable)
- [ ] Third-party integrations working

### Monitoring
- [ ] Site monitored for 24 hours
- [ ] No errors or issues
- [ ] Analytics tracking (if applicable)
- [ ] Search console configured (if applicable)

---

## 🧹 CLEANUP

### Old Hosting
- [ ] Old site verified backed up
- [ ] New site confirmed working
- [ ] Old hosting canceled (optional, after 30 days)
- [ ] Old hosting data downloaded (if canceling)

### Documentation
- [ ] Server credentials saved securely
- [ ] Database credentials saved securely
- [ ] Deployment process documented
- [ ] Future update process documented

---

## 📝 NOTES & CONTACTS

### Important Information:

**Dev Server:**
- URL: `________________________`
- cPanel: `________________________`
- Database: `________________________`
- FTP Host: `________________________`

**Prod Server:**
- URL: `________________________`
- cPanel: `________________________`
- Database: `________________________`
- FTP Host: `________________________`

**Namecheap Support:**
- Live Chat: Available 24/7 in account
- Ticket System: Submit in account dashboard

**Emergency Contacts:**
- Namecheap Support: _______________
- Client Contact: _______________
- Developer: _______________

---

## 🆘 TROUBLESHOOTING LOG

**Date:** _________ **Issue:** _________________________________

**Resolution:** _______________________________________________


**Date:** _________ **Issue:** _________________________________

**Resolution:** _______________________________________________


---

**Completion Date:** _________  
**Signed off by:** _______________
