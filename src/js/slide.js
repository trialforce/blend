/* global blend */
blend.slide = {};
blend.plugins.push(blend.slide);

blend.slide.register = function ()
{
};

blend.slide.start = function ()
{
    let sliders = Array.from(document.querySelectorAll('.slider:not(.loaded):not(.slider-outter)'));
    let slidersSemFilhos = sliders.filter(function (element)
    {
        return !element.querySelector('.slider') && (element.offsetWidth > 0 || element.offsetHeight > 0);
    });
    let medidas = new Map();

    slidersSemFilhos.forEach(function (element)
    {
        medidas.set(element, blend.slide.medeSlider(element));
    });
    // Inicializa os sliders internos depois de concluir todas as leituras de layout.
    slidersSemFilhos.forEach(function (element)
    {
        element.querySelector('.slider-wrapper > .slider-items > .slide').style.display = 'inline-block';
    });
    slidersSemFilhos.forEach(function (element)
    {
        slide('#' + element.id, medidas.get(element));
    });

    // Sliders externos preservam a inicialização depois dos internos.
    document.querySelectorAll('.slider:not(.loaded)').forEach(function (element)
    {
        slide('#' + element.id);
    });
};

blend.slide.medeSlider = function (element)
{
    let wrapper = element.querySelector(':scope > .slider-wrapper');
    let slides = wrapper.querySelectorAll(':scope > .slider-items > .slide');
    //copy outter width to inner
    let wrapperStyle = window.getComputedStyle(wrapper);
    let groupStyle = window.getComputedStyle(element);
    let wrapperExtra = wrapperStyle.boxSizing == 'border-box' ? parseFloat(wrapperStyle.paddingLeft) + parseFloat(wrapperStyle.paddingRight) + parseFloat(wrapperStyle.borderLeftWidth) + parseFloat(wrapperStyle.borderRightWidth) : 0;
    let groupExtra = groupStyle.boxSizing == 'border-box' ? parseFloat(groupStyle.paddingTop) + parseFloat(groupStyle.paddingBottom) + parseFloat(groupStyle.borderTopWidth) + parseFloat(groupStyle.borderBottomWidth) : 0;
    var outterWidth = parseFloat(wrapperStyle.width) - wrapperExtra + 1;
    var outterHeight = Math.trunc(parseFloat(groupStyle.height) - groupExtra);

    // Calcula a largura externa antes das alterações; restrições de tamanho mantêm a medição original.
    let slideStyle = window.getComputedStyle(slides[0]);
    let slideExtra = parseFloat(slideStyle.paddingLeft) + parseFloat(slideStyle.paddingRight) + parseFloat(slideStyle.borderLeftWidth) + parseFloat(slideStyle.borderRightWidth);
    let slideSize = null;

    if (slideStyle.minWidth == '0px' && slideStyle.maxWidth == 'none' && slides[0].style.getPropertyPriority('width') != 'important')
    {
        slideSize = Math.round(slideStyle.boxSizing == 'border-box' ? Math.max(outterWidth, slideExtra) : outterWidth + slideExtra);
    }

    return {width: outterWidth, height: outterHeight, size: slideSize};
};

/**
 * Create a simple slider, with mobile support
 * @param string selector the jquery selector
 * @returns void
 */
