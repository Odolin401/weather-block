document.addEventListener("DOMContentLoaded", () => {
  const blocks = document.querySelectorAll(".weather-block");
  if (!blocks.length) return;

  blocks.forEach((block) => {
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;
          const localHour = new Date().getHours();

          // 🔹 Envoi à WordPress pour vérifier/cache ou appeler API
          fetch(wb_ajax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
              action: "get_weather_data",
              lat: lat,
              lon: lon,
              hour: localHour,
            }),
          })
            .then((res) => res.json())
            .then((data) => {
              if (data.success) {
                const w = data.data;
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
                      <h3 style="margin:5px 0;">${w.city}</h3>
                      <p style="margin:5px 0; font-size:14px; color:#555;">${new Date().toLocaleDateString(
                        "fr-FR",
                        {
                          weekday: "long",
                          year: "numeric",
                          month: "long",
                          day: "numeric",
                        }
                      )}</p>
                      <img src="${w.icon}" alt="${
                        w.weather_condition
                      }" style="width:64px;height:64px;" />
                      <p style="font-size:16px; margin:5px 0;"><strong>${
                        w.weather_condition
                      }</strong></p>
                      <p style="font-size:18px; margin:5px 0; color:#333;">🌡️ ${
                        w.temperature
                      }°C</p>
                  </div>
                `;
              } else {
                block.innerHTML = `<span style="color:red">Erreur: ${data.data}</span>`;
              }
            });
        },
        () => {
          block.innerHTML = `<span style="color:red">Veuillez autoriser la géolocalisation pour afficher la météo.</span>`;
        },
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
      );
    } else {
      block.innerHTML = `<span style="color:red">La géolocalisation n'est pas supportée par ce navigateur.</span>`;
    }
  });
});
