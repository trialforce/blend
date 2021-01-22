blend.lazyloading = {};
blend.plugins.push(blend.lazyloading); 

blend.lazyloading.register = function()
{
    window.addEventListener("scroll", blend.lazyloading.onScroll);
    blend.lazyloading.parse();
}

blend.lazyloading.start = function()
{
    blend.lazyloading.parse();
} 

blend.lazyloading.onScroll = function()
{
    blend.lazyloading.parse();
}

blend.lazyloading.parse = function()
{
    var elements = $('[data-lazyloading-background-image]');
    var heightVisible = body.scrollTop+screen.height;
    
    elements.each(function(idx)
    {
        var element = $(elements[idx]);
        var offsetTop = element.offset().top;
    
        if ( heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-background-image');
            element.css('background-image', 'url('+image+')');
            element.removeData('lazyloading-background-image');
            element.removeAttr('data-lazyloading-background-image');
        }
        
        console.log(element);
    })
}