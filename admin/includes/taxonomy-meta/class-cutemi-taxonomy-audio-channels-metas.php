<?php

class CUTEMI_Taxonomy_Audio_Channels_Metas extends CUTEMI_Taxonomy_Customs_Metas {

	public $custom_tax_name = 'cutemi_audio_channels';

	public function __construct() {
		$this->tax_name    = $this->custom_tax_name;
		$this->meta_fields = array(
			array(
				'label' => __( 'Tags for the extraction from the video name/mediainfo', 'cute-mediainfo' ),
				'id'    => 'tags',
				'type'  => 'text',
				'desc'  => __( 'Separate by comma. Prefix "(?i)" for case insensitive, and (?: |-|\.|) for space, "-", "." or nothing. Ex: (?i)DTS(?: |-|\.|)HD(?: |-|\.|)MA(?: |-|\.|)5(?: |-|\.|)1, (?i)DD5(?: |-|\.|)1', 'cute-mediainfo' ),
			),
		);

		parent::__construct();
	}

	public function init() {
		$this->meta_fields = array(
			array(
				'label' => __( 'Tags for the extraction from the video name/mediainfo', 'cute-mediainfo' ),
				'id'    => 'tags',
				'type'  => 'text',
				'desc'  => __( 'Separate by comma. Prefix "(?i)" for case insensitive, and (?: |-|\.|) for space, "-", "." or nothing. Ex: (?i)DTS(?: |-|\.|)HD(?: |-|\.|)MA(?: |-|\.|)5(?: |-|\.|)1, (?i)DD5(?: |-|\.|)1', 'cute-mediainfo' ),
			),
		);

		parent::init();
	}

}

new CUTEMI_Taxonomy_Audio_Channels_Metas();
