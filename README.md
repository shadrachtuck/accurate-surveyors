# Accurate Surveyors WordPress Site

WordPress child theme for Accurate Surveyors & Mapping website.

## Project Structure

```
├── app/public/                    # WordPress installation
│   └── wp-content/
│       └── themes/
│           ├── blockchain/        # Parent theme
│           └── accurate surveyors 2025/  # Child theme
├── conf/                          # Local by Flywheel config (not in git)
└── logs/                          # Local by Flywheel logs (not in git)
```

## Child Theme Features

- Custom styling matching brand guidelines
- Service card layouts with hover overlays
- Team member card layouts
- Shared card component styles
- Custom templates for team and service listings

## Development

This site is developed using Local by Flywheel for local development.

## Deployment

This project is configured for deployment to **DigitalOcean** VPS servers.

For setup instructions, see:
- **Quick Start**: `DO-QUICK-START.md` - Fast setup guide
- **Detailed Guide**: `DIGITALOCEAN-SETUP.md` - Complete documentation

**Cost**: ~$11-17/month (dev + production droplets)

Alternative hosting options:
- **Shared Hosting**: Namecheap, SiteGround, Bluehost ($3-10/month)
- **Managed WordPress**: WP Engine, Kinsta (premium, $20+/month)

## Database

Local development uses:
- Database: `local`
- User: `root`
- Password: `root`
- Host: `localhost`

Production requires separate database configuration.
