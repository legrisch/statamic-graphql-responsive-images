<?php

namespace Legrisch\StatamicGraphQlResponsiveImages;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use Illuminate\Support\Facades\Log;
use Statamic\Assets\Asset;
use Statamic\Support\Str;
use League\Glide\Server;
use Statamic\Imaging\ImageGenerator;

class GraphQLProvider
{

    public static $imageGenerator;
    public static $glideServer;

    private static function getImageGenerator(): ImageGenerator
    {
        if (!self::$imageGenerator) {
            self::$imageGenerator = app(ImageGenerator::class);
        }
        return self::$imageGenerator;
    }

    private static function getGlideServer()
    {
        if (!self::$glideServer) {
            self::$glideServer = app(Server::class);
        }
        return self::$glideServer;
    }

    private static function makeAbsoluteUrl(string $url): string
    {
        if (!Str::startsWith($url, '/')) {
            return $url;
        }
        $baseUrl = config('statamic.graphql-responsive-images.base-url');
        return URL::tidy(Str::ensureLeft($url, $baseUrl));
    }

    private static function manipulateImage(Asset $asset, int $width, int $height, bool $webp, ?bool $base64, int $blur): string
    {
        if (isset($width)) {
            $options['w'] = $width;
            $options['h'] = $height;
        }

        if (isset($webp) && $webp) {
            $options['fm'] = "webp";
        }

        if (isset($blur) && $blur > 0) {
            $options['blur'] = $blur;
        }


        if ($base64) {
            $path = self::getImageGenerator()->generateByAsset($asset, $options);
            $source = base64_encode(self::getGlideServer()->getCache()->read($path));
            return "data:" . self::getGlideServer()->getCache()->getMimetype($path) . ";base64,{$source}";
        } else {
            $url = Image::manipulate($asset, $options);
            return self::makeAbsoluteUrl($url);
        }
    }

    private static function validateSrcsetArguments(
        ?int   $width,
        ?int   $height,
        ?float $ratio
    )
    {
        // No mixing of ratio and width/height
        if ((isset($width) || isset($height)) && isset($ratio)) {
            throw new \Exception("Parameters width and height and ratio mixed. Please provide either parameters width and height or parameter ratio.", 1);
        }

        if ((isset($width) && !isset($height)) || (!isset($width) && isset($height))) {
            throw new \Exception("Provide both parameters width and height to define a ratio.", 1);
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

    private static function validateSrcArguments(
        ?int   $width,
        ?int   $height,
        ?float $ratio,
        int    $blur
    )
    {
        if (isset($width) && isset($height) && isset($ratio)) {
            throw new \Exception("Parameters width and height and ratio mixed. Please provide either parameters width and height or parameter ratio.", 1);
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

        $maxWidth = config('statamic.graphql-responsive-images.src-max-width');
        if (isset($width) && $width > $maxWidth) {
            throw new \Exception("Parameter 'width' greater than $maxWidth.", 1);
        }

        $maxHeight = config('statamic.graphql-responsive-images.src-max-height');
        if (isset($height) && $height > $maxHeight) {
            throw new \Exception("Parameter 'height' greater than $maxHeight.", 1);
        }

        if (isset($blur) && $blur < 0) {
            throw new \Exception("Parameter 'blur' less than 0.", 1);
        }
    }

    static public function calculateAspectRatioFit(int $srcWidth, int $srcHeight, int $maxWidth, int $maxHeight): array
    {
        $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);
        return [$srcWidth * $ratio, $srcHeight * $ratio];
    }

    static public function createSrc(
        Asset  $asset,
        ?int   $width,
        ?int   $height,
        ?float $ratio,
        bool   $webp,
        bool   $base64,
        int    $blur
    )
    {

        $maxWidth = config('statamic.graphql-responsive-images.src-max-width');
        $maxHeight = config('statamic.graphql-responsive-images.src-max-height');

        $newWidthAndHeight = self::calculateAspectRatioFit($asset->width(), $asset->height(), $maxWidth, $maxHeight);

        $newWidth = $newWidthAndHeight[0];
        $newHeight = $newWidthAndHeight[1];

        if (isset($width) && isset($height)) {
            $newWidth = $width;
            $newHeight = $height;
        } else if (isset($width) && isset($ratio)) {
            $newWidth = $width;
            $newHeight = $width / $ratio;
        } else if (isset($height) && isset($ratio)) {
            $newHeight = $height;
            $newWidth = $height * $ratio;
        } else if (isset($ratio)) {
            $newHeight = $newWidth / $ratio;
        } else if (isset($width)) {
            $newWidth = $width;
            $newHeight = $width / ($asset->width() / $asset->height());
        } else if (isset($height)) {
            $newHeight = $height;
            $newWidth = $height * ($asset->width() / $asset->height());
        }

        return self::manipulateImage($asset, $newWidth, $newHeight, $webp, $base64, $blur);
    }

    static public function createSrcSet(
        Asset $asset,
        float $ratio,
        ?bool $webp
    )
    {

        $widths = config('statamic.graphql-responsive-images.srcset-widths');

        $srcSetItems = [];

        foreach ($widths as $width) {
            $height = $width / $ratio;
            $url = self::manipulateImage($asset, $width, $height, $webp, false, 0);
            $itemString = $url . " " . $width . "w";
            array_push($srcSetItems, $itemString);
        }

        return implode(", ", $srcSetItems);
    }

    static private function parseRatio(
        ?int   $width,
        ?int   $height,
        ?float $ratio,
        Asset  $asset
    ): float
    {
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
                        if ($asset === null || !$asset->isImage()) return null;

                        $width = $args["width"] ?? null;
                        $height = $args["height"] ?? null;
                        $ratio = $args["ratio"] ?? null;
                        $webp = $args["webp"] ?? false;

                        self::validateSrcsetArguments($width, $height, $ratio);

                        $ratio = self::parseRatio($width, $height, $ratio, $asset);

                        return self::createSrcSet($asset, $ratio, $webp);
                    } catch (\Throwable $th) {
                        Log::error("Unable to resolve field 'srcset': " . $th->getMessage());
                        throw new \Exception("Unable to resolve field 'srcset': " . $th->getMessage(), 1);
                    }
                },
            ];
        });

        GraphQL::addField('AssetInterface', 'src', function () {
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
                ],
                "base64" => [
                    'type' => GraphQL::boolean(),
                ],
                "blur" => [
                    'type' => GraphQL::int(),
                ]
            ];

            return [
                'type' => GraphQL::string(),
                'args' => $arguments,
                'resolve' => function (Asset $asset, $args) {
                    try {
                        if ($asset === null || !$asset->isImage()) return null;

                        $width = $args["width"] ?? null;
                        $height = $args["height"] ?? null;
                        $ratio = $args["ratio"] ?? null;
                        $webp = $args["webp"] ?? false;
                        $base64 = $args["base64"] ?? false;
                        $blur = $args["blur"] ?? 0;

                        self::validateSrcArguments($width, $height, $ratio, $blur);

                        return self::createSrc($asset, $width, $height, $ratio, $webp, $base64, $blur);
                    } catch (\Throwable $th) {
                        Log::error("Unable to resolve field 'src': " . $th->getMessage());
                        throw new \Exception("Unable to resolve field 'src': " . $th->getMessage(), 1);
                    }
                },
            ];
        });
    }
}
