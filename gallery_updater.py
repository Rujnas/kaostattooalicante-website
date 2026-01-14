#!/usr/bin/env python3
"""
Gallery HTML Updater - Integrates synced images into main website
Updates the main index.html with new gallery content
"""

import json
import re
from pathlib import Path
from typing import Dict, List, Any
from datetime import datetime

class GalleryUpdater:
    def __init__(self):
        self.website_root = Path(__file__).parent
        self.gallery_data_file = self.website_root / "gallery_data.json"
        self.index_file = self.website_root / "index.html"
        self.backup_file = self.website_root / f"index_backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}.html"
        
    def load_gallery_data(self) -> Dict[str, Any]:
        """Load gallery data from JSON"""
        try:
            with open(self.gallery_data_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception as e:
            print(f"Error loading gallery data: {e}")
            return {"images": {}}
    
    def backup_index(self):
        """Create backup of current index.html"""
        try:
            if self.index_file.exists():
                import shutil
                shutil.copy2(self.index_file, self.backup_file)
                print(f"Created backup: {self.backup_file}")
        except Exception as e:
            print(f"Error creating backup: {e}")
    
    def generate_gallery_items(self, style_name: str, images: List[Dict]) -> str:
        """Generate HTML gallery items for a style"""
        if not images:
            return ""
        
        html_items = []
        for i, image in enumerate(images):
            delay = 100 + (i * 100)
            
            # Extract title from filename
            title = image['name'].split('.')[0].replace('_', ' ').title()
            description = f"Obra de estilo {style_name.title()}"
            category = style_name.title()
            alt = f"{style_name} tattoo work - {title}"
            
            html_item = f"""                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="{delay}">
                        <div class="image-container" onclick="openLightbox('{image['local_path']}', '{title}', '{description}')">
                            <img src="{image['local_path']}" alt="{alt}">
                            <div class="image-overlay">
                                <div class="overlay-content">
                                    <span class="image-category">{category}</span>
                                    <h3>{title}</h3>
                                    <p>{description}</p>
                                </div>
                            </div>
                        </div>
                    </div>"""
            
            html_items.append(html_item)
        
        return "\n".join(html_items)
    
    def update_style_gallery(self, style_name: str, gallery_html: str) -> str:
        """Update gallery section for a specific style"""
        try:
            with open(self.index_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Find the gallery section for this style
            pattern = rf'(        <div id="{style_name}" class="page">\s*.*?<section class="fineline-hero">.*?</section>\s*<div class="fineline-gallery">\s*<div class="gallery-masonry">)(.*?)(</div>\s*</div>\s*</div>\s*</div>)'
            
            replacement = f'\\1{gallery_html}\\3'
            
            new_content = re.sub(pattern, replacement, content, flags=re.DOTALL)
            
            if new_content != content:
                with open(self.index_file, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Updated gallery for {style_name}")
                return True
            else:
                print(f"No changes needed for {style_name}")
                return False
                
        except Exception as e:
            print(f"Error updating {style_name} gallery: {e}")
            return False
    
    def update_all_galleries(self):
        """Update all gallery sections in index.html"""
        try:
            # Backup current index
            self.backup_index()
            
            # Load gallery data
            gallery_data = self.load_gallery_data()
            
            updated_styles = []
            
            for style_name, images in gallery_data.get("images", {}).items():
                if not images:
                    continue
                
                # Generate HTML for this style
                gallery_html = self.generate_gallery_items(style_name, images)
                
                # Update the gallery section
                if self.update_style_gallery(style_name, gallery_html):
                    updated_styles.append(style_name)
            
            print(f"Updated galleries for: {', '.join(updated_styles)}")
            return len(updated_styles) > 0
            
        except Exception as e:
            print(f"Error updating galleries: {e}")
            return False

def main():
    """Main execution"""
    updater = GalleryUpdater()
    
    if updater.update_all_galleries():
        print("Gallery update completed successfully")
        return 0
    else:
        print("No gallery updates needed")
        return 0

if __name__ == "__main__":
    exit(main())
