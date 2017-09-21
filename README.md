# Chuvisco_CatalogRuleFix
The CatalogRuleFix overrides Magento's default behaviour of the CatalogRule indexing.

Following two things are overridden:
- The cronjob for catalogrule_apply_all is `0 0 * * *` instead of `0 1 * * *`
- The index refresher uses local time instead of gmt time
- Inserting of rules by the resource model uses local time instead of gmt time

## Installation
**NOTE:** Running Chuvisco_CatalogRuleFix requires you to run the cronjobs of Magento. If they are not set up, please 
do so by following the [docs](http://devdocs.magento.com/guides/m1x/install/installing_install.html#install-cron).

This module can be installed in various ways: via modman, Composer or manually.
Currently installation via Magento Connect is not supported.

#### Modman

1. Install [modman](https://github.com/colinmollenhour/modman)
2. Execute the following command from your Magento installation folder:
```sh
modman clone https://github.com/Chuvisco88/Chuvisco_CatalogRuleFix.git
```

### Composer

1. Install [Composer](http://getcomposer.org/download/)
2. Install [Magento Composer](https://github.com/magento-hackathon/magento-composer-installer)
3. Add the repository to the Composer configuration of your project:
```sh
composer config repositories.catalogrulefix vcs https://github.com/Chuvisco88/Chuvisco_CatalogRuleFix.git
```
4. Add the package to the Composer configuration of your project:
```sh
composer require "chuvisco/catalog-rule-fix"
```

### Manually

[Download the zip or tar.gz file](https://github.com/Chuvisco88/Chuvisco_CatalogRuleFix/releases) and extract it into your project webroot.
 
## Changelog

The changelog is [in another castle](CHANGELOG.md).

## Roadmap
- Tests and TravisCI integration
- Magento Connect integration

## Authors, contributors and maintainers
Author:
- [Fabian Schweizer](https://twitter.com/chuvisco88)

Contributions:
- see [Contribution Graph on Github](https://github.com/Chuvisco88/Chuvisco_CatalogRuleFix/graphs/contributors)

## Ideas, bugs, contributions, comments, feature suggestions?

Please get in touch with me via the [issue tracker on GitHub](https://github.com/Chuvisco88/Chuvisco_CatalogRuleFix/issues).

## Compatibility
- PHP:
    - 5.6
- Magento CE:
    - 1.9.3.2

I would love to hear from you, if it works with other systems (versions of php and/or magento) as well.

## License
[MIT](LICENSE)