<?php

namespace Legrisch\StatamicGraphQlResponsiveImages;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use Illuminate\Support\Facades\Log;
use Statamic\Assets\Asset;
use Statamic\Support\Str;

class GraphQLProvider
{
  private static function makeAbsoluteUrl(string $url) : string {
    if (! Str::startsWith($url, '/')) {
      return $url;
    }
    return URL::tidy(Str::ensureLeft($url, config('app.url')));
  }

  private static function manipulateImage(Asset $asset, int $width, int $height, bool $webp): string
  {
    if (isset($width)) {
      $options['w'] = $width;
      $options['h'] = $height;
    }

    if (isset($webp) && $webp) {
      $options['fm'] = "webp";
    }

    $url = Image::manipulate($asset, $options);
    return self::makeAbsoluteUrl($url);
  }

  private static function validateArguments(
    ?int $width,
    ?int $height,
    ?float $ratio,
    ?bool $webp
  ) {
    // No mixing of ratio and width/height
    if ((isset($width) || isset($height)) && isset($ratio)) {
      throw new \Exception("Parameters width and height and ratio mixed. Please provide either parameters width and height or parameter ratio.", 1);
    }
    
    if ((isset($width) && !isset($height)) || (!isset($width) && isset($height))) {
      throw new \Exception("Provide both parameters width and height to define a ratio.", 1);
    }
    
    if (!is_bool($webp)) {
      throw new \Exception("Parameter webp is not boolean.", 1);
    }
    
    if (isset($ratio) && $ratio <= 0) {
      throw new \Exception("Parameter ratio needs to be larger than 0.", 1);
    }
    if (isset($width) && $width < 1) {
      throw new \Exception("Parameter 'width' less than 1.", 1);
    }

    if (isset($height) && $height < 1) {
      throw new \Exception("Parameter 'height' less than 1.", 1);
    }
  }

  static public function createSrcSet(
    Asset $asset,
    ?float $ratio,
    ?bool $webp
  ) {

    $widths = config('statamic.graphql-responsive-images.widths');

    $srcSetItems = [];

    foreach ($widths as $width) {
      $height = $width / $ratio;
      $url = self::manipulateImage($asset, $width, $height, $webp);
      $itemString = $url . " " . $width . "w";
      array_push($srcSetItems, $itemString);
    }

    return implode(", ", $srcSetItems);
  }

  static private function parseRatio(
    ?int $width,
    ?int $height,
    ?float $ratio,
    Asset $asset
  ) :float {
    if (isset($ratio)) return $ratio;
    if (isset($width) && isset($height)) return $width / $height;
    return $asset->width() / $asset->height();
  }

  static public function createFields()
  {
    GraphQL::addField('AssetInterface', 'srcset', function () {

      $arguments = [
        "width" => [
          'type' => GraphQL::int(),
        ],
        "height" => [
          'type' => GraphQL::int(),
        ],
        "ratio" => [
          'type' => GraphQL::float(),
        ],
        "webp" => [
          'type' => GraphQL::boolean(),
        ]
      ];

      return [
        'type' => GraphQL::string(),
        'args' => $arguments,
        'resolve' => function (Asset $asset, $args) {
          try {
            // Confirm it's an image
            if ($asset === null || !$asset->isImage()) return null;

            $width = $args["width"] ?? null;
            $height = $args["height"] ?? null;
            $ratio = $args["ratio"] ?? null;
            $webp = $args["webp"] ?? false;

            self::validateArguments($width, $height, $ratio, $webp);

            $ratio = self::parseRatio($width, $height, $ratio, $asset);

            return self::createSrcSet($asset, $ratio, $webp);
          } catch (\Throwable $th) {
            Log::error("Unable to resolve field 'srcset': " . $th->getMessage());
            throw new \Exception("Unable to resolve field 'srcset': " . $th->getMessage(), 1);
          }
        },
      ];
    });
  }
}
