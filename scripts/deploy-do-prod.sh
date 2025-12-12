#!/bin/bash
# Deploy theme to DigitalOcean Production Server
# Usage: ./scripts/deploy-do-prod.sh

set -e

echo "🚀 Deploying to DigitalOcean Production Server..."

# Configuration - UPDATE THESE VALUES
PROD_IP="YOUR_PROD_DROPLET_IP"
PROD_USER="root"
PROD_PATH="/var/www/html/wp-content/themes/"
THEME_NAME="accurate surveyors 2025"
LOCAL_THEME_PATH="app/public/wp-content/themes/$THEME_NAME"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${RED}⚠️  PRODUCTION DEPLOYMENT${NC}"
echo -e "${YELLOW}Configuration:${NC}"
echo "  Server IP: $PROD_IP"
echo "  User: $PROD_USER"
echo "  Theme Path: $PROD_PATH"
echo "  Theme Name: $THEME_NAME"
echo ""

# Validate configuration
if [ "$PROD_IP" = "YOUR_PROD_DROPLET_IP" ]; then
    echo -e "${RED}❌ Error: Please update PROD_IP in this script!${NC}"
    exit 1
fi

# Check if theme directory exists
if [ ! -d "$LOCAL_THEME_PATH" ]; then
    echo -e "${RED}❌ Error: Theme directory not found at $LOCAL_THEME_PATH${NC}"
    exit 1
fi

# Double confirmation
read -p "Are you sure you want to deploy to PRODUCTION? (yes/no) " -r
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Deployment cancelled."
    exit 1
fi

echo -e "${YELLOW}📋 Pre-deployment checklist:${NC}"
echo "  [ ] Backup current production site"
echo "  [ ] Test on development server first"
echo "  [ ] Review git changes"
echo "  [ ] Verify theme is ready"
read -p "All checks complete? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 1
fi

echo -e "${YELLOW}Step 1: Creating temporary archive...${NC}"
TEMP_ZIP=$(mktemp).zip
cd "$(dirname "$LOCAL_THEME_PATH")"
zip -r "$TEMP_ZIP" "$THEME_NAME" -x "*.DS_Store" "*.git*" "*node_modules*" > /dev/null
cd - > /dev/null

echo -e "${YELLOW}Step 2: Uploading to server...${NC}"
scp "$TEMP_ZIP" ${PROD_USER}@${PROD_IP}:/tmp/theme.zip

echo -e "${YELLOW}Step 3: Extracting on server...${NC}"
ssh ${PROD_USER}@${PROD_IP} << 'ENDSSH'
cd /var/www/html/wp-content/themes/
# Backup existing theme
if [ -d "accurate surveyors 2025" ]; then
    BACKUP_NAME="accurate surveyors 2025.backup.$(date +%Y%m%d_%H%M%S)"
    mv "accurate surveyors 2025" "$BACKUP_NAME"
    echo "Backup created: $BACKUP_NAME"
fi
# Extract new theme
unzip -q /tmp/theme.zip -d .
rm /tmp/theme.zip
# Set permissions
chown -R www-data:www-data "accurate surveyors 2025"
chmod -R 755 "accurate surveyors 2025"
echo "Theme deployed successfully"
ENDSSH

# Cleanup
rm "$TEMP_ZIP"

echo -e "${GREEN}✅ Deployment complete!${NC}"
echo -e "${RED}⚠️  Remember to test after deployment!${NC}"
echo ""
echo "Next steps:"
echo "1. Visit your production site"
echo "2. Verify theme is active"
echo "3. Test all pages and functionality"
echo "4. Check for any errors in logs:"
echo "   ssh $PROD_USER@$PROD_IP 'tail -f /var/log/apache2/error.log'"
echo ""
