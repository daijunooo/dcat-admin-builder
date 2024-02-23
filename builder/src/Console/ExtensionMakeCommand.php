<?php

namespace System\Builder\Console;


use Dcat\Admin\Support\Helper;
use Illuminate\Support\Str;

class ExtensionMakeCommand extends \Dcat\Admin\Console\ExtensionMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:ext-build 
    {name : The name of the extension. Eg: author-name/extension-name} 
    {--namespace= : The namespace of the extension.}
    {--theme}
    {--alias= : The alias of the extension.}
    {--description= : The description of the extension.}
    {--author= : The author of the extension.}
    {--email= : The email of the extension.}
    ';

    protected $description = 'Build a dcat-admin app';


    protected function copyFiles()
    {
        $files = [
            $view = __DIR__.'/stubs/extension/view.stub' => 'resources/views/index.blade.php',
            $js = __DIR__.'/stubs/extension/js.stub'     => 'resources/assets/js/index.js',
            __DIR__.'/stubs/extension/css.stub'          => 'resources/assets/css/index.css',
            __DIR__.'/stubs/extension/.gitignore.stub'   => '.gitignore',
            __DIR__.'/stubs/extension/README.md.stub'    => 'README.md',
            __DIR__.'/stubs/extension/version.stub'      => 'version.php',
        ];

        if ($this->option('theme')) {
            unset($files[$view], $files[$js]);
        }

        $this->copy($files);
    }


    /**
     * Make extension files.
     */
    protected function makeFiles()
    {
        $this->namespace = $this->getRootNameSpace();

        $this->className = $this->getClassName();

        // copy files
        $this->copyFiles();

        // make composer.json
        $composerContents = str_replace(
            ['{package}', '{alias}', '{namespace}', '{className}', '{description}', '{author}', '{email}'],
            [$this->package, $this->option('alias'), str_replace('\\', '\\\\', $this->namespace).'\\\\', $this->className, $this->option('description'), $this->option('author'), $this->option('email')],
            file_get_contents(__DIR__.'/stubs/extension/composer.json.stub')
        );
        $this->putFile('composer.json', $composerContents);

        // make Setting
        $settingContents = str_replace(
            ['{namespace}'],
            [$this->namespace],
            file_get_contents(__DIR__.'/stubs/extension/setting.stub')
        );
        $this->putFile('src/Setting.php', $settingContents);

        $basePackage = Helper::slug(basename($this->package));

        // make class
        $classContents = str_replace(
            ['{namespace}', '{className}', '{title}', '{path}', '{basePackage}', '{property}', '{registerTheme}'],
            [
                $this->namespace,
                $this->className,
                Str::title($this->className),
                $basePackage,
                $basePackage,
                $this->makeProviderContent(),
                $this->makeRegisterThemeContent(),
            ],
            file_get_contents(__DIR__.'/stubs/extension/extension.stub')
        );
        $this->putFile("src/{$this->className}ServiceProvider.php", $classContents);

        if (! $this->option('theme')) {
            // make backend controller
            $controllerContent = str_replace(
                ['{namespace}', '{className}', '{name}'],
                [$this->namespace, $this->className, $this->extensionName],
                file_get_contents(__DIR__.'/stubs/extension/controller.stub')
            );
            $this->putFile("src/Http/Controllers/{$this->className}Controller.php", $controllerContent);

            // make index controller
            $controllerContent = str_replace(
                ['{package}', '{namespace}', '{className}', '{name}'],
                [str_replace('/', '.', $this->package), $this->namespace, $this->className, $this->extensionName],
                file_get_contents(__DIR__.'/stubs/extension/h5controller.stub')
            );
            $this->putFile("src/Http/Controllers/H5Controller.php", $controllerContent);

            // make model
            $model = str_replace(
                ['{namespace}', '{className}', '{table}'],
                [$this->namespace, $this->className, str_replace('\\', '_', strtolower($this->namespace))],
                file_get_contents(__DIR__.'/stubs/extension/model.stub')
            );
            $this->putFile("src/Models/{$this->className}.php", $model);

            // make migration
            $model = str_replace(
                ['{package}', '{className}', '{table}'],
                [$this->package, $this->className, str_replace('\\', '_', strtolower($this->namespace))],
                file_get_contents(__DIR__.'/stubs/extension/create_table.stub')
            );
            $this->putFile("updates/create_table.php", $model);

            $viewContents = str_replace(
                ['{name}'],
                [$this->extensionName],
                file_get_contents(__DIR__.'/stubs/extension/view.stub')
            );
            $this->putFile('resources/views/index.blade.php', $viewContents);

            // make admin routes
            $routesContent = str_replace(
                ['{namespace}', '{className}', '{path}'],
                [$this->namespace, $this->className, $basePackage],
                file_get_contents(__DIR__.'/stubs/extension/routes.stub')
            );
            $this->putFile('src/Http/routes.php', $routesContent);

            // make H5 routes
            $routesContent = str_replace(
                ['{namespace}', '{className}', '{path}'],
                [$this->namespace, $this->className, $basePackage],
                file_get_contents(__DIR__.'/stubs/extension/h5routes.stub')
            );
            $this->putFile('src/routes.php', $routesContent);
        }
    }

}
