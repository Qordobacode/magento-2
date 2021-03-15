
# Qordoba Connector Magento-2 Extension
 
 
### Extension Installation Process

##### Manual Installation

```$xslt
$ cd magento2_installation/app/code
$ mkdir Qordoba
$ git clone git@github.com:Qordobacode/magento-2.git ./Connector
$ cd magento_installtion
$ composer require qordoba/qordoba-php
$ magento setup:upgrade
$ magento setup:di:compile
```

##### Installation via Composer Package Manager

```$xslt
$ cd magento2_installation
$ composer require qordoba/magento2-connector
$ magento setup:upgrade
$ magento setup:di:compile
```

### Configuration

    1. Configure and run magento 2 cron
    2. Add your Qordoba Prefrences on Prefrences Page (Each Store have to have it's own configuration)

#### Manual usage

    1. Install n98-magerun2 in Magento 2 directory
    2. Run `php n98-magerun2.phar sys:cron:run qordoba_submit` to push your submission to Writer
    3. Run `php n98-magerun2.phar sys:cron:run qordoba_download` to pull from submission to Writer

