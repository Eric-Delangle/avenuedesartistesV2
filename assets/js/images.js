
/* Script d'agrandissement/retrecissement d'image */
const images = document.querySelectorAll('.image_petite');
  for (let i = 0; i < images.length; i++) {
    images[i].addEventListener('click', function() {
      this.classList.toggle('image_petite');
    });
  }
  