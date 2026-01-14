#!/usr/bin/env python3
"""
KAOS Tattoo Gallery Automation System
Automatically syncs Google Drive images with website gallery
"""

import os
import json
import logging
import time
import hashlib
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional, Any

# Load environment variables from .env file
def load_env():
    env_file = Path(__file__).parent / ".env"
    if env_file.exists():
        with open(env_file, 'r') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    key, value = line.split('=', 1)
                    os.environ[key.strip()] = value.strip()

load_env()

from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.errors import HttpError
from googleapiclient.http import MediaIoBaseDownload
import requests
from PIL import Image
import io

# Configuration
CONFIG = {
    "SERVICE_ACCOUNT_FILE": "credentials/service_account.json",
    "SCOPES": ["https://www.googleapis.com/auth/drive.readonly"],
    "BASE_FOLDER_ID": os.getenv("GOOGLE_DRIVE_FOLDER_ID"),  # Main gallery folder
    "WEBSITE_ROOT": Path(__file__).parent,
    "IMAGES_DIR": Path(__file__).parent / "images" / "STYLES",
    "GALLERY_DATA_FILE": Path(__file__).parent / "gallery_data.json",
    "LOG_FILE": Path(__file__).parent / "gallery_sync.log",
    "MAX_FILE_SIZE": 10 * 1024 * 1024,  # 10MB
    "ALLOWED_EXTENSIONS": {".jpg", ".jpeg", ".png", ".gif", ".webp"},
    "MIN_DIMENSIONS": (300, 300),  # Minimum image dimensions
    "MAX_DIMENSIONS": (4000, 4000),  # Maximum image dimensions
    "RATE_LIMIT_DELAY": 1,  # Seconds between API calls
}

