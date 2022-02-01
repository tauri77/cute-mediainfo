<?php

class CUTEMI_Taxonomy_Text_Langs_Metas extends CUTEMI_Taxonomy_Customs_Metas {

	public $custom_tax_name = 'cutemi_text_langs';

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
				'desc'  => __( 'Separate by comma. Prefix "(?i)" for case insensitive, and (?: |-|\.|) for space, "-", "." or nothing. Ex: SUBFRENCH', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'ISO 639-1 Code', 'cute-mediainfo' ),
				'id'    => 'code_one',
				'type'  => 'text',
				'desc'  => __( 'Check: https://www.loc.gov/standards/iso639-2/php/code_list.php or https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes, Ex: es', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'ISO 639-2 Code', 'cute-mediainfo' ),
				'id'    => 'code_two',
				'type'  => 'text',
				'desc'  => __( 'Ex: spa', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'ISO 3166-1 Region Code', 'cute-mediainfo' ),
				'id'    => 'region_code',
				'type'  => 'text',
				'desc'  => __( 'Ex: MX', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Native', 'cute-mediainfo' ),
				'id'    => 'native',
				'type'  => 'text',
				'desc'  => __( 'Ex: Español', 'cute-mediainfo' ),
			),
		);

		$this->meta_columns = array(
			'native' => array(
				'label'    => 'Native',
				'type'     => 'CHAR',
				'position' => 2,
				'sortable' => true,
			),
		);
		parent::init();
	}

}

new CUTEMI_Taxonomy_Text_Langs_Metas();
