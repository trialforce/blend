/* global CKEDITOR, shortcut, FormData */

"use strict";
//handle the back and forward buttons
var invalidHover = true;
var avoidUrlRegister = false;
var isAjax = false;
var blendJs = function(){};
var blend = {};
blend.defaultFormPost = 'form';
blend.plugins = [];
 
function pluginsRegister()
{
    for (var i = 0; i < blend.plugins.length; i++)
    {
        var plugin = blend.plugins[i];
        
        if ( typeof plugin.register == 'function')
        {
            plugin.register();
        }
    }
}

function pluginsStart()
{
    for (var i = 0; i < blend.plugins.length; i++)
    {
        var plugin = blend.plugins[i];
        
        if ( typeof plugin.start == 'function')
        {
            plugin.start();
        }
    }
}

//avoid console.log problems
if (!window.console)
{
    window.console = {};
    window.console.log = function ()
    {
    };
}

if (typeof $ == 'function')
{
    window.onpopstate = function(event) 
    {
        var okay = escape();
        
        if ( !okay )
        {
            avoidUrlRegister = true;
            p(window.location.href, true);
        }
        else
        {
            //não mudar a url
            return false;
        }       
    };
}

//Loading without ajax
window.onload =  function ()
{
    pluginsRegister();
    dataAjax();
        
    /**
     * Add support to play method in jquery
     *
     * @returns {jQuery.fn@call;each}
     */
    jQuery.fn.play = function () 
    {
        return this.each(function () 
        {
            if (typeof this.play === 'function')
            {
                this.play();
            }
        });
    };
    
    //jquery plugin to create element
    //https://github.com/ern0/jquery.create/blob/ster/jquery.create.js
    (function($) 
    {
        $.create = function(tag,id) 
        {
            let elm = document.createElement(tag.toUpperCase());

            if (typeof(id) != "undefined") 
            {
                elm.id = id;
            }

            return $(elm);
        }; // $.create()
    }(jQuery));
    
    //destroy popup on esc
    $(document).keyup(function(e) 
    {
        if (e.key === "Escape") 
        {
           return escape();
        }
    });
};

function escape()
{
    //main menu
    if ( $('body').hasClass('menu-open') )
    {
        menuClose();
        return true;
    }
    //popup
    else if ( $('.popup:visible').length )
    {
        //try to call the close action of the popup
        var jsText= $('#btbClosePopup:visible').attr('onclick');
        
        if (jsText)
        {
            eval(jsText);
        }
        else
        {
           popup('destroy');
        }
        
        return true;
    }
    //calendar
    else if ( $('.xdsoft_datetimepicker.xdsoft_noselect:visible').length )
    {
        $('.xdsoft_datetimepicker.xdsoft_noselect').hide();
        return true;
    }
    //slider full screen
    else if ( $('slider-full-screen').length > 0)
    {
        removeSlideFullScreen();
    }
    
    return false;
}

function convertAjaxLinks()
{
    //links
    $("[data-ajax]").each(function ()
    {
        var element = $(this);
        var dataAjax = element.attr('data-ajax');
        var href = element.attr('href');
        var disabled = element.attr('disabled');
        element.removeAttr('data-ajax');
        
        //if is an outside link do not use ajax system
        if (href && (href.indexOf('http://') === 0 || href.indexOf('https://') === 0) )
        {
            href = null;
        }

        if (href && dataAjax)
        {
            if (disabled == 'disabled')
            {
                element.click(function ()
                {
                    toast('Ação desabilitada!');
                    return false;
                });
            }
            else if (dataAjax === 'noFormData')
            {
                element.click(function () {
                    return g(href, '');
                });
            } 
            else
            {
                element.click(function () {
                    return p(href);
                });
            }
        }
    }
    );
}

function convertOnPressEnter()
{
    $("[data-on-press-enter]").each(function ()
    {
        var element = $(this);
        var myEvent = element.attr('data-on-press-enter');

        //get out if converted
        if (element.attr('data-on-press-enter-converted') == "1")
        {
            return;
        }

        //mark as converted
        element.attr('data-on-press-enter-converted', "1");
        element.keydown(
                function (e)
                {
                    if (e.keyCode == "13" && !e.shiftKey)
                    {
                        eval(myEvent);
                        e.preventDefault();
                    } else
                    {
                        return true;
                    }
                }
        );
    }
    );
}

