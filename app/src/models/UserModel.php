<?php

namespace mangaslib\models;

class UserModel {
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $role;
    public $wishlist;

    /**
     * Find a specific user. If the password is provided, it will check against that
     * condition and return null if it doesn't match.
     * @param $usernameOrEmail
     * @param $password
     * @return UserModel
     * @throws \ReflectionException
     */
    public static function find($usernameOrEmail, $password = null) {
        $helper = new DatabaseHelper();
        $cond = UserModel::toCondition($usernameOrEmail);
        if ($password) {
            $cond .= " AND password='$password'";
        }
        $fields = DatabaseHelper::getFields(UserModel::class, ['$password']); // NOTE: don't get the password locally.
        $query = "SELECT $fields FROM mangas_users WHERE $cond;";
        $result = $helper->query($query);
        /** @var UserModel $item */
        $item = $result->fetch_object(UserModel::class);
        return $item;
    }

    public static function count() {
        $helper = new DatabaseHelper();
        return $helper->count('email', 'mangas_users');
    }

    // TODO: create a resource wishlist
    public function wishlist() {
        return $this->wishlist ? json_decode($this->wishlist, true) : [];
    }

    public function updateWishlist(array $wishlist) {
        $helper = new DatabaseHelper();
        $this->wishlist = json_encode($wishlist);
        $w = $helper->real_escape_string($this->wishlist);
        $query = "UPDATE mangas_users SET wishlist=\"$w\" WHERE email='$this->email';";
        $helper->query($query);
        return true;
    }

    /**
     * @param UserModel $item
     * @return UserModel
     * @throws \ReflectionException
     */
    public static function add(UserModel $item) {
        $helper = new DatabaseHelper();
        $helper->query($helper->objectToInsert($item, 'mangas_users'));

        return UserModel::find($item->email);
    }

    private static function toCondition($usernameOrEmail) {
        $cond = "email='$usernameOrEmail'";
        if (strrpos($usernameOrEmail, "@") === FALSE) {
            $cond = "username='$usernameOrEmail'";
        }
        return $cond;
    }
}