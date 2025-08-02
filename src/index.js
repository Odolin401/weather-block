import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";

registerBlockType("weather/block", {
  title: __("Weather Block", "weather"),
  icon: "cloud",
  category: "widgets",

  edit: () => {
    return (
       <div
        {...useBlockProps({
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
        })}
      >
        <h3 style={{ margin: "5px 0" }}>🌤️ Weather Block</h3>
        <p style={{ fontSize: "14px", color: "#555" }}>
          Widget météo — Aperçu dans l’éditeur.
        </p>
      </div>
    );
  },

  save: () => {
    return null; // Comme on utilise render_callback, rien n'est sauvegardé
  },
});
