/* global shortcut, FormData */

"use strict";
//handle the back and forward buttons
var invalidHover = true;
var avoidUrlRegister = false;
var isAjax = false;
var blendJs = function(){};
var blend = {};
blend.defaultFormPost = 'form';
blend.plugins = [];

blend.ajax = {};
blend.ajax.timeout = 200;
blend.ajax.timer = null;
blend.isBack = false;

var b = function(selector)
{
    let nodeList;
    
    if ( selector instanceof NodeList)
    {
        nodeList = selector;
    }
    else
    {
        nodeList = document.querySelectorAll(selector);
    }
    
    nodeList.each = nodeList.forEach;
    
    nodeList.attr = function(attribute, value)
    {
        if (!this[0])
        {
            return;
        }
        
        if (typeof value == 'undefined')
        {
            return this[0].getAttribute(attribute);
        }
        else
        {
            this[0].setAttribute(attribute,value);
            return this[0];
        }
    };
    
    nodeList.append = function(content)
    {
        if (this[0])
        {
            if ( typeof content == 'string')
            {
                this[0].insertAdjacentHTML('beforeend', content);
            }
            else
            {
                this[0].append(content);
            }
            
            return true;
        }
    };
    
    nodeList.html = function(content)
    {
        if (this[0])
        {
            this[0].innerHTML = content;
            executeScriptElements(this[0]);
            return this[0];
        }
    };
    
    nodeList.hasClass = function(cssClass)
    {
        if (this[0])
        {
            return this[0].classList.contains(cssClass);
        }
    };
    
    nodeList.addClass = function(cssClass)
    {
        this.forEach(function(element)
        {
            element.classList.add(cssClass);
        });
    };

    nodeList.remove = function()
    {
        this.forEach(function(element)
        {
            element.remove();
        });
    };
    
    nodeList.removeClass = function(cssClass)
    {
        this.forEach(function(element)
        {
            element.classList.remove(cssClass);
        });
    };
    
    nodeList.hide = function()
    {
        this.forEach(function(element)
        {
            element.style.display = 'none';
        });
    };
    
    nodeList.show = function(display = 'block')
    {
        this.forEach(function(element)
        {
            element.style.display = display;
        });
    };
    
    nodeList.toggle = function(display = 'block')
    {
        var visible = this.isVisible();

        if (visible >= 0)
        {
            this.hide();
        }
        else
        {
            this.show(display);
        }
    };
    
    nodeList.css = function(rule, value)
    {
        if (typeof value == 'undefined' && this[0])
        {
            return this[0].style[rule];
        }
        
        this.forEach(function(element)
        {
            element.style[rule] = value;
        });
    };
    
    nodeList.isVisible = function()
    {
        for (let i in this)
        {
            let element  = this[i];
            let length = typeof element.getClientRects == 'function' ? element.getClientRects().length : null;
            
            if ( !!( element.offsetWidth || element.offsetHeight || length ))
            {
                return parseInt(i);
            }
        }
        
        return -1;
    }
    
    return nodeList;
}

function executeScriptElements(containerElement) 
{
    const scriptElements = containerElement.querySelectorAll("script");

    Array.from(scriptElements).forEach((scriptElement) => 
    {
        const clonedElement = document.createElement("script");

        Array.from(scriptElement.attributes).forEach((attribute) => 
        {
            clonedElement.setAttribute(attribute.name, attribute.value);
        });
    
        clonedElement.text = scriptElement.text;

        scriptElement.parentNode.replaceChild(clonedElement, scriptElement);
    });
}
 
function pluginsCallMethod(method)
{
    for (var i = 0; i < blend.plugins.length; i++)
    {
        var plugin = blend.plugins[i];
        var pluginMethod = plugin[method];
        
        if ( typeof pluginMethod == 'function')
        {
            pluginMethod();
        }
    }
}

//avoid console.log problems
if (!window.console)
{
    window.console = {};
    window.console.log = function () {};
}

window.onpopstate = function(event) 
{
    blend.isBack = true;
    avoidUrlRegister = true;
    p(window.location.href, true);
};

