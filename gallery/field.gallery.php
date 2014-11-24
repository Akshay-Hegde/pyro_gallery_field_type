<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Gallery field type
 *
 * @author		Jack Hannigan Popp
 */
class Field_gallery
{

	/**
	 * Field Type Name
	 *
	 * @var 	string
	 */
	public $field_type_name 		= 'Gallery';
	
	/**
	 * Field Type Slug
	 *
	 * @var 	string
	 */
	public $field_type_slug			= 'gallery';

	/**
	 * Table Name
	 *
	 * @var 	string
	 */
	public $table_name = 'gallery_fields';
	
	/**
	 * Alt Process
	 *
	 * Is this field type alternatively processed?
	 *
	 * @var 	bool
	 */
	public $alt_process				= true;

	/**
	 * Database Column Type
	 *
	 * Instead of a database colunn, we have a
	 * binding table, so we'll set this to false.
	 *
	 * @var 	string|bool
	 */
	public $db_col_type				= false;

	public $folder_name = 'Gallery Fields Folder';

	/**
	* Input Is File
	*
	* The passed input will be files
	*/
	public $input_is_file = true;

	public $prefix = null;

	public $CI;

	public function __construct()
	{
		$this->CI =& get_instance();

        $this->CI->load->database();

        $this->CI->load->library('files/files');

        $this->prefix = $this->CI->db->dbprefix;
	}

	public function pre_save($input, $field, $stream, $id, $form_data)
	{
		echo '<pre>';
		var_dump($input);
		var_dump($field);
		var_dump($stream);
		var_dump($id);
		var_dump($form_data);
		die();
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
	    	$image['path'] = $this->parse($image['path']);

	    $data = array('images' => $images, 'stream' => $field->stream_slug, 'entry_id' => $entry_id);

	    return $this->CI->type->load_view('gallery', 'view', $data, true);
	 }

	public function parse($value)
	{
		return preg_replace("/{{ url:site }}/", '', html_entity_decode($value));
	}

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
	* Uploads an image
	*/

	public function ajax_upload_image()
	{
	    // AJAX functionality here.
	    // upload image
	    // create record
	    // return url and id
	    //$image = $_FILES['file'];
	    // get stream and entry id

	    try
	    {
    		$stream = $_POST['stream'];
			$entryId = $_POST['entry_id'];

		    $folder = $this->CI->file_folders_m->get_by('name', $this->folder_name);

		    $file = $this->CI->files->upload($folder->id);

		    header('Content-Type: application/json');

		    if ($file['status'])
		    {
		    	// create entry
		    	$file['data']['path'] = $this->parse($file['data']['path']);

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
		    }
		    else 
		    {
		    	http_response_code(500);
		    }
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
	    $this->CI->db->where('id', $_POST['id'])->delete($this->table_name);
	}

}