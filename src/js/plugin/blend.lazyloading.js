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
blend.lazyloading.adjust = 40;
blend.plugins.push(blend.lazyloading); 

blend.lazyloading.register = function()
{
    window.addEventListener("scroll", blend.lazyloading.onScroll);
    blend.lazyloading.parse();
};

blend.lazyloading.start = function()
{
    blend.lazyloading.parse();
}; 

blend.lazyloading.onScroll = function()
{
    blend.lazyloading.parse();
};

blend.lazyloading.parse = function()
{
    var elements = $('[data-lazyloading-background-image]');
    var imgs = $('[data-lazyloading-src]');
    var actives = $('[data-lazyloading-active]');
    var heightVisible = body.scrollTop+screen.height;
    
    elements.each(function(idx)
    {
        var element = $(elements[idx]);
        var offsetTop = element.offset().top - blend.lazyloading.adjust;
    
        if ( heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-background-image');
            element.css('background-image', 'url('+image+')');
            element.removeData('lazyloading-background-image');
            element.removeAttr('data-lazyloading-background-image');
        }
    });
    
    imgs.each(function(idx)
    {
        var element = $(imgs[idx]);
        var offsetTop = element.offset().top - blend.lazyloading.adjust;
    
        if ( heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-src');
            element.attr('src', image);
            element.removeData('lazyloading-src');
            element.removeAttr('data-lazyloading-src');
        }
    });
    
    actives.each(function(idx)
    {
        var element = $(actives[idx]);
        var offsetTop = element.offset().top + (150);
          
        if ( heightVisible > offsetTop)
        {
            element.addClass('lazyloading-active');
            element.removeData('lazyloading-active');
            element.removeAttr('data-lazyloading-active');
        }
    });
};