/**
 * Parse data-ajax attribute, to make a link ajax
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
        hideLoading();
    }
    
    pluginsStart();
    
    //clear the function to avoid calling more times
    blendJs = function(){};
	
    convertAjaxLinks();
    convertOnPressEnter();

    //remove invalid on change
    if ( typeof validatorRemoveInvalid == 'function' )
    {
        validatorRemoveInvalid();
        validatorApplyInvalid();
    }

    //make masks work
    if (typeof jQuery().mask == 'function')
    {
        applyAllMasks(); 
    }

    //input float and integer
    if (typeof ($('input.float').autoNumeric) === "function")
    {
        applyAutonumeric();
    }

    if (typeof ($('.swipebox').swipebox) === "function")
    {
        $('.swipebox').swipebox();
    }

    //multipleSelect();
    if (typeof seletMenuItem == 'function') 
    {
        seletMenuItem();
    }
    
    if (typeof dateTimeInput == "function")
    {
        dateTimeInput();
    }
    
    //add system class
    if ( typeof isIos =="function" && isIos())
    {
        $('body').removeClass('os-ios').addClass('os-ios');
    }
    else if ( typeof isAndroid =="function" && isAndroid())
    {
        $('body').removeClass('os-android').addClass('os-android');
    }
    
    //blend slider
    $('.slider').each(function ()
    {
        slide('#' + $(this).attr('id'))
    });
    
    if (typeof actionList == 'function')
    {
        actionList.restore();
    }
    
    if ( typeof grid =='function')
    {
        grid.restoreTextSize();    
    }
    
    hideLoading();

    return false;
}

function applyAutonumeric()
{
    $('input.float').autoNumeric('init');
    
    //limpa campo quando entrar nele e for zerado
    $('input.float').focus(function () {
        if ($(this).val() == '0,00')
        {
            $(this).val('');
        }
    });

    //limpa campo quando entrar nele e for zerado
    $('input.float').blur(function () {
        if ($(this).val() == '')
        {
            $(this).val('0,00');
        }
    });

    $('input.integer').autoNumeric('init');
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

function getBaseUrl()
{
    var bases = document.getElementsByTagName('base');
    var base = '';
    
    if ( bases && bases[0])
    {
        base = bases[0].href;
    }
    
    return base;
}

function correctUrl(url)
{
    var base = getBaseUrl();
    
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

var avoidTab = function ()
{
    var keyCode = event.keyCode || event.which;

    if (keyCode == 9)
    {
        event.preventDefault();
    }
}

function showLoading()
{
    $("body").bind("keydown", avoidTab);
    $(".loading").fadeIn('fast');
}

function hideLoading()
{
    $("body").unbind("keydown", avoidTab);
    $(".loading").fadeOut('fast');
}

function getFormDataToPost(formData)
{
    var isEmpty = typeof formData === 'undefined' || formData == null;
    
    //1 - support html5 formdata
    if (formData instanceof FormData)
    {
        return formData;
    } 
    //2 - simple js object, make a "serialize"
    else if (typeof formData == 'object')
    {
        formData = $.param(formData);
        return formData;
    }
    //3 - string or simillar
    else if (!isEmpty)
    {
        return formData;   
    }
    
    //4 - this is the the defafult case, blend will post the entire form
    var hasFiles = $('input[type=file]').length > 0;

    //4.1 the post don't has files, make default formData (all forms)
    if ( !hasFiles )
    {
        formData = $(blend.defaultFormPost).serialize();
        return formData;
    }

    //4.2 - has files, so we need to make js magic in formData
    return mountHtml5FormData();
}

function mountHtml5FormData()
{
    var formData = new FormData();

    //add all files
    jQuery.each($('input[type=file]'), function (i, element)
    {
        var files = $(element).prop('files');
        formData.append('file-' + i, files[0]);
    });

    //add all form fields
    $('input, select, textarea').each(function ()
    {
        formData.append(this.name, this.value);
    });

    //control uncked checkbox
    $("input:checkbox:not(:checked)").each(function ()
    {
        formData.append(this.name, '0');
    });

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
    isAjax = true;
    var focused = $(':focus');

    //disable focused element, perhaps a button or link
    if (typeof focused.get(0) != 'undefined')
    {
        if (focused.get(0).tagName == 'a' || focused.get(0).tagName == 'button')
        {
            focused.attr('disabled', true);
        }
    }

    showLoading();
    
    //TODO refactor to plugin
    if (typeof updateEditors == 'function')
    {
        updateEditors();
    }

    var host = $('base').attr('href');
    var url = host + page.replace(host, '');

    //default jquery value https://api.jquery.com/jQuery.ajax/
    var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    formData = getFormDataToPost(formData);
   
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
        xhrFields: {
            withCredentials: true //make cookie work on ajax
        },
        success: function (data)
        {
            //enable the focused element
            focused.removeAttr('disabled');

            if (!data)
            {
                toast('Sem retorno do servidor!', 'danger');
                hideLoading();
                return;
            }

            //only make response if content exists, to avoid clean
            if (data.content !== '')
            {
                if (data.responseType === 'append')
                {
                    $('#' + data.response).append(data.content);
                } else
                {
                    $('#' + data.response).html(data.content);
                }
            }

            //try to get page from data.pushsate
            if (typeof data.pushState !== undefined && data.pushState !== null)
            {
                if (data.pushState.length > 0)
                {
                    page = data.pushState;
                }
            }

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
            
            updateUrl(page);
            //put the js inside body element, to execute
            data.script.replace('\\\"', '\\"');
            
            try
            {
                $('body').append('<script>' + data.script + '</script>');
            }
            catch (e) 
            {
                alert('Erro ao executar javascript vindo do servidor!');
                console.log(e);
                console.log(data.script);
            }
            
            //treat js especials
            dataAjax();
            
            if ( typeof callBack == 'function')
            {
                callBack();
            }
        }
        ,
        error: function (xhr, ajaxOptions, thrownError)
        {
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
        }
    });

    return false;
}

function getJson(page, formData, loadingShow, callBack)
{
    var host = $('base').attr('href');
    var url = host + page.replace(host, '');

    if (loadingShow)
    {
        showLoading();
    }

    $.ajax({
        dataType: "json",
        method: "POST",
        url: url,
        async: true,
        timeout: 20000,
        data: formData,
        xhrFields: {
            withCredentials: true //make cookie work on ajax
        },
        success: function (response)
        {
            if (response && typeof response.script == 'string')
            {
                response.script.replace('\\\"', '\\"');
                $('body').append('<script>' + response.script + '</script>');
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

function getSelected(selector)
{
    var result = $(selector).map(function (i, el) {
        return $(el).val();
    });

    return
}

/**
 * Return current page
 * @returns {string}
 */
