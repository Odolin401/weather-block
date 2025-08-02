document.addEventListener("DOMContentLoaded", () => {
  const blocks = document.querySelectorAll(".weather-block");
  if (!blocks.length) return;

  blocks.forEach((block) => {
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          block.innerHTML = `Latitude: ${position.coords.latitude}, Longitude: ${position.coords.longitude}`;
        },
        (err) => {
          block.innerHTML = `<span style="color:red">Veuillez autoriser la géolocalisation pour afficher la météo.</span>`;
        }
      );
    } else {
      block.innerHTML = `<span style="color:red">La géolocalisation n'est pas supportée par ce navigateur.</span>`;
    }
  });
});
