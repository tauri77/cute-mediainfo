/*globals jQuery:false,tb_remove:false,tb_show:false,cutemiData:false,MediaInfoLib:false,Promise:false,MediaInfo:false,wp:false*/
/*globals cutemiItemUp:true,cutemiItemDown:true,cutemiItemDelete:true,cutemiItemAdd:true,cutemiMetaManager:true,cutemiStreams:true,cutemiLinkInputsItem:true,cutemiLinkDataFromInputs:true*/
/*jshint bitwise: false*/

(function ( $ ) {
	'use strict';
	var o               = $( {} );
	$.cutemiOn          = function () {
		o.on.apply( o, arguments );
	};
	$.cutemiOff         = function () {
		o.off.apply( o, arguments );
	};
	$.cutemiTrigger     = function () {
		o.trigger.apply( o, arguments );
	};
	$.fn.cutemiFilter   = function ( f ) {
		return this.on(
			'input keydown keyup mousedown mouseup select contextmenu drop',
			function () {
				if ( f( this.value ) ) {
					this.oldValue          = this.value;
					this.oldSelectionStart = this.selectionStart;
					this.oldSelectionEnd   = this.selectionEnd;
				} else if ( this.hasOwnProperty( 'oldValue' ) ) {
					this.value = this.oldValue;
					this.setSelectionRange( this.oldSelectionStart, this.oldSelectionEnd );
				} else {
					this.value = '';
				}
			}
		);
	};
	$.fn.cutemiNoSubmit = function () {
		return this.on(
			'keypress',
			function ( event ) {
				var keyPressed = event.keyCode || event.which;
				if ( keyPressed === 13 ) {
					event.preventDefault();
					return false;
				}
			}
		);
	};
}( jQuery ));

