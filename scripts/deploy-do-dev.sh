#!/bin/bash
# Deploy theme to DigitalOcean Development Server
# Usage: ./scripts/deploy-do-dev.sh

set -e

echo "🚀 Deploying to DigitalOcean Development Server..."

# Configuration - UPDATE THESE VALUES
DEV_IP="YOUR_DEV_DROPLET_IP"
DEV_USER="root"
DEV_PATH="/var/www/html/wp-content/themes/"
THEME_NAME="accurate surveyors 2025"
LOCAL_THEME_PATH="app/public/wp-content/themes/$THEME_NAME"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}Configuration:${NC}"
echo "  Server IP: $DEV_IP"
echo "  User: $DEV_USER"
echo "  Theme Path: $DEV_PATH"
echo "  Theme Name: $THEME_NAME"
echo ""

# Validate configuration
if [ "$DEV_IP" = "YOUR_DEV_DROPLET_IP" ]; then
    echo -e "${RED}❌ Error: Please update DEV_IP in this script!${NC}"
    exit 1
fi

# Check if theme directory exists
if [ ! -d "$LOCAL_THEME_PATH" ]; then
    echo -e "${RED}❌ Error: Theme directory not found at $LOCAL_THEME_PATH${NC}"
    exit 1
fi

# Confirmation
read -p "Continue with deployment? (y/n) " -n 1 -r
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
scp "$TEMP_ZIP" ${DEV_USER}@${DEV_IP}:/tmp/theme.zip

echo -e "${YELLOW}Step 3: Extracting on server...${NC}"
ssh ${DEV_USER}@${DEV_IP} << 'ENDSSH'
cd /var/www/html/wp-content/themes/
# Backup existing theme if it exists
if [ -d "accurate surveyors 2025" ]; then
    mv "accurate surveyors 2025" "accurate surveyors 2025.backup.$(date +%Y%m%d_%H%M%S)"
fi
# Extract new theme
unzip -q /tmp/theme.zip -d .
rm /tmp/theme.zip
# Set permissions
chown -R www-data:www-data "accurate surveyors 2025"
chmod -R 755 "accurate surveyors 2025"
echo "Theme extracted and permissions set"
ENDSSH

# Cleanup
rm "$TEMP_ZIP"

echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo "Next steps:"
echo "1. Visit: http://$DEV_IP/wp-admin"
echo "2. Go to Appearance → Themes"
echo "3. Activate 'Accurate Surveyors 2025'"
echo ""

