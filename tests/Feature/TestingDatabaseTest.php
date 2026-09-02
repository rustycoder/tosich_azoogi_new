<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TestingDatabaseTest extends TestCase
{
    public function test_feature_tests_use_isolated_sqlite_memory(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertNull(config('database.connections.sqlite.url'));
        $this->assertSame('testing_forbidden', config('database.connections.mysql.database'));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }
}
