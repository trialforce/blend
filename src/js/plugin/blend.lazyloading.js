/* global blend */

/**
 * Lazy loading of images src and background images and functions
 * Use data-lazyloading-background-image, data-lazyloading-src or other metohods
 *
 * It can parse data-lazyloading-active to, you can use it
 * if you want to add 'lazyloading-active' when scrooll reaches this
 * element, very usefull if you want to add on scroll animations
 *
 */
blend.lazyloading = {};
//default image offset adjust, 0 for lighthouse/pagespeed
blend.lazyloading.adjust = navigator.userAgent.indexOf("Chrome-Lighthouse") == -1 ? 0 : 40;
//current height visible
blend.lazyloading.heightVisible = 0;
//last height visible
blend.lazyloading.lastHeightVisible = 0;
//scroll events/methods
blend.lazyloading.onScrollUp = null;
blend.lazyloading.onScrollDown = null;
//if you need to "translate" the image to arbitrary change the url
blend.lazyloading.srcTranslate = null;
//add to plugin list
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
    // Lê todas as posições antes de alterar as imagens para evitar reflows entre os itens.
    let positions = elements.map(function ()
    {
        return $(this).offset().top;
    }).get();

    //background image
    elements.each(function (idx)
    {
        var element = $(elements[idx]);
        var offsetTop = positions[idx] - blend.lazyloading.adjust;

        if (blend.lazyloading.heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-background-image');
            
            if (image)
            {
                if (typeof blend.lazyloading.srcTranslate == 'function' )
                {
                    image = blend.lazyloading.srcTranslate(image);
                }
        
                element.css('background-image', 'url(' + image + ')');
                element.removeData('lazyloading-background-image');
                element.removeAttr('data-lazyloading-background-image');
            }
        }
    });
}

blend.lazyloading.medeImagens = function ()
{
    let imagens = [];

    document.querySelectorAll('[data-lazyloading-src]').forEach(function (element)
    {
        let rect = element.getBoundingClientRect();

        if (element.offsetWidth > 0 || element.offsetHeight > 0)
        {
            imagens.push({element: element, top: rect.top + window.scrollY});
        }
    });

    return imagens;
};

blend.lazyloading.parseSrcImages = function (imgs = blend.lazyloading.medeImagens())
{
    //images with href
    imgs.forEach(function (imagem)
    {
        var element = $(imagem.element);
        var offsetTop = imagem.top - blend.lazyloading.adjust;

        if (blend.lazyloading.heightVisible > offsetTop)
        {
            var image = element.data('lazyloading-src');
            
            if (image)
            {
                if (typeof blend.lazyloading.srcTranslate == 'function' )
                {
                    image = blend.lazyloading.srcTranslate(image);
                }
                
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
    // Lê as posições antes de alterar as classes dos elementos.
    let positions = actives.map(function ()
    {
        return $(this).offset().top;
    }).get();

    //element to active (add class lazyloading-active)
    actives.each(function (idx)
    {
        var element = $(actives[idx]);
        var adjust = 150;
        var offsetTop = positions[idx] + adjust;

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
    // Lê as posições antes de executar funções que podem alterar o layout.
    let positions = functions.map(function ()
    {
        return $(this).offset().top;
    }).get();

    //elements to call function
    functions.each(function (idx)
    {
        var element = $(functions[idx]);
        var adjust = 150;
        var offsetTop = positions[idx] + adjust;

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
    blend.lazyloading.heightVisible = window.scrollY + screen.height;


    let imagens = blend.lazyloading.medeImagens();

    blend.lazyloading.parseBackImages();
    blend.lazyloading.parseSrcImages(imagens);
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
