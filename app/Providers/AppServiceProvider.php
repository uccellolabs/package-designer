<?php

namespace Uccello\PackageDesigner\Providers;

use Illuminate\Support\ServiceProvider;
use Uccello\PackageDesigner\Console\Commands\MakePackageCommand;

/**
 * App Service Provider
 */
class AppServiceProvider extends ServiceProvider
{
  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = false;

  public function boot()
  {
    // Commands
    if ($this->app->runningInConsole()) {
      $this->commands([
        MakePackageCommand::class,
      ]);
    }
  }

  public function register()
  {

  }
}