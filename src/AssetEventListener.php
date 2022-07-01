<?php

namespace Legrisch\StatamicGraphQlResponsiveImages;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Statamic\Events\AssetSaved;
use Statamic\Facades\Glide;

class AssetEventListener
{

    public function handle(AssetSaved $event)
    {
        if (!config("statamic.graphql-responsive-images.experimental-cache-management")) return;

        try {
            /** @var Cache $cache */
            $cache = Glide::cacheStore();

            $asset = $event->asset;
            $assetId = $asset->id;
            $cacheKeys = Cache::get('statamic-graphql-responsive-images::' . $assetId) ?? [];

            foreach ($cacheKeys as $key) {
                $has = $cache->has($key);
                if ($has) {
                    $cache->forget($key);
                }
            }

            $dirname = 'containers/'.$asset->containerHandle().'/'.$asset->path();
            GraphQLProvider::getGlideServer()->deleteCache($dirname);
        } catch (\Throwable $e) {
            Log::warning("Unable to delete glide cache for asset");
            Log::error($e->getMessage());
        }

    }
}
