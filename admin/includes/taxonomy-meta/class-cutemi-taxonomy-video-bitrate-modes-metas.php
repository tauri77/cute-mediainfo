<?php

class CUTEMI_Taxonomy_Video_Bitrate_Modes_Metas extends CUTEMI_Taxonomy_Customs_Metas {

	public $custom_tax_name = 'cutemi_video_bitrate_mode';

	public function __construct() {
		$this->tax_name = $this->custom_tax_name;

		parent::__construct();
	}

	public function init() {
		$this->meta_fields = array(
			array(
				'label' => __( 'Tags for the extraction from the video name/mediainfo', 'cute-mediainfo' ),
				'id'    => 'tags',
				'type'  => 'text',
				'desc'  => __( 'Separate by comma. Prefix "(?i)" for case insensitive. Ex: (?i)VBR', 'cute-mediainfo' ),
			),
		);
		parent::init();
	}
}

new CUTEMI_Taxonomy_Video_Bitrate_Modes_Metas();