function getCurrentPage()
{
    var relativeUrl = window.location.pathname.replace($('base').attr('href').replace(window.location.protocol + '//' + window.location.host, ''), '');
    return relativeUrl.split('/')[0];
}

/**
 * Return current event
 * @returns {string}
 */
function getCurrentEvent()
{
    var relativeUrl = window.location.pathname.replace($('base').attr('href').replace(window.location.protocol + '//' + window.location.host, ''), '');
    return relativeUrl.split('/')[1];
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
    var toast = $("<div class='toast " + type + "'>" +
            msg +
            "<strong style=\"float:right;cursor:pointer;\" onclick=\"$(this).parent().remove();\">X</strong></div>")
            .appendTo('body');
            
    setTimeout(function(){toast.addClass('show')}, 100);
    setTimeout(function(){toast.removeClass('show')}, duration);
    setTimeout(function(){toast.remove()}, duration*2);

    return false;
}

/**
 * Set focus on first field.
 * Supports popup;
 *
 * @returns false;
 */
function setFocusOnFirstField()
{
    //support popup
    if ($('.popup').length)
    {
        $('.popup').find('input:not([readonly]):not([disabled]):first').focus();
    } 
    else
    {
        $('.content input:not([readonly]):not([disabled]):first').focus();
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
    var list = document.getElementsByTagName('script');
    var i = list.length;
    var findedOnDoc = false;
    var compare = src.replace(getBaseUrl(),'');

    //verify if is already loaded
    while (i--)
    {
        var myCompare = list[i].src.replace(getBaseUrl(),'');
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
        script.onload = callBack;
        document.getElementsByTagName('body')[0].appendChild(script);
    }
    //if already on document, we only call the callback
    else
    {
        callBack();
    }
}


/**
 * Scroll to top
 * @returns void
 */
function scrollTop()
{
    $("html, body").animate({ scrollTop: 0 }, 300);
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
    var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/&,:;";
    var to   = "aaaaaeeeeeiiiiooooouuuunc--_---";

    for (var i=0, l=from.length ; i<l ; i++) 
    {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }

    str = str.replace(/[^a-z0-9 -_]/g, '') // remove invalid chars
      .replace(/\s+/g, '-') // collapse whitespace and replace by -
      .replace(/-+/g, '-'); // collapse dashes

    return str;
}