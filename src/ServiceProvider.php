<?php

namespace Legrisch\StatamicGraphQlResponsiveImages;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{

    public function register()
    {
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'statamic.graphql-responsive-images'
        );
    }
    
    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('statamic/graphql-responsive-images.php'),
        ], 'statamic.graphql-responsive-images');

        GraphQLProvider::createFields();
    }
}
