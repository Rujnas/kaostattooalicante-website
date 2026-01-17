document.addEventListener('DOMContentLoaded', () => {
  fetch('https://script.google.com/macros/s/AKfycbwMrodQkrw03aAP_Ue8JNlZ26DkbW4s1qLUE8vG3H5fZbytPbIZM_J3jhHeF-81Qmkr/exec')
    .then(res => res.json())
    .then(gallery => {
      console.log('Gallery data loaded:', gallery);
      
      Object.entries(gallery).forEach(([sectionId, images]) => {
        const container = document.querySelector(
          `#${sectionId} .gallery-masonry`
        );
        if (!container) {
          console.warn(`Gallery container not found for section: ${sectionId}`);
          return;
        }

        container.innerHTML = '';
        console.log(`Loading ${images.length} images for section: ${sectionId}`);

        images.forEach((img, index) => {
          const item = document.createElement('div');
          item.className = 'masonry-item';
          item.dataset.aos = 'fade-up';
          item.dataset.aosDelay = 100 + index * 50;

          item.innerHTML = `
          <div class="image-container">
            <img 
              src="${img.url}" 
              alt="${img.name}" 
              loading="lazy"
              onerror="
                const img = this;
                const originalSrc = img.src;
                console.error('Failed to load image:', originalSrc);
                
                // Try alternative Google Drive formats
                if (originalSrc.includes('drive.google.com/uc?id=')) {
                  const fileId = originalSrc.match(/id=([^&]+)/)?.[1];
                  if (fileId) {
                    // Try direct download format
                    img.src = 'https://drive.google.com/uc?export=view&id=' + fileId;
                    img.onerror = function() {
                      // Try thumbnail format as last resort
                      img.src = 'https://drive.google.com/thumbnail?id=' + fileId + '&sz=w800';
                      img.onerror = function() {
                        console.error('All formats failed for:', originalSrc);
                        img.style.display='none';
                      };
                    };
                  }
                } else {
                  img.style.display='none';
                }
              "
              onload="console.log('Image loaded successfully:', '${img.name}');"
            >
            <div class="image-overlay">
              <div class="overlay-content">
                <span class="image-category">${sectionId}</span>
                <h3>${img.name.replace(/\.[^/.]+$/, '')}</h3>
                <p>Trabajo realizado en nuestro estudio</p>
              </div>
            </div>
          </div>
        `;

          container.appendChild(item);
        });
      });

      if (window.AOS) AOS.refresh();
    })
    .catch(err => console.error('Error galería:', err));
});
