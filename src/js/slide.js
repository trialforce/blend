
/**
 * Create a simple slider, with mobile support
 * @param string selector the jquery selector
 * @returns void
 */
function slide(selector)
{
    var wrapper = $(selector).get(0);
    var items = $(wrapper).find('.slider-items').get(0);
    var prev = $(wrapper).find('.slider-prev').get(0);
    var next = $(wrapper).find('.slider-next').get(0);

    //copy outter width to inner
    var outterWidth = $(wrapper).width();
    var outterHeight = $(wrapper).height();
    
    //don't proccess the same slide again
    if ($(wrapper).hasClass('loaded'))
    {
        return;
    }

    //if the height it not loaded yet, wait a little
    if (outterHeight == 0 || outterHeight == '0px')
    {
        setTimeout(function ()
        {
            slide(selector);
        }, 100);
        
        return;
    }
    
    //if it don't has any slide, does nothing
    var slideCount = $(wrapper).find('.slide').length;
    
    if (slideCount == 0 )
    {
        $(wrapper).find('.slider-prev').remove();
        $(wrapper).find('.slider-next').remove();
        return;
    }

    $(wrapper).find('.slide').css('width', outterWidth);
    $(wrapper).find('.slide').css('height', outterHeight);
    $(wrapper).find('.slider-items').css('left', '-' + outterWidth);
    $(wrapper).find('.slider-wrapper').css('height', outterHeight);

    var posX1 = 0;
    var posX2 = 0;
    var posInitial = 0;

    var posInitialY = 0;
    var posY1 = 0;
    var posY2 = 0;

    var posFinal;
    var index = 0;
    var threshold = 30;
    var allowShift = true;

    var slides = items.getElementsByClassName('slide');
    var slidesLength = slides.length;
    var slideSize = items.getElementsByClassName('slide')[0].offsetWidth;

    var firstSlide = slides[0];
    var lastSlide = slides[slidesLength - 1];

    var cloneFirst = firstSlide.cloneNode(true);
    var cloneLast = lastSlide.cloneNode(true);

    // Clone first and last slide
    items.appendChild(cloneFirst);
    items.insertBefore(cloneLast, firstSlide);
    wrapper.classList.add('loaded');

    // Mouse and Touch events
    items.onmousedown = dragStart;

    // Touch events
    items.addEventListener('touchstart', dragStart, {passive: true});
    items.addEventListener('touchend', dragEnd, {passive: true});
    items.addEventListener('touchmove', dragAction, {passive: true});

    // Click events
    if (prev)
    {
        prev.addEventListener('click', function (event)
        {
            event.preventDefault();
            shiftSlide(-1); 
        }, {passive: true});
    }

    if (next)
    {
        next.addEventListener('click', function (event)
        {
            event.preventDefault();
            shiftSlide(1);
        }, {passive: true});
    }

    // Transition events
    items.addEventListener('transitionend', checkIndex, true);

    function dragStart(e)
    {
        e = e || window.event;
        //e.preventDefault();
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
            //posY1 = e.clientY;
        }

        items.style.left = (items.offsetLeft - posX2) + "px";

        $(window).scrollTop(posInitialY + posY2);
    }

    function dragEnd(e)
    {
        posFinal = items.offsetLeft;
        
        var diff = (posFinal - posInitial);

        //click
        if( diff === 0)
        {
            var onclickCode = $(items).parents('*[data-onclick]').data('onclick');
            var tmpFunc = new Function(onclickCode);
            tmpFunc();
        }
        //draf left
        else if (diff < -threshold)
        {
            shiftSlide(1, 'drag');
        } 
        //drag right
        else if (diff > threshold)
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
        items.classList.add('shifting');

        if (allowShift)
        {
            if (!action)
            {
                posInitial = items.offsetLeft;
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

        //event.preventDefault();
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

        allowShift = true;
    }
};