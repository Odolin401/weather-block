# Weather Block - Plugin WordPress 🌤️

## 📌 Description
Weather Block est un plugin WordPress qui ajoute un bloc Gutenberg pour afficher la météo en fonction de la localisation des visiteurs.  
Les données sont mises en cache en base de données pour éviter des appels inutiles à l’API WeatherAPI.

---

## 🚀 Installation
1. Télécharger ou cloner ce dépôt.
2. Compresser le dossier `weather-block` en `.zip`.
3. Importer le `.zip` dans **Extensions > Ajouter** sur WordPress.
4. Activer le plugin.
5. Ajouter le bloc **Weather Block** dans une page ou un article via l’éditeur Gutenberg.

---

## ⚙️ Configuration
- Une clé API gratuite est nécessaire : [WeatherAPI](https://www.weatherapi.com/).
- Ouvrez `reglage de wordpress -> weather block ` et mettez votre api dans le champ  `Clé API WeatherAPI` et enregistrer le.

---

## 📡 Fonctionnement
- Lorsqu’un visiteur arrive sur le site :
  1. Sa localisation est demandée via le navigateur (latitude & longitude).
  2. Le plugin vérifie si des données météo existent déjà en base pour aujourd’hui.
  3. Si oui → affichage depuis la base (pas d’appel API).
  4. Si non → appel à WeatherAPI et enregistrement en base.

---

## 📌 Messages d’erreur
- **Refus de localisation** → Message invitant à autoriser la géolocalisation.
- **Erreur API + pas de données en base** → Message d’erreur affiché.

---

## 🛠️ Technologies
- PHP (WordPress Plugin API)
- JavaScript (fetch + API Geolocation)
- MySQL (cache météo)
- API WeatherAPI

---

## 📄 Licence
Ce plugin est distribué. Vous pouvez l’utiliser, le modifier et le redistribuer librement.
