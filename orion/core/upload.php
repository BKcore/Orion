<?php

namespace Orion\Core;


/**
 * \Orion\Core\Upload
 * 
 * Orion upload class for images or files
 *
 * Usage: new Upload(ID, FOLDER); upload(); [thumbnail();]
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class Upload
{
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

    /**
     * Allow or disallow folder creation when it does not exist
     * @var boolean
     */
    private $ALLOW_FOLDER_CREATION = false;

    /**
     * Base upload directory placeholder
     * @var String
     */
    private $BASE_UPLOAD_DIR = '';
    
    // Extensions
    public static $BMP = array( 'bmp' );
    public static $JPEG = array( 'jpeg', 'jpg' );
    public static $PNG = array( 'png' );
    public static $GIF = array( 'gif' );
    public static $SWF = array( 'swf' );
    public static $XML = array( 'xml' );
    public static $OGV = array( 'ogv', 'ogg' );
    public static $FILE3D = array( 'dae', 'tmw' );
    public static $ZIP = array( 'zip' );

    const IMAGE_UPLOAD_DIR = 'IMAGE_UPLOAD_DIR';
    const FILE_UPLOAD_DIR = 'FILE_UPLOAD_DIR';

    /**
     *
     * @global configuration $config
     * @param string $id the file form identifier
     * @param string $folder path where to store the file, either real path or Upload::IMAGE_UPLOAD_DIR, Upload::FILE_UPLOAD_DIR to use configuration file
     * @return object Upload object or false if error
     */
    function __construct( $id=null, $folder=null )
    {
        if ( $id == null ) // blank usage, no bindings.
            return;

        if ( !isset( $_FILES[ $id ] ) )
            throw new Exception( 'File in $_FILES["' . $id . '"] not found.', E_USER_ERROR, get_class() );

        if ( !\Orion::config()->defined( 'UPLOAD_DIR' ) )
            throw new Exception( 'UPLOAD_DIR is not defined in Orion configuration.', E_ERROR, get_class() );

        $this->BASE_UPLOAD_DIR = \Orion::config()->get( 'UPLOAD_DIR' );

        if ( \Orion::config()->defined( $folder ) )
        {
            $this->UPLOAD_DIR = $this->BASE_UPLOAD_DIR . \Orion::config()->get( $folder );
            if ( !file_exists( $this->UPLOAD_DIR ) )
                throw new Exception( 'Upload directory read from configuration does not exist.', E_ERROR, get_class() );
        }
        else
        {
            if ( file_exists( $this->BASE_UPLOAD_DIR . $folder ) )
            {
                $this->UPLOAD_DIR = $this->BASE_UPLOAD_DIR . $folder;
            }
            else
            {
                throw new Exception( 'Please provide a valid path to upload file.', E_USER_ERROR, get_class() );
            }
        }

        if ( !is_writable( $this->UPLOAD_DIR ) )
            throw new Exception( 'Directory [' . Security::preventInjection( $this->UPLOAD_DIR ) . '] is not writable. Upload failed.', E_USER_ERROR, get_class() );

        $this->_INPUT = &$_FILES[ $id ];

        $this->_DATA = $this->getData();
    }

    /**
     * Get useful data for upload
     * @return array data
     */
    private function getData()
    {
        $data = pathinfo( $this->_INPUT[ 'name' ] );
        $data[ 'extension' ] = strtolower($data[ 'extension' ]);
        $data[ 'tmpfile' ] = $this->_INPUT[ 'tmp_name' ];
        $data[ 'size' ] = $this->_INPUT[ 'size' ];
        $data[ 'type' ] = $this->_INPUT[ 'type' ];
        $data[ 'targetdir' ] = $this->UPLOAD_DIR;
        $data[ 'targetfile' ] = $data[ 'filename' ].'.'.$data['extension'];

        return $data;
    }

    /**
     * Prepare upload by checking parameters integrity and dupe existence
     * @return boolean success or failure
     */
    private function prepare()
    {
        if ( $this->ALLOW_FOLDER_CREATION && !is_dir( $this->_DATA[ 'targetdir' ] ) )
            mkdir( $this->_DATA[ 'targetdir' ], 0755, true );

        if ( !file_exists( $this->_DATA[ 'targetdir' ] ) )
            throw new Exception( 'Target directory [' . $this->_DATA[ 'targetdir' ] . '] does not exist', E_USER_ERROR, get_class() );
        if ( !is_uploaded_file( $this->_DATA[ 'tmpfile' ] ) )
            throw new Exception( 'Internal error, unable to upload image.', E_USER_ERROR, get_class() );
        if ( !empty( $this->ALLOWED_FILETYPES ) && !in_array( $this->_DATA[ 'extension' ], $this->ALLOWED_FILETYPES ) )
            throw new Exception( 'Trying to upload an unauthorized filetype', E_USER_ERROR, get_class() );
        if ( strpos( $this->_DATA[ 'targetfile' ], '..' ) !== false )
            throw new Exception( 'Unauthorized ".." char in file name.', E_USER_ERROR, get_class() );

        if ( file_exists( $this->_DATA[ 'targetdir' ] . $this->_DATA[ 'targetfile' ] ) )
            $this->fixDupe();

        return true;
    }

    /**
     * Define a new filename if file already exists (e.g. filename-1.ext)
     */
    private function fixDupe()
    {
        $i = 1;
        $tmpname = $this->_DATA[ 'targetfile' ];
        $path = pathinfo( $this->_DATA[ 'targetfile' ] );
        while ( file_exists( $this->_DATA[ 'targetdir' ] . $tmpname ) )
        {
            $tmpname = $path[ 'filename' ] . '-' . $i . '.' . $path[ 'extension' ];
            $i++;
        }

        $this->_DATA[ 'targetfile' ] = $tmpname;
    }

    /**
     * Allows creation of folder when it does not exist
     */
    public function enableFolderCreation()
    {
        $this->ALLOW_FOLDER_CREATION = true;
    }
    
    public function sanitizeFilename()
    {
        $this->_DATA[ 'targetfile' ] = preg_replace("/[^[:alnum:][:punct:]]/","_", $this->_DATA[ 'filename' ]).'.'.$this->_DATA['extension'];
    }

    /**
     * Adds a prefix to the filename
     * @param string $prefix
     */
    public function setPrefix( $prefix )
    {
        $this->_DATA[ 'prefix' ] = $prefix;
        $this->_DATA[ 'targetfile' ] = $prefix . $this->_DATA[ 'targetfile' ];
    }

    /**
     * Puts the file into provided sub folder(s)
     * @param string $folders Sub folders (multiple = func_get_args)
     */
    public function setSubFolder( $folders=null )
    {
        $subpath = '';
        $args = func_get_args();
        if ( empty( $args ) )
            return;

        foreach ( $args as $folder )
        {
            if ( !empty( $folder ) )
            {
                $subpath .= $folder . DS;
            }
        }

        $this->_DATA[ 'targetdir' ] .= $subpath;
    }

    /**
     * Restricts file types
     * @param [string|string[]] mime types
     */
    public function restrict()
    {
        $arr = func_get_args();
        foreach ( $arr as $type )
        {
            if ( is_array( $type ) )
                foreach ( $type as $item )
                    $this->ALLOWED_FILETYPES[ ] = $item;
            else
                $this->ALLOWED_FILETYPES[ ] = $type;
        }
    }

    /**
     * Create a thumbnail using uploaded image as source
     * @param int[] $maxsize in pixels or an array [width, height]
     * @param string $newname The new name for the thumbnail
     * @param string [$_targetdir] The target directory if the thumbnail needs to be in another directory
     * @param string [$_source] The source image path (To generate a thumbnail not from an uploaded image, but from a static file on the server)
     * @param string[] [$format] Provide an overriden format of the thumbnail. This forat consists of an array if the imagenew method in [0] and the extention in [1], example: array('ImageJpeg', 'jpg')
     * @return boolean success or failure
     */
    public function thumbnail( $maxsize, $newname=null, $_targetdir=null, $_source=null, $format=null )
    {
        if ( !$this->_SUCCESS && $_source == null )
            throw new Exception( 'You need to execute upload() before calling thumbnail().', E_USER_WARNING, get_class() );
        
        if( $_source != null && !file_exists( $_source) )
            throw new Exception( 'Source image does not exist' );

        $dir = ($_targetdir == null ? $this->_DATA[ 'targetdir' ] . '.thumb/' : $_targetdir);
        
        if(!is_dir($dir))
            mkdir( $dir, 0755, true );
            
        if($_source != null)
        {  
            $info = pathinfo($_source);
            $ext = $info[ 'extension' ];
            $fullpath = $_source;
        }
        else
        {
            $ext = $this->_DATA[ 'extension' ];
            $fullpath = $this->_DATA[ 'fullpath' ];
        }
        
        if ( !in_array( $ext, array('jpg','jpeg','png','gif','tmp') ) )
            throw new Exception( 'Unable to resize image to create thumbnail. Invalid extension ('.$ext.').', E_USER_WARNING, get_class() );
        
        $target = empty( $newname ) ? $dir . $this->_DATA[ 'targetfile' ] : $dir . $newname . $ext;

        switch ( $ext )
        {
            case "jpeg":
            case "jpg":
            case "tmp":
                $function_image_create = "ImageCreateFromJpeg";
                $function_image_new = "ImageJpeg";
                break;
            case "png":
                $function_image_create = "ImageCreateFromPng";
                $function_image_new = "ImagePNG";
                break;
            case "gif":
                $function_image_create = "ImageCreateFromGif";
                $function_image_new = "ImageGif";
                break;
            default:
                throw new Exception( 'Image was not resized : type not supported.', E_USER_NOTICE, get_class() );
                break;
        }
        
        if($format != null && is_array($format))
        {
            $function_image_new = $format[0];
            $pos = strlen($target) - strlen($ext);
            $target = substr( $target, 0, $pos) . $format[1];
        }

        list($width, $height) = getimagesize( $fullpath );
        if(is_array($maxsize))
        {
            $newwidth = $maxsize[0];
            $newheight = $maxsize[1];
            $newratio = $newwidth/$newheight;
            $ratio = $width / $height;
            
            if($ratio > $newratio)
            { // fixheight
                $cropheight = $height;
                $cropwidth = $height*$newratio;
                $cropx = floor(($width - $cropwidth)/2);
                $cropy = 0;
            }
            else
            { // fixwidth
                $cropheight = $width/$newratio;
                $cropwidth = $width;
                $cropx = 0;
                $cropy = floor(($height - $cropheight)/2);
            }
            
            $thumb = ImageCreateTrueColor( $newwidth, $newheight );
            $source = @$function_image_create( $fullpath );

            if($source == false)
                throw new Exception('Image is too small to be resized.');

            ImageCopyResampled( $thumb, $source, 0, 0, $cropx, $cropy, $newwidth, $newheight, $cropwidth, $cropheight );

            $this->_DATA[ 'thumbnail' ] = $target;

            if ( @$function_image_new( $thumb, $target ) )
                $this->_SUCCESS = true;

            return $this->_SUCCESS;
        }
        elseif ( $width > $maxsize || $height > $maxsize ) // below is a compatibility fallback for ratio-keeping resize (ignore)
        {
            $ratio = $height / $width;
            $newheight = ($height > $width) ? $maxsize : $maxsize * $ratio;
            $newwidth = ($width > $height) ? $maxsize : $newheight / $ratio;

            $thumb = ImageCreateTrueColor( $newwidth, $newheight );
            $source = @$function_image_create( $fullpath );

            ImageCopyResampled( $thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

            $this->_DATA[ 'thumbnail' ] = $target;

            if ( @$function_image_new( $thumb, $target ) )
                $this->_SUCCESS = true;

            return $this->_SUCCESS;
        }

        throw new Exception( 'Image was not resized : Image is too small.', E_USER_NOTICE, get_class() );
    }

    /**
     * Process the final upload using copy()
     * @return boolean success or failure
     */
    public function upload()
    {
        if ( !$this->prepare() )
            throw new Exception( 'Internal error, unable to prepare image for upload.', E_USER_ERROR, get_class() );

        $this->_DATA[ 'fullpath' ] = $this->_DATA[ 'targetdir' ] . $this->_DATA[ 'targetfile' ];
        $this->_SUCCESS = copy( $this->_DATA[ 'tmpfile' ], $this->_DATA[ 'fullpath' ] );
        
        if ( !$this->_SUCCESS )
            throw new Exception( 'Internal error, unable to copy image to upload directory.', E_USER_ERROR, get_class() );

        return true;
    }

    /**
     * Used to retrive an identifier to store in a database for example
     * @param string $type 'path' or 'name' for full path or only filename
     * @return string Identifier
     */
    public function getIdentifier( $type='path', $noprefix=false )
    {
        switch ( $type )
        {
            case 'name':
            case 'NAME':
                if ( $noprefix && isset( $this->_DATA[ 'prefix' ] ) )
                    return substr( $this->_DATA[ 'targetfile' ], strlen( $this->_DATA[ 'prefix' ] ) );
                else
                    return $this->_DATA[ 'targetfile' ];
                break;
            case 'path':
            case 'PATH':
            default:
                return $this->_DATA[ 'targetdir' ] . $this->_DATA[ 'targetfile' ];
                break;
        }
    }
    
    public function isImage($extension=null)
    {
        return in_array( ($extension==null ? $this->_DATA[ 'extension'] : $extension ), array('jpg','jpeg','png','gif') );
    }

    /**
     * Gets uploaded file size in Octets
     * @return int 
     */
    public function getSize()
    {
        return $this->_DATA[ 'size' ];
    }

    /**
     * Used to retrive an identifier of the thumbnail to store in a database for example
     * @param string $type 'path' or 'name' for full path or only filename of the thumbnail
     * @return string Thumbnail identifier
     */
    public function getThumbnailIdentifier( $type='path' )
    {
        switch ( $type )
        {
            case 'name':
            case 'NAME':
                return basename( $this->_DATA[ 'thumbnail' ] );
                break;
            case 'path':
            case 'PATH':
            default:
                return $this->_DATA[ 'thumbnail' ];
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

    /**
     * Deletes a file from upload dir
     * @param String $path The relative path to the file to delete. (relative to the upload directory)
     * @return boolean 
     */
    public static function delete( $path=null )
    {
        if ( $path == null )
            throw new Exception( 'Trying to delete a file with an empty path.' );

        if ( !\Orion::config()->defined( 'UPLOAD_DIR' ) )
            throw new Exception( 'UPLOAD_DIR is not defined in Orion configuration.', E_ERROR, get_class() );

        $uploadDir = \Orion::config()->get( 'UPLOAD_DIR' );

        if ( !Tools::startWith( $path, $uploadDir ) )
            $path = $uploadDir . $path;

        if ( !file_exists( $path ) )
            throw new Exception( 'Trying to delete a file that does not exist' );

        if ( !@unlink( $path ) )
            throw new Exception( 'Internal error. Could not delete file.' );

        return true;
    }

    /**
     *
     * @param String $directory The relative path to the directory to empty. (relative to the upload directory)
     * @param boolean $empty Set this to TRUE to only empty the directory, FALSE|NULL to empty AND remove the directory
     * @return boolean 
     */
    public static function deleteDir( $directory, $empty = false )
    {
        if ( !\Orion::config()->defined( 'UPLOAD_DIR' ) )
            throw new Exception( 'UPLOAD_DIR is not defined in Orion configuration.', E_ERROR, get_class() );

        $base = $directory;
        $directory = \Orion::config()->get( 'UPLOAD_DIR' ) . $directory;

        if ( substr( $directory, -1 ) == DS )
        {
            $directory = substr( $directory, 0, -1 );
        }

        if ( !file_exists( $directory ) || !is_dir( $directory ) )
        {
            return false;
        }
        elseif ( !is_readable( $directory ) )
        {
            return false;
        }
        else
        {
            $directoryHandle = opendir( $directory );

            while ( $contents = readdir( $directoryHandle ) )
            {
                if ( $contents != '.' && $contents != '..' )
                {
                    $path = $directory . "/" . $contents;

                    if ( is_dir( $path ) )
                    {
                        self::deleteDir( $base."/".$contents );
                    }
                    else
                    {
                        unlink( $path );
                    }
                }
            }

            closedir( $directoryHandle );

            if ( $empty == false )
            {
                if ( !rmdir( $directory ) )
                {
                    return false;
                }
            }

            return true;
        }
    }
    
    /**
     * Get the size of a file/directory in K-blocks (~Ko)
     * /!\ Works only on Linux servers /!\
     * @param String $dir Path
     * @return int The size in K-blocks (~Ko)
     */
    public static function getSizeOf( $dir )
    {
        $f = Security::sanitizePath( $dir );
        if(!file_exists($f))
            return 0;
        else
        {
            $io = popen ( '/usr/bin/du -sk "' . $f.'"', 'r' );
            $size = fgets ( $io, 4096);
            $size = substr ( $size, 0, strpos ( $size, ' ' ) );
            pclose ( $io );
            return intval($size);
        }
    }

}

?>
