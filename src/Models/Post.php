<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Post
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $db = $this->db;

        $query = "SELECT * FROM posts";

        $stmt = $db->prepare($query);

        $stmt->execute();
        $response = $stmt->fetchAll();

        if (count($response) <= 0) {
            return $response;
        }

        return $response;

        // Ejemplo de salida
        // [
        //     {
        //         "id": 1,
        //         "title": "Mi artículo",
        //         "taxonomies": {
        //             "category": [
        //                 {"id": 5, "name": "Tecnología", "slug": "tecnologia", "meta": {"color": "#3498db"}}
        //             ],
        //             "tag": [
        //                 {"id": 8, "name": "PHP", "slug": "php", "meta": []},
        //                 {"id": 9, "name": "MySQL", "slug": "mysql", "meta": []}
        //             ]
        //         }
        //     }
        // ]
    }

    public function get(int $int = 01): array
    {
        return [
            "id" => $int,
            "title" => "Supermercados Simona",
            "description" => "",
            "date" => "2023",
            "categories" => ["Web"],
            "tags" => ["WordPress", "Elementor"]
        ];
    }
}