# Style mapping - Google Drive folder names to website style names
STYLE_MAPPING = {
    "Fine Line": "fineline",
    "Realismo": "realismo", 
    "Tradicional: Old School": "tradicional",
    "Anime": "anime",
    "Blackwork": "blackwork",
    "Cartoon": "cartoon",
    "Geometrico": "geometrico",
    "Japones": "japones",
    "Lettering": "lettering",
    "Microrealismo": "microrealismo",
    "Dibujos y Cuadros": "dibujos-cuadros"
}

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s",
    handlers=[
        logging.FileHandler(CONFIG["LOG_FILE"]),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

class GallerySyncError(Exception):
    """Custom exception for gallery sync errors"""
    pass

class GoogleDriveGallerySync:
    def __init__(self):
        self.service = None
        self.gallery_data = self._load_gallery_data()
        
    def _load_gallery_data(self) -> Dict[str, Any]:
        """Load existing gallery data from JSON file"""
        try:
            if CONFIG["GALLERY_DATA_FILE"].exists():
                with open(CONFIG["GALLERY_DATA_FILE"], 'r', encoding='utf-8') as f:
                    return json.load(f)
        except Exception as e:
            logger.warning(f"Could not load gallery data: {e}")
        
        return {
            "last_sync": None,
            "images": {},
            "styles": {}
        }
    
    def _save_gallery_data(self):
        """Save gallery data to JSON file"""
        try:
            with open(CONFIG["GALLERY_DATA_FILE"], 'w', encoding='utf-8') as f:
                json.dump(self.gallery_data, f, indent=2, ensure_ascii=False)
            logger.info("Gallery data saved successfully")
        except Exception as e:
            logger.error(f"Failed to save gallery data: {e}")
    
    def _authenticate(self) -> bool:
        """Authenticate with Google Drive using service account"""
        try:
            if not os.path.exists(CONFIG["SERVICE_ACCOUNT_FILE"]):
                raise GallerySyncError(f"Service account file not found: {CONFIG['SERVICE_ACCOUNT_FILE']}")
            
            if not CONFIG["BASE_FOLDER_ID"]:
                raise GallerySyncError("GOOGLE_DRIVE_FOLDER_ID environment variable not set")
            
            credentials = service_account.Credentials.from_service_account_file(
                CONFIG["SERVICE_ACCOUNT_FILE"],
                scopes=CONFIG["SCOPES"]
            )
            
            self.service = build('drive', 'v3', credentials=credentials)
            logger.info("Successfully authenticated with Google Drive")
            return True
            
        except Exception as e:
            logger.error(f"Authentication failed: {e}")
            return False
    
    def _validate_image(self, file_info: Dict) -> bool:
        """Validate image file before processing"""
        try:
            # Check file extension
            file_name = file_info.get('name', '').lower()
            if not any(file_name.endswith(ext) for ext in CONFIG["ALLOWED_EXTENSIONS"]):
                logger.warning(f"Invalid file extension: {file_name}")
                return False
            
            # Check file size
            file_size = file_info.get('size', 0)
            if int(file_size) > CONFIG["MAX_FILE_SIZE"]:
                logger.warning(f"File too large: {file_name} ({file_size} bytes)")
                return False
            
            return True
            
        except Exception as e:
            logger.error(f"Error validating image {file_info.get('name', 'unknown')}: {e}")
            return False
    
    def _download_image(self, file_id: str, file_name: str) -> Optional[bytes]:
        """Download image from Google Drive"""
        try:
            request = self.service.files().get_media(fileId=file_id)
            file_io = io.BytesIO()
            downloader = MediaIoBaseDownload(file_io, request)
            
            done = False
            while done is False:
                status, done = downloader.next_chunk()
                if status:
                    logger.debug(f"Download {int(status.progress() * 100)}%.")
            
            return file_io.getvalue()
            
        except Exception as e:
            logger.error(f"Failed to download {file_name}: {e}")
            return None
    
    def _process_image(self, image_data: bytes, file_name: str) -> Optional[bytes]:
        """Process and validate image"""
        try:
            with Image.open(io.BytesIO(image_data)) as img:
                # Validate image dimensions
                if img.size[0] < CONFIG["MIN_DIMENSIONS"][0] or img.size[1] < CONFIG["MIN_DIMENSIONS"][1]:
                    logger.warning(f"Image too small: {file_name} {img.size}")
                    return None
                
                if img.size[0] > CONFIG["MAX_DIMENSIONS"][0] or img.size[1] > CONFIG["MAX_DIMENSIONS"][1]:
                    logger.warning(f"Image too large: {file_name} {img.size}")
                    # Optionally resize images here if needed
                    return None
                
                # Convert to RGB if necessary
                if img.mode in ('RGBA', 'LA', 'P'):
                    background = Image.new('RGB', img.size, (255, 255, 255))
                    if img.mode == 'P':
                        img = img.convert('RGBA')
                    background.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
                    img = background
                
                # Save as JPEG with quality optimization
                output = io.BytesIO()
                img.save(output, format='JPEG', quality=85, optimize=True)
                return output.getvalue()
                
        except Exception as e:
            logger.error(f"Error processing image {file_name}: {e}")
            return None
    
    def _get_file_hash(self, data: bytes) -> str:
        """Generate SHA-256 hash of file data"""
        return hashlib.sha256(data).hexdigest()
    
    def _get_style_folders(self) -> List[Dict]:
        """Get all style folders from Google Drive"""
        try:
            query = f"'{CONFIG['BASE_FOLDER_ID']}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false"
            results = self.service.files().list(
                q=query,
                fields="files(id, name, createdTime, modifiedTime)"
            ).execute()
            
            return results.get('files', [])
            
        except Exception as e:
            logger.error(f"Failed to get style folders: {e}")
            return []
    
    def _get_images_in_folder(self, folder_id: str) -> List[Dict]:
        """Get all images in a specific folder"""
        try:
            # Query for image files
            mime_types = [
                "image/jpeg",
                "image/png", 
                "image/gif",
                "image/webp"
            ]
            
            mime_query = " or ".join([f"mimeType = '{mt}'" for mt in mime_types])
            query = f"'{folder_id}' in parents and ({mime_query}) and trashed = false"
            
            results = self.service.files().list(
                q=query,
                fields="files(id, name, createdTime, modifiedTime, size, md5Checksum)",
                orderBy="modifiedTime desc"
            ).execute()
            
            time.sleep(CONFIG["RATE_LIMIT_DELAY"])  # Rate limiting
            return results.get('files', [])
            
        except Exception as e:
            logger.error(f"Failed to get images in folder {folder_id}: {e}")
            return []
    
    def _save_image_locally(self, image_data: bytes, style_name: str, file_name: str) -> Optional[str]:
        """Save image to local filesystem"""
        try:
            style_dir = CONFIG["IMAGES_DIR"] / style_name
            style_dir.mkdir(parents=True, exist_ok=True)
            
            # Clean filename
            clean_name = "".join(c for c in file_name if c.isalnum() or c in (' ', '-', '_', '.')).rstrip()
            local_path = style_dir / clean_name
            
            # Avoid filename conflicts
            counter = 1
            original_path = local_path
            while local_path.exists():
                stem = original_path.stem
                suffix = original_path.suffix
                local_path = style_dir / f"{stem}_{counter}{suffix}"
                counter += 1
            
            with open(local_path, 'wb') as f:
                f.write(image_data)
            
            # Return relative path for website
            return str(local_path.relative_to(CONFIG["WEBSITE_ROOT"]))
            
        except Exception as e:
            logger.error(f"Failed to save image {file_name}: {e}")
            return None
    
    def sync_gallery(self) -> bool:
        """Main sync function"""
        try:
            logger.info("Starting gallery sync...")
            
            if not self._authenticate():
                return False
            
            # Get all style folders
            style_folders = self._get_style_folders()
            logger.info(f"Found {len(style_folders)} style folders")
            
            total_new_images = 0
            
            for folder in style_folders:
                folder_name = folder.get('name', '')
                folder_id = folder.get('id', '')
                
                # Map folder name to style name
                style_name = STYLE_MAPPING.get(folder_name)
                if not style_name:
                    logger.warning(f"Unknown style folder: {folder_name}")
                    continue
                
                logger.info(f"Processing style: {folder_name} -> {style_name}")
                
                # Get images in this folder
                images = self._get_images_in_folder(folder_id)
                
                # Initialize style in gallery data if not exists
                if style_name not in self.gallery_data["images"]:
                    self.gallery_data["images"][style_name] = []
                
                existing_images = {img.get('id'): img for img in self.gallery_data["images"][style_name]}
                new_images = []
                
                for image in images:
                    image_id = image.get('id', '')
                    image_name = image.get('name', '')
                    
                    # Skip if already processed and unchanged
                    if image_id in existing_images:
                        existing_hash = existing_images[image_id].get('hash', '')
                        drive_hash = image.get('md5Checksum', '')
                        if existing_hash == drive_hash:
                            continue
                    
                    # Validate image
                    if not self._validate_image(image):
                        continue
                    
                    # Download and process
                    raw_data = self._download_image(image_id, image_name)
                    if not raw_data:
                        continue
                    
                    processed_data = self._process_image(raw_data, image_name)
                    if not processed_data:
                        continue
                    
                    # Save locally
                    local_path = self._save_image_locally(processed_data, style_name, image_name)
                    if not local_path:
                        continue
                    
                    # Create image record
                    image_record = {
                        'id': image_id,
                        'name': image_name,
                        'local_path': local_path,
                        'hash': image.get('md5Checksum', ''),
                        'created_time': image.get('createdTime', ''),
                        'modified_time': image.get('modifiedTime', ''),
                        'size': len(processed_data),
                        'sync_time': datetime.now().isoformat()
                    }
                    
                    new_images.append(image_record)
                    total_new_images += 1
                    logger.info(f"Added new image: {image_name} -> {local_path}")
                
                # Update gallery data for this style
                if new_images:
                    self.gallery_data["images"][style_name].extend(new_images)
            
            # Update sync timestamp
            self.gallery_data["last_sync"] = datetime.now().isoformat()
            self._save_gallery_data()
            
            logger.info(f"Sync completed. Added {total_new_images} new images.")
            return True
            
        except Exception as e:
            logger.error(f"Sync failed: {e}")
            return False
    
    def generate_gallery_html(self) -> bool:
        """Generate HTML gallery sections based on synced images"""
        try:
            html_template = """
            <div class="masonry-item" data-aos="fade-up" data-aos-delay="{delay}">
                <div class="image-container" onclick="openLightbox('{path}', '{title}', '{description}')">
                    <img src="{path}" alt="{alt}">
                    <div class="image-overlay">
                        <div class="overlay-content">
                            <span class="image-category">{category}</span>
                            <h3>{title}</h3>
                            <p>{description}</p>
                        </div>
                    </div>
                </div>
            </div>
            """
            
            for style_name, images in self.gallery_data["images"].items():
                if not images:
                    continue
                
                html_content = ""
                for i, image in enumerate(images):
                    delay = 100 + (i * 100)  # Staggered animation delays
                    
                    # Extract title from filename or use default
                    title = image['name'].split('.')[0].replace('_', ' ').title()
                    description = f"Obra de estilo {style_name.title()}"
                    category = style_name.title()
                    alt = f"{style_name} tattoo work - {title}"
                    
                    html_content += html_template.format(
                        delay=delay,
                        path=image['local_path'],
                        title=title,
                        description=description,
                        category=category,
                        alt=alt
                    )
                
                # Save to style-specific HTML file
                output_file = CONFIG["WEBSITE_ROOT"] / f"gallery_{style_name}.html"
                with open(output_file, 'w', encoding='utf-8') as f:
                    f.write(html_content)
                
                logger.info(f"Generated gallery HTML for {style_name}: {len(images)} images")
            
            return True
            
        except Exception as e:
            logger.error(f"Failed to generate gallery HTML: {e}")
            return False

def main():
    """Main execution function"""
    try:
        logger.info("Starting KAOS Gallery Automation System")
        
        sync = GoogleDriveGallerySync()
        
        # Perform sync
        if sync.sync_gallery():
            # Generate HTML
            sync.generate_gallery_html()
            logger.info("Gallery sync and HTML generation completed successfully")
        else:
            logger.error("Gallery sync failed")
            return 1
            
    except KeyboardInterrupt:
        logger.info("Sync interrupted by user")
        return 1
    except Exception as e:
        logger.error(f"Unexpected error: {e}")
        return 1
    
    return 0

if __name__ == "__main__":
    exit(main())
