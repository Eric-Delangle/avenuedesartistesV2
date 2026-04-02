import '../css/app.css';

// Coordonnées des villes connues pour éviter trop d'appels API
const CITY_COORDS = {
    'Paris':     [48.8566, 2.3522],
    'Lyon':      [45.7640, 4.8357],
    'Marseille': [43.2965, 5.3698],
    'Nice':      [43.7102, 7.2620],
    'Rouen':     [49.4432, 1.0993],
    'Elbeuf':    [49.2833, 1.0167],
    'Bordeaux':  [44.8378, -0.5792],
    'Toulouse':  [43.6047, 1.4442],
    'Nantes':    [47.2184, -1.5536],
    'Strasbourg':[48.5734, 7.7521],
    'Montpellier':[43.6108, 3.8767],
    'Lille':     [50.6292, 3.0573],
    'Rennes':    [48.1173, -1.6778],
    'Reims':     [49.2583, 4.0317],
    'Toulon':    [43.1242, 5.9280],
};

async function geocode(city) {
    if (CITY_COORDS[city]) return CITY_COORDS[city];

    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(city)},France&format=json&limit=1`,
            { headers: { 'Accept-Language': 'fr' } }
        );
        const data = await res.json();
        if (data.length > 0) {
            return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
        }
    } catch (e) {
        console.warn('Geocoding failed for', city, e);
    }
    return null;
}

async function main() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;

    const map = L.map('map').setView([46.5, 2.5], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18,
    }).addTo(map);

    const clusterGroup = L.markerClusterGroup();

    let members;
    try {
        const res = await fetch('/members.json');
        members = await res.json();
    } catch (e) {
        console.error('Impossible de charger members.json', e);
        return;
    }

    for (const member of members) {
        const coords = await geocode(member.location);
        if (!coords) continue;

        const marker = L.marker(coords);

        const categories = member.categories.map(c => c.name).join(', ');
        marker.bindPopup(
            `<strong>${member.firstName} ${member.lastName}</strong><br>` +
            `${member.location}<br>` +
            `<small>${categories}</small><br>` +
            `<a href="/share/${member.slug}">Voir le profil →</a>`
        );

        marker.on('click', () => {
            const nomEl = document.getElementById('nomMembre');
            const catEl  = document.getElementById('categorie');
            const villeEl = document.getElementById('ville');
            const profilEl = document.getElementById('profil');

            if (nomEl)   nomEl.textContent   = `${member.firstName} ${member.lastName}`;
            if (catEl)   catEl.textContent   = categories;
            if (villeEl) villeEl.textContent = member.location;
            if (profilEl) profilEl.innerHTML = `<a href="/share/${member.slug}">Voir le profil</a>`;
        });

        clusterGroup.addLayer(marker);
    }

    map.addLayer(clusterGroup);
}

window.main = main;

// Auto-démarrage si #map est présent dans la page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('map')) {
        main();
    }
});
