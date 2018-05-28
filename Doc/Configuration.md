# BetterSerializerBundle for Symfony

## Configuration

The configuration of this bundle currently supports these features:

1) Caching backend
2) Custom extensions registration

To be able to use this bundle, it needs to be enabled in the `config/bundles.php` file in a Symfony project:

```php
return [
    ...
    BetterSerializerBundle\BetterSerializerBundle::class => ['all' => true],
    ...
];
```

Consequently, the bundle configuration file has to be created in the `config/packages` folder, 
`config/packages/better_serialzier.yaml` is recommended.

The Serializer service is tagged as `BetterSerializer\Serializer` with the alias of `better_serializer`.

### Caching backend

Currently only APCU and Filesystem are available. The available values can be defined as following constants:

```yaml
- !php/const 'BetterSerializerBundle\Config\Cache::APCU'
- !php/const 'BetterSerializerBundle\Config\Cache::FILESYSTEM'
```

One of them has to be defined under the `cache` key in the configuration file.

### Custom extensions

If you'd like to use [custom extensions for Better Serializer](https://github.com/better-serializer/better-serializer/blob/master/doc/Extensions.md), 
they need to be registered in the configuration. They are expected to be defined as array under the `extensions`
key in the configuration file. The values have to contain the FQCN of the extensions.

### Sample configuration

This is a sample configuration file for the BetterSerializerBundle:

```yaml
better_serializer:
    cache: !php/const 'BetterSerializerBundle\Config\Cache::FILESYSTEM'
    extensions:
        - CustomLib\CustomExtension1
        - CustomLib\CustomExtension2
    namingStrategy: !php/const 'BetterSerializer\Common\NamingStrategy::CAMEL_CASE'
        ...
```
