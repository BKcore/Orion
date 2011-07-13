<?php
/**
 * Orion upload class for images or files
 * Usage: new Upload(ID, FOLDER); upload(); [thumbnail();]
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionUpload
{
    const CLASS_NAME = 'OrionUpload';

	/**
	 * Store the upload data : ['tmpfile','size','type','targetdir','targetfile','basename','thumbnail','fullpath']
	 * @var array $_DATA
	 */
	private $_DATA;
	/**
	 * contains upload status
	 * @var boolean $_SUCCESS
	 */
	private $_SUCCESS = false;
	/**
	 * Stores temporary form FILE
	 * @var FILE $_INPUT
	 */
	private $_INPUT;
	/**
	 * Contains the upload dir path
	 * @var string $UPLOAD_DIR
	 */
	private $UPLOAD_DIR;
	/**
	 * Type image/Jpeg for example
	 * @var array $ALLOWED_FILETYPES
	 */
	private $ALLOWED_FILETYPES;

	const JPEG = 'image/jpeg';
	const PNG = 'image/png';
	const GIF = 'image/gif';
	const SWF = 'application/x-shockwave-flash';

	const IMAGE_UPLOAD_DIR = 'IMAGE_UPLOAD_DIR';
	const FILE_UPLOAD_DIR = 'FILE_UPLOAD_DIR';

	/**
	 *
	 * @global configuration $config
	 * @param string $id the file form identifier
	 * @param string $folder path where to store the file, either real path or Upload::IMAGE_UPLOAD_DIR, Upload::FILE_UPLOAD_DIR to use configuration file
	 * @return object Upload object or false if error
	 */
	function __construct( $id, $folder )
	{
        if($id == null)
            throw new OrionException('You need to provide a field ID to uploada file.', E_USER_ERROR, self::CLASS_NAME);

        if(!isset($_FILES[$id]))
            throw new OrionException('File in $_FILES["'.$id.'"] not found.', E_USER_ERROR, self::CLASS_NAME);

		if(file_exists($folder))
		{
			$this->UPLOAD_DIR = $folder;
		}
		else
		{
			if(Orion::config()->defined($folder))
			{
				$this->UPLOAD_DIR = Orion::config()->get($folder);
			}
			else
			{
				throw new OrionException('Please provide a valid path to upload file.', E_USER_ERROR, self::CLASS_NAME);
			}
		}

        if(!is_writable($this->UPLOAD_DIR))
            throw new OrionException('Directory ['.$this->UPLOAD_DIR.'] is not writable. Upload failed.', E_USER_ERROR, self::CLASS_NAME);

		$this->_INPUT = &$_FILES[$id];

		$this->_DATA = $this->getData();
	}
	/**
	 * Get useful data for upload
	 * @return array data
	 */
	private function getData()
	{
		$data = pathinfo($this->_INPUT['name']);
		$data['tmpfile'] = $this->_INPUT['tmp_name'];
		$data['size'] = $this->_INPUT['size'];
		$data['type'] = $this->_INPUT['type'];
		$data['targetdir'] = $this->UPLOAD_DIR;
		$data['targetfile'] = $data['basename'];

		return $data;
	}
	/**
	 * Prepare upload by checking parameters integrity and dupe existence
	 * @return boolean success or failure
	 */
	private function prepare()
	{
		if(!file_exists($this->_DATA['targetdir']))
            throw new OrionException('Target directory ['.$this->_DATA['targetdir'].'] does not exist', E_USER_ERROR, self::CLASS_NAME);
		if(!is_uploaded_file($this->_DATA['tmpfile']))
            throw new OrionException('Internal error, unable to upload image.', E_USER_ERROR, self::CLASS_NAME);
		if(!empty($this->ALLOWED_FILETYPES) && !in_array($this->_DATA['type'], $this->ALLOWED_FILETYPES))
            throw new OrionException('Trying to upload an unauthorized filetype',E_USER_ERROR, self::CLASS_NAME);

		if(file_exists($this->_DATA['targetdir'].$this->_DATA['targetfile'])) $this->fixDupe();

		return true;
	}
	/**
	 * Define a new filename if file already exists (e.g. filename-1.ext)
	 */
	private function fixDupe()
	{
		$i = 1;
		$tmpname = $this->_DATA['targetfile'];
		$path = pathinfo($this->_DATA['targetfile']);
		while(file_exists($this->_DATA['targetdir'].$tmpname))
		{
			$tmpname = $path['filename'].'-'.$i.'.'.$path['extension'];
			$i++;
		}

		$this->_DATA['targetfile'] = $tmpname;
	}
	/**
	 * Adds a prefix to the filename
	 * @param string $prefix
	 */
	public function setPrefix( $prefix )
	{
		$this->_DATA['targetfile'] = $prefix.$this->_DATA['targetfile'];
	}
	/**
	 * Restricts file types
	 * @example restrict(Upload::JPEG[, Upload::PNG, ...]);
	 * @example using Upload::[JPEG|PNG|GIF|...]
	 */
	public function restrict()
	{
		$this->ALLOWED_FILETYPES = func_get_args();
	}
	/**
	 * Create a thumbnail using uploaded image as source
	 * @param int $maxsize in pixels
	 * @param string $newname prefix for thumbnail. 'thumb-' by default
	 * @return boolean success or failure
	 */
	public function thumbnail( $maxsize, $newname = NULL )
	{
		if(!$this->_SUCCESS) 
            throw new OrionException('You need to execute upload() before calling thumbnail().', E_USER_WARNING, self::CLASS_NAME);
		if(reset(explode('/', $this->_DATA['type'])) != 'image')
            throw new OrionException('Unable to resize image to create thumbnail.', E_USER_WARNING, self::CLASS_NAME);

		$target = empty($newname) ? $this->_DATA['targetdir'].'thumb-'.$this->_DATA['targetfile'] : $this->_DATA['targetdir'].$newname.$this->_DATA['extension'];

		switch($type){
			case "image/jpeg":
			$function_image_create = "ImageCreateFromJpeg";
			$function_image_new = "ImageJpeg";
			break;
			case "image/png":
			$function_image_create = "ImageCreateFromPng";
			$function_image_new = "ImagePNG";
			break;
			case "image/gif":
			$function_image_create = "ImageCreateFromGif";
			$function_image_new = "ImageGif";
			break;
			default:
				throw new OrionException('Image was not resized : type not supported.', E_USER_NOTICE, self::CLASS_NAME);
			break;
		}

		list($width, $height) = getimagesize($this->_DATA['fullpath']);

		if ($width>$maxsize || $height>$maxsize)
		{
			$ratio = $height/$width;
			$newheight = ($height > $width) ? $maxsize : $maxsize*$ratio;
			$newwidth = ($width > $height) ? $maxsize : $newheight/$ratio;

			$thumb = ImageCreateTrueColor($newwidth,$newheight);
			$source = @$function_image_create($this->_DATA['fullpath']);

			ImageCopyResampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

			$this->_DATA['thumbnail'] = $target;

			if(@$function_image_new($thumb, $target)) $this->_SUCCESS = true;

			return $this->_SUCCESS;
		}

		throw new OrionException('Image was not resized : Image is too small.', E_USER_NOTICE, self::CLASS_NAME);
	}
	/**
	 * Process the final upload using copy()
	 * @return boolean success or failure
	 */
	public function upload()
	{
		if(!$this->prepare())
            throw new OrionException('Internal error, unable to prepare image for upload.', E_USER_ERROR, self::CLASS_NAME);

		$this->_SUCCESS = copy($this->_DATA['tmpfile'], $this->_DATA['targetdir'].$this->_DATA['targetfile']);

        if(!$this->_SUCCESS)
            throw new OrionException('Internal error, unable to copy image to upload directory.', E_USER_ERROR, self::CLASS_NAME);

        return true;
	}
	/**
	 * Used to retrive an identifier to store in a database for example
	 * @param string $type 'path' or 'name' for full path or only filename
	 * @return string Identifier
	 */
	public function getIdentifier($type='path')
	{
		switch($type)
		{
			case 'name':
			case 'NAME':
				return $this->_DATA['targetfile'];
			break;
			case 'path':
			case 'PATH':
			default:
				return $this->_DATA['targetdir'].$this->_DATA['targetfile'];
			break;
		}
	}
	/**
	 * Used to retrive an identifier of the thumbnail to store in a database for example
	 * @param string $type 'path' or 'name' for full path or only filename of the thumbnail
	 * @return string Thumbnail identifier
	 */
	public function getThumbnailIdentifier($type='path')
	{
		switch($type)
		{
			case 'name':
			case 'NAME':
				return basename($this->_DATA['thumbnail']);
			break;
			case 'path':
			case 'PATH':
			default:
				return $this->_DATA['thumbnail'];
			break;
		}
	}
	/**
	 * Returns if the Upload was a success or a failure
	 * @return boolean success or failure
	 */
	public function isSuccess()
	{
		return $this->_SUCCESS;
	}
}

?>
