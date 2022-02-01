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

import {Button, Modal, TextControl, SelectControl, ToolbarButton, ToolbarGroup, DropdownMenu, Icon} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';


import { BlockControls } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';


var lastPosts = null;
var cutemiProfiles = null;

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	let _options = [];
	let _profiles = [
		{
			value: 'full',
			label: 'Full'
		},
		{
			value: 'summary',
			label: 'Summary'
		},
	];
	const [ show, setShow ]         = useState(false);
	const [ options, setOptions ]   = useState(!lastPosts ? _options : lastPosts);
	const [ profiles, setProfiles ] = useState(!window.cutemiProfiles ? _profiles : window.cutemiProfiles);
	const [ profile, setProfile ]   = useState(attributes.profile ? attributes.profile : '');
	const [ post, setPost ]         = useState(attributes.id ? attributes.id : '');
	const [ searchInput, setSearchInput ] = useState( '' );
	const [ isLoading, setLoading]  = useState(false);

	if (lastPosts === null) {
		lastPosts = false;
		apiFetch({path: "/wp/v2/cute_mediainfo"}).then(posts => {
			var _options = [];
			let k = 0;
			while(posts.length>k) {
				let val = posts[k];
				_options.push({label: val.title.rendered, value: val.id});
				k = k + 1;
			}
			lastPosts = _options;
			setOptions(_options);
			return _options;
		});
	}

	const showModal = e => {
		setShow(!show);
	};

	const searchOptionsNow = () => {
		setLoading(true);
		return apiFetch({path: "/wp/v2/search?per_page=5&page=1&subtype=cute_mediainfo&search="+
				encodeURIComponent(searchInput)}).then(posts => {
			setLoading(false);
			var _options = [];
			let k = 0;
			while(posts.length>k) {
				let val = posts[k];
				_options.push({label: val.title, value: val.id});
				k = k + 1;
			}
			setOptions(_options);
		}).catch((err)=>{
			alert("Error on search!");
			setLoading(false);
			setOptions([]);
		});
	}

	const searchOptions = (value) => {
		setSearchInput(value);
	}

	useEffect(() => {
		const timer = setTimeout(() => searchOptionsNow(), 600);
		return () => clearTimeout(timer);
	}, [searchInput]);

	const selectPost = (postID) => {
		setPost( postID );
		setShow( false );
		setAttributes( { id: postID } );
	}

	const saveData = (postID) => {
		setAttributes( { id: postID } );
	}

	const onChangeProfile = (newProfile) => {
		setProfile( newProfile );
		setAttributes( { profile: newProfile } );
	}

	const decodeHTMLEntities = (str) => {
		if( str && typeof str === 'string' ) {
			const element_dec = document.createElement('div');
			// strip tags
			str = str.replace(/<script[^>]*>([\S\s]*?)<\s*\/\s*script[^>]*>/gmi, '');
			str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
			element_dec.innerHTML = str;
			str = element_dec.textContent;
			element_dec.textContent = '';
		}

		return str;
	}

	const getToolbarEditButton = ( open ) => {
		const profilesControls = [];
		profiles.forEach( function(_profile){
			profilesControls.push({
				title: _profile.label,
				icon: ( profile ==_profile.value ) ? 'yes-alt' : 'marker',
				onClick: function() { onChangeProfile(_profile.value) },
			});
		});
		return (
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						title={ __( 'Video' ) }
						icon={() => (
							<Icon icon={ <svg viewBox="0 0 24 24">
								<path d="M2.6 3c-1 0-1.8.9-1.8 2v12c0 1 .8 1.8 1.8 1.8h9.6v-1.6H2.5V4.7h18.4v7.7h1.6V4.9c0-1.1-.8-1.8-1.8-1.8L2.6 3zm6 3.9v8l6-4-6-4zm2.8.8 6.9 4.7-6.9-4.7zm2.6 5.7v2h8.4v-2H14zm0 3.2v2h8.4v-2H14zm0 3.2v2h8.4v-2H14z"/>
							</svg> } />
						)  }
						onClick={ open }
					/>
					<DropdownMenu
						icon={() => (
							<Icon icon={ <svg viewBox="0 0 24 24">
								<path d="M2.6 3c-1 0-1.8.9-1.8 2v12c0 1 .8 1.8 1.8 1.8h9.6v-1.6H2.5V4.7h18.4v7.7h1.6V4.9c0-1.1-.8-1.8-1.8-1.8L2.6 3zm6 3.9v8l6-4zm2.8.8 6.9 4.7-6.9-4.7z"/><path d="M21.7 17.6a4 4 0 0 0 0-1l1-.8c.2 0 .2-.2.1-.3l-1-1.7c0-.2-.2-.2-.3-.2l-1.2.5-.8-.4-.2-1.4-.3-.2h-2l-.2.2-.2 1.4c-.3 0-.6.2-.8.4l-1.3-.5c-.1 0-.2 0-.3.2l-1 1.7v.3l1.1.8a4 4 0 0 0 0 1l-1 .8c-.1 0-.2.2 0 .3l1 1.8s.1.1.2 0l1.3-.4.8.5.2 1.3.2.2h2l.3-.2.2-1.3.8-.5 1.2.5.3-.1 1-1.8v-.3zM18 18.9a1.7 1.7 0 1 1 0-3.5 1.7 1.7 0 0 1 0 3.5z"/>
							</svg> } />
						)  }
						label="Profile"
						controls={ profilesControls }
					/>
				</ToolbarGroup>
			</BlockControls>
		);
	}

	return (
		<div {...useBlockProps()}>
			{getToolbarEditButton(setShow)}
			{ ( post === "" ) && ( <div className="cutemi-block-edit components-placeholder">
					<SelectControl
						label={__("Profile",'my-fist-block')}
						value={profile}
						options={profiles}
						onChange={ ( newProfile ) => onChangeProfile( newProfile ) }
					/>
					<TextControl
						label={ __('MediaInfo ID', 'cute-mediainfo') }
						type="number"
						value={ post }
						onChange={ ( value ) => saveData( value ) }
						className="cutemi-block-mediainfo-id"
					/>
					<Button isDefault onClick={ () => setShow( true ) }>{__('Select MediaInfo', 'cute-mediainfo')}</Button>
				</div>
			) }
			{ show && ( <Modal
				title={__('Select MediaInfo', 'cute-mediainfo')}
				onClose2={showModal}
				className="cutemi-modal-search"
				onRequestClose={ () => setShow( false ) } show={show}>
				<TextControl
					label={ __('Search By Title', 'cute-mediainfo') }
					value={ searchInput }
					onChange={ ( value ) => searchOptions( value ) }
					className={isLoading ? "cutemi-search-loading" : ""}
				/>
				{options.map(function(post){
					return <label key={post.value} className="cutemi-vertical-radio-label"><input type="radio"
					                                                                              value={post.value}
					                                                                              key={post.value}
					                                                                              name="post_result"
					                                                                              onChange={(event)=>{ selectPost(event.target.value) }}
					/>{decodeHTMLEntities(post.label)}</label>;
				}, this)}
			</Modal> ) }
			<ServerSideRender
				block="cute-mediainfo/cutemi-block"
				attributes={ attributes }
			/>
		</div>
	);
}
