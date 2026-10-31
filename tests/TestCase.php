<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    /** Uploads made by tests go to a throw-away folder that is removed afterwards, never into the real public/uploads. */
    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.upload_dir' => 'uploads_test']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads_test'));
        parent::tearDown();
    }
}
