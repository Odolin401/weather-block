import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('weather/block', {
    title: __('Weather Block', 'weather'),
    icon: 'cloud',
    category: 'widgets',
    edit: () => {
        return <div {...useBlockProps()}>🌤️ Weather Block (Éditeur)</div>;
    },
    save: () => {
        return <div {...useBlockProps()}>🌤️ Weather Block (Frontend)</div>;
    }
});
