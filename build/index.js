(() => {
  "use strict";
  const e = window.wp.blocks,
    i = window.wp.i18n,
    t = window.wp.blockEditor,
    o = window.ReactJSXRuntime;
  (0, e.registerBlockType)("weather/block", {
    title: (0, i.__)("Weather Block", "weather"),
    icon: "cloud",
    category: "widgets",
    edit: () =>
      (0, o.jsxs)("div", {
        ...(0, t.useBlockProps)({
          style: {
            background: "linear-gradient(135deg, #f0f4ff, #dce3f7)",
            borderRadius: "12px",
            padding: "15px",
            maxWidth: "280px",
            margin: "10px auto",
            textAlign: "center",
            fontFamily: "sans-serif",
            boxShadow: "0 2px 5px rgba(0,0,0,0.1)",
          },
        }),
        children: [
          (0, o.jsx)("h3", {
            style: { margin: "5px 0" },
            children: "🌤️ Weather Block",
          }),
          (0, o.jsx)("p", {
            style: { fontSize: "14px", color: "#555" },
            children: "Widget météo — Aperçu dans l’éditeur.",
          }),
        ],
      }),
    save: () => null,
  });
})();
