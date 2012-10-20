MageMan by Doug Hatcher
superterran@gmail.com

This is a php script that runs from the cli which allows you to
manage local magento installations. 

do the following to install:
    
        sudo link ./mageman.php /usr/local/mageman
        chmod a+x /usr/local/mageman
    
        Create ~/.mageman from dot_mageman-sample
    
Right now the sript only does one thing...
    
        from a magento root folder run:
    
                 mageman clearcache
    
        to, wait for it... clear the cache.
