import {Modal} from "@wordpress/components";
import {__} from "@wordpress/i18n";

import InputSearchFetch from './InputSearchFetch';
import PropTypes from "prop-types";

import searchPost from '../services/search';
import initialPost from '../services/initial'

function SelectPostDialog( props ) {

	function onResponse( posts, cb ) {
		if ( posts.map ) {
			cb(
				posts.map(
					function ( post ) {
						return Object.assign(
							post,
							{
								label: typeof post.title === 'object' ? post.title.rendered : post.title,
								value: post.id
							}
						);
					}
				)
			);
		} else {
			cb( [] );
		}
	}

	function defaults( cb ) {
		initialPost( { postType: props.postType } ).then(
			function ( posts ) {
				onResponse( posts, cb );
			}
		);
	}

	function search( query, cb ) {
		searchPost( { postType: props.postType, query } ).then(
			function (posts) {
				onResponse( posts, cb );
			}
		).catch(
			function () {
				alert( "Error on search!" );
				cb( [] );
			}
		);
	}

	return (<Modal
		title={ __( 'Select MediaInfo', 'cute-mediainfo' ) }
		className="cutemi-modal-search"
		onRequestClose={ () => props.onRequestClose() }>
		<InputSearchFetch
			searchLabel={ __( 'Search By Title', 'cute-mediainfo' ) }
			search={ ( q, cb ) => search( q, cb ) }
			defaults={ ( cb ) => defaults( cb ) }
			minLengthSearch={ props.minLengthSearch }
			onSelect={ ( selected ) => props.onSelect( selected ) }
		/>
	</Modal>);
}

SelectPostDialog.defaultProps = {
	onSelect: () => [],
	onRequestClose: () => [],
	minLengthSearch: 2,
	postType: 'posts'
}

SelectPostDialog.propTypes = {
	onSelect: PropTypes.func,
	onRequestClose: PropTypes.func,
	minLengthSearch: PropTypes.number,
	postType: PropTypes.string
};

export default SelectPostDialog;
