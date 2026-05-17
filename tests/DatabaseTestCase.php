<?php

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use App\Core\Database;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::getConnection();

        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        parent::tearDown();
    }
}
