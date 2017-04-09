<?php
class BanUser extends Action
{
    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        global $logger;
        
        $barcode = $_POST['barcode'];
        if (preg_match('/^[0-9]+$/', $barcode)) {            
            $user = new User();
            $user->cat_username = $barcode;
            if ($user->find(true)) {
                $user->banned = 1;
                $user->update();
                UserAccount::destroyUserSessions($user);
            }
        }
    }
}
?>