(function () {
	'use strict';

	var calculating_size_from_parts = false;

	function localCreateElement( tag, id, classes, addto, onclick, code ) {
		var element = document.createElement( tag );
		if ( id ) {
			element.id = id;
		}
		if ( classes ) {
			element.className = classes;
		}
		if ( addto ) {
			addto.append( element );
		}
		if ( onclick ) {
			element.addEventListener( 'click', onclick );
		}
		if ( code ) {
			element.innerHTML = code;
		}
		return element;
	}

	/**
	 * UI elements startup
	 */
	jQuery(
		function ( $ ) {
			$.widget(
				'custom.cutemiselectmenu',
				$.ui.selectmenu,
				{
					_renderItem: function ( ul, item ) {
						var class_li = item.element.data( 'class' ),
							li       = localCreateElement( 'li', false, class_li, ul[ 0 ] ),
							wrapper  = localCreateElement( 'div', false, false, li );

						if ( item.disabled ) {
							$( li ).addClass( 'ui-state-disabled' );
						}
						localCreateElement( 'span', false, 'item-label', wrapper, false, item.label );
						localCreateElement( 'span', false, 'item-sub', wrapper, false, item.element.attr( 'data-sub' ) );
						localCreateElement( 'span', false, 'ui-customicon', wrapper, false, false ).setAttribute( 'style', item.element.attr( 'data-style' ) );
						return $( li );
					}, _renderButtonItem: function ( item ) {
						var buttonItem = localCreateElement( 'span', false, 'ui-selectmenu-customicon' );
						localCreateElement( 'span', false, 'ui-customicon', buttonItem, false, false ).setAttribute( 'style', item.element.attr( 'data-style' ) );
						localCreateElement( 'span', false, 'cutemi-selectmenu-text', buttonItem, false, item.label );
						return $( buttonItem );
					}, _renderMenu: function ( ul, items ) {
						var that = this;
						$.each(
							items,
							function ( index, item ) {
								that._renderItemData( ul, item );
							}
						);
					}, create: function ( ) {

					}
				}
			);

			renderVfiSelectMenu( document );
			$( '.cutemi-datepicker' ).datepicker(
				{
					dateFormat: 'yy-mm-dd',
					beforeShow: function( textbox, instance ){
						var $d = $( '#ui-datepicker-div' );
						if ( $d.parent().prop( 'tagName' ) !== 'SPAN' ) {
							$d.wrap( '<span class=""></span>' );
						}
						if ( $( textbox ).hasClass( 'cutemi-datepicker' ) ) {
							$d.parent().addClass( 'cutemi-jquery-ui' );
						} else {
							$d.parent().removeClass( 'cutemi-jquery-ui' );
						}
					}
				}
			);
		}
	);

	function renderVfiSelectMenu( where ) {
		jQuery( where ).find( '.ui-cutemiselectmenu' ).each(
			function () {
				var $inputFilter = false;
				var menu         = jQuery( this ).cutemiselectmenu(
					{
						open: function () {
							if ( this ) {
								if ( $inputFilter === false ) {
									$inputFilter = jQuery( '<input placeholder="..." style="width: 90%;margin: 9px 5% 5px;" class="iconselect-filter-input">' );
									$inputFilter.on(
										'keyup',
										function () {
											var s = jQuery( this ).val().toLowerCase();
											menuElement.find( 'li' ).each(
												function () {
													if ( s === '' || jQuery( this ).text().toLowerCase().indexOf( s ) > -1 ) {
														jQuery( this ).show();
													} else {
														jQuery( this ).hide();
													}
												}
											);
										}
									);
									jQuery( '<div></div>' ).append( $inputFilter ).prependTo( menuElement );
								}
								$inputFilter.focus();
							}
						}
					}
				);
				var menuElement  = menu.cutemiselectmenu( 'menuWidget' ).addClass( 'ui-menu-customicons' );
				menuElement.parents( '.ui-selectmenu-menu' ).last().wrap( '<span class="cutemi-jquery-ui"></span>' );
				menu.cutemiselectmenu( 'widget' ).addClass( jQuery( this ).attr( 'class' ) );
			}
		);
	}

	function cutemiChangeRows( $first, $second ) {

		$first.parent().find( 'li' ).stop( true, true ); //End if animation

		var h1    = $first.outerHeight( true ), h2 = $second.outerHeight( true ), styleOriginal1 = {
			'marginTop': jQuery( $first ).css( 'margin-top' ),
			'marginBottom': jQuery( $first ).css( 'margin-bottom' ),
			'opacity': jQuery( $first ).css( 'opacity' )
		}, pOrig2 = {
			'marginTop': jQuery( $second ).css( 'margin-top' ),
			'marginBottom': jQuery( $second ).css( 'margin-bottom' ),
			'opacity': jQuery( $second ).css( 'opacity' )
		};

		$first.animate(
			{
				marginTop: h1, marginBottom: -h1, opacity: 0.5
			},
			200,
			'linear',
			function () {
				$first.css( styleOriginal1 );
			}
		);
		$second.animate(
			{
				marginTop: -2 * h2, marginBottom: h2, opacity: 0.5
			},
			200,
			'linear',
			function () {
				$second.css( pOrig2 );
				$second.insertBefore( $first );
			}
		);
	}

	/*************************************************
	 ***************** Globals functions *************
	 *************************************************/
	window.cutemiItemUp = function ( btn ) {
		var $li = jQuery( btn ).parents( '.cutemi-list-item' );
		cutemiChangeRows( $li.prev(), $li );
	};

	window.cutemiItemDown = function ( btn ) {
		var $li = jQuery( btn ).parents( '.cutemi-list-item' );
		if ( ! $li.next().hasClass( 'cutemi-list-item-template' ) ) {
			cutemiChangeRows( $li, $li.next() );
		}
	};

	window.cutemiItemDelete = function ( btn ) {
		var $li = jQuery( btn ).parents( '.cutemi-list-item' );
		$li.remove();
	};

	window.cutemiItemAdd = function ( btn, values ) {
		var meta = window.cutemiMetaManager( jQuery( btn ) );
		if ( meta === false ) {
			return false;
		}
		if ( typeof values !== 'undefined' ) {
			return meta.addItem( values );
		}
		return meta.addItem();
	};

	window.cutemiMetaManager = function ( $child ) {
		if ( $child === null || $child.length === 0 ) {
			return false;
		}
		var $metaBox  = $child.first().parents( '.cutemi-metabox' ), $btn = $metaBox.find( '.cutemi-button-add' ),
			$template = $metaBox.find( '.cutemi-list-item-template' ), that = {};

		that.setRowValues = function ( $rnew, values, index ) {
			for ( var prop in values ) {
				if ( values.hasOwnProperty( prop ) ) {
					var selector = '[name=' + prop + '\\[\\]],[name=' + prop + '\\[' + index + '\\]]';
					if ( $btn.length === 0 || $template.length === 0 ) {
						selector = '[name=' + prop + ']';
					}
					var $input = $rnew.find( selector );
					if ( $input.length > 0 ) {
						$input.val( values[ prop ] );
						$input.trigger( 'change' );
						var r = $input.cutemiselectmenu( 'instance' );
						if ( r && r.refresh ) {
							r.refresh();
						}
					}
				}
			}
		};

		that.addItem = function ( values ) {
			if ( $btn.length > 0 && $template.length > 0 ) {
				var $rnew,
					useLastItem = false,
					$lastItem   = that.getItems().last();

				//Check if the last row is empty and use this
				if ( $lastItem.length > 0 ) {
					useLastItem = true;
					$lastItem.find( 'input,textarea,select' ).each(
						function () {
							if ( jQuery( this ).val() !== '' ) {
								useLastItem = false;
							}
						}
					);
				}
				if ( useLastItem ) {
					$rnew = $lastItem;
				} else {
					//Not empty last, create from template
					$rnew = $template.clone( false )
						.show().removeClass( 'cutemi-list-item-template' ).addClass( 'cutemi-list-item' );
					//remove jquery-uis and listener flags
					$rnew.find( '.ui-button.ui-cutemiselectmenu' ).remove();
					$rnew.find( '*' ).removeAttr( 'id' );
					$rnew.find( '.cutemi-ready' ).removeClass( 'cutemi-ready' );

					$rnew.insertBefore( $template );

					renderVfiSelectMenu( $rnew );

					jQuery.cutemiTrigger( 'cutemi_item_created', { target: $rnew } );
				}

				//set fields values
				if ( typeof values !== 'undefined' ) {
					that.setRowValues( $rnew, values, $rnew.index() );
				}

				return $rnew;
			}
			return false;
		};

		that.countItems = function () {
			if ( $btn.length === 0 || $template.length === 0 ) {
				return 1;
			}
			return $metaBox.find( 'li.cutemi-list-item' ).length;
		};

		that.getItems = function () {
			if ( $btn.length === 0 || $template.length === 0 ) {
				return [ $metaBox ];
			}
			return $metaBox.find( 'li.cutemi-list-item' );
		};

		that.getItem = function ( index ) {
			if ( $btn.length === 0 || $template.length === 0 ) {
				return $metaBox;
			}
			var $items = $metaBox.find( 'li.cutemi-list-item' );
			if ( $items.length <= index ) {
				return false;
			}
			return jQuery( $items.get( index ) );
		};

		that.setItem = function ( index, values ) {
			var $rnew = that.getItem( index );
			if ( $rnew === false ) {
				that.addItem( values );
				return;
			}
			if ( typeof values !== 'undefined' ) {
				that.setRowValues( $rnew, values, index );
			}
		};
		return that;
	};

	window.cutemiStreams = function ( type ) {
		var $child = null;
		if ( type === 'videos' ) {
			$child = jQuery( '.tax-cutemi_video_resolution' );
		} else if ( type === 'audios' ) {
			$child = jQuery( '.tax-cutemi_audio_langs' );
		} else if ( type === 'texts' ) {
			$child = jQuery( '.tax-cutemi_text_langs' );
		} else if ( type === 'general' ) {
			$child = jQuery( '.tax-cutemi_file_format' );
		}

		return window.cutemiMetaManager( $child );
	};

	function cutemiSetPostTitle( title ) {
		var $titleInput = jQuery( '[name="post_title"]' );
		if ( $titleInput.val() === '' ) {
			$titleInput.val( title ).parent().find( 'label' ).addClass( 'screen-reader-text' );
		}
	}

	/*********************************************************************
	 * ********************* Links ***************************************
	 *********************************************************************/
	jQuery( cutemiLinkListener );
	jQuery.cutemiOn( 'cutemi_item_created', cutemiLinkListener );

	function cutemiLinkListener() {
		var change_time = null;
		//Fire extract data on change link
		jQuery( '.field-original_link:not(.cutemi-ready)' ).addClass( 'cutemi-ready' ).each(
			function () {
				var $input = jQuery( this );
				if ( ! $input.hasClass( 'cutemi-field' ) ) {
					$input = jQuery( this ).parents( '.cutemi-field' );
				}
				var last_check_val = '';
				var _extract       = function ( _this ) {
					if ( last_check_val !== jQuery( _this ).val() ) {
						last_check_val = jQuery( _this ).val();
						cutemiExtractFromLink( _this );
					}
				};
				$input             = jQuery( $input.find( 'input,select,textarea' ).first() );
				$input.on(
					'change',
					function () {
						clearTimeout( change_time );
						_extract( this );
					}
				).on(
					'keyup',
					function () {
						clearTimeout( change_time );
						var _this = this;
						if ( ! /^https?:\/\/[^\/]*\/./i.test( jQuery( this ).val() ) ) {
							return;
						}
						change_time = setTimeout(
							function () {
								_extract( _this );
							},
							300
						);
					}
				);
			}
		);

		//Fire calculate total size
		if ( calculating_size_from_parts ) {
			jQuery( '.field-part_size:not(.cutemi-ready),.field-part_nro:not(.cutemi-ready)' ).on(
				'change',
				function () {
					cutemiUpdateSizeFromParts();
				}
			).addClass( 'cutemi-ready' );
		}
	}

	function cutemiGetInputControl( $container, query ) {
		var $q = $container.find( query );
		if ( ! $q.hasClass( 'cutemi-field' ) ) {
			$q = $q.parents( '.cutemi-field' );
		}
		return jQuery( $q.find( 'input,select,textarea' ).first() );
	}

	window.cutemiLinkInputsItem = function ( $container ) {
		var $link           = {};
		$link.container     = $container;
		$link.original_link = cutemiGetInputControl( $container, '.field-original_link' );
		$link.external_id   = cutemiGetInputControl( $container, '.field-external_id' );
		$link.site_source   = cutemiGetInputControl( $container, '.tax-cutemi_site_source' );
		$link.part_nro      = cutemiGetInputControl( $container, '.field-part_nro' );
		$link.part_size     = cutemiGetInputControl( $container, '.field-part_size' );
		$link.link_title    = cutemiGetInputControl( $container, '.field-link_title' );
		$link.link_status   = cutemiGetInputControl( $container, '.field-link_status' );
		return $link;
	};

	window.cutemiLinkDataFromInputs = function ( $link, action ) {
		return {
			'action': action,
			'nonce': cutemiData.nonce_ajax,
			'link': $link.original_link.val(),
			'sitesource': $link.site_source.val(),
			'external_id': $link.external_id.val(),
			'part_nro': $link.part_nro.val(),
			'part_size': $link.part_size.val(),
			'status': $link.link_status.val(),
			'title': $link.link_title.val()
		};
	};

	function cutemiExtractFromLink( target ) {
		if ( ! cutemiData.link_data_extractor ) {
			return;
		}

		var $link = cutemiLinkInputsItem( jQuery( target ).parents( '.cutemi-list-item' ) );
		$link.container.addClass( 'cutemi-link-loading' );
		var data = cutemiLinkDataFromInputs( $link, 'extract_cute_mediainfo_data' );

		jQuery.post(
			cutemiData.ajaxUrl,
			data,
			function ( response ) {
				$link.container.removeClass( 'cutemi-link-loading' );

				if ( response.nonce ) {
					cutemiData.nonce_ajax = response.nonce;
				}

				if ( ! response.candidates ) {
					return;
				}

				if ( response.candidates.length === 1 ) {
					cutemiSetLink( $link, response.candidates[ 0 ] );
				}

				if ( response.candidates.length > 1 ) {
					//crate dialog for user select between sources matched
					jQuery( '#cutemi_select_valid_source' ).remove();
					var $wrapper = jQuery( '<div></div>' ).attr(
						{
							id: 'cutemi_select_valid_source', style: 'display:none;'
						}
					);
					$wrapper.append( '<h3>' + cutemiData.select_the_link_source + ' </h3>' );
					jQuery( response.candidates ).each(
						function ( indexInArray, valid ) {
							var $a = jQuery( '<a></a>' ).attr(
								{
									href: '#', class: 'source-link-choose-item'
								}
							);

							if ( valid.sitesourceimg ) {
								var $add = jQuery( '<span class="taxonomy-select-ui-choose-img"> </span>' )
									.css( 'background-image', 'url(' + valid.sitesourceimg + ')' );
								$a.append( $add );
							}
							$a.append( '<span class="taxonomy-select-ui-choose-text">' + valid.sitesource + ' </span>' );
							$a.append( '<span class="taxonomy-select-ui-choose-sub">ID:' + valid.external_id + '</span>' );

							var data     = valid;
							var onSelect = function () {
								cutemiSetLink( $link, data );
								tb_remove();
								return false;
							};
							$a.on( 'click', onSelect );
							$a.appendTo( $wrapper );
						}
					);
					jQuery( 'body' ).append( $wrapper );
					tb_show( cutemiData.choose_a_source, '#TB_inline?height=300&amp;width=400&amp;inlineId=cutemi_select_valid_source' );
				}

			}
		);
	}

	function cutemiSetLink( $link, data ) {
		var taxsInputs = {};

		if ( data.external_id ) {
			$link.external_id.val( data.external_id );
		}
		if ( data.title ) {
			$link.link_title.val( data.title );
		}
		if ( data.size ) {
			$link.part_size.val( data.size );
			$link.part_size.trigger( 'change' );
		}
		if ( data.offline && data.offline === 1 ) {
			$link.link_status.val( 1 );
			$link.link_status.trigger( 'change' );
		}
		if ( data.online && data.online === 1 ) {
			$link.link_status.val( '' );
			$link.link_status.trigger( 'change' );
		}
		if ( data.part_nro ) {
			$link.part_nro.val( data.part_nro );
			$link.part_nro.trigger( 'change' );
		}
		if ( data.sitesource ) {
			$link.site_source.val( data.sitesource );
			var selectMenu = $link.site_source.cutemiselectmenu( 'instance' );
			if ( selectMenu && selectMenu.refresh ) {
				selectMenu.refresh();
			}
		}
		//General:
		if ( data.title ) {
			cutemiSetPostTitle( data.title );
		}

		taxsInputs.cutemi_video_resolution   = jQuery( '[name="cutemi_video_resolution[0]"]' );
		taxsInputs.cutemi_video_bitrate      = jQuery( '[name="cutemi_video_bitrate[0]"]' );
		taxsInputs.cutemi_video_bitrate_mode = jQuery( '[name="cutemi_video_bitrate_mode[0]"]' );

		taxsInputs.cutemi_audio_langs        = jQuery( '[name="cutemi_audio_langs[0]"]' );
		taxsInputs.cutemi_audio_tech         = jQuery( '[name="cutemi_audio_tech[0]"]' );
		taxsInputs.cutemi_audio_channels     = jQuery( '[name="cutemi_audio_channels[0]"]' );
		taxsInputs.cutemi_audio_bitrate      = jQuery( '[name="cutemi_audio_bitrate[0]"]' );
		taxsInputs.cutemi_audio_bitrate_mode = jQuery( '[name="cutemi_audio_bitrate_mode[0]"]' );

		taxsInputs.cutemi_file_format = jQuery( '[name="cutemi_file_format"]' );

		jQuery( 'select.cutemi-tax-field' ).each(
			function () {
				taxsInputs[ jQuery( this ).attr( 'name' ) ] = jQuery( this );
			}
		);

		if ( data.taxonomies ) {
			jQuery.each(
				data.taxonomies,
				function ( i, v ) {
					if ( v.candidate_max && v.candidate_max.for ) {
						if ( taxsInputs[ i ] && taxsInputs[ i ].val() === '' ) {
							taxsInputs[ i ].val( v.candidate_max.for );
							var r = taxsInputs[ i ].cutemiselectmenu( 'instance' );
							if ( r && r.refresh ) {
								r.refresh();
							}
						}
					}
				}
			);
		}
	}

	function cutemiUpdateSizeFromParts() {
		var parts = [], size = 0;

		jQuery( '.field-part_size' ).each(
			function () {
				var $obj = jQuery( this ).parents( 'li' ).first(),
				partNro  = cutemiGetInputControl( $obj, '.field-part_nro' ).val();
				if ( ! parts[ 'part' + partNro ] ) {
					parts[ 'part' + partNro ] = [];
				}
				parts[ 'part' + partNro ].push( $obj );
			}
		);

		Object.keys( parts ).forEach(
			function ( key ) {
				jQuery.each(
					parts[ key ],
					function ( key, partLink ) {
						if ( cutemiGetInputControl( partLink, '.field-part_size' ).val() > 0 ) {
							size = size + parseInt( partLink.find( '.field-part_size' ).val(), 10 );
							return false;
						}
					}
				);
			}
		);
		jQuery( '[name="size"]' ).val( size ).trigger( 'change' );
	}

	/***************************************************
	 *            MediaInfo Data Extractor             *
	 ***************************************************/
	jQuery( mediaInfoExtractor );

	function mediaInfoExtractor() {
		var MediaInfoModule = null; //Module for handle video local file
		if ( typeof cutemiData.mediainfo_lib === 'undefined' ) {
			cutemiData.mediainfo_lib = 'buzz_port_cdn';
		}
		var $mediainfoField = jQuery( '#mediainfo' );
		var $containerBox   = $mediainfoField.parents( '.cutemi-metabox' ).parent();
		if ( $mediainfoField.length === 1 ) {
			var $mediainfoActions = jQuery( '<div>' ).addClass( 'cutemi-mediainfo-actions' );

			var $d1 = jQuery( '<div>' ).css( 'text-align', 'center' );
			var $d2 = $d1.clone();

			if ( typeof Promise !== 'undefined' ) {
				var $localLabel = jQuery( '<label>' )
					.html( cutemiData.select_local_video )
					.addClass( 'cutemi-adm-btn' )
					.attr( 'for', 'cutemi-mediainfo-file' );

				var $localInput = jQuery( '<input type="file">' )
					.addClass( 'cutemi-hidden-input' )
					.attr( 'id', 'cutemi-mediainfo-file' )
					.on(
						'change',
						function () {
							mediaInfoFileSelected( this );
						}
					);

				$mediainfoActions.append( $d1.append( $localLabel ).append( $localInput ) );
			} else {
				$mediainfoActions.append( $d1.append( jQuery( '<span> Update your Browser! </span>' ) ) );
			}

			var $extractBtn = jQuery( '<a href="#"> </a>' )
				.addClass( 'cutemi-adm-btn' ).hide()
				.html( cutemiData.load_data_from_mediainfo )
				.on( 'click', extractFromMediaInfo );

			var $mediaBtn = jQuery( '<a href="#"> </a>' )
				.addClass( 'cutemi-adm-btn' )
				.html( cutemiData.select_from_media_library )
				.on( 'click', mediaInfoFromWP );
			$d1.append( $mediaBtn );

			$mediainfoField.after( $mediainfoActions );
			$mediainfoActions.append( $d2.append( $extractBtn ) );
			$mediainfoField.on( 'change', showHideExtractorBtn );
			jQuery.cutemiOn( 'cutemi-file-mediainfo-loaded', showHideExtractorBtn );
			showHideExtractorBtn();
		}

		function showHideExtractorBtn() {
			if ( $mediainfoField.val().length > 0 ) {
				$extractBtn.show();
			} else {
				$extractBtn.hide();
			}
		}

		function loadMediaInfoAndRun( MediaInfoFileRuner ) {
			if ( MediaInfoModule === null ) {
				loadMediaInfoLibrary(
					function ( module ) {
						MediaInfoModule = module;
						MediaInfoFileRuner();
					}
				);
			} else {
				MediaInfoFileRuner();
			}
		}

		function mediaInfoFromUrlContinue( url, fileRemote ) {
			var CHUNK_SIZE = 1024 * 128;
			var readChunk  = function ( chunkSize, offset ) {
				return new Promise(
					function ( resolve, reject ) {
						if ( offset === fileRemote.size ) {
							resolve( new Uint8Array( '' ) );
							return;
						}
						if ( offset >= fileRemote.size ) {
							reject( new Error( 'offset > size' ) );
							return;
						}
						var req             = new XMLHttpRequest();
						req.withCredentials = true;
						req.responseType    = 'arraybuffer';
						req.addEventListener(
							'load',
							function () {
								resolve( new Uint8Array( req.response ) );
							}
						);
						[ 'abort', 'error' ].forEach(
							function ( evt ) {
								req.addEventListener(
									evt,
									function ( error ) {
										reject( error );
									}
								);
							}
						);
						req.open( 'GET', url );
						req.setRequestHeader( 'Range', 'bytes=' + offset + '-' + (offset + chunkSize - 1) );
						req.send();
					}
				);
			};

			var getSize = function () {
				return fileRemote.size;
			};

			if ( MediaInfoModule.MediaInfo ) {
				var offset     = 0;
				var processing = true;
				var MI         = new MediaInfoModule.MediaInfo();
				MI.Option( 'File_FileName', fileRemote.name );
				MI.Open_Buffer_Init( fileRemote.size, 0 );

				var finish = function () {
					MI.Close();
					MI.delete();
					processing = false;
				};

				var loop = function ( length ) {
					if ( processing ) {
						readChunk( length, offset ).then(
							function ( data ) {
								processChunk( data );
							}
						).catch(
							function ( reason ) {
								finish();
								alert( 'An error happened reading your file! ' + reason.stack );
							}
						);
					} else {
						finish();
					}
				};

				var processChunk = function ( e ) {
					// Send the buffer to MediaInfo
					var state = MI.Open_Buffer_Continue( e );

					//Test if there is a MediaInfo request to go elsewhere
					var seekTo = MI.Open_Buffer_Continue_Goto_Get();
					if ( seekTo === -1 ) {
						offset += e.byteLength;
					} else {
						offset = seekTo;
						MI.Open_Buffer_Init( fileRemote.size, seekTo ); // Inform MediaInfo we have seek
					}

					// Bit 3 set means finalized
					if ( state & 0x08 || e.byteLength < 1 || offset >= fileRemote.size ) {
						MI.Open_Buffer_Finalize();
						mediaInfoReady( MI.Inform(), fileRemote, MI );
						return;
					}

					loop( CHUNK_SIZE );
				};

				// Start
				loop( CHUNK_SIZE );
			} else {
				MediaInfoModule
					.analyzeData( getSize, readChunk )
					.then(
						function ( result ) {
							mediaInfoReady( result, fileRemote, MediaInfoModule );
						}
					)
					.catch(
						function ( error ) {
							alert( 'An error occured:' + error.stack );
						}
					);
			}
		}

		function mediaInfoFromUrl( url ) {
			jQuery.ajax(
				{
					type: 'HEAD', url: url
				}
			).done(
				function ( message, text, jqXHR ) {
					var fileName = url.substring( url.lastIndexOf( '/' ) + 1 );
					fileName     = fileName.split( '?' )[ 0 ];
					if ( ! jqXHR.getResponseHeader( 'Content-Length' ) ) {
						mediaInfoReady( '', false, false );
						return;
					}
					var fileRemote = {
						'type': 'url',
						'name': fileName,
						'size': Number( jqXHR.getResponseHeader( 'Content-Length' ) ),
						'url': url
					};
					mediaInfoFromUrlContinue( url, fileRemote );
				}
			).fail(
				function ( ) {
					mediaInfoReady( '', false, false );
				}
			);
		}

		function mediaInfoReady( inform, selfInput, MI ) {
			var file = (selfInput && selfInput.files && selfInput.files.length === 1) ? selfInput.files[ 0 ] : false;
			if ( ! file ) {
				//"file" from url
				file = (selfInput && selfInput.name) ? selfInput : false;
			}
			if ( -1 === inform.indexOf( 'Complete name' ) && file ) {
				var regex = /(General)(\s*)([^\s][\s\S]*)/gm;
				var subst = '$1$2Complete name                            : ' + file.name + '$2$3';
				inform    = inform.replace( regex, subst );
			}
			$mediainfoField.val( inform );
			$containerBox.removeClass( 'cutemi-link-loading' );
			jQuery.cutemiTrigger(
				'cutemi-file-mediainfo-loaded',
				{
					target: selfInput,
					mediainfo: MI,
					field: $mediainfoField
				}
			);
		}

		var frame = false;

		function mediaInfoFromWP( event ) {
			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.open();
				return;
			}

			// Create a new media frame
			frame = wp.media(
				{
					title: '', button: {
						text: cutemiData.use_this_video
					}, library: {
						type: [ 'video' ]
					}, multiple: false
				}
			);

			// When an image is selected in the media frame...
			frame.on(
				'select',
				function () {
					var attachments = frame.state().get( 'selection' );
					attachments.each(
						function ( attachment2 ) {
							var attachment = attachment2.toJSON();
							loadMediaInfoAndRun(
								function () {
									$containerBox.addClass( 'cutemi-link-loading' );
									if (
										typeof attachment.title === 'string' &&
										typeof attachment.subtype === 'string' &&
										typeof attachment.filesizeInBytes === 'number'
									) {
										var fileRemote = {
											'type': 'url',
											'name': attachment.title + '.' + attachment.subtype,
											'size': attachment.filesizeInBytes,
											'url': attachment.url
										};
										mediaInfoFromUrlContinue( attachment.url, fileRemote );
									} else {
										mediaInfoFromUrl( attachment.url );
									}
								}
							);
						}
					);
				}
			);

			// Finally, open the modal on click
			frame.open();
		}

		function mediaInfoFileSelected( selfInput ) {

			if ( selfInput.files && selfInput.files.length !== 1 ) {
				return false;
			}

			$containerBox.addClass( 'cutemi-link-loading' );
			var MediaInfoFileRuner = function () {
				if ( MediaInfoModule.MediaInfo ) {
					// Initialise MediaInfo
					var MI = new MediaInfoModule.MediaInfo();

					//Open the file
					MI.Open(
						selfInput.files[ 0 ],
						function () {
							MI.Option( 'Complete', '0' );
							mediaInfoReady( MI.Inform(), selfInput, MI );
							MI.Close();
							MI.delete();
						}
					);
				} else {
					//buzz port
					var file = selfInput.files[ 0 ];

					var getSize = function () {
						return file.size;
					};

					var readChunk = function ( chunkSize, offset ) {
						return new Promise(
							function ( resolve, reject ) {
								var reader    = new FileReader();
								reader.onload = function ( event ) {
									if ( event.target.error ) {
										reject( event.target.error );
									}
									resolve( new Uint8Array( event.target.result ) );
								};
								reader.readAsArrayBuffer( file.slice( offset, offset + chunkSize ) );
							}
						);
					};

					MediaInfoModule
						.analyzeData( getSize, readChunk )
						.then(
							function ( result ) {
								mediaInfoReady( result, selfInput, MediaInfoModule );
							}
						)
						.catch(
							function ( error ) {
								alert( 'An error occured:' + error.stack );
							}
						);
				}
			};

			loadMediaInfoAndRun( MediaInfoFileRuner );

			jQuery.cutemiTrigger( 'cutemi-file-selected', { target: selfInput } );
		}

		function extractFromMediaInfo() {
			if ( $mediainfoField.val() === '' ) {
				alert( 'First complete mediainfo field' );
				return false;
			}
			$containerBox.addClass( 'cutemi-link-loading' );
			var data = {
				mediainfo: $mediainfoField.val(), action: 'cutemi_mediainfo_extract', nonce: cutemiData.nonce_ajax
			};
			jQuery.post(
				cutemiData.ajaxUrl,
				data,
				function ( response ) {
					$containerBox.removeClass( 'cutemi-link-loading' );

					if ( response.nonce ) {
						cutemiData.nonce_ajax = response.nonce;
					}

					if ( response.general && response.general[ 0 ] && response.general[ 0 ].completename ) {
						cutemiSetPostTitle( response.general[ 0 ].completename );
					}

					jQuery( [ 'videos', 'audios', 'texts', 'general' ] ).each(
						function ( i, type ) {
							var streamList = window.cutemiStreams( type );
							if ( streamList === false ) {
								return;
							}
							jQuery( response[ type ] ).each(
								function ( indexInArray, stream ) {
									var setValues = {}, taxField;
									for ( taxField in stream.taxs ) {
										if ( stream.taxs.hasOwnProperty( taxField ) ) {
											setValues[ taxField ] = stream.taxs[ taxField ];
										}
									}
									for ( taxField in stream.vals ) {
										if ( stream.vals.hasOwnProperty( taxField ) ) {
											setValues[ taxField ] = stream.vals[ taxField ];
										}
									}
									streamList.setItem( indexInArray, setValues );
								}
							);
						}
					);
				}
			);
			return false;
		}
	}

	function loadMediaInfoLibrary( callback ) {
		var MediaInfoJs = document.createElement( 'script' );
		var baseUrl     = cutemiData.plugin_dir_url + 'admin/assets/third-party/';
		if ( cutemiData.mediainfo_lib === 'buzz_port_cdn' && 'WebAssembly' in window ) {
			MediaInfoJs.src = 'https://unpkg.com/mediainfo.js@0.1.7/dist/mediainfo.min.js';
		} else if ( cutemiData.mediainfo_lib === 'buzz_port' && 'WebAssembly' in window ) {
			MediaInfoJs.src = baseUrl + 'buzz/mediainfo.min.js';
		} else {
			if ( 'WebAssembly' in window && typeof Promise !== 'undefined' ) {
				MediaInfoJs.src = baseUrl + 'MediaInfo/MediaInfoWasm.js';
			} else {
				MediaInfoJs.src = baseUrl + 'MediaInfo/MediaInfo.js';
			}
		}
		document.body.appendChild( MediaInfoJs );
		MediaInfoJs.onload = function () {
			// Initialise emscripten module
			if ( typeof MediaInfoLib != 'undefined' ) {
				var MediaInfoModule = MediaInfoLib(
					{
						'postRun': function () {
							if ( typeof Promise !== 'undefined' && MediaInfoModule instanceof Promise ) {
								MediaInfoModule.then(
									function ( module ) {
										callback( module );
									}
								);
							} else {
								callback( MediaInfoModule );
							}
						}
					}
				);
			} else {
				//buzz
				MediaInfo(
					{ format: 'text' },
					function ( module ) {
						callback( module );
					}
				);
			}
		};

	}

	/*************************************************
	 *             Human file sizes                  *
	 *************************************************/

	jQuery( cutemiUpdateSizeHuman );
	jQuery.cutemiOn( 'cutemi_item_created', cutemiUpdateSizeHuman );

	function cutemiUpdateSizeHuman() {
		jQuery( '.field-part_size:not(.size-handle),.field-size:not(.size-handle)' ).each(
			function () {
				cutemiAddSizeHandle( jQuery( this ).find( 'input' ) );
			}
		);
	}

	function cutemiAddSizeHandle( $self ) {
		if ( jQuery( $self ).parents( '.cutemi-list-item-template' ).length > 0 ) {
			return;
		}
		if ( $self.hasClass( 'size-handle' ) ) {
			return;
		}

		var height     = getHeightElement( $self, 'outerHeight' );
		var marginLeft = 79 + parseFloat( $self.css( 'border-right-width' ) ) +
			parseFloat( $self.css( 'margin-right' ) );
		$self.addClass( 'size-handle' );
		var $human                      = jQuery( '<input>' ).val( '0B' ).css(
			{
				'width': '80px',
				'min-width': '80px',
				'margin-top': $self.css( 'margin-top' ),
				'margin-left': '-' + marginLeft + 'px',
				'box-sizing': 'border-box',
				'position': 'relative',
				'display': 'inline-block',
				'text-align': 'center',
				'line-height': $self.css( 'line-height' ),
				'background': '#B6D8E5',
				'white-space': 'nowrap',
				'border': 'solid ' + $self.css( 'border-top-width' ) + ' #242424',
				'color': '#000',
				'height': height,
				'border-top-right-radius': '3px',
				'padding': '0',
				'border-bottom-right-radius': '3px'
			}
		);
		$human[ 0 ].style.verticalAlign = $self[ 0 ].style.verticalAlign ? $self[ 0 ].style.verticalAlign : 'middle';
		$human.on(
			'change keyup',
			function () {
				var size = cutemiToByteSize( $human.val(), cutemiData.input_force_1024 );
				if ( size !== false ) {
					$self.val( size );
				}
			}
		).cutemiNoSubmit();

		var setHuman = function () {
			$human.val( cutemiHumanSize( $self.val(), false, 2 ) );
		};
		$self.css( 'padding-right', '80px' ).after( $human ).on( 'change keyup', setHuman ).cutemiNoSubmit();
		setHuman();
	}

	function cutemiHumanSize( bytes, siUnit, d_p ) {
		var k     = siUnit ? 1000 : 1024, useSI = ! ! siUnit, digits = d_p ? d_p : 0, unitsCount = 0,
			units = [ 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB' ], unit = -1, mult;

		if ( Math.abs( bytes ) < k ) {
			return bytes + ' B';
		}

		if ( useSI ) {
			units = [ 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
		}

		unitsCount = units.length;
		mult       = Math.pow( 10, digits );
		do {
			bytes /= k;
			++unit;
		} while ( Math.round( Math.abs( bytes ) * mult ) / mult >= k && unit < unitsCount - 1 );

		return bytes.toFixed( digits ) + ' ' + units[ unit ];
	}

	function cutemiToByteSize( sizeString, forceBased1024 ) {
		//remove whitespaces
		sizeString = sizeString.replace( /\s+/, '' );

		var valFloat = parseFloat( sizeString ),
			iUnits   = { 'B': 0, 'KIB': 1, 'MIB': 2, 'GIB': 3, 'TIB': 4, 'PIB': 5, 'EIB': 6, 'ZIB': 7, 'YIB': 8 },
			units    = { 'KB': 1, 'MB': 2, 'GB': 3, 'TB': 4, 'PB': 5, 'EB': 6, 'ZB': 7, 'YB': 8 },
			valUnit  = sizeString.substr( -3 ), based = 1024, power = 0;

		while ( /^(\d)$/.test( valUnit.substr( 0, 1 ) ) ) {
			valUnit = valUnit.substr( 1 );
		}
		//ignore case
		valUnit = valUnit.toUpperCase();

		if ( typeof iUnits[ valUnit ] !== 'undefined' ) {
			power = iUnits[ valUnit ];
		} else if ( units[ valUnit ] ) {
			based = forceBased1024 ? 1024 : 1000;
			power = units[ valUnit ];
		} else {
			return false;
		}

		return Math.round( valFloat * Math.pow( based, power ) );
	}

	/*************************************************
	 *             Human Duration                    *
	 *************************************************/

	jQuery( cutemiUpdateDurationHuman );

	function cutemiUpdateDurationHuman() {
		jQuery( '.field-duration:not(duration-handle)' ).each(
			function () {
				cutemiAddDurationHandle( jQuery( this ).find( 'input' ) );
			}
		);
	}

	function cutemiAddDurationHandle( $self ) {
		var height, $sep, $human, $hr, $mn, $sc;

		if ( $self.hasClass( 'duration-handle' ) ) {
			return;
		}

		$self.addClass( 'duration-handle' );

		height = getHeightElement( $self, 'outerHeight' );

		$sep                            = jQuery( '<span>' ).html( ':' ).css(
			{
				'width': '3px',
				'padding': '0',
				'line-height': (height - 10) + 'px',
				'margin-top': '5px',
				'text-align': 'center',
				'vertical-align': 'middle'
			}
		);
		$human                          = jQuery( '<div>' ).addClass( 'cutemi-duration-human' ).css(
			{
				'width': '80px',
				'margin-top': $self.css( 'margin-top' ),
				'margin-left': '-' + (79 + parseFloat( $self.css( 'border-right-width' ) ) + parseFloat( $self.css( 'margin-right' ) )) + 'px',
				'box-sizing': 'border-box',
				'position': 'relative',
				'display': 'inline-block',
				'text-align': 'center',
				'line-height': $self.css( 'line-height' ),
				'background': '#B6D8E5',
				'white-space': 'nowrap',
				'border': 'solid ' + $self.css( 'border-top-width' ) + ' #242424',
				'color': '#000',
				'height': height + 'px',
				'border-top-right-radius': '3px',
				'border-bottom-right-radius': '3px'
			}
		);
		$human[ 0 ].style.verticalAlign = $self[ 0 ].style.verticalAlign ? $self[ 0 ].style.verticalAlign : 'middle';

		$hr = jQuery( '<input>' ).attr( 'title', 'h' ).appendTo( $human ).after( $sep );
		$mn = jQuery( '<input>' ).attr( 'title', 'm' ).appendTo( $human ).after( $sep.clone() );
		$sc = jQuery( '<input>' ).attr( 'title', 's' ).appendTo( $human );

		var padZero = function ( s ) {
			s        = '' + s;
			var sLen = s.length;
			while ( sLen < 2 ) {
				s    = '0' + s;
				sLen = s.length;
			}
			return s;
		};

		var setHuman = function () {
			$hr.val( padZero( Math.floor( $self.val() / 3600 ) ) );
			$mn.val( padZero( Math.floor( ($self.val() - (parseInt( $hr.val(), 10 ) * 3600)) / 60 ) ) );
			$sc.val( padZero( Math.floor( $self.val() - (parseInt( $hr.val(), 10 ) * 3600) - (parseInt( $mn.val(), 10 ) * 60) ) ) );
		};

		var setSecs = function () {
			$self.val( parseInt( $hr.val(), 10 ) * 3600 + parseInt( $mn.val(), 10 ) * 60 + parseInt( $sc.val(), 10 ) );
		};

		$human.find( 'input' ).css(
			{
				'width': '23px',
				'padding': '0',
				'height': (height - 10) + 'px',
				'margin': '0',
				'margin-top': '5px',
				'text-align': 'center',
				'background': 'transparent',
				'border': 'none',
				'box-shadow': 'none'

			}
		).on( 'change keyup', setSecs ).focus(
			function () {
				jQuery( this ).select();
			}
		).attr( 'inputmode', 'numeric' ).cutemiFilter(
			function ( value ) {
				return /^\d*$/.test( value );
			}
		).cutemiNoSubmit();

		$self.css( 'padding-right', '80px' ).after( $human ).on( 'change keyup', setHuman ).cutemiNoSubmit();

		setHuman();
	}

	function getHeightElement( $target, method, margin ) {
		var outer, dim, lh;

		outer = /(outer)/.test( method );
		dim   = outer ? $target[ method ]( typeof margin === 'undefined' ? false : margin ) : $target[ method ]();

		if ( dim > 0 ) {
			return dim;
		}

		lh = parseFloat( $target.css( 'line-height' ) );
		if ( lh > 0 ) {
			if ( method !== 'height' ) {
				lh = lh + parseFloat( $target.css( 'padding-top' ) ) + parseFloat( $target.css( 'padding-bottom' ) );
				if ( method !== 'innerHeight' ) {
					lh = lh + parseFloat( $target.css( 'border-top' ) ) + parseFloat( $target.css( 'border-bottom' ) );
					if ( outer ) {
						lh = lh + parseFloat( $target.css( 'margin-top' ) ) + parseFloat( $target.css( 'margin-bottom' ) );
					}
				}
			}
			return lh;
		}
		return 30;
	}

})();
