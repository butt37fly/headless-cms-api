<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}