//Loading without ajax
window.onload = function ()
{
    pluginsCallMethod('register');
    dataAjax();
    updateUrl(window.location.href);
    
    //destroy popup on esc
    document.addEventListener('keydown', function(e)
    {
        //avoid holding button bug
        if (e.repeat)
        {
            return;
        }

        if(e.key=='Escape'||e.key=='Esc'||e.keyCode==27)
        {
            if (blendEscape())
            {
                e.preventDefault();
                return false;
            }
        }
    }, true)
    
};

/**
 * @todo convert to plugin method
 * @returns {boolean}
 */
function blendEscape()
{
    //main menu
    if ( b('body').hasClass('menu-open') )
    {
        menuClose();
        return true;
    }
    //popup
    else if ( b('.popup').isVisible() >= 0 )
    {
        //try to call the close action of the popup
        var list = b('#btbClosePopup');
        var index = list.isVisible();
        var jsText = list[index].onclick;
        
        if (jsText && typeof jsText === 'function')
        {
            // Lucide creates a svg and a simulated click() doesn't work
            list[index].onclick();
        }
        else
        {
           popup('destroy');
        }
        
        return true;
    }
    else if ( b('.dropDownContainer').isVisible() >= 0)
    {
        comboHideDropdown();
        return true;
    }
    //calendar
    else if ( b('.xdsoft_datetimepicker.xdsoft_noselect').isVisible() >= 0 )
    {
        b('.xdsoft_datetimepicker.xdsoft_noselect').hide();
        return true;
    }
    //slider full screen
    else if ( b('slider-full-screen').length > 0)
    {
        removeSlideFullScreen();
    }
    else if (!isMobile())
    {
        history.back();
    }
    
    return false;
}

/**
 * Make all actions that is need after some post/ajax post
 *
 * @returns boolean always false
 */
function dataAjax()
{
    try 
    {
        blendJs();
    }
    catch (e) 
    {
        alert('Erro ao executar javascript da página!');
        console.error(e);
        return hideLoading();
    }
    
    //clear the function to avoid calling more times
    blendJs = function(){};
    pluginsCallMethod('start');
  
    return hideLoading();
}

/**
 * Update browser url
 *
 * @param {string} page
 * @returns {void}
 */
function updateUrl(page)
{
    if (window.history.pushState === undefined || page === 'undefined')
    {
        return false;
    }

    if (avoidUrlRegister)
    {
        avoidUrlRegister = false;
        return false;
    }
    
    var urlToRegister = correctUrl(page);
    
    //don't register same url twice (simplifies back)
    if (urlToRegister == window.location.href)
    {
        return false;
    }
    
    window.history.pushState({url: urlToRegister}, "", urlToRegister);
    avoidUrlRegister = false;
    return true;
}

function correctUrl(url)
{
    var base = b('base').attr('href');
    
    var startsWith = url.substr(0, base.length) === base;

    //make full url
    if (!startsWith)
    {
        url = base + url;
    }

    //remove # and after from end
    url = url.split('#')[0];

    //remove ? in end
    if (url.substr(-1, 1) === '?')
    {
        url = url.substr(0, url.length - 1);
    }

    return url;
}

/**
 * Page Post
 *
 * @param {String} page
 * @param {String} formData
 * @returns {Boolean}
 */
function p(page, formData, callBack)
{
    return r("POST", page, formData, callBack);
}

/**
 * Page get
 *
 * @param {String} page
 * @param {String} formData
 * @returns {Boolean}
 */
function g(page, formData)
{
    return r("GET", page, formData);
}

/**
 * Make a event to current page
 *
 * @param {string} event
 * @param {mixed} formData
 * @returns {boolean}
 */
function e(event, formData)
{
    return p(getCurrentPage() + '/' + event, formData);
}

/**
 * http://abandon.ie/notebook/simple-file-uploads-using-jquery-ajax
 *
 * @deprecated since 25/09/2014
 *
 * @param {string} page saasd
 * @returns boolean false
 */
