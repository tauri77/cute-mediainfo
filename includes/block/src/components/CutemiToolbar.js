import { useState, useEffect } from '@wordpress/element';
import PropTypes from 'prop-types';
import {BlockControls} from "@wordpress/block-editor";
import {DropdownMenu, Icon, ToolbarButton, ToolbarGroup, ToolbarItem} from "@wordpress/components";
import {__} from "@wordpress/i18n";

function CutemiToolbar(props) {

	const [ profilesControls, setProfilesControls ] = useState( false );

	const updateProfilesControls = function () {
		const _profilesControls = props.profiles.map(
			function ( _profile ) {
				return {
					title: _profile.label,
					icon: ( props.profile == _profile.value ) ? 'yes-alt' : 'marker',
					onClick: () => { props.onSelect( _profile.value ) },
				};
			}
		);
		setProfilesControls( _profilesControls );
	}

	useEffect(
		function () {
			if ( profilesControls === false ) {
				updateProfilesControls();
			}
		}
	);
	useEffect(
		updateProfilesControls,
		[props.profile]
	);

	const editHandle = function() {
		window.open( props.editLink, '_blank' );
	};

	return (
		<BlockControls>
			<ToolbarGroup>
				<ToolbarButton
					title={ __( 'Video', 'cute-mediainfo' ) }
					icon={() => (
						<Icon icon={ <svg viewBox="0 0 24 24">
							<path d="M2.6 3c-1 0-1.8.9-1.8 2v12c0 1 .8 1.8 1.8 1.8h9.6v-1.6H2.5V4.7h18.4v7.7h1.6V4.9c0-1.1-.8-1.8-1.8-1.8L2.6 3zm6 3.9v8l6-4-6-4zm2.8.8 6.9 4.7-6.9-4.7zm2.6 5.7v2h8.4v-2H14zm0 3.2v2h8.4v-2H14zm0 3.2v2h8.4v-2H14z"/>
						</svg> } />
					)  }
					onClick={ props.open }
				/>
				{ props.editLink && (<ToolbarButton
					title={ __( 'Edit MediaInfo', 'cute-mediainfo' ) }
					icon={() => (
						<Icon icon={ <svg viewBox="0 0 24 24">
							<path d="M2.6 3c-1 0-1.8.9-1.8 2v12c0 1 .8 1.8 1.8 1.8h9.6v-1.6H2.5V4.7h18.4v6.1h1.6V4.9c0-1.1-.8-1.8-1.8-1.8L2.6 3zm6 3.9v8l6-4zm11.3 6.5.71-.71a1 1 0 0 1 1.41 0l.71.71a1 1 0 0 1 0 1.41l-.71.71zm-.71.71-5.3 5.3v2.12h2.12l5.3-5.3z"/>
						</svg> } />
					)  }
					onClick={ editHandle }
				/>) }
				<ToolbarItem>
					{ ( toolbarItemHTMLProps ) => (
					<DropdownMenu
						toggleProps={ toolbarItemHTMLProps }
						icon={() => (
							<Icon icon={ <svg viewBox="0 0 24 24">
								<path d="M2.6 3c-1 0-1.8.9-1.8 2v12c0 1 .8 1.8 1.8 1.8h9.6v-1.6H2.5V4.7h18.4v7.7h1.6V4.9c0-1.1-.8-1.8-1.8-1.8L2.6 3zm6 3.9v8l6-4zm2.8.8 6.9 4.7-6.9-4.7z"/><path d="M21.7 17.6a4 4 0 0 0 0-1l1-.8c.2 0 .2-.2.1-.3l-1-1.7c0-.2-.2-.2-.3-.2l-1.2.5-.8-.4-.2-1.4-.3-.2h-2l-.2.2-.2 1.4c-.3 0-.6.2-.8.4l-1.3-.5c-.1 0-.2 0-.3.2l-1 1.7v.3l1.1.8a4 4 0 0 0 0 1l-1 .8c-.1 0-.2.2 0 .3l1 1.8s.1.1.2 0l1.3-.4.8.5.2 1.3.2.2h2l.3-.2.2-1.3.8-.5 1.2.5.3-.1 1-1.8v-.3zM18 18.9a1.7 1.7 0 1 1 0-3.5 1.7 1.7 0 0 1 0 3.5z"/>
							</svg> } />
						)  }
						label={ __( 'Profile', 'cute-mediainfo' ) }
						controls={ profilesControls }
					/> ) }
				</ToolbarItem>
			</ToolbarGroup>
		</BlockControls>
	);
}

CutemiToolbar.propTypes = {
	profiles: PropTypes.array.isRequired,
	profile: PropTypes.string.isRequired,
	onSelect: PropTypes.func.isRequired,
	open: PropTypes.func.isRequired,
	editLink: PropTypes.func.isRequired,
};

export default CutemiToolbar;
