# Statamic GraphQL Responsive Images <!-- omit in toc -->

> Statamic GraphQL Responsive Images is a Statamic addon that provides GraphQL queries for responsive images.

- [How to Install](#how-to-install)
- [Setup](#setup)
- [How to Use](#how-to-use)
  - [Field `src`](#field-src)
    - [Schema](#schema)
    - [Example](#example)
      - [Query](#query)
      - [Response](#response)
  - [Field `srcset`](#field-srcset)
    - [Schema](#schema-1)
    - [Example](#example-1)
      - [Query](#query-1)
      - [Response](#response-1)
- [Notes](#notes)
- [License](#license)

## How to Install

```bash
composer require legrisch/statamic-graphql-responsive-images
```

## Setup

This addon can be configured. To publish the configuration run

```bash
php artisan vendor:publish --tag=statamic.graphql-responsive-images --force
```

and see _config → statamic → graphql-responsive-images.php_ for configuration options.

> Enable `experimental-cache-management` to automatically clear the Glide cache for images that have been modified, e.g. when the focal point has changed.

## How to Use

This addon aims to serve the most typical situation: Statamic is serving an image and you need to implement the image in your frontend. Statamic currently provides no means of using [Glide](https://statamic.dev/tags/glide) with GraphQL. This addon adds two fields to any `AssetInterface`: `src` and `srcset`.

### Field `src`

Use the field `src` to query for single transformed images. All parameters are optional.

#### Schema

```graphql
src(
  base64: Boolean
  blur: Int
  height: Int
  ratio: Float
  webp: Boolean
  width: Int
  quality: Int
): String
```

#### Example

##### Query

```graphql
query {
  asset(container: "assets", path: "Some-Asset.png") {
    src
    placeholder: src(width: 10, blur: 5, base64: true)
    squareSrc: src(ratio: 1)
    bannerSrc: src(ratio: 1.78, width: 1920)
  }
}
```

##### Response

```json
{
  "data": {
    "asset": {
      "src": "http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=2000&h=2000&s=f8a1252efb6eb175c671328d652a5b5b",
      "placeholder": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAOCAYAAAAWo42rAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAA7UlEQVQokVWQS7LCIBQFO+FC/hG1MtDFuP+16FAnaoQ3OKFSb3AGQNPnQnW7pTxNsCzKNEFVwesFjwfc7/B8gsUI8wzHI8QI4yjQDNYVvl9wDux6leV8htMJ+l5g10FdK8MAdrnoMEZdaBoAWUDgOIItC4QgOAQd5KzqrhPc99uMZkqx5Kz6ELSfEtjhoM0C/H5aOyeozGnzLCAlvXBdBde1QO+3S/O8Q++3UmxNo3rnwIZBhs9nN+csoOsEOwfWtgJhr85ZlU0Dbbs9NoR9Lu//g97L7D2Y97KVwVOClHegxJzbP7iAxVj+1jn4A/hbUvKyuollAAAAAElFTkSuQmCC",
      "squareSrc": "http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=2000&h=2000&s=f8a1252efb6eb175c671328d652a5b5b",
      "bannerSrc": "http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1920&h=1078&s=bdde1baba1447497f8a29373c54d0505"
    }
  }
}
```

### Field `srcset`

Use the field `srcset` to query for ready-to-use srcsets. All parameters are optional.

#### Schema

```graphql
srcset(
  height: Int
  ratio: Float
  webp: Boolean
  width: Int
  quality: Int
): String
```

#### Example

##### Query

```graphql
query {
  asset(container: "assets", path: "Some-Asset.png") {
    srcset
    squareSrcset: srcset(ratio: 1)
    bannerSrcset: srcset(ratio: 1.78)
  }
}
```

##### Response

```json
{
  "data": {
    "asset": {
      "srcset": "http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=2048&h=2896&s=e274c37f659b51b8803c570b4bbc1552 2048w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1920&h=2715&s=4b970d1a5d4e2f90a887f3cd7a45550a 1920w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1800&h=2545&s=6b44271580f3bd6dc53030c90ba34dcd 1800w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1680&h=2375&s=8ff5cc2a1d6a8bc8269a7c4e34638a29 1680w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1600&h=2262&s=6cc3ece67f49616b1fc9c70e4ebda877 1600w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1440&h=2036&s=604a9377aef8421aabdb7563dbc2870b 1440w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1400&h=1979&s=baa13919a212253a74ac3c39973e1828 1400w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1366&h=1931&s=93f2ff095043dd3363caf15488a37b8d 1366w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1280&h=1810&s=403427fb30c463ab4a5b1a47b2fabf2d 1280w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1200&h=1696&s=968681d7eed53f52cfa7fb9101da862e 1200w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1024&h=1448&s=0c18f3a03773cf22feeee53334d103de 1024w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1000&h=1414&s=99c6e78bd2dba86bb16809143632adf2 1000w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=800&h=1131&s=e7c2f0b0da782393512da15ba2d27cb8 800w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=768&h=1086&s=3df4a13440ca1d4fe0b1386fdde00e09 768w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=600&h=848&s=53aa7fdcefcb9a8726f714a8fffbe03b 600w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=400&h=565&s=e95656f39840c6e56d8bcdab63beccd9 400w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=200&h=282&s=c25be8503fdf7438987924eed5e0ab6c 200w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=100&h=141&s=176d6ac9b9f22c38181982e29fc0c4f1 100w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=10&h=14&s=b41d71a59ea56602f0b35745367a9447 10w",
      "squareSrcset": "http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=2048&h=2048&s=2275a019f2b6877d506a8673ed520560 2048w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1920&h=1920&s=2657525327d04f34c942cce3c18b87c9 1920w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1800&h=1800&s=59d399ad3114d91c995a12bcd9ecc454 1800w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1680&h=1680&s=d5ad2338a02d7566b5708314fd56910d 1680w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1600&h=1600&s=b733805ef4c2f37780b568c0ef825973 1600w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1440&h=1440&s=5561a2b2f929b0b56fe66c3c3e19116e 1440w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1400&h=1400&s=35e1185a35e82daa307edbfde2a36565 1400w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1366&h=1366&s=a06d42366f2a38d7a71f206dd6dac096 1366w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1280&h=1280&s=313f6349ceb2e46f50fae21b4d7b6c9d 1280w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1200&h=1200&s=df0f9d805729f5800e85a8abc6cf0802 1200w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1024&h=1024&s=678130a05cbd1890f1841a0386119917 1024w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1000&h=1000&s=d91f7d705ee9d459231777ca6f56a66e 1000w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=800&h=800&s=7e998864c91870454bd48a0de26c7318 800w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=768&h=768&s=860f3669bc9dc3081f9490b8b27d9428 768w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=600&h=600&s=08be27d65f6f8016ccb5c97eba66de6a 600w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=400&h=400&s=a7dbac1a5404c43756c3f2961a3af29f 400w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=200&h=200&s=1c1ac409fa4982a3f55c07530874f519 200w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=100&h=100&s=8a08d101c812bb3d4ffeb4b8c39dbe78 100w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=10&h=10&s=86a8bc3529faa434725c9f0dabfae150 10w",
      "bannerSrcset": "http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=2048&h=1150&s=62ec4a06098b8178fcb4bcede369eded 2048w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1920&h=1078&s=bdde1baba1447497f8a29373c54d0505 1920w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1800&h=1011&s=ab7c2cac6a28732ef29e24087fc4b84d 1800w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1680&h=943&s=e9c4f3e1d9801ccb8db90d78d9cf90f2 1680w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1600&h=898&s=46b6c4d9bec992ae4258275c4dd65bed 1600w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1440&h=808&s=1d1ac50044e4ca905463355d3b22ddc1 1440w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1400&h=786&s=e6b7c6bf7910e837f7e5a9aae2995f0d 1400w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1366&h=767&s=d8ba1b6bd049763b178d21fe6101a698 1366w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1280&h=719&s=e44150dcdbbccbc73273deb9b1f1e3de 1280w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1200&h=674&s=965ffdf297a8ed1df9b50d3f5c24c89f 1200w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1024&h=575&s=7f58520c180965a88c4e5377bf77fd02 1024w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=1000&h=561&s=3b6710be0c955944b9406dc81fe1502d 1000w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=800&h=449&s=f1beb72b3a0503d46d172d2db3169a85 800w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=768&h=431&s=a5cd5cdd96313c9901189c7b4b5f908f 768w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=600&h=337&s=fdaa56a07fda2739642ef4440869163c 600w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=400&h=224&s=adbc77746263a36124e2b584e5fea68e 400w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=200&h=112&s=625d25394fb0dc4f255e24f1c849f703 200w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=100&h=56&s=2fd6d4a335c9ed0f702682b0d603d2ce 100w, http://statamic-graphql-responsive-images.test/img/asset/YXNzZXRzL0hhbm5haC1Db29rZS1WYXNlLnBuZw==?w=10&h=5&s=78900a4ef7000b513118521b901e6c03 10w"
    }
  }
}
```

## Notes

Please be aware that this addon comes with the usual risks of dynamic HTTP based image manipulation.

---

## License

This project is licensed under the MIT License.
