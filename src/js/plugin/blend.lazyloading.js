/* global blend */

/**
 * Lazy loading of images src and background images
 * Use data-lazyloading-background-image
 * Or data-lazyloading-src
 *
 * It can parse data-lazyloading-active to, you can use it
 * if you want to add 'lazyloading-active' when scrooll reaches this
 * element, very usefull if you want to add on scroll animations
 *
 */

blend.lazyloading = {};
//default image offset adjust
blend.lazyloading.adjust = 40;
//current height visible
blend.lazyloading.heightVisible = 0;
blend.lazyloading.lastHeightVisible = 0;
//scroll events/methods
blend.lazyloading.onScrollUp = null;
blend.lazyloading.onScrollDown = null;
blend.plugins.push(blend.lazyloading);

blend.lazyloading.register = function ()
{
    window.addEventListener("scroll", blend.lazyloading.onScroll, {passive: true});
    blend.lazyloading.parse();
};

blend.lazyloading.start = function ()
{
    blend.lazyloading.parse();
};

blend.lazyloading.onScroll = function ()
{
    blend.lazyloading.parse();
};

blend.lazyloading.parseBackImages = function ()
{
    var elements = $('[data-lazyloading-background-image]');

    //background image
    elements.each(function (idx)
    {
        var element = $(elements[idx]);
        var offsetTop = element.offset().top - blend.lazyloading.adjust;

        if (blend.lazyloading.heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-background-image');
            
            if (image)
            {
                element.css('background-image', 'url(' + image + ')');
                element.removeData('lazyloading-background-image');
                element.removeAttr('data-lazyloading-background-image');
            }
        }
    });
}

blend.lazyloading.parseSrcImages = function ()
{
    var imgs = $('[data-lazyloading-src]');

    //images with href
    imgs.each(function (idx)
    {
        var element = $(imgs[idx]);
        var offsetTop = element.offset().top - blend.lazyloading.adjust;

        if (blend.lazyloading.heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-src');
            
            if (image)
            {
                element.attr('src', image);
                element.removeData('lazyloading-src');
                element.removeAttr('data-lazyloading-src');
            }
        }
    });
}

blend.lazyloading.parseActives = function ()
{
    var actives = $('[data-lazyloading-active]');

    //element to active (add class lazyloading-active)
    actives.each(function (idx)
    {
        var element = $(actives[idx]);
        var adjust = 150;
        var offsetTop = element.offset().top + (adjust);

        if (blend.lazyloading.heightVisible > offsetTop)
        {
            element.addClass('lazyloading-active');
            element.removeData('lazyloading-active');
            element.removeAttr('data-lazyloading-active');
        }
    });
}

blend.lazyloading.parseFunctions = function ()
{
    var functions = $('[data-lazyloading-function]');

    //elements to call function
    functions.each(function (idx)
    {
        var element = $(functions[idx]);
        var adjust = 150;
        var offsetTop = element.offset().top + (adjust);

        if (blend.lazyloading.heightVisible > offsetTop)
        {
            var method = element.data('lazyloading-function');

            if (typeof method == 'function')
            {
                method();
            } 
            else
            {
                eval(method);
            }

            element.addClass('lazyloading-function');
            element.removeData('lazyloading-function');
            element.removeAttr('data-lazyloading-function');
        }
    });
}

blend.lazyloading.parse = function ()
{
    var scrollingElement = document.documentElement;

    if (document.scrollingElement)
    {
        scrollingElement = document.scrollingElement;
    }

    blend.lazyloading.heightVisible = scrollingElement.scrollTop + screen.height;
    blend.lazyloading.parseBackImages();
    blend.lazyloading.parseSrcImages();
    blend.lazyloading.parseActives();
    blend.lazyloading.parseFunctions();

    if (blend.lazyloading.heightVisible > blend.lazyloading.lastHeightVisible)
    {
        if (typeof blend.lazyloading.onScrollDown == 'function')
        {
            blend.lazyloading.onScrollDown();
        }
    } 
    else if (blend.lazyloading.heightVisible < blend.lazyloading.lastHeightVisible)
    {
        if (typeof blend.lazyloading.onScrollUp == 'function')
        {
            blend.lazyloading.onScrollUp();
        }
    }

    blend.lazyloading.lastHeightVisible = blend.lazyloading.heightVisible;
};