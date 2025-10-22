<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeAPIService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'make:service {name : The name of the service class, including optional subfolders}';
    protected $signature = 'make:api-service {name : The name of the API service class, including optional subfolders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class with standard methods and try-catch blocks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Extract subdirectories and class name
        $pathParts = explode('/', $name);
        $className = array_pop($pathParts);
        $namespace = implode('\\', $pathParts);

        $directory = app_path('Services/API/' . implode('/', $pathParts));
        $filePath = $directory . '/' . $className . '.php';

        // Ensure the directory exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info('Services directory created.');
        }

        // Check if the file already exists
        if (File::exists($filePath)) {
            $this->error("Service {$className} already exists!");
            return Command::FAILURE;
        }

        // Generate the service class content with methods and try-catch blocks
        $namespaceDeclaration = 'App\\Services\\API' . ($namespace ? "\\{$namespace}" : '');
        $content = <<<EOT
<?php

namespace {$namespaceDeclaration};

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class {$className}
{
    protected \$user;

    public function __construct()
    {
        \$this->user = Auth::user();
    }
    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function index(\$request)
    {
        try {
            
        } catch (Exception \$e) {
         Log::error("{$className}::index" . \$e->getMessage());
            throw \$e;
             
        }
    }

    /**
     * Store a new resource.
     *
     * @param array \$validatedData
     * @return mixed
     */
    public function store(array \$validatedData)
    {
        try {
            
        } catch (Exception \$e) {
         Log::error("{$className}::store" . \$e->getMessage());
            throw \$e;
        }
    }

    /**
     * Display a specific resource.
     *
     * @param int \$id
     * @return mixed
     */
    public function show(int \$id)
    {
        try {
            
        } catch (Exception \$e) {
         Log::error("{$className}::show" . \$e->getMessage());
            throw \$e;
        }
    }

    /**
     * Update a specific resource.
     *
     * @param int \$id
     * @param array \$validatedData
     * @return mixed
     */
    public function update(int \$id, array \$validatedData)
    {
        try {
            
        } catch (Exception \$e) {
         Log::error("{$className}::update" . \$e->getMessage());
            throw \$e;
        }
    }

    /**
     * Delete a specific resource.
     *
     * @param int \$id
     * @return mixed
     */
    public function destroy(int \$id)
    {
        try {
            // Logic to delete a specific resource
        } catch (Exception \$e) {
         Log::error("{$className}::destroy" . \$e->getMessage());
            throw \$e;
        }
    }

}
EOT;

        // Create the file
        File::put($filePath, $content);

        $this->info("Service {$className} created successfully in namespace {$namespaceDeclaration}!");
        return Command::SUCCESS;
    }
}
