<?php

class Plugin_Gallery extends Plugin
{

	protected $slug;
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('files/files', $config);
		$this->CI->load->database();
		$this->uri = $this->CI->uri->uri_string();
	}

	public function fetch_gallery_images($name)
	{
		// if namespace passed and its not pages then we do something else to get da correct data
		$namespace = $this->attributes('namespace');

		if (empty($namespace))
		{
			return $this->fetchPageGallery($name);
		}
		/*
			$name = data_fields.slug
			join data_fields_assignements,  data_field_assignments.field_id = data_fields.id
			join default_data_streams, data_stream_id = data_field_assignemnts.stream_id

			// use stream name and stream_namespace to get table to get the 
		*/
	}

	public function fetchPageGallery($name)
	{
		// default pages, get by slug, get entry id, figure out stream slug, get images
		$res = $this->CI->db->from('default_pages')
			->select('default_page_types.slug, default_pages.entry_id, default_gallery_fields.*, default_files.*')
			->where('default_pages.uri', $this->uri)
			->where('default_gallery_fields.entry_id = default_pages.entry_id')
			->join("default_page_types", "default_pages.type_id = default_page_types.id", 'left')
			->join("default_gallery_fields", "default_gallery_fields.stream_name = default_page_types.slug", 'left')
			->join("default_files", "default_gallery_fields.file_id = default_files.id", 'left')
			->get()
			->result_array();

		$this->CI->db->last_query();

		if ( count($res) == 0)
			return array();

		return $res;
	}

	public function __call($name, $arguments)
	{
		return $this->fetch_gallery_images($name);
	}
	
}