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

blend.lazyloading.isPageSpeed = function ()
{
    return navigator.userAgent.indexOf('Speed Insights') > -1
        || navigator.userAgent.indexOf('Chrome-Lighthouse') > -1
        || navigator.userAgent.indexOf('GoogleBot') > -1
        || navigator.userAgent.indexOf('GTmetrix') > -1;
};

//default image offset adjust, 0 for lighthouse/pagespeed
blend.lazyloading.adjust = blend.lazyloading.isPageSpeed() ? 40 : 0;
//current height visible
blend.lazyloading.heightVisible = 0;
//last height visible
blend.lazyloading.lastHeightVisible = 0;
//scroll events/methods
blend.lazyloading.onScrollUp = null;
blend.lazyloading.onScrollDown = null;
blend.lazyloading.idleLoading = {
    waitTime: 2000,
    timer: null,
    callback: null,
    lastInteraction: 0,
    loadingImage: false,
    imageElement: null,
    backgroundImage: null,
    backgroundUrl: null
};
//add to plugin list
blend.plugins.push(blend.lazyloading);

blend.lazyloading.register = function ()
{
    window.addEventListener("scroll", blend.lazyloading.onScroll, {passive: true});
    blend.lazyloading.parse();

    if (!blend.lazyloading.isPageSpeed())
    {
        blend.lazyloading.idleLoading.lastInteraction = Date.now();
        window.addEventListener("pointerdown", blend.lazyloading.idleLoading.handleInteraction, {passive: true});
        window.addEventListener("keydown", blend.lazyloading.idleLoading.handleInteraction);
        document.addEventListener("visibilitychange", blend.lazyloading.idleLoading.handleInteraction);
        blend.lazyloading.idleLoading.schedule();
    }
};

blend.lazyloading.start = function ()
{
    blend.lazyloading.parse();
    blend.lazyloading.idleLoading.schedule();
};

blend.lazyloading.onScroll = function ()
{
    blend.lazyloading.parse();
    blend.lazyloading.idleLoading.handleInteraction();
};

blend.lazyloading.loadSrc = function (element, lowPriority)
{
    let image = element.getAttribute('data-lazyloading-src');

    if (lowPriority && element.tagName == 'IMG' && !element.hasAttribute('fetchpriority'))
    {
        element.setAttribute('fetchpriority', 'low');
    }

    element.setAttribute('src', image);
    $(element).removeData('lazyloading-src');
    element.removeAttribute('data-lazyloading-src');
};

blend.lazyloading.loadBackground = function (element, image)
{
    image = image || element.getAttribute('data-lazyloading-background-image');
    element.style.backgroundImage = 'url(' + image + ')';
    $(element).removeData('lazyloading-background-image');
    element.removeAttribute('data-lazyloading-background-image');
};

blend.lazyloading.executeFunction = function (element, silent)
{
    let method = $(element).data('lazyloading-function');
    let previousSilent = blend.ajax.silent;
    blend.ajax.silent = previousSilent || silent === true;

    try
    {
        if (typeof method == 'function')
        {
            method();
        }
        else
        {
            eval(method);
        }
    }
    finally
    {
        blend.ajax.silent = previousSilent;
    }

    element.classList.add('lazyloading-function');
    $(element).removeData('lazyloading-function');
    element.removeAttribute('data-lazyloading-function');
};

blend.lazyloading.idleLoading.handleInteraction = function ()
{
    blend.lazyloading.idleLoading.lastInteraction = Date.now();
    blend.lazyloading.idleLoading.schedule();
};

blend.lazyloading.idleLoading.canLoad = function ()
{
    let dataSaver = navigator.connection && navigator.connection.saveData;

    return !blend.lazyloading.isPageSpeed() && !document.hidden && !dataSaver;
};

blend.lazyloading.idleLoading.cancel = function ()
{
    if (blend.lazyloading.idleLoading.timer)
    {
        clearTimeout(blend.lazyloading.idleLoading.timer);
        blend.lazyloading.idleLoading.timer = null;
    }

    if (blend.lazyloading.idleLoading.callback)
    {
        if (typeof window.cancelIdleCallback == 'function')
        {
            window.cancelIdleCallback(blend.lazyloading.idleLoading.callback);
        }
        else
        {
            clearTimeout(blend.lazyloading.idleLoading.callback);
        }

        blend.lazyloading.idleLoading.callback = null;
    }
};

blend.lazyloading.idleLoading.schedule = function ()
{
    blend.lazyloading.idleLoading.cancel();

    if (!blend.lazyloading.idleLoading.canLoad() || blend.lazyloading.idleLoading.loadingImage)
    {
        return;
    }

    let timeWithoutInteraction = Date.now() - blend.lazyloading.idleLoading.lastInteraction;
    let wait = Math.max(blend.lazyloading.idleLoading.waitTime - timeWithoutInteraction, 0);
    blend.lazyloading.idleLoading.timer = setTimeout(blend.lazyloading.idleLoading.request, wait);
};

blend.lazyloading.idleLoading.continue = function ()
{
    let timeWithoutInteraction = Date.now() - blend.lazyloading.idleLoading.lastInteraction;

    if (timeWithoutInteraction < blend.lazyloading.idleLoading.waitTime)
    {
        blend.lazyloading.idleLoading.schedule();
        return;
    }

    blend.lazyloading.idleLoading.cancel();
    blend.lazyloading.idleLoading.timer = setTimeout(blend.lazyloading.idleLoading.request, 250);
};