function fileUpload(page)
{
    var data = new FormData();

    // Adiciona todos arquivos selecionados no campo
    jQuery.each($('input[type=file]'), function (i, element)
    {
        var files = $(element).prop('files');

        for (var x = 0; x < files.length; x++)
        {
            data.append('file-' + i + x, files[x]);
        }
    });

    // Adiciona demais campos do formulário
    $('input, select').each(function ()
    {
        data.append(this.name, this.value);
    });

    return r("POST", page, data);
}

function showLoadingTimeout()
{
    if (!blend.ajax.timer)
    {
        blend.ajax.timer = setTimeout(showLoading, blend.ajax.timeout);
    }
    
    return false;
}

function showLoading()
{
    b(".loading").removeClass('hide');
    return false;
}

function hideLoading()
{
    if (blend.ajax.timer)
    {
        clearTimeout(blend.ajax.timer);
        blend.ajax.timer = null;
    }
    b(".loading").addClass('hide');
    return false;
}

function getFormDataToPost(formData,type)
{
    var isEmpty = typeof formData === 'undefined' || formData == null;
    
    //1 - support html5 formdata
    if (formData instanceof FormData)
    {
        return formData;
    } 
    //2 - simple js object, make a "serialize"
    else if (!isEmpty && typeof formData == 'object')
    {
        formData = $.param(formData);
        return formData;
    }
    //3 - string or simillar
    else if (!isEmpty)
    {
        return formData;   
    }
       
    //4 - this is the the default case, blend will post the entire form
    var hasFiles = $('input[type=file]').length > 0;

    //4.1 the post don't has files, make default formData (all forms)
    if ( !hasFiles )
    {
        formData = $(blend.defaultFormPost).serialize();
        
        //put the current url in post, usefull to get real url, when you are inside a component
        //only in post, so we not "fill" wrong data inside get
        if (type =='POST')
        {
            formData+='&page-url='+$('body').data('page-url');
        }
        
        return formData;
    }
    
    //4.2 - has files, so we need to make js magic in formData
    return mountHtml5FormData();
}

function mountHtml5FormData()
{
    let formData = new FormData();

    //add all files
    jQuery.each($('input[type=file]'), function (i, element)
    {
        let files = $(element).prop('files');
        let name = element.name;

        if (element.multiple)
        {
            for (let x = 0; x < this.files.length; x++)
            {
                formData.append(name+'-'+x, files[x]);
            }
        }
        else
        {
            //todo this is wrong, must use name, but this change will broke a lot of pages
            formData.append('file-' + i, files[0]);
        }
    });

    //add all form fields
    $('input, select, textarea').each(function ()
    {
        let avoidFile = !(this.type == 'file');

        if (avoidFile && (this.type !== 'checkbox' || this.checked === true))
        {
	        formData.append(this.name, this.value);
        }
    });

    //put the current url in post, usefull to get real url, when you are inside a component
    formData.append('page-url',$('body').data('page-url'));

    //minnor support for multiple values
    $("select[multiple]").each(function () 
    {
        var el = $(this);
        var id = el.attr('id').replace('[', '\\[').replace(']', '\\]');
        var name = el.attr('name').replace('[', '').replace(']', '');

        var value = Array();

        $("#" + id + " :selected").map(function (i, el) {
            value[i] = $(el).val();
        });

        formData.append(name, value);
    });
    
    return formData;
}

function disableFocused()
{
    let focused = $(':focus');

    //disable focused element, perhaps a button or link
    if (typeof focused.get(0) != 'undefined')
    {
        if (focused.get(0).tagName == 'a' || focused.get(0).tagName == 'button')
        {
            focused.attr('disabled', true);
        }
    }
    
    return focused;
}

/**
 *
 * Make a ajax to a page
 *
 * @param {string} type
 * @param {string} page
 * @param {string} formData
 * @returns {Boolean} Boolean always return fase, so it can be use in buttons and onclicks
 */
