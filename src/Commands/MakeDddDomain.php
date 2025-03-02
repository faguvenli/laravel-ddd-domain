<?php

namespace Laravelddd\Domain\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeDddDomain extends Command
{
    protected $signature = 'make:ddd-domain {name : The name of the domain}';
    protected $description = 'Create a new DDD domain structure';

    public function handle()
    {
        $name = $this->argument('name');
        $singularName = Str::singular($name);
        $pluralName = Str::plural($name);
        $studlyName = Str::studly($singularName);
        $camelName = Str::camel($singularName);
        $lowerName = Str::lower($singularName);

        $this->info("Creating DDD structure for {$studlyName} domain...");

        // Create domain directories
        $this->createDirectories($studlyName);

        // Create domain files
        $this->createDomainFiles($studlyName, $camelName);

        // Create API directories and files
        $this->createApiFiles($studlyName, $camelName, $lowerName, $pluralName);

        // Update routes file
        $this->updateRoutesFile($studlyName, $lowerName, $pluralName);

        $this->info('DDD domain structure created successfully!');
        $this->info("Don't forget to run 'composer dump-autoload' to update autoloading.");
    }

    protected function createDirectories($name)
    {
        $domainBasePath = config('laravelddd-domain.paths.domain', 'src/Domain');
        $appBasePath = config('laravelddd-domain.paths.app', 'src/app');
        
        $directories = [
            // Domain directories
            "{$domainBasePath}/{$name}/Actions",
            "{$domainBasePath}/{$name}/DataTransferObjects",
            "{$domainBasePath}/{$name}/Models",
            "{$domainBasePath}/{$name}/Exceptions",
            "{$domainBasePath}/{$name}/QueryBuilders",

            // App directories
            "{$appBasePath}/Api/{$name}/Controllers",
            "{$appBasePath}/Api/{$name}/Factories",
            "{$appBasePath}/Api/{$name}/Queries",
            "{$appBasePath}/Api/{$name}/Requests",
            "{$appBasePath}/Api/{$name}/Resources",
        ];

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->line("<info>Created directory:</info> {$directory}");
            }
        }
    }

    protected function createDomainFiles($name, $camelName)
    {
        $domainBasePath = config('laravelddd-domain.paths.domain', 'src/Domain');
        
        // Create Model
        $this->createFile(
            "{$domainBasePath}/{$name}/Models/{$name}.php",
            $this->getModelStub($name)
        );

        // Create Data Transfer Object
        $this->createFile(
            "{$domainBasePath}/{$name}/DataTransferObjects/{$name}Data.php",
            $this->getDtoStub($name)
        );

        // Create QueryBuilder
        $this->createFile(
            "{$domainBasePath}/{$name}/QueryBuilders/{$name}QueryBuilder.php",
            $this->getQueryBuilderStub($name, $camelName)
        );

        // Create Actions
        $this->createFile(
            "{$domainBasePath}/{$name}/Actions/{$name}CreateAction.php",
            $this->getCreateActionStub($name)
        );

        $this->createFile(
            "{$domainBasePath}/{$name}/Actions/{$name}UpdateAction.php",
            $this->getUpdateActionStub($name)
        );
    }

    protected function createApiFiles($name, $camelName, $lowerName, $pluralName)
    {
        $appBasePath = config('laravelddd-domain.paths.app', 'src/app');
        
        // Create Controller
        $this->createFile(
            "{$appBasePath}/Api/{$name}/Controllers/{$name}Controller.php",
            $this->getControllerStub($name, $pluralName)
        );

        // Create Requests
        $this->createFile(
            "{$appBasePath}/Api/{$name}/Requests/{$name}CreateRequest.php",
            $this->getCreateRequestStub($name, $lowerName)
        );

        $this->createFile(
            "{$appBasePath}/Api/{$name}/Requests/{$name}UpdateRequest.php",
            $this->getUpdateRequestStub($name, $lowerName)
        );

        // Create Factories
        $this->createFile(
            "{$appBasePath}/Api/{$name}/Factories/{$name}CreateDataFactory.php",
            $this->getCreateDataFactoryStub($name)
        );

        $this->createFile(
            "{$appBasePath}/Api/{$name}/Factories/{$name}UpdateDataFactory.php",
            $this->getUpdateDataFactoryStub($name)
        );

        // Create Resource
        $this->createFile(
            "{$appBasePath}/Api/{$name}/Resources/{$name}Resource.php",
            $this->getResourceStub($name)
        );

        // Create Query
        $this->createFile(
            "{$appBasePath}/Api/{$name}/Queries/{$name}IndexQuery.php",
            $this->getIndexQueryStub($name, $camelName)
        );
    }

    protected function createFile($path, $content)
    {
        if (! File::exists($path)) {
            File::put($path, $content);
            $this->line("<info>Created file:</info> {$path}");
        } else {
            $this->line("<comment>File already exists:</comment> {$path}");
        }
    }

    protected function updateRoutesFile($name, $lowerName, $pluralName)
    {
        $apiRoutesPath = base_path('routes/api.php');
        $apiRoutes = File::get($apiRoutesPath);

        // Check if the controller import already exists
        $controllerImport = "use App\\Api\\{$name}\\Controllers\\{$name}Controller;";
        if (! Str::contains($apiRoutes, $controllerImport)) {
            // Add the controller import
            $apiRoutes = preg_replace(
                '/use (.*);/',
                "use App\\Api\\{$name}\\Controllers\\{$name}Controller;\nuse $1;",
                $apiRoutes,
                1
            );
        }

        // Check if the route registration already exists
        $routeRegistration = "Route::apiResource('{$pluralName}', {$name}Controller::class);";
        if (! Str::contains($apiRoutes, $routeRegistration)) {
            // Check if v1 group exists
            if (Str::contains($apiRoutes, "Route::prefix('v1')->group(function () {")) {
                // Add to existing v1 group
                $apiRoutes = preg_replace(
                    "/(Route::prefix\('v1'\)->group\(function \(\) \{\n[^}]*)(}\);)/s",
                    "$1    Route::apiResource('{$pluralName}', {$name}Controller::class);\n$2",
                    $apiRoutes
                );
            } else {
                // Create new v1 group with the route registration
                $apiRoutes .= "\nRoute::prefix('v1')->group(function () {\n    Route::apiResource('{$pluralName}', {$name}Controller::class);\n});\n";
            }
        }

        File::put($apiRoutesPath, $apiRoutes);
        $this->line("<info>Updated file:</info> {$apiRoutesPath}");
    }

    protected function getModelStub($name)
    {
        return "<?php

namespace Domain\\{$name}\\Models;

use Domain\\{$name}\\QueryBuilders\\{$name}QueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasFactory;

    protected \$fillable = [
        // Define your fillable attributes here
    ];

    public function newEloquentBuilder(\$query): {$name}QueryBuilder
    {
        return new {$name}QueryBuilder(\$query);
    }
}
";
    }

    protected function getDtoStub($name)
    {
        return "<?php

namespace Domain\\{$name}\\DataTransferObjects;

class {$name}Data
{
    public function __construct(
        // Define your constructor parameters here
        // Example: public readonly string \$name
    ) {
    }

    public static function fromArray(array \$data): self
    {
        return new self(
            // Map array keys to constructor parameters
            // Example: name: \$data['name']
        );
    }
}
";
    }

    protected function getQueryBuilderStub($name, $camelName)
    {
        return "<?php

namespace Domain\\{$name}\\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class {$name}QueryBuilder extends Builder
{
    // Add your query methods here
    // Example:
    // public function whereActive(bool \$active = true): self
    // {
    //     return \$this->where('is_active', \$active);
    // }
}
";
    }

    protected function getCreateActionStub($name)
    {
        return "<?php

namespace Domain\\{$name}\\Actions;

use Domain\\{$name}\\DataTransferObjects\\{$name}Data;
use Domain\\{$name}\\Models\\{$name};

class {$name}CreateAction
{
    public function execute({$name}Data \${$name}Data): {$name}
    {
        return {$name}::create([
            // Map DTO properties to model attributes
            // Example: 'name' => \${$name}Data->name
        ]);
    }
}
";
    }

    protected function getUpdateActionStub($name)
    {
        return "<?php

namespace Domain\\{$name}\\Actions;

use Domain\\{$name}\\DataTransferObjects\\{$name}Data;
use Domain\\{$name}\\Models\\{$name};

class {$name}UpdateAction
{
    public function execute({$name} \${$name}, {$name}Data \${$name}Data): {$name}
    {
        \${$name}->update([
            // Map DTO properties to model attributes
            // Example: 'name' => \${$name}Data->name
        ]);

        return \${$name}->refresh();
    }
}
";
    }

    protected function getControllerStub($name, $pluralName)
    {
        return "<?php

namespace App\\Api\\{$name}\\Controllers;

use App\\Api\\{$name}\\Requests\\{$name}CreateRequest;
use App\\Api\\{$name}\\Requests\\{$name}UpdateRequest;
use App\\Api\\{$name}\\Resources\\{$name}Resource;
use App\\Api\\{$name}\\Queries\\{$name}IndexQuery;
use App\\Api\\{$name}\\Factories\\{$name}CreateDataFactory;
use App\\Api\\{$name}\\Factories\\{$name}UpdateDataFactory;
use App\\Http\\Controllers\\Controller;
use Domain\\{$name}\\Actions\\{$name}CreateAction;
use Domain\\{$name}\\Actions\\{$name}UpdateAction;
use Domain\\{$name}\\Models\\{$name};
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Resources\\Json\\AnonymousResourceCollection;

class {$name}Controller extends Controller
{
    public function index({$name}IndexQuery \$query): AnonymousResourceCollection
    {
        \${$pluralName} = \$query->paginate();

        return {$name}Resource::collection(\${$pluralName});
    }

    public function store(
        {$name}CreateRequest \$request,
        {$name}CreateDataFactory \$factory,
        {$name}CreateAction \$action
    ): {$name}Resource {
        \${$name}Data = \$factory->create(\$request);
        \${$name} = \$action->execute(\${$name}Data);

        return {$name}Resource::make(\${$name});
    }

    public function show({$name} \${$name}): {$name}Resource
    {
        return {$name}Resource::make(\${$name});
    }

    public function update(
        {$name} \${$name},
        {$name}UpdateRequest \$request,
        {$name}UpdateDataFactory \$factory,
        {$name}UpdateAction \$action
    ): {$name}Resource {
        \${$name}Data = \$factory->create(\$request);
        \${$name} = \$action->execute(\${$name}, \${$name}Data);

        return {$name}Resource::make(\${$name});
    }

    public function destroy({$name} \${$name}): JsonResponse
    {
        \${$name}->delete();

        return response()->json(['message' => '{$name} deleted successfully']);
    }
}
";
    }

    protected function getCreateRequestStub($name, $lowerName)
    {
        return "<?php

namespace App\\Api\\{$name}\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class {$name}CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Define validation rules for creating a {$lowerName}
            // Example: 'name' => ['required', 'string', 'max:255']
        ];
    }
}
";
    }

    protected function getUpdateRequestStub($name, $lowerName)
    {
        return "<?php

namespace App\\Api\\{$name}\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class {$name}UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Define validation rules for updating a {$lowerName}
            // Example: 'name' => ['sometimes', 'string', 'max:255']
        ];
    }
}
";
    }

    protected function getCreateDataFactoryStub($name)
    {
        return "<?php

namespace App\\Api\\{$name}\\Factories;

use App\\Api\\{$name}\\Requests\\{$name}CreateRequest;
use Domain\\{$name}\\DataTransferObjects\\{$name}Data;

class {$name}CreateDataFactory
{
    public function create({$name}CreateRequest \$request): {$name}Data
    {
        return {$name}Data::fromArray([
            // Map request data to DTO properties
            // Example: 'name' => \$request->name
        ]);
    }
}
";
    }

    protected function getUpdateDataFactoryStub($name)
    {
        return "<?php

namespace App\\Api\\{$name}\\Factories;

use App\\Api\\{$name}\\Requests\\{$name}UpdateRequest;
use Domain\\{$name}\\DataTransferObjects\\{$name}Data;
use Domain\\{$name}\\Models\\{$name};

class {$name}UpdateDataFactory
{
    public function create({$name}UpdateRequest \$request): {$name}Data
    {
        /** @var {$name} \${$name} */
        \${$name} = \$request->{$name};

        return {$name}Data::fromArray([
            // Map request data to DTO properties with fallback to existing values
            // Example: 'name' => \$request->name ?? \${$name}->name
        ]);
    }
}
";
    }

    protected function getResourceStub($name)
    {
        return "<?php

namespace App\\Api\\{$name}\\Resources;

use Domain\\{$name}\\Models\\{$name};
use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        /** @var {$name} \$this */
        return [
            'id' => \$this->id,
            // Map model attributes to resource fields
            // Example: 'name' => \$this->name,
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
";
    }

    protected function getIndexQueryStub($name, $camelName)
    {
        return "<?php

namespace App\\Api\\{$name}\\Queries;

use Domain\\{$name}\\Models\\{$name};
use Illuminate\\Database\\Eloquent\\Builder;
use Illuminate\\Http\\Request;
use Illuminate\\Pagination\\LengthAwarePaginator;

class {$name}IndexQuery
{
    protected Builder \$query;

    public function __construct(
        protected Request \$request
    ) {
        \$this->query = {$name}::query();
        \$this->applyFilters();
        \$this->applySorting();
    }

    protected function applyFilters(): void
    {
        // Apply filters based on request parameters
        // Example:
        // if (\$this->request->has('is_active')) {
        //     \$this->query->whereActive(\$this->request->boolean('is_active'));
        // }
    }

    protected function applySorting(): void
    {
        \$sortBy = \$this->request->get('sort_by', 'created_at');
        \$sortDirection = \$this->request->get('sort_direction', 'desc');

        \$this->query->orderBy(\$sortBy, \$sortDirection);
    }

    public function paginate(int \$perPage = 15): LengthAwarePaginator
    {
        \$perPage = \$this->request->get('per_page', \$perPage);

        return \$this->query->paginate(\$perPage);
    }
}
";
    }
}