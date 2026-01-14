# KAOS Tattoo Gallery Automation Setup

## Overview
This system automatically syncs images from Google Drive folders to your website gallery, maintaining security and requiring no manual intervention.

## Prerequisites
- Python 3.7+
- Google Cloud Project with Drive API enabled
- Google Drive organized by tattoo style folders

## Setup Instructions

### 1. Google Cloud Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google Drive API
4. Create Service Account:
   - Go to IAM & Admin → Service Accounts
   - Click "Create Service Account"
   - Name: `gallery-sync@your-project.iam.gserviceaccount.com`
   - Role: Viewer (minimal permissions)
5. Create and download JSON key (save as `credentials/service_account.json`)

### 2. Google Drive Setup
1. Create main folder: `KAOS Gallery`
2. Create subfolders for each style:
   - `Fine Line`
   - `Realismo`
   - `Tradicional: Old School`
   - `Anime`
   - `Blackwork`
   - `Cartoon`
   - `Geometrico`
   - `Japones`
   - `Lettering`
   - `Microrealismo`
3. Share the main folder with your service account email:
   - Right-click folder → Share
   - Add service account email
   - Permission: Viewer

### 3. Server Setup
```bash
# Install Python dependencies
pip install -r requirements.txt

# Create credentials directory
mkdir -p credentials

# Place your service account JSON file
mv your-service-account-key.json credentials/service_account.json

# Set environment variable
export GOOGLE_DRIVE_FOLDER_ID="your_main_folder_id"
```

### 4. Get Folder ID
1. Open Google Drive
2. Navigate to your main `KAOS Gallery` folder
3. Copy folder ID from URL: `https://drive.google.com/drive/folders/FOLDER_ID_HERE`

### 5. Test the System
```bash
python gallery_automation.py
```

## Security Features
✅ Service account authentication (no passwords stored)
✅ Read-only access to specific folders only
✅ Server-side processing only
✅ Image validation and sanitization
✅ Rate limiting to prevent API abuse
✅ Comprehensive logging for audit trails
✅ Environment variables for sensitive data

## Automation Setup

### Option 1: Cron Job (Recommended)
```bash
# Edit crontab
crontab -e

# Add sync every 30 minutes
*/30 * * * * cd /path/to/kaostattooalicante-website && python gallery_automation.py >> /var/log/gallery_sync.log 2>&1
```

### Option 2: Systemd Service
Create `/etc/systemd/system/gallery-sync.service`:
```ini
[Unit]
Description=KAOS Gallery Sync
After=network.target

[Service]
Type=oneshot
User=www-data
WorkingDirectory=/path/to/kaostattooalicante-website
ExecStart=/usr/bin/python3 gallery_automation.py
Environment=GOOGLE_DRIVE_FOLDER_ID=your_folder_id

[Install]
WantedBy=multi-user.target
```

Create timer `/etc/systemd/system/gallery-sync.timer`:
```ini
[Unit]
Description=Run gallery sync every 30 minutes
Requires=gallery-sync.service

[Timer]
OnCalendar=*:0/30
Persistent=true

[Install]
WantedBy=timers.target
```

Enable and start:
```bash
sudo systemctl enable gallery-sync.timer
sudo systemctl start gallery-sync.timer
```

## File Structure
```
kaostattooalicante-website/
├── gallery_automation.py          # Main sync script
├── requirements.txt              # Python dependencies
├── credentials/
│   └── service_account.json     # Google Drive credentials
├── gallery_data.json            # Image metadata database
├── gallery_sync.log            # Sync logs
└── images/STYLES/             # Local image storage
    ├── fineline/
    ├── realismo/
    ├── tradicional/
    └── ...
```

## Monitoring
- Check logs: `tail -f gallery_sync.log`
- View sync status: View `gallery_data.json`
- Monitor disk space in `images/STYLES/`

## Troubleshooting
1. **Authentication Error**: Verify service account permissions and folder sharing
2. **Folder Not Found**: Check GOOGLE_DRIVE_FOLDER_ID environment variable
3. **Rate Limit**: Increase RATE_LIMIT_DELAY in script
4. **Permission Denied**: Ensure service account has Viewer access to folders

## Security Notes
- Service account has read-only access
- No credentials exposed to client-side
- Images validated before processing
- All actions logged for audit
- API keys stored in environment variables
