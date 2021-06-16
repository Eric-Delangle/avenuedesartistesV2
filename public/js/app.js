//import { userInfo } from "os";
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
//require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

// DEBUT DE MES SCRIPTS
// dans cet objet j'appele toutes mes classes

class Main
 {
  constructor() {
    this.map = new Gmap();
    this.map.initMap();
  }
}

// création de la classe Gmap
class Gmap 
{ 
  constructor () {
   
    this.infoMember = document.getElementById('infoMember');
    this.nomMembre = document.getElementById('nomMembre');
    this.ville = document.getElementById('ville');
    this.categorie = document.getElementById('categorie');
    this.message = document.getElementById('message');
    this.markers = [];
  }

  // apparition de la map leaflet
    initMap() {
    const lat = 46.413340;
    const long = 0.788320;
    const bounds = [lat, long];
    let mymap;// je crée une variable vide au début
    const markers = [];
        // cette requete va me permettre de transformer des villes en lat et long
        $(document).ready(function(){ 
          mymap = L.map('map').setView([lat, long], 6.4);
          L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicG9sdnUiLCJhIjoiY2s0c3FmY2FoMTFzMDNlcXVmeXZhdGR1YiJ9.XDjMZFILlUhTvOnBqMAucg', {
          attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
          maxZoom: 18,
          minZoom: 6,
          maxBounds: bounds,
          id: 'mapbox/streets-v11',
          }).addTo(mymap);
          let icone =  L.icon({ // creation des icones
            iconUrl: '/images/marker3.png',
            iconSize: [50, 50],
            iconAnchor: [25, 50],
            popupAnchor: [-3, -76],
          });
          // requete qui permet de récuperer les inos du membre
          $.ajax({
            url : 'members.json',
            type : 'GET',
            dataType : 'json',
      
            success:function(response){
         
              const req = response;
              const villes =[];
              const usersByCity = {};

              $.each(req, function(i) {

                // methode pour mettre les premières lettres en majuscule
                String.prototype.ucFirst=function(){return this.substr(0,1).toUpperCase()+this.substr(1);}

               // let mess = req[i]["messages"];
                let city =req[i]["location"];
                let Ville = city.ucFirst();
                let prenom = req[i]["firstName"];
                let preNom = prenom.ucFirst();
                let nom = req[i]["lastName"];
                let Nom = nom.ucFirst();
                let slug = req[i]["slug"];
                let rep = req[i]['categories'];
              
                    let cat = '';
                        for (let j= 0; j < rep.length; j++) { 
    
                          cat += rep[j].name + ' ';
                      }
                    
                    // test api oms
                     $.ajax({
                      url: "https://nominatim.openstreetmap.org/search", // URL de Nominatim
                      type: 'get', // Requête de type GET
                      data: "q="+city+"&format=json&addressdetails=1&limit=1&polygon_svg=1" // Données envoyées (q -> adresse complète, format -> format attendu pour la réponse, limit -> nombre de réponses attendu, polygon_svg -> fournit les données de polygone de la réponse en svg)
                      }).done(function (response) {
                      if(response != ""){
                        let lat = response[0]['lat'];
                        let lon = response[0]['lon'];
                        let marker = L.marker([lat, lon], {icon: icone});// creation des markers
            
                        markersclusters.addLayer(marker);
                      
                        marker.addEventListener('mouseover', ()=> {
                        
                          document.getElementById('infoMember').style.display = "block";
                          document.getElementById('nomMembre').innerHTML = "Membre: "  + preNom + ' ' + Nom;  
                          document.getElementById('ville').innerHTML = "Ville: " + Ville;
                      
                          document.getElementById('categorie').innerHTML = "Catégorie(s): " + cat;
                                
                          // fonction qui afficher le lien
                          document.getElementById('profil').innerHTML = "<span class='btn btn-success' style='cursor:pointer;'>Profil</span>" ;   
                          document.getElementById("profil").onclick = function(){window.location="http://127.0.0.1:8000/public_show/" + slug;};
                            map.addEventListener('click', ()=> {
                              document.getElementById('infoMember').style.display = "none";
                          })      
                          
                        });// fin marker.addEventListener
                      }});// fin ajax openstreetmap
                    
                if (villes.indexOf(ville) < 0) {

                  usersByCity[ville]=[];
                  villes.push(ville);
                } 
            
                usersByCity[ville].push({
                  nom: Nom,
                  prenom: preNom,
                  ville: ville,
                  categories: cat,
                  id: req[i]["id"],
                })
 
              villes.push(city);
              })
            }
          })
          let  markersclusters = new L.MarkerClusterGroup(); // Nous initialisons les groupes de marqueurs
           mymap.addLayer(markersclusters);
        })     
    }// fin initmap     
}