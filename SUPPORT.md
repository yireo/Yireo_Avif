# Requesting support

## First check to see if our extension is doing its job
Before requesting support, please make sure that you properly check whether this extension is working or not. Do not look at your browser and definitely do not use third party SEO tools to make any conclusions. Instead, think like a webdeveloper and inspect the source. Our extension modifies the HTML so that regular `<img>` tags are replaced with something similar to the following:
```html
<picture>
  <source type="image/avif" srcset="example.avif">
  <source type="image/png" srcset="example.png">
  <img src="example.png" />
</picture>
```

If similar HTML is there, but your browser is not showing the AVIF image, then realize that this is not due to our extension, but due to your browser. Unfortunately, we are not browser manufacturors and we can't change this. Refer to https://caniuse.com/#search=avif instead.

## Opening an issue for this extension
Feel free to open an **Issue** in the GitHub project of this extension. However, do make sure to be thorough and answer the following:

- What browser did you test this with?
- What is your Magento version? What is your PHP version?
- Which version of the AVIF extension did you install? 
    - Don't say *the latest* but please provide the actual version number :)
- How did you install this module? 
    - Which composer command did you use, or did you copy files? 
    - What did you do to enable the module?
- Have you tested this after flushing all caches?
- Have you tested this after disabling all caches, especially the Full Page Cache?
    - The Full Page Cache will prevent you from seeing dynamic results. The Full Page Cache works with our extension, it just pointless to troubleshoot things with FPC enabled, unless you are troubleshooting FPC itself.
- Are you testing this in the Developer Mode or in the Production Mode?
    - We recommend you to use the Developer Mode. Do not use the Default Mode anywhere. Do not use the Production Mode to make modifications to Magento.
- Under **Stores > Configuration > Yireo > Yireo AVIF**, what settings do you see?
    - Value of **Enabled** 
    - Value of **Debug log**
    - Value of **GD support**
    - Value of **PHP version**
    - Value of **Module version**
- Could you supply a URL to a live demo?
- Could you please supply a snapshot of the HTML source of a product page?

Please note that we never recommend Windows to be used to run Magento. Magento itself is not supporting Windows either. If you are stuck with Windows, make sure to use the WSL (Windows Subsystem for Linux) and use the Linux layer instead.

If the AVIF configuration section is showing in the backend, but the HTML source in the frontend is not modified, please send the output of the command `bin/magento dev:di:info "\Magento\Framework\View\LayoutInterface"`.

