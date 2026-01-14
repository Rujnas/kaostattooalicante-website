#!/bin/bash

# KAOS Tattoo Gallery Sync Script
# This script runs the complete gallery sync and update process

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="$SCRIPT_DIR/gallery_sync.log"
PYTHON_CMD="python3"

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Check if required files exist
check_requirements() {
    log "Checking requirements..."
    
    if [ ! -f "$SCRIPT_DIR/gallery_automation.py" ]; then
        log "ERROR: gallery_automation.py not found"
        exit 1
    fi
    
    if [ ! -f "$SCRIPT_DIR/gallery_updater.py" ]; then
        log "ERROR: gallery_updater.py not found"
        exit 1
    fi
    
    if [ ! -f "$SCRIPT_DIR/credentials/service_account.json" ]; then
        log "ERROR: Service account credentials not found"
        exit 1
    fi
    
    if [ -z "$GOOGLE_DRIVE_FOLDER_ID" ]; then
        log "ERROR: GOOGLE_DRIVE_FOLDER_ID environment variable not set"
        exit 1
    fi
    
    log "Requirements check passed"
}

# Run gallery sync
run_sync() {
    log "Starting gallery sync..."
    cd "$SCRIPT_DIR"
    
    if $PYTHON_CMD gallery_automation.py; then
        log "Gallery sync completed successfully"
        return 0
    else
        log "ERROR: Gallery sync failed"
        return 1
    fi
}

# Update HTML galleries
update_html() {
    log "Updating HTML galleries..."
    cd "$SCRIPT_DIR"
    
    if $PYTHON_CMD gallery_updater.py; then
        log "HTML update completed successfully"
        return 0
    else
        log "ERROR: HTML update failed"
        return 1
    fi
}

# Cleanup old backups
cleanup_backups() {
    log "Cleaning up old backups..."
    cd "$SCRIPT_DIR"
    
    # Keep only last 10 backups
    find . -name "index_backup_*.html" -type f | sort -r | tail -n +11 | xargs -r rm
    
    log "Backup cleanup completed"
}

# Main execution
main() {
    log "=== Starting KAOS Gallery Sync Process ==="
    
    check_requirements
    
    if run_sync; then
        if update_html; then
            cleanup_backups
            log "=== Gallery sync process completed successfully ==="
            exit 0
        else
            log "=== HTML update failed ==="
            exit 1
        fi
    else
        log "=== Gallery sync failed ==="
        exit 1
    fi
}

# Run main function
main "$@"
