#!/bin/bash
# Deployment script for Development Server
# Usage: ./scripts/deploy-dev.sh

set -e

echo "🚀 Deploying to Development Server..."

# Configuration (update these with your dev server details)
DEV_SERVER="dev.yourdomain.com"
DEV_USER="your_ftp_username"
DEV_PATH="/public_html/wp-content/themes/"
THEME_NAME="accurate surveyors 2025"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Configuration:${NC}"
echo "  Server: $DEV_SERVER"
echo "  Path: $DEV_PATH"
echo "  Theme: $THEME_NAME"
echo ""

# Check if theme directory exists locally
if [ ! -d "app/public/wp-content/themes/$THEME_NAME" ]; then
    echo "❌ Error: Theme directory not found!"
    exit 1
fi

# Ask for confirmation
read -p "Continue with deployment? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 1
fi

# Method 1: FTP Upload (requires lftp or similar)
echo -e "${YELLOW}Uploading via FTP...${NC}"

# Create temporary archive
TEMP_ZIP=$(mktemp).zip
cd "app/public/wp-content/themes/"
zip -r "$TEMP_ZIP" "$THEME_NAME" -x "*.DS_Store" "*.git*"
cd - > /dev/null

echo "📦 Created archive: $TEMP_ZIP"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Connect to your dev server via FTP/SFTP"
echo "2. Navigate to: $DEV_PATH"
echo "3. Upload the archive: $TEMP_ZIP"
echo "4. Extract on server and remove archive"
echo ""
echo "Or use File Manager in cPanel to upload and extract."

# Cleanup
rm "$TEMP_ZIP"

echo -e "${GREEN}✅ Deployment package ready!${NC}"
