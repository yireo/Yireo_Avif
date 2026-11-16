# Installation
Use composer to install this extension. First make sure that Magento is installed via composer, and that there is a valid `composer.json` file present.

Next, make sure that PHP GD is compiled with AVIF support:

    php -r 'var_dump(function_exists("imageavif"));'

Next, install our module using the following command:

    composer require yireo/magento2-avif

Next, install the new module into Magento itself:

    bin/magento module:enable Yireo_NextGenImages Yireo_Avif
    bin/magento setup:upgrade

The module is enabled by default. Configure it under **Stores > Configuration > Yireo > Yireo AVIF**.
