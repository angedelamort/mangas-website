<?php

namespace mangaslib\utilities;

use mangaslib\db\Library;
use mangaslib\models\UserModel;

class InitializationHelper {

    public static function IsInitialized() {
        $file = dirname(dirname(dirname(__DIR__))) . '/.init_cache';
        if (file_exists($file)) {
            return true;
        } else if (InitializationHelper::IsDatabaseInitialized() && InitializationHelper::HasAdmin()) {
            file_put_contents($file, '## generated after initialization by InitializationHelper.php');
            return true;
        }

        return false;
    }

    public static function IsDatabaseInitialized() {
        $file = Library::getDbConfig();
        return file_exists($file);
    }

    public static function HasAdmin() {
        if (InitializationHelper::IsDatabaseInitialized()) {
            return UserModel::size();
        }
        return false;
    }

    /**
     * @param string $uri
     * @param string $username
     * @param string $password
     * @param string $dbName
     * @param int $port
     * @return bool
     */
    public static function InitializeDatabaseConfig(string $uri, string $username, string $password, string $dbName, int $port = 3306) {
        $file = Library::getDbConfig();
        file_put_contents($file,"[default]
uri=$uri
username=$username
password=$password
dbname=$dbName
port=$port
");
        return true;
    }
}