function r(type, page, formData, callBack)
{
    //in the case is a hash
    if (page.indexOf('#') == 0)
    {
        window.scrollTo( { top: document.querySelector(page).offsetTop, behavior: 'smooth'} );
        return false;
    }

    showLoadingTimeout();
    pluginsCallMethod('beforeSubmit');
    isAjax = true;
    let focused = disableFocused();
    let host = b('base').attr('href');
    let url = host + page.replace(host, '');
    let contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    formData = getFormDataToPost(formData,type);
   
    if (formData instanceof FormData)
    {
        contentType = false;
    }
    
    $.ajax({
        type: type,
        url: url,
        data: formData,
        cache: false,
        dataType: "json",
        contentType: contentType,
        processData: false,
        success: function (data)
        {
            //enable the focused element
            focused.removeAttr('disabled');

            if (!data)
            {
                toast('Sem retorno do servidor!', 'danger');
                return hideLoading();
            }

            data.cmds.filter(parseResponse);

            //if is GET get page from url+ formdata
            if (type === 'GET')
            {
                var append = '?';

                if (url.includes(append))
                {
                    append = '&';
                }

                page = url + append + formData;
            }

            //try to get page from data.pushsate
            if (typeof data.pushState !== undefined && data.pushState !== null)
            {
                if (data.pushState.length > 0)
                {
                    page = data.pushState;
                }
            }

            updateUrl(page);
            dataAjax(); //treat js especials

            if ( typeof callBack == 'function')
            {
                callBack();
            }
            
            blend.isBack = false;
        }
        ,
        error: function (xhr, ajaxOptions, thrownError)
        {
            console.log(thrownError);
            hideLoading();

            if (xhr.responseText === '')
            {
                toast('Sem resposta do servidor! Verifique sua conexão!', 'alert');
            } 
            else
            {
                focused.removeAttr('disabled');
                toast(xhr.responseText);
                dataAjax();
            }
            
            blend.isBack = false;
        }
    });

    return false;
}

function parseResponse(data)
{
    //only make response if content exists, to avoid clean
    if (data.content !== '')
    {
        let element = b('#' + data.selector);

        if (data.cmd === 'append')
        {
            element.append(data.content);
        }
        else if (data.cmd === 'html')
        {
            element.html(data.content);
        }
        else if (data.cmd === 'script')
        {
            runScriptOnce(data.content);
        }
        else
        {
            console.error("CMD not found:" + data.cmd);
        }
    }
}

async function getJson(page, formData, loadingShow, callBack)
{
    var host = b('base').attr('href');
    var url = host + page.replace(host, '');

    if (loadingShow)
    {
        showLoading();
    }

    return $.ajax({
        dataType: "json",
        cache: false,
        method: "POST",
        url: url,
        async: true,
        timeout: 20000,
        data: formData,
        success: function (response)
        {
            if (response && typeof response.script == 'string')
            {
                response.script.replace('\\\"', '\\"');
                b('body').append('<script>' + response.script + '</script>');
            } 
            else if ( typeof callBack == 'function')
            {
                callBack(response);
            }
            
            hideLoading();
        }
        , error: function (xhr, ajaxOptions, thrownError)
        {
            if (xhr.responseText === '')
            {
                toast('Sem resposta do servidor! Verifique sua conexão!', 'alert');
            }
            
            hideLoading();
        }
    });
}

function getRelativeUrl()
{
    return window.location.pathname.replace(b('base').attr('href').replace(window.location.protocol + '//' + window.location.host, ''), '');
}

/**
 * Return current page
 * @returns {string}
 */
function getCurrentPage()
{
    return getRelativeUrl().split('/')[0];
}

/**
 * Return current event
 * @returns {string}
 */
function getCurrentEvent()
{
    let relative = getRelativeUrl();
    let value = relative.split('/')[1];
    return value ? value : null ;
}

/**
 * Return the current value
 * @returns {string}
 */
function getCurrentValue()
{
    let relative = getRelativeUrl();
    let value = relative.split('/')[2];
    return value ? value : null ;
}

/**
 * Make a simple toast, cool not?
 *
 * @param msg message to show in toast.
 * @param type additional css class.
 * @param duration int.
 * @returns Boolean false.
 */
