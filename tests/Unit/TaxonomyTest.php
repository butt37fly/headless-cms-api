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
    public function test_fails_on_duplicate_title_insert()
    {
        $this->expectException(\InvalidArgumentException::class);

        $taxonomy = new Taxonomy();
        $taxonomy->create("Testing Tag", "testing-tag");

        $taxonomy->create("Testing Tag", "testing-tag-2");
    }

    #[TestDox("Arroja una excepción al intentar crear una taxonomía con el mismo slug")]
    public function test_fails_on_duplicate_slug_insert()
    {
        $this->expectException(\InvalidArgumentException::class);

        $taxonomy = new Taxonomy();
        $taxonomy->create("Testing Tag", "testing-tag");

        $taxonomy->create("Testing Tag 2", "testing-tag");
    }

    #[TestDox("Verifica que se hayan insertado nuevos datos en la DB")]
    public function test_check_if_data_was_created()
    {
        $taxonomy = new Taxonomy();
        $taxonomies = $taxonomy->getAll();
        $created_taxonomies = $this->createTaxonomies($taxonomy);

        $this->assertNotEquals($taxonomies, $created_taxonomies);
    }

    #[TestDox("Devuelve un arreglo al consultar información")]
    public function test_return_an_array_as_result()
    {
        $taxonomy = new Taxonomy();
        $taxonomies = $taxonomy->getAll();

        $this->assertIsArray($taxonomies);
    }

    #[TestDox("Devuelve un arreglo vacío si no encuentra resultados")]
    public function test_return_an_empty_array_as_result_if_there_is_no_content()
    {
        $taxonomy = new Taxonomy();
        $taxonomies = $taxonomy->getAll();

        $this->assertEmpty($taxonomies);
    }

    #[TestDox("Devuelve un arreglo con la estructura esperada")]
    public function test_return_expected_headers()
    {
        $headers = ["id", "name", "slug"];

        $taxonomy = new Taxonomy();
        $taxonomies = $this->createTaxonomies($taxonomy);

        $this->assertEquals(array_keys($taxonomies[0]), $headers);
    }

    #[TestDox("Arroja una excepción al intentar actualizar una taxonomía inexistente")]
    public function test_fails_on_update_an_unknown_taxonomy()
    {
        $this->expectException(\InvalidArgumentException::class);

        $taxonomy = new Taxonomy();
        $this->createTaxonomies($taxonomy);

        $taxonomy->update("Updated Taxonomy", "updated-taxonomy", "old-taxonomy");
    }

    #[TestDox("Arroja una excepción al intentar actualizar una taxonomía con título repetido")]
    public function test_fails_on_update_a_duplicated_title_taxonomy()
    {
        $this->expectException(\InvalidArgumentException::class);

        $taxonomy = new Taxonomy();
        $taxonomies = $this->createTaxonomies($taxonomy);

        $taxonomy->update($taxonomies[0]["name"], "updated-taxonomy", $taxonomies[0]["slug"]);
    }

    #[TestDox("Arroja una excepción al intentar actualizar una taxonomía con slug repetido")]
    public function test_fails_on_update_a_duplicated_slug_taxonomy()
    {
        $this->expectException(\InvalidArgumentException::class);

        $taxonomy = new Taxonomy();
        $taxonomies = $this->createTaxonomies($taxonomy);

        $taxonomy->update("Updated Taxonomy", $taxonomies[0]["slug"], $taxonomies[0]["slug"]);
    }

    #[TestDox("Verifica que se hayan actualizado datos en la DB")]
    public function test_check_if_data_was_updated()
    {
        $taxonomy = new Taxonomy();
        $taxonomies = $this->createTaxonomies($taxonomy);

        $new_taxonomy_title = "Updated Taxonomy";

        $taxonomy->update($new_taxonomy_title, "update-taxonomy", $taxonomies[0]["slug"]);

        $updated_taxonomies = $taxonomy->getAll();

        $this->assertEquals($updated_taxonomies[0]["name"], $new_taxonomy_title);
    }

    #[TestDox("Devuelve un false al intentar eliminar una taxonomía inexistente")]
    public function test_fails_on_delete_unknown_taxonomy()
    {
        $taxonomy = new Taxonomy();
        $this->createTaxonomies($taxonomy);

        $result = $taxonomy->delete("unknown-taxonomy");

        $this->assertFalse($result);
    }

    #[TestDox("Devuelve un true al eliminar una taxonomía")]
    public function test_asserts_on_delete_taxonomy()
    {
        $taxonomy = new Taxonomy();
        $taxonomies = $this->createTaxonomies($taxonomy);

        $result = $taxonomy->delete($taxonomies[0]["slug"]);

        $this->assertTrue($result);
    }
}
