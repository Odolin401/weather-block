import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";

registerBlockType("weather/block", {
  title: __("Weather Block", "weather"),
  icon: "cloud",
  category: "widgets",

  edit: () => {
    return (
      <div {...useBlockProps()}>🌤️ La météo apparaîtra ici (Frontend)</div>
    );
  },

  save: () => {
    return null; // Comme on utilise render_callback, rien n'est sauvegardé
  },
});
