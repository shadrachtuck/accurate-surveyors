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

For production deployment, consider:
- **Shared Hosting**: Namecheap, SiteGround, Bluehost ($3-10/month)
- **VPS**: DigitalOcean, Linode ($5-6/month)
- **Managed WordPress**: WP Engine, Kinsta (premium, $20+/month)

See `README-VERCEL.md` for Vercel deployment notes (not recommended for full WordPress).

## Database

Local development uses:
- Database: `local`
- User: `root`
- Password: `root`
- Host: `localhost`

Production requires separate database configuration.
