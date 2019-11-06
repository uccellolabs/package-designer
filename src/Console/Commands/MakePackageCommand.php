<?php

namespace Uccello\PackageDesigner\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakePackageCommand extends Command
{
    /**
     * The structure of the package.
     *
     * @var string
     */
    protected $package;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:package
                        {name? : Package name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Ask package information
        $this->askPackageInformation();

        // Make package
        $packageMade = $this->makePackage();

        if ($packageMade) {
            // Add local repository
            $this->addLocalRepository();

            $this->info('Package created!');
            $this->info('You can install with <comment>composer require ' . $this->package->name . '</comment>');
        } else {
            $this->error('Package not made');
        }
    }

    /**
     * Ask the user information to make the skeleton of the package.
     *
     * @return void
     */
    protected function askPackageInformation()
    {
        // Get package name from argument or ask it
        if ($this->argument('name')) {
            $packageName = $this->argument('name');
        } else {
            $packageName = $this->ask('<info>What is the package name? (e.g. vendor/package)</info>');
        }

        // The kebab_case function converts the given string to kebab-case
        $packageName = kebab_case($packageName);

        // If module name is not defined, ask again
        if (!$packageName) {
            $this->error('You must specify a package name');
            return $this->createPackage();
        }
        // Check if package name is only with alphanumeric characters
        elseif (!preg_match('`^[a-z0-9-]+/[a-z0-9-]+$`', $packageName)) {
            $this->error('You must use only alphanumeric characters');
            return $this->createPackage();
        }

        $packageData = explode('/', $packageName);

        // Create an empty object
        $this->package = new \stdClass();

        // Name
        $this->package->name = $packageName;

        // Vendor
        $this->package->vendor = $packageData[0];

        // Package
        $this->package->package = $packageData[1];

        // Description
        $this->package->description = $this->ask('Description');

        // Author name
        $this->package->authorName = $this->ask('Author name (e.g. John Smith)');

        // Author email
        $this->package->authorEmail = $this->ask('Author email (e.g. john@smith.com)');

        // Namespace
        $defaultNamespace = studly_case($this->package->vendor) . '\\' . studly_case($this->package->package); // The studly_case function converts the given string to StudlyCase
        $this->package->namespace = $this->ask('Namespace', $defaultNamespace);

        // Display module data
        $this->table(
            [
                'Name',
                'Description',
                'Author',
                'Email',
                'Namespace'
            ],
            [
                [
                    $this->package->name,
                    $this->package->description,
                    $this->package->authorName,
                    $this->package->authorEmail,
                    $this->package->namespace
                ]
            ]
        );

        // If information is not correct, restart step
        $isCorrect = $this->confirm('Is this information correct?', true);
        if (!$isCorrect) {
            return $this->createPackage();
        }
    }

    /**
     * Use module-skeleton to make a new package
     *
     * @return void
     */
    protected function makePackage()
    {
        // Make directory
        $packageMade = $this->makeDirectory();

        if ($packageMade) {
            // Generate composer.json
            $this->generateComposerJsonFile();

            // Generate webpack.mix.js
            $this->generateWebpackMixFile();

            // Generate src/Providers/AppServiceProvider.php
            $this->generateAppServiceProviderFile();

            // Generate src/Http/routes.php
            $this->generateRoutesFile();

            // Delete not necessary files and folders
            $this->deleteFilesAndFolders();
        }

        return $packageMade;
    }

    /**
     * Make package directory and copy package-skeleton files
     *
     * @return boolean
     */
    protected function makeDirectory(): bool
    {
        $packagePath = 'packages/' . $this->package->vendor . '/' . $this->package->package;

        if (File::exists($packagePath)) {
            $this->error('This package already exists');

            return false;
        }

        // Save path
        $this->package->path = $packagePath;

        // Make directory
        File::makeDirectory($packagePath, 0755, true);

        // Copy files from package-skeleton
        File::copyDirectory('vendor/uccello/package-skeleton', $packagePath);

        return true;
    }

