<?php
require_once 'Action.php';
require_once 'sys/CoverUploadHandler.php';

class UploadCover extends Action
{
    public function launch()
    {
        $user = UserAccount::isLoggedIn();
        if ($user === false || !$user->can_upload_covers) {
            header('Forbidden', true, 403);
            exit;
        }     
        $upload_handler = new CoverUploadHandler();
    } 
}
?>