##Sparsh Facebook Pixel Extension
This extension allows to add Facebook Pixel Code in your store to track your visitors’ events and the effectiveness of your Facebook ads.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the files
3. Create a folder [Magento_Root]/app/code/Sparsh/FacebookPixel
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/FacebookPixel'

#Enable Extension:
- php bin/magento module:enable Sparsh_FacebookPixel
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_FacebookPixel
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