function slide(selector, medidas)
{
    var group = $($(selector).get(0));
    var groupElement = group.get(0);

    if (!groupElement)
    {
        return;
    }
    
    // Evita consultar o layout de sliders que já foram inicializados.
    if (group.hasClass('loaded'))
    {
        return;
    }
       
    //don't process invisible elements
    if (!medidas && groupElement.checkVisibility && !groupElement.checkVisibility())
    {
        return;
    }
    
    //cached elements
    var wrapper = group.find('>.slider-wrapper');
    var items = wrapper.find('>.slider-items').get(0);
    
    //remove width for outter slider
    var prev = wrapper.find('>.slider-prev').get(0);
    var next = wrapper.find('>.slider-next').get(0);
    
    if ( !prev )
    {
        prev = group.find('>.slider-prev').get(0);
    }
    
    if (!next)
    {
        next = group.find('>.slider-next').get(0);
    }
    
    var slides= items.querySelectorAll(':scope >.slide'); //only first level child
    var slidesLength = slides.length;
    if (!medidas)
    {
        slides[0].style.display = 'inline-block';
    }

    var hasSubSlider = group.find('.slider').length>0;

    if (!items)
    {
        return;
    }
    
    //remove outter class, is not needed after parse
    if (!medidas)
    {
        group.get(0).classList.remove('slider-outter');
    }

    //data
    var autoSlide = group.data('auto-slide');
    var fullScreen = group.data('full-screen');
    var dataStartIndex = group.data('start-index');
    var dataChangeOnHover = group.data('change-on-hover');
    var dataDragDisable = group.data('drag-disabled');
    
    medidas = medidas || blend.slide.medeSlider(group.get(0));
    var outterWidth = medidas.width;
    var outterHeight = medidas.height;
    let slideSize = medidas.size;

    //if the height it not loaded yet, wait a little
    if (outterHeight == 0 || outterHeight == '0px')
    {
        setTimeout(function ()
        {
            slide(selector);
        }, 100);
        
        return;
    }
    
    //ajdust width e height
    items.style.left = '-' + outterWidth+'px';
    
    for (var i=0; i<slides.length; i++)
    {
        var curSlide = slides[i];
        curSlide.style.width = outterWidth+'px';
        curSlide.style.height = outterHeight+'px';
    }
    
    wrapper.css('height', outterHeight+'px');
    
    if (dataChangeOnHover)
    {
        $(group).mouseover( function(element)
        {
            if (slidesLength > 1)
            {
                setSlide(1);
            }
        });
        
        $(group).mouseleave( function(element)
        {
            if (slidesLength > 1)
            {
                setSlide(0);
            }
        });
    }
    
    //remove slide prev/next if not neeed
    if (slidesLength <= 1 )
    {
        if (prev)
        {
            prev.remove();
        }
        
        if (next)
        {
            next.remove();
        }   
    }
    else
    {
        // Click events
        if (prev)
        {
            prev.addEventListener('click', function (event)
            {
                shiftSlide(-1); 
                clearInterval(timerInterval);
            }, {passive: true});
        }

        if (next)
        {
            next.addEventListener('click', function (event)
            {
                shiftSlide(1);
                clearInterval(timerInterval);
            }, {passive: true});
        }
    }

    var slicker = group.find('.slider-slick');
    
    slicker.each( function(index)
    {
        var slick = slicker[index];
        slick.addEventListener('click', function (event)
        {
            setSlide(index); 
        }, {passive: true});
    });

    var posX1 = 0;
    var posX2 = 0;
    var posInitial = 0;

    var posInitialY = 0;
    var posY1 = 0;
    var posY2 = 0;

    var posFinal;
    var posFinalY;
    var index = 0;
    var threshold = 50;
    //needs to 15 at least for avoid shaking in iPhone
    var thresholdMove = 15; 
    var allowShift = true;

    slideSize = slideSize === null ? slides[0].offsetWidth : slideSize;
    var firstSlide = slides[0];
    var lastSlide = slides[slidesLength - 1];

    var cloneFirst = firstSlide.cloneNode(true);
    cloneFirst.classList.add('cloned');
    cloneFirst.id += '-cloned';

    if (cloneFirst.tagName == 'VIDEO')
    {
        cloneFirst.removeAttribute('autoplay');
    }
    
    var cloneLast = lastSlide.cloneNode(true);
    
    if (cloneLast.tagName == 'VIDEO')
    {
        cloneLast.removeAttribute('autoplay');
    }
    
    cloneLast.classList.add('cloned');
    cloneLast.id += '-cloned';

    //clone first and last slide
    items.appendChild(cloneFirst);
    items.insertBefore(cloneLast, firstSlide);
    group.addClass('loaded');

    //mouse, touch and transition events
    if (!dataDragDisable) {
        items.onmousedown = dragStart;
        items.addEventListener('touchstart', dragStart, {passive: true});
        items.addEventListener('touchend', dragEnd, {passive: true});
        items.addEventListener('touchmove', dragAction, {passive: true});
    }

    items.addEventListener('transitionend', checkIndex, true);

    var timerInterval;
    
    //auto slide
    if (Number.isInteger(autoSlide) && slidesLength > 1) 
    {
        //wait 3 second to start the banner movement to avoid problems with pagespeed
        setTimeout(function()
        {
            timerInterval = setInterval(function(){shiftSlide(1)}, autoSlide);
        },3000);
    }
    
    //start position/index
    if ( Number.isInteger(dataStartIndex))
    {
        if (index == 0)
        {
            setSlide(dataStartIndex);
        }
    }

    function dragStart(e)
    {
        e = e || window.event;

        //avoid right mouse button
        if (e.button === 2)
        {
            return false;
        }

        posInitial = items.offsetLeft;
        posInitialY = $(window).scrollTop();

        if (e.type == 'touchstart')
        {
            posX1 = e.touches[0].clientX;
            posY1 = e.touches[0].clientY;
        } 
        else
        {
            posX1 = e.clientX;
            posY1 = e.clientY;
            document.onmouseup = dragEnd;
            document.onmousemove = dragAction;
        }
    }

    function dragAction(e)
    {
        e = e || window.event;

        if (e.type == 'touchmove')
        {
            posX2 = posX1 - e.touches[0].clientX;
            posX1 = e.touches[0].clientX;

            posY2 = posY1 - e.touches[0].clientY;
        } 
        else
        {
            posX2 = posX1 - e.clientX;
            posX1 = e.clientX;

            posY2 = posY1 - e.clientY;
        }

        if (Math.abs(posX2)> thresholdMove)
        {
            items.style.left = (items.offsetLeft - posX2) + "px";
        }

        $(window).scrollTop(posInitialY + posY2);
    }

    function dragEnd(e)
    {
        posFinal = items.offsetLeft;
        posFinalY = $(window).scrollTop();
        
        var diffX = (posFinal - posInitial);
        var diffY = (posFinalY - posInitialY);

        //click
        if( diffX === 0 && diffY == 0 )
        {
            var onclickCode = $(items).parents('*[data-onclick]').data('onclick');
            
            if ( onclickCode)
            {
                var tmpFunc = new Function(onclickCode);
                tmpFunc();
            }
            else if ( fullScreen )
            {
                fullscreen();
            }
        }
        //draf left
        else if (diffX < -threshold)
        {
            shiftSlide(1, 'drag');
        } 
        //drag right
        else if (diffX > threshold)
        {
            shiftSlide(-1, 'drag');
        }
        //nothing, return original position
        else
        {
            items.style.left = (posInitial) + "px";
        }

        document.onmouseup = null;
        document.onmousemove = null;
    }

    function shiftSlide(dir, action)
    {
        // Consulta a posição antes de invalidar os estilos com a classe de transição.
        if (allowShift && !action)
        {
            posInitial = items.offsetLeft;
        }

        items.classList.add('shifting');

        if (allowShift)
        {
            //show all slides
            let slides = items.querySelectorAll(':scope >.slide');
            for (var i=0; i<slides.length; i++)
            {
                slides[i].style.display='inline-block';
            }

            if (dir == 1)
            {
                items.style.left = (posInitial - slideSize) + "px";
                index++;
            } 
            else if (dir == -1)
            {
                items.style.left = (posInitial + slideSize) + "px";
                index--;
            }
        };

        allowShift = false;
        return false;
    }
    
    function setSlide(position)
    {
        items.classList.add('shifting');
        slides[position].style.display = 'inline-block';
        items.style.left = (slideSize * (position + 1) * -1) + "px";
        index = position;
        clearInterval(timerInterval);

        return false;
    }

    function checkIndex()
    {
        items.classList.remove('shifting');

        if (index == -1)
        {
            items.style.left = -(slidesLength * slideSize) + "px";
            index = slidesLength - 1;
        }

        if (index == slidesLength)
        {
            items.style.left = -(1 * slideSize) + "px";
            index = 0;
        }
        
        setActiveSymbol(index);

        allowShift = true;
    }
    
    function fullscreen()
    {
        var oldIndex = index;
        var newSlider = $(selector).clone();
        
        newSlider.append('<div class="slider-close-full-screen" id="slider-close-full-screen" onclick="return removeSlideFullScreen()">&nbsp;</div>');
        newSlider.attr('data-start-index',index);
        
        newSlider.attr('class', '');
        newSlider.addClass('slider slider-full-screen');
        newSlider.css('height','90vh');
        
        newSlider.attr('id','slider-full-screen');
        newSlider.removeAttr('data-full-screen');
        
        var group = newSlider.find('.slider-wrapper');
        group.attr('class','');
        group.addClass('slider-wrapper');
        group.css('height','90vh');
        
        var sliderItems = newSlider.find('.slider-items');
        sliderItems.css('height',"");
        sliderItems.css('left',"");
        
        newSlider.find('.slide.cloned').remove();

        var slides = newSlider.find('.slide');
        
        //reset slides to open full screen
        slides .each(function(index)
        {
            var slide = $(slides[index]);
            slide.css('height',""); //reset widget and weight
            slide.css('width',"");
            slide.css('transform',''); //reset zoom
        });
        
        newSlider.find('[data-slider-delete-on-full-screen=true]').remove();
        
        $('body').append(newSlider).css('overflow','hidden');
        
        slide('#slider-full-screen');
    }
    
    function setActiveSymbol(position)
    {
        // refreshes page count symbols
        var pages = group.find('.slider-slick');
        
        if (typeof pages == undefined || pages.length == 0)
        {
            return false;
        }
        
        for (var i = 0; i < pages.length; i++)
        {
            var page = pages.get(i);
            page.classList.remove('active');
        }
        
        var page = pages.get(position);
        page.classList.add('active');
    }
};

function removeSlideFullScreen()
{
    $('#slider-full-screen').remove();
    
    $('body').css('overflow',"");
    
    return false;
}
