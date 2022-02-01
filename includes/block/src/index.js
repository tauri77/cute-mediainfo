/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import { Icon } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType('cute-mediainfo/cutemi-block', {
	icon: () => (
		<Icon icon={ <svg viewBox="0 0 24 24">
	<path d="M2.6 3c-1 0-1.8.9-1.8 2v12c0 1 .8 1.8 1.8 1.8h9.6v-1.6H2.5V4.7h18.4v7.7h1.6V4.9c0-1.1-.8-1.8-1.8-1.8zm6 3.9v8l6-4zm9.7 5.5c-3 0-5.4 2.2-5.4 5 0 2.7 2.4 5 5.4 5s5.3-2.3 5.3-5c0-2.8-2.4-5-5.3-5zm0 1c2.3 0 4.3 1.8 4.3 4s-2 4-4.3 4c-2.4 0-4.3-1.8-4.3-4s2-4 4.3-4zm-.6 1.5v1h1.1v-1zm0 2v3h1.1v-3z"/>
	</svg> } />
),
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,
});
