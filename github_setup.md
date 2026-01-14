# GitHub Integration for KAOS Gallery Automation

## 🎯 Why GitHub is Perfect for This

GitHub provides excellent collaboration features for your tattoo studio team:
- **Multiple collaborators** can work on the website
- **Automated workflows** handle gallery syncing
- **Version control** tracks all changes
- **Free hosting** with GitHub Pages
- **Secure secrets management** for API credentials

## 🚀 GitHub Workflow Setup

### 1. Initialize Repository
```bash
git init
git add .
git commit -m "Initial commit - KAOS Gallery Automation"
git branch -M main
git remote add origin https://github.com/yourusername/kaostattooalicante-website.git
git push -u origin main
```

### 2. Add Team Members
1. Go to your repository on GitHub
2. Settings → Collaborators
3. Add team members with write access
4. They can now push changes and trigger workflows

### 3. Setup GitHub Secrets
Go to repository Settings → Secrets and variables → Actions:

**Required Secrets:**
- `GOOGLE_SERVICE_ACCOUNT_JSON` - Your service account JSON content
- `GOOGLE_DRIVE_FOLDER_ID` - Your main Drive folder ID

**How to add:**
1. Click "New repository secret"
2. Name: `GOOGLE_SERVICE_ACCOUNT_JSON`
3. Value: Paste entire JSON content from your service account file
4. Repeat for folder ID

### 4. Enable GitHub Actions
1. Go to Actions tab in your repository
2. Click "I understand my workflows, go ahead and enable them"

## 🔄 How GitHub Automation Works

### Automatic Triggers:
- **Every 30 minutes** - Syncs new images from Drive
- **On code push** - Updates when team makes changes
- **Manual trigger** - Run sync on demand

### Workflow Process:
1. **Checks out** latest code
2. **Sets up Python** environment
3. **Loads credentials** from GitHub secrets
4. **Runs gallery sync** - Downloads new images
5. **Updates HTML** - Inserts new gallery content
6. **Commits changes** - Pushes updated website

## 👥 Team Collaboration Benefits

### For Tattoo Artists:
- **Upload images** to Google Drive folders
- **No technical knowledge** required
- **Images appear automatically** on website

### For Web Developers:
- **Edit code** in GitHub or locally
- **Pull requests** for review
- **Automatic deployment** on merge

### For Studio Management:
- **Complete audit trail** of all changes
- **Rollback capability** if needed
- **No server maintenance** required

## 🌐 Deployment Options

### Option 1: GitHub Pages (Free)
```bash
# Enable in repository Settings → Pages
# Source: Deploy from a branch
# Branch: main
# Folder: /root
```

### Option 2: Custom Domain
```bash
# Point your domain to GitHub Pages
# or use GitHub Actions to deploy to your hosting
```

### Option 3: Web Hosting with CI/CD
```yaml
# Add to .github/workflows/gallery-sync.yml
- name: Deploy to hosting
  run: |
    # Your deployment commands here
    rsync -avz --delete ./ user@yourhosting.com:/path/to/site/
```

## 🔒 Security with GitHub

### Benefits:
- **Secrets never exposed** in repository
- **Access control** through GitHub permissions
- **Audit log** of all actions
- **Branch protection** for main branch
- **Signed commits** verification

### Best Practices:
1. **Enable branch protection** for main branch
2. **Require pull requests** for code changes
3. **Use GitHub's 2FA** for all team members
4. **Regular secret rotation** for API keys
5. **Monitor workflow logs** for security

## 📋 Daily Workflow

### For Team Members:
1. **Upload new tattoo photos** to Google Drive folders
2. **GitHub syncs automatically** every 30 minutes
3. **Website updates** with new gallery content
4. **No manual intervention** needed

### For Developers:
1. **Make code changes** in feature branches
2. **Create pull requests** for review
3. **Merge to main** triggers deployment
4. **GitHub Actions handle** the rest

## 🛠️ Advanced Features

### Branch Strategy:
```bash
main          # Production website
develop        # Staging/testing
feature/*      # New features
hotfix/*       # Emergency fixes
```

### Pull Request Template:
Create `.github/pull_request_template.md`:
```markdown
## Gallery Update
- [ ] New images added to Drive
- [ ] HTML updated correctly
- [ ] Tested on local environment
- [ ] Ready for production

## Changes Made
- 

## Testing
- [ ] Gallery loads correctly
- [ ] Images display properly
- [ ] Lightbox functionality works
- [ ] Mobile responsive
```

### Issue Templates:
Create `.github/ISSUE_TEMPLATE/gallery_update.md`:
```markdown
---
name: Gallery Update Request
about: Request to add/update gallery content
title: '[GALLERY] '
labels: gallery
---

## Style Category
- [ ] Fine Line
- [ ] Realismo
- [ ] Tradicional
- [ ] Anime
- [ ] Blackwork
- [ ] Other: 

## Description
Describe what needs to be updated in the gallery:

## Images
Link to Google Drive folder or specific images:

## Additional Notes
Any special requirements or considerations:
```

## 📊 Monitoring

### GitHub Actions Dashboard:
- View workflow runs
- Check success/failure rates
- Monitor execution times
- Debug failed runs

### Repository Analytics:
- Track contributor activity
- Monitor code changes
- View traffic to repository

## 🚀 Getting Started

1. **Create GitHub repository** for your website
2. **Add team members** as collaborators
3. **Setup secrets** for Google Drive access
4. **Push code** to enable workflows
5. **Upload images** to Google Drive
6. **Monitor automatic syncs** in Actions tab

Your team can now collaborate efficiently while maintaining complete security and automation!
