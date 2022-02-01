<?php
/** @noinspection DuplicatedCode */
/** @noinspection HtmlDeprecatedTag */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_XFileSharing_Data_Extract extends CUTEMI_Link_Source_Base {

	public $source   = '';
	public $domain   = '';
	public $regex404 = '@(?i)(>\s*File Not Found\s*<|>\s*The file was removed by|Reason for deletion:\n|>\s*The file expired|>\s*File could not be found due to expiration or removal by the file owner|>\s*The file of the above link no longer exists|>\s*video you are looking for is not found)@';

	public function supports_mass_linkcheck_over_website() {
		return false;
	}

	public function mass_linkchecker_website( $urls ) {
		if ( null === $urls || 0 === count( $urls ) ) {
			return false;
		}

		$ret                    = array();
		$linkchecker_has_failed = false;
		$check_type_current     = null;
		/* Checks link checking via: examplehost.com/?op=checkfiles AND examplehost.com/?op=check_files */
		$check_type_old                   = 'checkfiles';
		$check_type_new                   = 'check_files';
		$check_type_last_used_and_working = $this->get_property( 'ALT_AVAILABLECHECK_LAST_WORKING', null );
		$check_url                        = null;
		$linkcheck_type_try_count         = 0;

		$check_form = null;
		/* Check if the mass-linkchecker works and which check we have to use */
		while ( $linkcheck_type_try_count <= 1 ) {
			if ( null !== $check_type_current ) {
				/* No matter which checkType we tried first - it failed and we need to try the other one! */
				if ( $check_type_current === $check_type_new ) {
					$check_type_current = $check_type_old;
				} else {
					$check_type_current = $check_type_new;
				}
			} elseif ( $this->prefer_availablecheck_filesize_alt_type_old() ) {
				/* Old checkType forced? */
				$check_type_current = $check_type_old;
			} elseif ( ! empty( $check_type_last_used_and_working ) ) {
				/* Try to re-use last working method */
				$check_type_current = $check_type_last_used_and_working;
			} else {
				/* First launch */
				$check_type_current = $check_type_new;
			}
			/*
			 * Sending the Form without a previous request might e.g. fail if the website requires "www." but
			 * supports_availablecheck_filesize_alt_fast returns false.
			 */

			$check_url = '/?op=' . $check_type_current;

			/* Get- and prepare Form */
			if ( $this->supports_availablecheck_filesize_alt_fast() ) {
				/* Quick way - we do not access the page before and do not need to parse the Form. */
				$check_form                      = array();
				$check_form['method']            = 'POST';
				$check_form['action']            = $check_url;
				$check_form['params']            = array();
				$check_form['params']['op']      = $check_type_current;
				$check_form['params']['process'] = 'Check+URLs';
			} else {
				/* Try to get the Form IF NEEDED as it can contain tokens which would otherwise be missing. */
				$this->browser->simple_get( $check_url );

				$check_form = null;
				$forms      = $this->browser->get_forms();
				foreach ( $forms as $form ) {
					if ( isset( $form['params']['op'] ) && $form['params']['op'] === $check_type_current ) {
						$check_form = $form;
					}
				}
				if ( null === $check_form ) {
					//Failed to find Form for checkType: $checkTypeCurrent;
					$linkcheck_type_try_count ++;
					continue;
				}
			}

			if ( empty( $check_form['action'] ) ) {
				$check_form['action'] = $check_url;
			}
			$check_form['params']['list'] = implode( '\n', $urls );

			$this->browser->submit_form( $check_form );

			/*
			 * Some hosts will not display any errorpage but also we will not be able to find any of our checked file-IDs inside
			 * the html --> Use this to find out about non-working linkchecking method!
			 */
			$example_fuid = $this->get_FUID_from_URL( $urls[0] );
			if (
				404 === $this->browser->response_code ||
				strpos( $this->browser->response, $check_type_current ) === false ||
				strpos( $this->browser->response, $example_fuid ) === false
			) {
				/*
				 * This method of linkcheck is not supported - increase the counter by one to find out if ANY method worked in
				 * the end.
				 */
				//Failed to find check_files Status via checkType: $checkTypeCurrent
				$linkcheck_type_try_count ++;
				continue;
			} else {
				break; //OK
			}
		}

		foreach ( $urls as $dl ) {
			$row = $this->mass_linkchecker_parse_file_info( $dl );
			if ( 'UNCHECKED' === $row ) {
				//Failed to find any information for current DownloadLink --> Possible mass-linkchecker failure
				$linkchecker_has_failed = true;
				continue;
			} else {
				$ret[ $dl ] = $row;
			}
		}
		if ( ! $linkchecker_has_failed ) {
			$this->set_property( 'ALT_AVAILABLECHECK_LAST_WORKING', $check_type_current );
		}
		/* else
			//Seems like checkfiles availablecheck is not supported by this host
			set_property "ALT_AVAILABLECHECK_LAST_FAILURE_TIMESTAMP" =>> time()
		}*/

		if ( $linkchecker_has_failed ) {
			return false;
		}

		return $ret;
	}

	public function get_property( $key, $default ) {
		$option  = get_option( 'cutemi_sources_internals', array() );
		$arr_key = $key . '_' . $this->source;
		if ( isset( $option[ $arr_key ] ) ) {
			return $option[ $arr_key ];
		}

		return $default;
	}

	public function prefer_availablecheck_filesize_alt_type_old() {
		return false;
	}

	public function supports_availablecheck_filesize_alt_fast() {
		return true;
	}

	public function get_FUID_from_URL( $url ) {
		$pattern = '`[^/]*//[^/]*(?:/(?:th|i)/\d+/|/d/|/file/|/embed-|/)([0-9a-z]+)`i';
		if ( preg_match( $pattern, $url, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	/**
	 * Parses and sets file info returned after doing a mass-linkchecking request to a an XFS website.
	 *
	 * @param $dl
	 *
	 * @return array|string
	 */
	public function mass_linkchecker_parse_file_info( $dl ) {

		$fuid               = $this->get_FUID_from_URL( $dl );
		$is_new_linkchecker = true;
		$html_for_fuid      = $this->regex( '(<tr>(?:(?!</?tr>)[\S\s])*?' . $fuid . '(?:(?!</?tr>)[\S\s])*?</tr>)', false, - 1 );
		if ( empty( $html_for_fuid ) ) {
			$html_for_fuid      = $this->regex( "<font color=\'(?:green|red)\'>[^>]*?" . $fuid . '[^>]*?</font>', false, - 1 );
			$is_new_linkchecker = false;
		}
		if ( null === $html_for_fuid ) {
			return 'UNCHECKED';
		}
		//<tr><td>https://down.fast-down.com/itf2e1grbw0a</td><td style="color:red;">Not found!</td><td></td></tr>
		$is_offline = null;
		if ( $is_new_linkchecker ) {
			$is_offline = preg_match( '@Not found@i', $html_for_fuid );
		} else {
			$is_offline = preg_match( "@<font color='red@i", $html_for_fuid );
		}
		$data = array();
		if ( $is_offline ) {
			$data['offline'] = 1;
		}
		if ( preg_match( '@<td>([0-9.,]+\s*(?:KB|MB|GB|B))@i', $html_for_fuid, $tabla_data ) ) {
			$data['size_str'] = $tabla_data[1];
			$data['online']   = 1;
		} elseif ( preg_match( '@\s+\(([0-9.,]+\s*(?:KB|MB|GB|B))\)\s*</td>@i', $html_for_fuid, $tabla_data ) ) {
			$data['size_str'] = $tabla_data[1];
			$data['online']   = 1;
		}
		if ( ! isset( $data['online'] ) ) {
			if ( preg_match( "@<font color='green@i", $html_for_fuid ) ) {
				$data['online'] = 1;
			}
		}

		return $data;
	}

	public function set_property( $key, $value ) {
		$option             = get_option( 'cutemi_sources_internals', array() );
		$arr_key            = $key . '_' . $this->source;
		$option[ $arr_key ] = $value;

		return update_option( 'cutemi_sources_internals', $option, false );
	}

	public function complete_data( $valid, $link ) {

		//fix end with .html title
		if (
			! empty( $valid['title'] ) &&
			'.html' === substr( $valid['title'], strlen( $valid['title'] ) - 5, 5 )
		) {
			$valid['title'] = substr( $valid['title'], 0, strlen( $valid['title'] ) - 5 );
		}

		if ( ! empty( $valid['size'] ) && ! empty( $valid['title'] ) ) {
			return $valid;
		}

		if ( empty( $this->source ) ) {
			$this->source = $valid['sitesource'];
		}

		if ( empty( $this->domain ) ) {
			$parse = wp_parse_url( $valid['urls']['link'] );
			if ( empty( $parse['host'] ) ) {
				return $valid;
			}
			$this->domain = $parse['host'];
		}

		$this->browser->init( $this->domain );

		$this->browser->set_cookie_value( 'lang', 'english' );
		$this->browser->set_cookie_value( 'file_code', $valid['external_id'] );

		$url = 'https://' . $this->domain . '/' . $valid['external_id'];

		$html = '';

		if ( empty( $html ) ) {
			$html                  = $this->browser->simple_get( $url );
			$valid['html'][ $url ] = $html;

			$_url = $this->browser->last_url;

			if ( ! empty( $_url ) ) {
				$this->browser->set_referer( $_url );
				$this->browser->set_url( $_url );
				$this->browser->last_url = $_url;
			}
		}

		if ( ! empty( $this->regex404 ) && preg_match( $this->regex404, $html ) ) {
			$valid['offline'] = 1;
		}

		$valid = $this->scanInfo( $valid );
		if ( empty( $valid['size'] ) && ! empty( $valid['size_str'] ) ) {
			$valid['size'] = cutemi_to_byte_size( $valid['size_str'] );
		}

		if ( empty( $valid['title'] ) || empty( $valid['size'] ) ) {
			$forms = $this->browser->get_forms();
			foreach ( $forms as $form ) {
				if ( isset( $form['params']['op'] ) && preg_match( '@download[1-3]@i', $form['params']['op'] ) ) {
					if ( isset( $form['params']['adblock_detected'] ) ) {
						$form['params']['adblock_detected'] = '0';
					}
					if ( ! isset( $form['params']['method_free'] ) ) {
						$form['params']['method_free'] = 'Liberta Descarga >>';
					}
					if ( strpos( $form['code'], 'g-recaptcha' ) !== false ) {
						continue;
					}

					$html = $this->browser->submit_form( $form );

					$valid['html'][ 'form_' . $form['action'] ] = $html;
					$valid                                      = $this->scanInfo( $valid );

					if ( empty( $valid['size'] ) && ! empty( $valid['size_str'] ) ) {
						$valid['size'] = cutemi_to_byte_size( $valid['size_str'] );
					}
					break;
				}
			}
		}

		if (
			( empty( $valid['title'] ) || empty( $valid['size'] ) ) &&
			$this->supports_mass_linkcheck_over_website()
		) {
			$res = $this->mass_linkchecker_website( array( $url ) );
			if ( is_array( $res ) && isset( $res[ $url ] ) ) {
				if ( empty( $valid['size'] ) && ! empty( $res[ $url ]['size_str'] ) ) {
					$valid['size_str'] = $res[ $url ]['size_str'];
					$valid['size']     = cutemi_to_byte_size( $res[ $url ]['size_str'] );
				}

				if ( empty( $valid['offline'] ) && ! empty( $res[ $url ]['offline'] ) ) {
					$valid['offline'] = $res[ $url ]['offline'];
				}
			}
		}

		if ( empty( $valid['title'] ) && $this->supports_availablecheck_filename_abuse() ) {
			$filename = $this->getFnameViaAbuseLink( $url );
			if ( 'NOT_FOUND' === $filename ) {
				$valid['offline'] = 1;
			} elseif ( ! empty( $filename ) ) {
				$valid['title'] = $filename;
			}
		}

		return $valid;
	}

	public function scanInfo( $file_info ) {
		$sharebox0 = 'copy\(this\);.+>(.+) - ([\d\.]+ (?:B|KB|MB|GB))</a></textarea>[rnt ]+</div>';
		$sharebox1 = 'copy\(this\);.+\](.+) - ([\d\.]+ (?:B|KB|MB|GB))\[/URL\]';
		/* 2019-05-08: 'Forum Code': Sharebox with filename & filesize (bytes), example: brupload.net, qtyfiles.com */
		$sharebox2 = '\[URL=https?://(?:www\.)?[^/"]+/' . $file_info['external_id'] .
						'[^\]]*?\]([^"]*?)\s*\-\s*(\d+)\[/URL\]';
		/* First found for pixroute.com URLs */
		$sharebox2_without_filesize = '\[URL=https?://(?:www\.)?[^/"]+/' . $file_info['external_id'] .
										'/([^<>"/\]]*?)(?:\.html)?\]';
		/*
		 * 2019-05-21: E.g. uqload.com, vidoba.net - this method will return a 'cleaner' filename than in other places - their titles will
		 * often end with " mp4" which we have to correct later!
		 */
		$sharebox3_videohost = '\[URL=https?://[^/]+/' . $file_info['external_id'] .
								'[^/<>\]]*?\]\[IMG\][^<>"\[\]]+\[/IMG\]([^<>"]+)\[/URL\]';
		/* standard traits from base page */
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex(
				'You have requested.*?https?://(?:www\\.)?[^/]+/' .
				$file_info['external_id'] . '/([^<>"]+)<'
			);
		}
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( 'name="fname" (?:type="hidden" )?value="(.*?)"' );
		}
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( '<h2>.*?Download File(?:<span>)?\s*(.*?)\s*(</span>)?\s*</h2>' );
		}
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( '<td(?:(?!</?td>)[\s\S])*?Download\s+File(?:(?!<td)[\s\S])*<td[^>]*>\s*([^<]+?)\s*<' );
		}
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( 'Filename:?\s*(<[^>]+>\s*)+?([^<>"]+)', true, 1 );
		}

		/* Next - details from sharing boxes (new RegExes to old) */
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( $sharebox2 );
			if ( empty( $file_info['title'] ) ) {
				$file_info['title'] = $this->regex( $sharebox2_without_filesize );
			}
			if ( empty( $file_info['title'] ) ) {
				$file_info['title'] = $this->regex( $sharebox1 );
				if ( empty( $file_info['title'] ) ) {
					$file_info['title'] = $this->regex( $sharebox0 );
				}
				if ( empty( $file_info['title'] ) ) {
					/* Link of the box without filesize */
					$file_info['title'] = $this->regex(
						'onFocus="copy\(this\);">https?://(?:www\.)?[^/]+/' .
						$file_info['external_id'] . '/([^<>"]*?)</textarea'
					);
				}
			}
		}
		/* Next - RegExes for videohosts */
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( $sharebox3_videohost );
			if ( empty( $file_info['title'] ) ) {
				/* 2017-04-11: Typically for XVideoSharing sites */
				$file_info['title'] = $this->regex( '<title>\s*Watch(?:ing)?\s*([^<>"]+)\s*</title>' );
			}
		}
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( 'class="dfilename">([^<>"]*?)<' );
		}
		if (
			( empty( $file_info['title'] ) || strtolower( $file_info['title'] ) === 'no title' ) &&
			! empty( $this->regex( '/embed-' . $file_info['external_id'] . '\.html' ) )
		) {
			/* 2019-10-15: E.g. vidoza.net */
			$cur_file_name = $this->regex( 'var\s*curFileName\s*=\s*"(.*?)"' );
			if ( ! empty( $cur_file_name ) ) {
				$file_info['title'] = $cur_file_name;
			}
		}
		/*
		 * 2019-05-16: Experimental RegEx to find 'safe' filesize traits which can always be checked, regardless of the
		 * 'supports_availablecheck_filesize_html' setting:
		 */
		if ( empty( $file_info['size_str'] ) ) {
			$file_info['size_str'] = $this->regex( $sharebox2, true, 1 );
		}
		/* 2019-07-12: Example: Katfile.com */
		if ( empty( $file_info['size_str'] ) ) {
			$file_info['size_str'] = $this->regex( 'id\s*=\s*"fsize[^"]*"\s*>\s*([0-9\.]+\s*[MBTGK]+)\s*<' );
		}
		if ( empty( $file_info['size_str'] ) ) {
			/* 2019-07-12: Example: Katfile.com */
			$file_info['size_str'] = $this->regex( 'class\s*=\s*"statd"\s*>\s*size\s*</span>\s*<span>\s*([0-9\.]+\s*[MBTGK]+)\s*<' );
		}
		if ( empty( $file_info['size_str'] ) ) {
			/* 2020-08-10: E.g. myqloud.org */
			$file_info['size_str'] = $this->regex( 'Download[\s\S]{40,900}Original[\s\S]{0,50}\s*(?:</a>)?\s*</(?:td|h[1-5])>\s*<(?:td|span)[^>]*>[^<]*?([0-9\.]*\s*(?:B|KB|MB|GB|TB))\s*<' );
		}
		if ( $this->supports_availablecheck_filesize_html() ) {
			if ( empty( $file_info['size_str'] ) ) {
				$file_info['size_str'] = $this->regex( $sharebox0, true, 1 );
				if ( empty( $file_info['size_str'] ) ) {
					$file_info['size_str'] = $this->regex( $sharebox1, true, 1 );
				}
			}
			if ( empty( $file_info['size_str'] ) ) {
				$file_info['size_str'] = $this->regex( '\(([0-9]+) bytes\)' );
				if ( empty( $file_info['size_str'] ) ) {
					$file_info['size_str'] = $this->regex( "</font>[ ]+\(([^<>\"'/]+)\)(.*?)</font></font>[ ]+\(([^<>\"'/]+)\)(.*?)</font>" );
				}
			}
			if ( empty( $file_info['size_str'] ) ) {
				$file_info['size_str'] = $this->regex( '(?:>\s*|\(\s*|"\s*|\[\s*|\s+)([0-9\.]+(?:\s+|\&nbsp;)?(TB|GB|MB|KB)(?!ps|/s|\s*Storage|\s*Disk|\s*Space))' );
			}
		}

		if (
			! empty( $file_info['size_str'] ) &&
			strtoupper( substr( $file_info['size_str'], strlen( $file_info['size_str'] ) - 1, 1 ) ) !== 'B'
		) {
			$file_info['size_str'] = $file_info['size_str'] . 'B';
		}

		return $file_info;
	}

	public function supports_availablecheck_filesize_html() {
		return true;
	}

	public function supports_availablecheck_filename_abuse() {
		return true;
	}

	public function getFnameViaAbuseLink( $dl ) {
		$this->browser->simple_get( '/?op=report_file&id=' . $this->get_FUID_from_URL( $dl ) );
		/*
		 * ONLY "No such file" as response might always be wrong and should be treated as a failure!
		 * Example: xvideosharing.com
		 */
		if ( ! empty( $this->regex( '(>\s*No such file[\n\s]*<)', false ) ) ) {
			return 'NOT_FOUND';
		}
		$filename = $this->regexFilenameAbuse();
		if ( null !== $filename ) {
			return $filename;
		}

		return '';
	}

	/** Part of getFnameViaAbuseLink(). */
	public function regexFilenameAbuse() {
		$filename     = null;
		$filename_src = $this->regex( '<b>Filename\s*:?\s*<[^\n]+</td>', true, - 1 );
		if ( null !== $filename_src ) {
			$filename = $this->regex( $filename_src, '>([^>]+)</td>$' );
		}
		if ( null === $filename ) {
			/*e.g. sama-share.com, pandafiles.com */
			$filename = $this->regex( 'name="file_name"[^>]*value="([^<>"]+)"' );
		}
		if ( null === $filename ) {
			/*New XFS style e.g. userupload.net */
			$filename = $this->regex( '<label>\s*Filename\s*</label>\s*<input[^>]*class="form-control form-control-plaintext"[^>]*value="([^"]+)"' );
		}

		return $filename;
	}

}
