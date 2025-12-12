# Vercel Deployment Guide

## Prerequisites

1. Install Vercel CLI: `npm i -g vercel`
2. Have a Vercel account (sign up at https://vercel.com)

## Deployment Steps

### Option 1: Deploy via CLI

```bash
# Navigate to project root
cd "/Users/shadrachtuck/Local Sites/accurate-surveying-mapping"

# Login to Vercel (first time only)
vercel login

# Deploy to preview/staging
vercel

# Deploy to production
vercel --prod
```

### Option 2: Deploy via GitHub

1. Push your code to a GitHub repository
2. Connect your repository to Vercel at https://vercel.com/new
3. Vercel will auto-deploy on every push

## Important Notes

⚠️ **Database Configuration**: 
- You'll need to set up a remote database (MySQL/MariaDB)
- Update `wp-config.php` with production database credentials
- Never commit `wp-config.php` with sensitive credentials - use environment variables instead

⚠️ **File Uploads**:
- WordPress uploads folder is excluded from deployment (in .vercelignore)
- Consider using a CDN or cloud storage (S3, Cloudinary) for media files

⚠️ **Vercel PHP Limitations**:
- Vercel's PHP runtime has limitations with WordPress
- Consider using a headless WordPress setup or static site generation
- Alternative: Deploy WordPress backend separately and use Vercel for frontend

## Environment Variables

Set these in your Vercel project settings:

```
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password
DB_HOST=your_database_host
WP_ENV=production
```

## Recommended Alternative Approaches

1. **Static Export**: Use a plugin like WP2Static to generate static HTML and deploy to Vercel
2. **Headless WordPress**: Keep WordPress as CMS backend, build frontend with Next.js/React
3. **Traditional Hosting**: For full WordPress features, consider WP Engine, Kinsta, or similar

## Theme-Only Deployment

If you only want to deploy the theme files (WordPress hosted elsewhere):

1. Update `vercel.json` to only include theme directory
2. Point `dest` to `wp-content/themes/accurate surveyors 2025`
3. Deploy theme files separately
