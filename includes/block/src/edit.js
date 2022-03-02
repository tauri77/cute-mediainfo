/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

import {Button, TextControl, SelectControl} from '@wordpress/components';
import {useState, useEffect, useRef} from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import SelectPostDialog from './components/SelectPostDialog';
import CutemiToolbar from './components/CutemiToolbar';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	let _profiles = [ { value: 'full', label: 'Full' } ];

	const [ show, setShow ]         = useState( false );
	const [ profiles, setProfiles ] = useState( ! window.cutemiProfiles ? _profiles : window.cutemiProfiles );
	const [ profile, setProfile ]   = useState( attributes.profile ? attributes.profile : '' );
	const [ post, setPost ]         = useState( attributes.id ? attributes.id : '' );
	const [ editLink, setEditLink ] = useState( false );

	//first(or manual id) use preview edit link, then use api response, elegance 0, else extra request
	const domRef              = useRef( null );
	const [ timer, setTimer ] = useState( false ); //false "not enqueue", true "enqueue", null "ready"
	const searchEditLink      = function() {
		const id = post;
		setTimeout(
			function () {
				if ( timer === null ) {
					return;
				}
				if ( post !== id ) { //Edit id field
					setTimer( false );
					return;
				}
				let $link = jQuery( domRef.current ).parent().find( '.cutemi-preview-edit-link' );
				if ( $link.length > 0 ) {
					setEditLink( $link.attr( 'href' ) );
					setTimer( null );
				} else {
					setTimer( false );
				}
			},
			1000
		)
	};
	useEffect(
		function () {
			if ( editLink === false && timer === false ) {
				setTimer( true );
				searchEditLink();
			}
		}
	);

	const selectPost = (selected) => {
		setPost( selected.value );
		setShow( false );
		setAttributes( { id: '' + selected.value } );
		if ( selected.edit_link ) {
			setEditLink( selected.edit_link );
		} else {
			setEditLink( '' );
		}
	}

	const saveData = (postID) => {
		setPost( postID );
		setAttributes( { id: '' + postID } );
		setTimer( true );
	}

	const onChangeProfile = (newProfile) => {
		setProfile( newProfile );
		setAttributes( { profile: newProfile } );
	}

	return (
		<div {...useBlockProps()}>
			<CutemiToolbar
				profile={ profile }
				profiles={ profiles }
				onSelect={ onChangeProfile }
				editLink={ editLink }
				open={ setShow } />
			{ ( ! editLink ) && ( <div className="cutemi-block-edit components-placeholder">
				<SelectControl
					label={ __("Profile",'cute-mediainfo') }
					value={ profile }
					options={ profiles }
					onChange={ ( newProfile ) => onChangeProfile( newProfile ) }
				/>
				<TextControl
					label={ __('MediaInfo ID', 'cute-mediainfo') }
					type="number"
					value={ post }
					onChange={ ( value ) => saveData( value ) }
					className="cutemi-block-mediainfo-id"
				/>
				<Button isSecondary onClick={ () => setShow( true ) }>
					{ __('Select MediaInfo', 'cute-mediainfo') }
				</Button>
			</div>
			) }
			{ show && ( <SelectPostDialog
									onRequestClose={ () => setShow( false ) }
			                        postType="cute_mediainfo"
			                        minLengthSearch={ 1 }
			                        onSelect={ ( e ) => selectPost( e ) } /> ) }
			<small ref={domRef} ></small>
			<ServerSideRender
				block="cute-mediainfo/cutemi-block"
				attributes={ attributes }
			/>
		</div>
	);
}
