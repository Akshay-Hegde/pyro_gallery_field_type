<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Gallery field type
 *
 * @author		Jack Hannigan Popp
 */
class Field_gallery
{
	
	public $field_type_slug	= 'gallery';
	public $alt_process	= true;
	public $db_col_type = false;
	public $table_name = 'gallery_fields';
	public $version = '0.1';
	public $folder_name = 'Gallery Fields Folder';

	private $cache;

	/**
	* Allows us to set some javascript/css
	*/

	public function event($field)
	{
		$this->CI->type->add_js('gallery', 'dropzone.min.js');
	    $this->CI->type->add_js('gallery', 'main.js');
	    $this->CI->type->add_css('gallery', 'main.css');
	}

	/**
	* Outputs the form we will add to
	*/

	public function form_output($data, $entry_id, $field)
	{
	    $images = $this->CI->db->from($this->table_name)
	    	->where("stream_name", $field->stream_slug)
	    	->where("entry_id", $entry_id)
	    	->join("{$this->prefix}files", "{$this->prefix}files.id = {$this->prefix}{$this->table_name}.file_id", 'left')
	    	->select("{$this->table_name}.id as id, {$this->table_name}.caption as caption, {$this->prefix}files.path")
	    	->get()
	    	->result_array();

	    foreach ($images as &$image)
	    	$image['path'] = $this->parse_image($image['path']);

	    $data = array('images' => $images, 'stream' => $field->stream_slug, 'entry_id' => $entry_id, 'field' => $field->field_slug);

	    return $this->CI->type->load_view('gallery', 'view', $data, true);
	}

	public function pre_save($images, $field, $stream, $row_id, $data_form) 
	{
		// remove the fist id as it's a blank one 
		array_shift($_POST['ids']);

		foreach ($_POST[$field->field_slug] as $key => $caption) {

			$this->CI->db->where('id', $_POST['ids'][$key])
				->update($this->table_name, array('caption' => $caption));

		}
	}

	public function alt_pre_output($row_id, $params, $field_type, $stream)
	{
		
	}

	/**
	* Runs when a field assignment happens
	*/

	public function field_assignment_construct($field, $stream)
	{
		$this->CI->load->dbforge();
		// create overall table if it doesnt exist
		$fields = array(
			'id' => array(
				'type'           => 'INT',
				'constraint'     => 11, 
				'unsigned'       => true,
				'auto_increment' => true
				),
			'stream_name' => array(
				'type'       => 'VARCHAR',
				'constraint' => 255
				),
			'entry_id' => array(
				'type'       => 'INT',
				'constraint' => 11
				),
			'file_id' => array(
				'type'       => 'VARCHAR',
				'constraint' => 255
				),
			'caption' => array(
				'type'       => 'VARCHAR',
				'constraint' => 255
			)
		);     
		
		$this->CI->dbforge->add_field($fields);
		$this->CI->dbforge->add_key('id', true);
		$this->CI->dbforge->create_table($table_name);

		// create folder if it doesnt exist
		$this->CI->files->create_folder($parent = 0, $name = $this->folder_name, $location = 'local', $remote_container = '');
	}

	/**
	* Called when removing a field assignment
	*/

	public function field_assignment_destruct($field, $stream)
	{
		// delete appropriate entries
	}

	public function alt_rename_column($field, $stream)
	{
		return null;
	}

	/**
	* Stop the rename column function that happens as we are not using one.
	*/
	/*

	public function alt_rename_column($field, $stream)
	{
		return null;
	} */

	public function parse_image($value)
	{
		return preg_replace("/{{ url:site }}/", '', html_entity_decode($value));
	}


	/**
	* Uploads an image
	*/

	public function ajax_upload_image()
	{
		header('Content-Type: application/json');

	    try
	    {
	    	if ( ! is_logged_in())
	    		throw new Exception("User not logged in");

	    	$config['allowed_types'] = 'jpg|png';
			$config['max_size']	= '2048';
			$this->CI->load->library('files/files', $config);
	    		
    		$stream = $_POST['stream'];
			$entryId = $_POST['entry_id'];
		    $folder = $this->CI->file_folders_m->get_by('name', $this->folder_name);
		    $file = $this->CI->files->upload($folder->id);

		    if ( ! $file['status'])
		    	throw new Exception("Error uploading file");
		    	

			// create entry
			$file['data']['path'] = $this->parse_image($file['data']['path']);

			$data = array(
				'stream_name' => $stream,
				'entry_id' => $entryId,
				'file_id' => $file['data']['id'],
				'caption' => ''
			);

			$this->CI->db->insert($this->table_name, $data);

			$file['data']['id'] = $this->CI->db->insert_id();

			http_response_code(201);
			echo json_encode($file['data']);

	    } catch(Exception $e)
	    {
	    	http_response_code(500);
	    }
	}

	/**
	* Deletes an image
	*/

	public function ajax_delete_image()
	{
		header('Content-Type: application/json');

		try
		{
			if ( ! is_logged_in())
	    		throw new Exception("User not logged in");

			$this->CI->db->where('id', $_POST['id'])->delete($this->table_name);
			// delete file entry
			http_response_code(204);

		} catch(Exception $e)
		{
			http_response_code(500);
		}    
	}

}