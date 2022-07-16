/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../styles/app.css';

// start the Stimulus application
import '../bootstrap';


/* Script d'agrandissement/retrecissement d'image */

var ig = document.getElementsByTagName("img"); // je créé un tableau vide pour y mettre toutes les images de la page

var images = [];
images.push(ig);

function agrandir() {
  // il faut que j'arrive a recuperer la liste des images
 // console.log(images);

  for (var i = 0; i < images.length; i++) {
    console.log(images[i].classList);

 
       images[i].classList.toggle('image_grande');
  
  }
}

/* AGRANDIR

function agrandir() {
  // il faut que j'arrive a recuperer la liste des images
  console.log(images);

  for (var i = 0; i < images.length; i++) {
    console.log(images[0][i].classList);

    if (images[0][i].classList == "image_petite") {
      // images[i][j].classList.toggle('image_grande');
      images[0][i].classList.remove('image_petite');
      images[0][i].classList.add('image_grande');
    } else if (images[0][i].classList == "image_grande") {
      images[0][i].classList.remove('image_grande');
      images[0][i].classList.add('image_petite');
    }
  }
}
*/

function retrecir() {
  var ig = document.getElementsByClassName("image_grande");
  ig.classList.remove("image_grande");
  ig.classList.add("image_petite");
}
