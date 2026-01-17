document.addEventListener('DOMContentLoaded', () => {
  fetch('https://script.google.com/macros/s/AKfycbwMrodQkrw03aAP_Ue8JNlZ26DkbW4s1qLUE8vG3H5fZbytPbIZM_J3jhHeF-81Qmkr/exec')
    .then(res => res.json())
    .then(gallery => {
      Object.entries(gallery).forEach(([sectionId, images]) => {
        const container = document.querySelector(
          `#${sectionId} .gallery-masonry`
        );
        if (!container) return;

        container.innerHTML = '';

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
              referrerpolicy="no-referrer"
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
