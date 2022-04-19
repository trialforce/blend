/* global blend */
blend.slide = {};
blend.plugins.push(blend.slide);

blend.slide.register = function ()
{
};

blend.slide.start = function ()
{
    //first inner slider
    $('.slider').not('.slider-outter').each(function ()
    {
        slide('#' + $(this).attr('id'));
    });
    
    //after that the rest
    $('.slider').each(function ()
    {
        slide('#' + $(this).attr('id'));
    });
};

/**
 * Create a simple slider, with mobile support
 * @param string selector the jquery selector
 * @returns void
 */
function slide(selector)
{
    var group = $($(selector).get(0));
    
    //don't process invisible elements
    if (!group.is(":visible"))
    {
        return;
    }
       
    //don't proccess the same slide again
    if ($(group).hasClass('loaded'))
    {
        return;
    }
    
    //cached elements
    var wrapper = group.find('>.slider-wrapper');
    var items = wrapper.find('>.slider-items').get(0);
    
    //remove width for outter slider
    var prev = group.find('>.slider-prev').get(0);
    var next = group.find('>.slider-next').get(0);
    var slides = items.querySelectorAll(':scope >.slide'); //only first level child
    var slidesLength = slides.length;
    var hasSubSlider = group.find('.slider').length>0;
    
    if (!items)
    {
        return;
    }
    
    //remove outter class, is not needed after parse
    group.get(0).classList.remove('slider-outter');

    //data
    var autoSlide = group.data('auto-slide');
    var fullScreen = group.data('full-screen');
    var dataStartIndex = group.data('start-index');
    var dataChangeOnHover = group.data('change-on-hover');
    
    //copy outter width to inner
    var outterWidth = wrapper.width();
    var outterHeight = parseInt(group.height());

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
        prev.remove();
        next.remove();
    }
    else
    {
        // Click events
        if (prev)
        {
            prev.addEventListener('click', function (event)
            {
                shiftSlide(-1); 
            }, {passive: true});
        }

        if (next)
        {
            next.addEventListener('click', function (event)
            {
                shiftSlide(1);
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
    var thresholdMove = 5;
    var allowShift = true;

    var slideSize = slides[0].offsetWidth;
    var firstSlide = slides[0];
    var lastSlide = slides[slidesLength - 1];

    var cloneFirst = firstSlide.cloneNode(true);
    cloneFirst.classList.add('cloned');
    
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

    //clone first and last slide
    items.appendChild(cloneFirst);
    items.insertBefore(cloneLast, firstSlide);
    group.addClass('loaded');

    //mouse, touch and transition events
    items.onmousedown = dragStart;
    items.addEventListener('touchstart', dragStart, {passive: true});
    items.addEventListener('touchend', dragEnd, {passive: true});
    items.addEventListener('touchmove', dragAction, {passive: true});
    items.addEventListener('transitionend', checkIndex, true);
    
    //auto slide
    if (Number.isInteger(autoSlide) && slidesLength > 1) 
    {
        setInterval(function(){shiftSlide(1)}, autoSlide);
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
        return false;
    }
    
    function setSlide(position)
    {
        items.classList.add('shifting');
        items.style.left = (slideSize * (position + 1) * -1) + "px";
        index = position;

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