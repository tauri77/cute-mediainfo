<?php

class CUTEMI_Taxonomy_Site_Sources_Metas extends CUTEMI_Taxonomy_Customs_Metas {

	public $custom_tax_name = 'cutemi_site_source';

	public function __construct() {
		$this->tax_name = $this->custom_tax_name;

		parent::__construct();
	}

	public function init() {
		$this->meta_fields = array(
			array(
				'label' => __( 'Extract File ID Regexs', 'cute-mediainfo' ),
				'id'    => 'extract_regexs',
				'type'  => 'textarea',
				'raw'   => true,
				'desc'  => __( 'Regex per line, Ex: https?://source-domain.com/file/([a-zA-Z0-9_-]+)', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Url template', 'cute-mediainfo' ),
				'id'    => 'url_template',
				'type'  => 'text',
				'desc'  => __( 'Ex: https://source-domain.com/file/%ID%', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Url template for extract title', 'cute-mediainfo' ),
				'id'    => 'url_template_title',
				'type'  => 'text',
				'desc'  => __( 'Ex: https://source-domain.com/file/%ID%', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Url template for extract size', 'cute-mediainfo' ),
				'id'    => 'url_template_size',
				'type'  => 'text',
				'desc'  => __( 'Ex: https://source-domain.com/file/%ID%', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Regex Title', 'cute-mediainfo' ),
				'id'    => 'regex_title',
				'type'  => 'text',
				'raw'   => true,
				'desc'  => __( 'Regex to extract title from page. <br>Ex: class="title"[^>]*>\s*([^<]*)', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Regex Size', 'cute-mediainfo' ),
				'id'    => 'regex_size',
				'type'  => 'text',
				'raw'   => true,
				'desc'  => __( 'Regex to extract size from page. <br>Ex: [\s:]((?:\d*\.?\d+)\s*(?(?=[KMGT])(?:[KMGT])(?:i?B)?|B))', 'cute-mediainfo' ),
			),
			array(
				'label' => __( 'Proxy lists', 'cute-mediainfo' ),
				'id'    => 'proxy_list',
				'type'  => 'textarea',
				'raw'   => true,
				'desc'  => __( 'One proxy per line. Format: 11.12.123.123:8080[:http|socks4|socks5][:user:pass], Ex: 11.12.123.123:8080:http:user:pass', 'cute-mediainfo' ),
			),
			array(
				'label'          => __( 'Hidden', 'cute-mediainfo' ),
				'id'             => 'hidden',
				'type'           => 'checkbox',
				'checkbox_label' => 'Don\'t show these links to users',
				'desc'           => '',
				'default'        => '',
			),
		);
		parent::init();
	}

}

new CUTEMI_Taxonomy_Site_Sources_Metas();
