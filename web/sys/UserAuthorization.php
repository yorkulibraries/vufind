<?php
require_once 'sys/Role.php';

class UserAuthorization
{
    const ROLE_ADMIN = 'admin';
    const ROLE_TRANSLATOR = 'translator';
    
    public static function hasRole($user, $role)
    {
        $r = new Role();
        $r->user_id = $user->id;
        $r->role = $role;
        $hasRole = $r->find();
        return $hasRole;
    }
}
?>