    /**
     * Generate composer.json file
     *
     * @return void
     */
    protected function generateComposerJsonFile()
    {
        $filePath = $this->package->path . '/composer.json';

        // Get file content
        $content = file_get_contents($filePath);

        // Formatted namespace
        $namespace = str_replace('\\', '\\\\', $this->package->namespace);

        // Replace data
        $content = str_replace(
            [
                'uccello/package-skeleton',
                'Uccello\\\\PackageSkeleton',
                'Package skeleton for Uccello',
                'Jonathan SARDO',
                'jonathan@uccellolabs.com',
                '"laravel": {}'
            ],
            [
                $this->package->name,
                $namespace,
                $this->package->description,
                $this->package->authorName,
                $this->package->authorEmail,
                '"laravel": {' . "\n" .
                '            "providers": [' . "\n" .
                '                "' . $namespace . '\\\\Providers\\\\AppServiceProvider"' . "\n" .
                '            ]' . "\n" .
                '        }'
            ],
            $content);

        // Save data
        file_put_contents($filePath, $content);
    }

    /**
     * Generate webpack.mix.js file
     *
     * @return void
     */
    protected function generateWebpackMixFile()
    {
        $filePath = $this->package->path . '/webpack.mix.js';

        // Get file content
        $content = file_get_contents($filePath);

        // Replace data
        $content = str_replace(
            [
                'uccello/package-skeleton',
            ],
            [
                $this->package->name,
            ],
            $content);

        // Save data
        file_put_contents($filePath, $content);
    }

    /**
     * Generate src/Providers/AppServiceProvider.php file
     *
     * @return void
     */
    protected function generateAppServiceProviderFile()
    {
        $filePath = $this->package->path . '/src/Providers/AppServiceProvider.php';

        // Get file content
        $content = file_get_contents($filePath);

        // Replace data
        $content = str_replace(
            [
                'uccello/package-skeleton',
                'Uccello\\PackageSkeleton',
                'package-skeleton',
            ],
            [
                $this->package->name,
                $this->package->namespace,
                $this->package->package,
            ],
            $content);

        // Save data
        file_put_contents($filePath, $content);
    }

    /**
     * Generate src/Http/routes.php file
     *
     * @return void
     */
    protected function generateRoutesFile()
    {
        $filePath = $this->package->path . '/src/Http/routes.php';

        // Get file content
        $content = file_get_contents($filePath);

        // Replace data
        $content = str_replace(
            [
                'Uccello\\PackageSkeleton',
                'package-skeleton',
            ],
            [
                $this->package->namespace,
                $this->package->package,
            ],
            $content);

        // Save data
        file_put_contents($filePath, $content);
    }

    /**
     * Add local repository to root composer.json
     *
     * @return void
     */
    protected function addLocalRepository()
    {
        $content = file_get_contents('composer.json');

        $composerData = json_decode($content);

        // Add repository if does not exist
        if (!isset($composerData->repositories)) {
            $composerData->repositories = [];
        }

        $repository = [
            'type' => 'path',
            'url' => './' . $this->package->path
        ];

        $composerData->repositories[] = $repository;

        // Save file
        file_put_contents('composer.json', json_encode($composerData, JSON_PRETTY_PRINT));
    }

    /**
     * Delete not necessary files and folders
     *
     * @return void
     */
    protected function deleteFilesAndFolders()
    {
        // Delete README.md
        unlink($this->package->path . '/README.md');

        // Delete .git directory (because it is for package-skeleton)
        $this->removeDirectory(($this->package->path . '/.git'));
    }

    /**
     * Remove a directory and files inside
     *
     * @param string $path
     * @return void
     */
    protected function removeDirectory(string $path) {
        if (!is_dir($path)) {
            return;
        }

        $files = glob($path . '/*');

        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }

        rmdir($path);
   }
}
