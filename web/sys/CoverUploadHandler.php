<?php
require_once 'sys/UploadHandler.php';
class CoverUploadHandler extends UploadHandler {
    public function __construct() {
        global $configArray;
        parent::__construct(array(
            'script_url' => $configArray['Site']['url'] . '/AJAX/UploadCover/',
            'upload_dir' => '/tmp/' . $_REQUEST['id'] . '/',
            'image_library' => 0
        ));
    }
    
    protected function generate_response($content, $print_response = true) {
        global $configArray, $logger;
        global $user;
        $id = $_REQUEST['id'];
        $files = $content['files'];
        if (!empty($files)) {
            $local_dir = $configArray['Site']['local'] . '/images/covers/local';
            is_dir($local_dir . '/original') || mkdir($local_dir . '/original', 0755, true);
            is_dir($local_dir . '/small') || mkdir($local_dir . '/small', 0755, true);
            is_dir($local_dir . '/medium') || mkdir($local_dir . '/medium', 0755, true);
            is_dir($local_dir . '/large') || mkdir($local_dir . '/large', 0755, true);

            $original = $local_dir . '/original/' . $id . '.jpg';
            $small = $local_dir . '/small/' . $id . '.jpg';
            $medium = $local_dir . '/medium/' . $id . '.jpg';
            $large = $local_dir . '/large/' . $id . '.jpg';
            file_exists($small) && unlink($small);
            file_exists($medium) && unlink($medium);
            file_exists($large) && unlink($large);
            $uploaded_file = $this->get_upload_path() . '/' . $files[0]->name;
            rename($uploaded_file, $original);
            $this->delete_directory($this->get_upload_path());
            
            // set the url to the proper bookcover.php url
            $content['files'][0]->url = $configArray['Site']['url'] . '/bookcover.php?size=medium&id=' . $id;
            
            // log the upload
            $logger->log($user->cat_username . ' uploaded image for record ' . $id, PEAR_LOG_NOTICE);
        }
        return parent::generate_response($content, $print_response);
    }
    
    private function delete_directory($dir) {
        $files = glob($dir . '/*');
        foreach($files as $file) {
            if(is_dir($file)) {
                $this->delete_directory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
?>
