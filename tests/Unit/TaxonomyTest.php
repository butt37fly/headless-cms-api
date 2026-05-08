<?php

namespace Tests\Unit;

use App\Models\Taxonomy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\DatabaseTestCase;

#[CoversClass(Taxonomy::class)]
class TaxonomyTest extends DatabaseTestCase
{
    private function createTaxonomies(Taxonomy $taxonomy): array
    {
        $taxonomy->create("Testing Tag", "testing-tag");
        $taxonomy->create("Testing Category", "testing-category");

        return $taxonomy->getAll();
    }

    #[TestDox("Arroja una excepción al intentar crear una taxonomía con el mismo título")]
    public function test_throw_an_exception_on_duplicate_title_insert()
    {
        $this->expectException(\PDOException::class);

        $taxonomy = new Taxonomy();
        $taxonomy->create("Testing Tag", "testing-tag");

        $taxonomy->create("Testing Tag", "testing-tag-2");
    }

    #[TestDox("Arroja una excepción al intentar crear una taxonomía con el mismo slug")]
    public function test_throw_an_exception_on_duplicate_slug_insert()
    {
        $this->expectException(\PDOException::class);

        $taxonomy = new Taxonomy();
        $taxonomy->create("Testing Tag", "testing-tag");

        $taxonomy->create("Testing Tag 2", "testing-tag");
    }

    #[TestDox("Devuelve un arreglo al consultar información")]
    public function test_return_an_array_as_result()
    {
        $taxonomy = new Taxonomy();
        $result = $taxonomy->getAll();

        $this->assertIsArray($result);
    }

    #[TestDox("Devuelve un arreglo vacío si no encuentra resultados")]
    public function test_return_an_empty_array_as_result_if_there_is_no_content()
    {
        $taxonomy = new Taxonomy();
        $result = $taxonomy->getAll();

        $this->assertEmpty($result);
    }

    #[TestDox("Devuelve un arreglo con la estructura esperada")]
    public function test_return_expected_headers()
    {
        $headers = ['id', 'name', 'slug'];

        $taxonomy = new Taxonomy();
        $result = $this->createTaxonomies($taxonomy);

        $this->assertEquals(array_keys($result[0]), $headers);
    }
}
