<?php

use PHPunit\Framework\TestCase;
use App\Models\Taxonomy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Taxonomy::class)]
class TaxonomyTest extends TestCase
{
    #[TestDox("El slug de la taxonomía no existe")]
    public function test_throws_exception_when_taxonomy_does_not_exist()
    {
        $this->expectException(RuntimeException::class);

        new Taxonomy("foo");
    }
}
