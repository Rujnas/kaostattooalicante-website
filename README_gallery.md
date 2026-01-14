# KAOS Tattoo Gallery Automation System

## 🎯 Overview
This automated system keeps your tattoo gallery website synchronized with Google Drive folders. Team members can upload images to designated Drive folders, and they'll automatically appear on your website without any manual intervention.

## 🔒 Security Features
- **OAuth 2.0 Service Account** - No passwords stored
- **Read-Only Access** - Minimal permissions to specific folders only
- **Server-Side Processing** - Drive connection never exposed to users
- **Image Validation** - File type, size, and dimension checks
- **Audit Logging** - All actions logged for security review
- **Rate Limiting** - Prevents API abuse
- **Environment Variables** - Sensitive data never in code

## 📁 Required Google Drive Structure
```
KAOS Gallery/                    ← Main folder (share with service account)
├── Fine Line/
├── Realismo/
├── Tradicional: Old School/
├── Anime/
├── Blackwork/
├── Cartoon/
├── Geometrico/
├── Japones/
├── Lettering/
├── Microrealismo/
└── Dibujos y Cuadros/
```

## 🚀 Quick Start

### 1. Install Dependencies
```bash
pip install -r requirements.txt
```

### 2. Setup Google Credentials
```bash
mkdir -p credentials
# Place your service account JSON file here
mv your-service-key.json credentials/service_account.json
```

### 3. Configure Environment
```bash
cp .env.example .env
# Edit .env with your Google Drive folder ID
```

### 4. Run Sync
```bash
./sync_gallery.sh
```

## 📋 Files Created

| File | Purpose |
|------|---------|
| `gallery_automation.py` | Main sync script - downloads and processes images |
| `gallery_updater.py` | Updates HTML with new gallery content |
| `sync_gallery.sh` | Complete sync process with logging |
| `requirements.txt` | Python dependencies |
| `gallery_data.json` | Image metadata database (auto-generated) |
| `.env.example` | Environment variables template |

## ⚙️ Automation Options

### Cron Job (Recommended)
```bash
# Edit crontab
crontab -e

# Sync every 30 minutes
*/30 * * * * /path/to/kaostattooalicante-website/sync_gallery.sh
```

### Manual Sync
```bash
# Run complete sync process
./sync_gallery.sh

# Or run individual components
python3 gallery_automation.py    # Download images
python3 gallery_updater.py       # Update HTML
```

## 📊 Monitoring

### Check Logs
```bash
tail -f gallery_sync.log
```

### View Sync Status
```bash
cat gallery_data.json | jq '.last_sync'
```

### Monitor Disk Space
```bash
du -sh images/STYLES/*
```

## 🔧 Configuration

### Image Processing Settings
- **Max File Size**: 10MB (configurable)
- **Allowed Formats**: JPG, PNG, GIF, WebP
- **Min Dimensions**: 300x300px
- **Max Dimensions**: 4000x4000px
- **Output Quality**: 85% JPEG (optimized for web)

### Rate Limiting
- **API Delay**: 1 second between calls (prevents quota issues)
- **Retry Logic**: Automatic retry on transient errors

## 🛠️ Troubleshooting

### Common Issues

**Authentication Failed**
- Verify service account JSON file exists
- Check folder sharing permissions
- Ensure GOOGLE_DRIVE_FOLDER_ID is correct

**No New Images Found**
- Check if images are in correct Drive folders
- Verify file formats are supported
- Check log for validation errors

**HTML Not Updating**
- Ensure gallery_data.json has new entries
- Check index.html file permissions
- Verify backup creation succeeded

**Rate Limit Errors**
- Increase RATE_LIMIT_DELAY in script
- Check Google Drive API quota

### Debug Mode
```bash
# Enable debug logging
export DEBUG=1
python3 gallery_automation.py
```

## 🔐 Security Checklist

- [ ] Service account has Viewer role only
- [ ] Folder sharing limited to service account
- [ ] Credentials file permissions set to 600
- [ ] Environment variables used for sensitive data
- [ ] Log files monitored regularly
- [ ] Backup system tested
- [ ] Rate limiting configured
- [ ] File validation enabled

## 📞 Support

### Log Analysis
Check `gallery_sync.log` for detailed error messages and sync status.

### Backup Recovery
If HTML update fails, restore from backup:
```bash
cp index_backup_YYYYMMDD_HHMMSS.html index.html
```

### Performance Tuning
- Adjust `RATE_LIMIT_DELAY` for API quota
- Modify `JPEG_QUALITY` for file size vs quality
- Change sync frequency in cron job

## 🔄 Workflow

1. **Team uploads** images to Google Drive folders
2. **Automation runs** (every 30 minutes or manual)
3. **Images downloaded** and validated
4. **HTML updated** with new gallery content
5. **Website refreshed** automatically shows new images

This system provides a secure, automated workflow that requires no manual intervention while maintaining complete control over your gallery content.
