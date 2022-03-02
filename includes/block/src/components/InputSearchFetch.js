import { useState, useEffect, useRef } from '@wordpress/element';
import {TextControl, Button} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import PropTypes from 'prop-types';

function InputSearchFetch( props ) {

	const [ searchInput, setSearchInput ] = useState( '' );
	const [ isLoading, setLoading ]       = useState( false );
	const [ options, setOptions ]         = useState( [] );
	const [ defaults, setDefaults ]       = useState( null );

	const inputRef = useRef( null );
	useEffect( () => { inputRef.current && inputRef.current.focus(); }, [] );

	useEffect(
		function () {
			if ( null === defaults ) {
				call_defaults();
			}
		}
	);

	useEffect(
		function () {
			if ( props.minLengthSearch <= searchInput.length ) {
				const timer = setTimeout( () => search(), 600 );
				return () => clearTimeout( timer );
			} else if ( searchInput.length === 0 && defaults != null ) {
				setOptions( defaults );
			}
		},
		[searchInput]
	);

	function onSelect(value) {
		for ( let i = 0; i < options.length; i++ ) {
			let item = options[i];
			if ( "" + item.value === value ) {
				props.onSelect( Object.assign( {}, item ) );
				return;
			}
		}
		alert( "Error on InputSearchFetch component! Not Found " + value );
	}

	function call_defaults() {
		setDefaults( false );
		props.defaults(
			function(list) {
				//return array of object with "label" and "value" keys
				setOptions( list );
				setDefaults( list );
			}
		)
	}

	function search() {
		setLoading( true );
		props.search(
			searchInput,
			function( _options ) {
				//return array of object with "label" and "value" keys
				setLoading( false );
				setOptions( _options );
			}
		);
	}

	function handleChange(val) {
		setSearchInput( val );
	}

	function decodeHTMLEntities (str) {
		if ( str && typeof str === 'string' ) {
			const element_dec = document.createElement( 'div' );
			// strip tags
			str                     = str.replace( /<\s*script[^>]*>([\S\s]*?)<\s*\/\s*script[^>]*>/gmi, '' );
			str                     = str.replace( /<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '' );
			element_dec.innerHTML   = str;
			str                     = element_dec.textContent;
			element_dec.textContent = '';
		}

		return str;
	}

	return (<>
		<TextControl
			ref={ inputRef }
			autoFocus={ true }
			label={ props.searchLabel }
			value={ searchInput }
			onChange={ handleChange }
			className={ isLoading ? "cutemi-search-loading" : "" }
		/>
		{ options.map(function(item){
			return ( <Button key={ item.value }
			                    className="cutemi-input-search-result"
								label={ decodeHTMLEntities( item.label ) }
	                            value={ item.value }
	                            key={ item.value }
	                            onClick={ (event) => { onSelect( event.target.value ) } }
			>{ decodeHTMLEntities( item.label ) }</Button> );
		}) }
	</>);
}

InputSearchFetch.defaultProps = {
	search: () => [],
	defaults: () => [],
	searchLabel: __( 'Search Here', 'cute-mediainfo' ),
	minLengthSearch: 2,
}

InputSearchFetch.propTypes = {
	search: PropTypes.func,
	defaults: PropTypes.func,
	onSelect: PropTypes.func.isRequired,
	searchLabel: PropTypes.string,
	minLengthSearch: PropTypes.number,
};

export default InputSearchFetch;
