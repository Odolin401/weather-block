document.addEventListener("DOMContentLoaded", () => {
  const blocks = document.querySelectorAll(".weather-block");
  if (!blocks.length) return;

  const apiKey = "7a3383d971da4775b4462059250208";

  blocks.forEach((block) => {
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;

          console.log("Latitude:", lat, "Longitude:", lon);

          try {
            // Appel API WeatherAPI
            const response = await fetch(
              `https://api.weatherapi.com/v1/current.json?key=${apiKey}&q=${lat},${lon}&lang=fr`
            );
            if (!response.ok) throw new Error("Erreur API");

            const data = await response.json();

            // Récupération des infos
            const location = `${data.location.name}, ${data.location.country}`;
            const date = new Date().toLocaleDateString("fr-FR", {
              weekday: "long",
              year: "numeric",
              month: "long",
              day: "numeric",
            });
            const condition = data.current.condition.text;
            const temp = `${data.current.temp_c}°C`;
            const icon = data.current.condition.icon;

            // Affichage en style card
            block.innerHTML = `
                            <div style="
                                background: linear-gradient(135deg, #f0f4ff, #dce3f7);
                                border-radius: 12px;
                                padding: 15px;
                                max-width: 280px;
                                margin: auto;
                                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                                text-align: center;
                                font-family: sans-serif;
                            ">
                                <h3 style="margin:5px 0;">${location}</h3>
                                <p style="margin:5px 0; font-size:14px; color:#555;">${date}</p>
                                <img src="${icon}" alt="${condition}" style="width:64px;height:64px;" />
                                <p style="font-size:16px; margin:5px 0;"><strong>${condition}</strong></p>
                                <p style="font-size:18px; margin:5px 0; color:#333;">🌡️ ${temp}</p>
                            </div>
                        `;
          } catch (error) {
            block.innerHTML = `<span style="color:red">Impossible de récupérer la météo.</span>`;
          }
        },
        (err) => {
          block.innerHTML = `<span style="color:red">Veuillez autoriser la géolocalisation pour afficher la météo.</span>`;
        },
        {
          enableHighAccuracy: true,
          timeout: 5000,
          maximumAge: 0,
        }
      );
    } else {
      block.innerHTML = `<span style="color:red">La géolocalisation n'est pas supportée par ce navigateur.</span>`;
    }
  });
});
