<?php

$generals = array();
$v        = false;
if ( isset( $mediainfo['videos'] ) && isset( $mediainfo['videos'][0] ) ) {
	$v = $mediainfo['videos'][0];
}

$generals[] = ! empty( $v ) && isset( $v['resolution'] ) && isset( $v['resolution']['text'] ) ? $v['resolution']['text'] : '';
$generals[] = ! empty( $mediainfo['format'] ) && isset( $mediainfo['format']['text'] ) ? $mediainfo['format']['text'] : '';
$generals[] = ! empty( $mediainfo['size'] ) && is_numeric( $mediainfo['size'] ) ?
	cutemi_human_filesize( $mediainfo['size'] ) : '';
$generals[] = ! empty( $mediainfo['duration'] ) && is_numeric( $mediainfo['duration'] ) ?
	cutemi_human_duration( $mediainfo['duration'] ) : '';
$generals[] = ! empty( $v ) && isset( $v['tech'] ) && isset( $v['tech']['text'] ) ? $v['tech']['text'] : '';

foreach ( $generals as $l => $general ) {
	if ( empty( $general ) ) {
		unset( $generals[ $l ] );
	}
}
?>
<p>
	<?php
	if ( ! empty( $generals ) ) {
		end( $generals );
		$last_general = key( $generals );
		foreach ( $generals as $l => $general ) {
			echo '<span>' . esc_html( $general ) . '</span>';
			if ( $l !== $last_general ) {
				echo ' | ';
			}
		}
	}
	if ( is_array( $mediainfo['audios'] ) && ! empty( $mediainfo['audios'] ) ) {
		echo '. <br>';
		$rows = array();
		foreach ( $mediainfo['audios'] as $k => $audio ) {
			if ( $audio['tech'] ) {
				$row = '';
				if ( ! empty( $audio['tech'] ) && ! empty( $audio['tech']['text'] ) ) {
					$row .= $audio['tech']['text'];
					$row .= ' ';
				}
				if ( ! empty( $audio['channels'] ) && ! empty( $audio['channels']['text'] ) ) {
					$row .= $audio['channels']['text'];
					$row .= ' ';
				}
				if ( ! empty( $audio['channels'] ) && ! empty( $audio['lang']['text'] ) ) {
					$row .= $audio['lang']['text'];
				}
				if ( ! empty( $row ) ) {
					$rows[] = $row;
				}
			}
		}
		echo esc_html( implode( ' | ', $rows ) );
	}
	if ( is_array( $mediainfo['texts'] ) && ! empty( $mediainfo['texts'] ) ) {
		echo '. <br>';
		$rows = array();
		foreach ( $mediainfo['texts'] as $k => $text ) {
			if ( $text['format'] || $text['lang'] ) {
				$row = '';
				if ( ! empty( $text['format'] ) && ! empty( $text['format']['text'] ) ) {
					$row .= $text['format']['text'];
					$row .= ' ';
				}
				if ( ! empty( $text['lang'] ) && ! empty( $text['lang']['text'] ) ) {
					$row .= $text['lang']['text'];
				}
				if ( ! empty( $row ) ) {
					$rows[] = $row;
				}
			}
		}
		echo esc_html( implode( ' | ', $rows ) );
		echo '.';
	}

	?>
</p>