function toast(msg, type, duration)
{
    duration = duration === undefined ? 3000 : duration;
    type = type+ '' === 'undefined' ? '' : type;

    let isFrame = window.self !== window.top;

    if ( isFrame )
    {
        return parent.toast(msg, type, duration);
    }

    let toast = document.createElement('div');
    toast.setAttribute('class', 'toast ' + type);
    toast.innerHTML = msg + "<strong style=\"float:right;cursor:pointer;\" onclick=\"parentNode.remove();\">X</strong>";

    b('body').append(toast);

    setTimeout(function(){toast.classList.add('show')}, 100);
    setTimeout(function(){toast.classList.remove('show')}, duration);
    setTimeout(function(){toast.remove()}, duration*2);

    return false;
}

function isMobile()
{
    //simple way to detect mobile https://stackoverflow.com/questions/7838680/detecting-that-the-browser-has-no-mouse-and-is-touch-only
    let isMobile = window.matchMedia("(any-pointer: coarse)").matches ? true : false;

    return isMobile;
}

/**
 * Set focus on first field.
 * Supports popup;
 *
 * @returns false;
 */
function setFocusOnFirstField(onMobileToo)
{
    //don't focus on mobile
    if (isMobile() && onMobileToo != true)
    {
        return;
    }

    //support popup
    if (b('.popup').isVisible() >= 0 )
    {
        $('.popup').find('input:not([readonly]):not([disabled]):visible:first').focus();
    } 
    else
    {
        $('.content input:not([readonly]):not([disabled]):visible:first').focus();
    }

    return false;
}

/**
 * Send focus to next element, work great with on-press-enter
 * @returns {undefined}
 */
function focusNextElement()
{
    var element = document.activeElement;

    if (element)
    {
        var inputs = $(':input:visible, select:visible, a:visible').not('[tabindex=-1]').not('[disabled]').not('[readonly]');
        var next = inputs.eq(inputs.index(element) + 1);
        next.focus();
    }
}

function addScriptOnce(src, callBack)
{
    var baseUrl = b('base').attr('href');
    var list = document.getElementsByTagName('script');
    var i = list.length;
    var findedOnDoc = false;
    var compare = src.replace(baseUrl,'');

    //verify if is already loaded
    while (i--)
    {
        var myCompare = list[i].src.replace(baseUrl,'');
        
        if ( myCompare == compare)
        {
            findedOnDoc = true;
            break;
        }
    }
    
    // if we didn't find it on the page, add it
    if (!findedOnDoc)
    {
        var script = document.createElement('script');
        script.src = src;
        script.async = 'async';
        script.onload = callBack;
        document.querySelector('body').appendChild(script);
    }
    //if already on document, we only call the callback
    else
    {
        if (typeof callBack == 'function')
        {
            callBack();
        }
    }
}

function runScriptOnce(content,callback)
{
    if (!content)
    {
        return;
    }

    //legacy replace
    content.replace('\\\"', '\\"');
    let script = document.createElement('script');
    script.innerHTML = content;
    script.onload = callback;
    document.querySelector('body').append(script);
    script.remove();
}

/**
 * Scroll to top
 * @returns void
 */
function scrollTop()
{
    window.scrollTo({top: 0, behavior: 'smooth'});
}

/**
 * Adicionado ao Blend temporariamente, para o site.
 * 
 * Turn string into a url optimized string easily.
 * @param string str
 * @returns url optimized string
 */
function slug(str) 
{
    str = str.replace(/^\s+|\s+$/g, ''); // strong trim
    str = str.toLowerCase();

    // remove accents, swap ñ for n, etc
    var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/&,:;%";
    var to = "aaaaaeeeeeiiiiooooouuuunc-@_----";

    for (var i=0, l=from.length ; i<l ; i++) 
    {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }

    str = str.replace(/[^a-z0-9 -_]/g, '') // remove invalid chars
      .replace(/\s+/g, '-') // collapse whitespace and replace by -
      .replace(/-+/g, '-') // collapse dashes
      .replace('.','-');

    return str;
}   
