<?php

namespace Legrisch\StatamicGraphQlResponsiveImages;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Statamic\Assets\Asset;
use Statamic\Events\GlideImageGenerated;

class GlideEventListener
{

    public function handle(GlideImageGenerated $event)
    {
        if (!config("statamic.graphql-responsive-images.experimental-cache-management")) return;

        try {
            // removing the cached image path
            $pathWithoutCachedImage = preg_replace('/\/([^\/])*$/', '', $event->path);

            // removing /containers from $path
            $pathWithoutContainer = substr($pathWithoutCachedImage, 11);

            $matchesContainer = [];
            preg_match('/^[^\/]*/', $pathWithoutContainer, $matchesContainer);
            $container = $matchesContainer[0];

            // replace / for :: to get asset id
            $assetId = str_replace($container.'/', $container.'::', $pathWithoutContainer);

            // get asset
            /** @var Asset $asset */
            $asset = \Statamic\Facades\Asset::findById($assetId);
            $assetId = $asset->id;
            $cacheKey = 'asset::'.$assetId.'::'.md5(json_encode($event->params));

            // add new cache key to existing cache keys for asset
            $cacheKeys = Cache::get('statamic-graphql-responsive-images::'.$assetId) ?? [];
            if (!in_array($cacheKey, $cacheKeys))
            {
                array_push($cacheKeys, $cacheKey);
            }

            // store new cache keys for asset
            Cache::forever('statamic-graphql-responsive-images::'.$assetId, $cacheKeys);
        } catch (\Throwable $e) {
            Log::warning("Unable to add cache key to cache keys of asset");
            Log::error($e->getMessage());
        }
    }
}
