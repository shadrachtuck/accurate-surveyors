#!/bin/bash
# Deployment script for Production Server
# Usage: ./scripts/deploy-prod.sh

set -e

echo "🚀 Deploying to Production Server..."

# Configuration (update these with your prod server details)
PROD_SERVER="yourdomain.com"
PROD_USER="your_ftp_username"
PROD_PATH="/public_html/wp-content/themes/"
THEME_NAME="accurate surveyors 2025"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${RED}⚠️  PRODUCTION DEPLOYMENT${NC}"
echo -e "${YELLOW}Configuration:${NC}"
echo "  Server: $PROD_SERVER"
echo "  Path: $PROD_PATH"
echo "  Theme: $THEME_NAME"
echo ""

# Check if theme directory exists locally
if [ ! -d "app/public/wp-content/themes/$THEME_NAME" ]; then
    echo "❌ Error: Theme directory not found!"
    exit 1
fi

# Double confirmation for production
read -p "Are you sure you want to deploy to PRODUCTION? (yes/no) " -r
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Deployment cancelled."
    exit 1
fi

# Create backup reminder
echo -e "${YELLOW}📋 Pre-deployment checklist:${NC}"
echo "  [ ] Backup current production site"
echo "  [ ] Test on development server first"
echo "  [ ] Review git changes: git log --oneline -10"
echo "  [ ] Verify theme is ready"
echo ""
read -p "All checks complete? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 1
fi

# Create temporary archive
TEMP_ZIP=$(mktemp).zip
cd "app/public/wp-content/themes/"
zip -r "$TEMP_ZIP" "$THEME_NAME" -x "*.DS_Store" "*.git*"
cd - > /dev/null

echo "📦 Created archive: $TEMP_ZIP"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Connect to your production server via FTP/SFTP"
echo "2. Navigate to: $PROD_PATH"
echo "3. BACKUP existing theme folder first!"
echo "4. Upload the archive: $TEMP_ZIP"
echo "5. Extract on server and remove archive"
echo ""
echo "Or use File Manager in cPanel to upload and extract."

# Cleanup
rm "$TEMP_ZIP"

echo -e "${GREEN}✅ Deployment package ready!${NC}"
echo -e "${RED}⚠️  Remember to test after deployment!${NC}"
