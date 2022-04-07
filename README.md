#Extended Routing Aspects

## What does it do?

Adds some more Mappers for Routing Aspects feature in TYPO3. 


## How does it work?

With four new Routing aspects you can handle more possibilities for advanced routing in TYPO3.

## The mappers

### DateTimeMapper

Allows you to create customized url parts including localization to get DateTime values inspeaking parts
without using a dataset with defined slug.

```yaml
Archive:
  type: Extbase
  extension: News
  plugin: Archive
  routes:
    - routePath: '/{day}'
      _controller: 'Archive::list'
      _arguments:
        day: day
  defaultController: 'Archive::list'
  aspects:
    day:
      type: DateTimeMapper
      format: Y-m-d
      localeFormat:
        -
          locale: 'de_.*'
          format: d-m-Y
```

### PersistedSanitizedPatternMapper

Adds a new routing aspect extending the PersistedPatternMapper from TYPO3 core with sanitized URL parts.
Localization is respected, if needed.

```yaml
aspects:
  country:
    type: PersistedSanitizedPatternMapper
    tableName: static_countries
    routeFieldPattern: '^(.*)-(?P<uid>\d+)$'
    routeFieldResult: '{cn_short_de|sanitized}-{uid}'
    localeMap:
      - locale: 'de_*'
        field: cn_short_de
      - locale: 'en_*'
        field: cn_short_en
  territory:
    type: PersistedSanitizedPatternMapper
    tableName: static_territories
    routeFieldPattern: '^(.*)-(?P<uid>\d+)$'
    routeFieldResult: '{tr_name_de|sanitized}-{uid}'
    localeMap:
      - locale: 'de_*'
        field: tr_name_de
      - locale: 'en_*'
        field: tr_name_en
```

### PersistedDisabledAliasMapper

Allows creating full URLs for disabled elements by TCA enablecolumns. Helpful, if you have to create the URLs
before publishing the record.
Usage as the default PersistedAliaMapper.

```yaml
News:
  type: Extbase
  extension: News
  plugin: news
  routes:
    -
      routePath: '/{title}'
      _controller: 'News::single'
      _arguments:
        title: news
  aspects:
    title:
      type: PersistedDisabledliasMapper
      tableName: tx_news_domain_model_news
      routeFieldName: path_segment
```

### PersistedNullableAliasMapper

Allows a default value for normally aliased URL path segment. For example, a news without category, but
URL part is defined with category.

```yaml
News:
  type: Extbase
  extension: News
  plugin: News
  routes:
    -
      routePath: '/{category}/{title}'
      _controller: 'News::single'
      _arguments:
        category: category
        title: news
  aspects:
    category:
      type: PersistedNullableAliasMapper
      default: 'no-category'
      tableName: tx_news_domain_model_category
      routeFieldName: path_segment
    title:
      type: PersistedAliasMapper
      tableName: tx_news_domain_model_news
      routeFieldName: path_segment
```

## Installation

Possible via extensions.typo3.org, ZIP upload or composer.

### Extension Repository
[a link]https://extensions.typo3.org/extension/extended_routing

### Composer
```shell
composer require calien/extended-routing
```