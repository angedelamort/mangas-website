<?php

namespace mangaslib\models;

class UserModel extends BaseModel {
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $role;
    const role_type = "int";
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
        $cond = self::toCondition($usernameOrEmail);
        if ($password) {
            $cond .= " AND password='$password'";
        }
        $fields = self::getFields(UserModel::class, ['$password']); // NOTE: don't get the password locally.
        $query = "SELECT $fields FROM mangas_users WHERE $cond;";
        $result = self::query($query);
        /** @var UserModel $item */
        $item = $result->fetch_object(UserModel::class);
        return $item;
    }

    public static function size() {
        return self::count('email', 'mangas_users');
    }

    // TODO: create a resource wishlist
    public function wishlist() {
        return $this->wishlist ? json_decode($this->wishlist, true) : [];
    }

    public function updateWishlist(array $wishlist) {
        $this->wishlist = json_encode($wishlist); // since it's in the object and the real request doesn't exists, we need to update it manually.
        $escapedWishlist = self::escapeString($this->wishlist);
        $query = "UPDATE mangas_users SET wishlist=\"$escapedWishlist\" WHERE email='$this->email';";
        self::query($query);
        return $wishlist;
    }

    /**
     * @param UserModel $item
     * @return UserModel
     * @throws \ReflectionException
     */
    public static function add(UserModel $item) {
        self::query(self::insert($item, 'mangas_users'));
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