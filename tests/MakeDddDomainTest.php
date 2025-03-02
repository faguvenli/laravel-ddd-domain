<?php

namespace Laravelddd\Domain\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Laravelddd\Domain\LaravelDddDomainServiceProvider;

class MakeDddDomainTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDddDomainServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test directories if they don't exist
        if (!File::isDirectory('src')) {
            File::makeDirectory('src');
        }
        
        if (!File::isDirectory('routes')) {
            File::makeDirectory('routes');
        }
        
        // Create a test api.php routes file if it doesn't exist
        if (!File::exists('routes/api.php')) {
            File::put('routes/api.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        }
    }

    protected function tearDown(): void
    {
        // Clean up test directories
        if (File::isDirectory('src')) {
            File::deleteDirectory('src');
        }
        
        if (File::isDirectory('routes')) {
            File::deleteDirectory('routes');
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_creates_domain_files_and_directories()
    {
        // Run the command
        Artisan::call('make:ddd-domain', ['name' => 'Test']);
        
        // Check if directories are created
        $this->assertTrue(File::isDirectory('src/Domain/Test/Actions'));
        $this->assertTrue(File::isDirectory('src/Domain/Test/DataTransferObjects'));
        $this->assertTrue(File::isDirectory('src/Domain/Test/Models'));
        $this->assertTrue(File::isDirectory('src/Domain/Test/QueryBuilders'));
        
        $this->assertTrue(File::isDirectory('src/app/Api/Test/Controllers'));
        $this->assertTrue(File::isDirectory('src/app/Api/Test/Requests'));
        $this->assertTrue(File::isDirectory('src/app/Api/Test/Resources'));
        
        // Check if files are created
        $this->assertTrue(File::exists('src/Domain/Test/Models/Test.php'));
        $this->assertTrue(File::exists('src/Domain/Test/DataTransferObjects/TestData.php'));
        $this->assertTrue(File::exists('src/Domain/Test/Actions/TestCreateAction.php'));
        $this->assertTrue(File::exists('src/app/Api/Test/Controllers/TestController.php'));
    }
}