blend.lazyloading.idleLoading.request = function ()
{
    blend.lazyloading.idleLoading.timer = null;

    if (!blend.lazyloading.idleLoading.canLoad())
    {
        return;
    }

    if (typeof window.requestIdleCallback == 'function')
    {
        blend.lazyloading.idleLoading.callback = window.requestIdleCallback(blend.lazyloading.idleLoading.load);
    }
    else
    {
        blend.lazyloading.idleLoading.callback = setTimeout(blend.lazyloading.idleLoading.load, 0);
    }
};

blend.lazyloading.idleLoading.load = function ()
{
    blend.lazyloading.idleLoading.callback = null;

    if (!blend.lazyloading.idleLoading.canLoad())
    {
        return;
    }

    if (blend.ajax.pending > 0)
    {
        blend.lazyloading.idleLoading.continue();
        return;
    }

    let elements = document.querySelectorAll('[data-lazyloading-src], [data-lazyloading-background-image], [data-lazyloading-function]');
    let element = null;

    for (let index = 0; index < elements.length; index++)
    {
        if (elements[index].offsetWidth > 0 || elements[index].offsetHeight > 0)
        {
            element = elements[index];
            break;
        }
    }

    if (!element)
    {
        return;
    }

    if (element.hasAttribute('data-lazyloading-src'))
    {
        blend.lazyloading.idleLoading.loadSrc(element);
    }
    else if (element.hasAttribute('data-lazyloading-background-image'))
    {
        blend.lazyloading.idleLoading.loadBackground(element);
    }
    else
    {
        blend.lazyloading.idleLoading.executeFunction(element);
    }
};

blend.lazyloading.idleLoading.loadSrc = function (element)
{
    blend.lazyloading.idleLoading.loadingImage = true;
    blend.lazyloading.idleLoading.imageElement = element;
    element.addEventListener('load', blend.lazyloading.idleLoading.finishSrc);
    element.addEventListener('error', blend.lazyloading.idleLoading.finishSrc);
    blend.lazyloading.loadSrc(element, true);

    if (element.tagName == 'IMG' && element.complete)
    {
        blend.lazyloading.idleLoading.finishSrc();
    }
};

blend.lazyloading.idleLoading.finishSrc = function ()
{
    let element = blend.lazyloading.idleLoading.imageElement;

    if (element)
    {
        element.removeEventListener('load', blend.lazyloading.idleLoading.finishSrc);
        element.removeEventListener('error', blend.lazyloading.idleLoading.finishSrc);
    }

    blend.lazyloading.idleLoading.imageElement = null;
    blend.lazyloading.idleLoading.loadingImage = false;
    blend.lazyloading.idleLoading.continue();
};

blend.lazyloading.idleLoading.loadBackground = function (element)
{
    let image = element.getAttribute('data-lazyloading-background-image');

    blend.lazyloading.idleLoading.loadingImage = true;
    blend.lazyloading.idleLoading.imageElement = element;
    blend.lazyloading.idleLoading.backgroundUrl = image;
    blend.lazyloading.idleLoading.backgroundImage = new Image();
    blend.lazyloading.idleLoading.backgroundImage.fetchPriority = 'low';
    blend.lazyloading.idleLoading.backgroundImage.addEventListener('load', blend.lazyloading.idleLoading.finishBackground);
    blend.lazyloading.idleLoading.backgroundImage.addEventListener('error', blend.lazyloading.idleLoading.finishBackground);

    if ((image.startsWith("'") && image.endsWith("'")) || (image.startsWith('"') && image.endsWith('"')))
    {
        image = image.substring(1, image.length - 1);
    }

    blend.lazyloading.idleLoading.backgroundImage.src = image;
};

blend.lazyloading.idleLoading.finishBackground = function (event)
{
    let element = blend.lazyloading.idleLoading.imageElement;
    let image = blend.lazyloading.idleLoading.backgroundImage;

    if (event && event.type == 'load' && element)
    {
        blend.lazyloading.loadBackground(element, blend.lazyloading.idleLoading.backgroundUrl);
    }
    else if (element)
    {
        $(element).removeData('lazyloading-background-image');
        element.removeAttribute('data-lazyloading-background-image');
    }

    image.removeEventListener('load', blend.lazyloading.idleLoading.finishBackground);
    image.removeEventListener('error', blend.lazyloading.idleLoading.finishBackground);
    blend.lazyloading.idleLoading.imageElement = null;
    blend.lazyloading.idleLoading.backgroundImage = null;
    blend.lazyloading.idleLoading.backgroundUrl = null;
    blend.lazyloading.idleLoading.loadingImage = false;
    blend.lazyloading.idleLoading.continue();
};

blend.lazyloading.idleLoading.executeFunction = function (element)
{
    blend.lazyloading.executeFunction(element, true);
    blend.lazyloading.idleLoading.continue();
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
            if (element.data('lazyloading-background-image'))
            {
                blend.lazyloading.loadBackground(elements[idx]);
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
            if (element.data('lazyloading-src'))
            {
                blend.lazyloading.loadSrc(imagem.element, false);
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
            blend.lazyloading.executeFunction(functions[idx], false);
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
