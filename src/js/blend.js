/* global CKEDITOR, shortcut */

"use strict";
//handle the back and forward buttons
var formChangedAdvice = false;
var invalidHover = true;
var avoidUrlRegister = false;
var isAjax = false;
var blendJs = function(){};

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
    $(window).bind('popstate', function (event)
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
    });
}

//Loading without ajax
window.onload =  function ()
{
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
    else if ( $('.popup').length )
    {
        //try to call the close action of the popup
        var jsText= $('#btbClosePopup').attr('onclick');
        
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

//polyfill to old browser
function startsWith(originalString, searchString)
{
    if (typeof searchString == 'undefined')
    {
        return false;
    }

    return originalString.substr(0, searchString.length) === searchString;
}

function stripTags(str)
{
    return str.replace(/<\/?[^>]+(>|$)/g, "");
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
    
    //clear the function to avoid calling more times
    blendJs = function(){};
	
    //links
    $("[data-ajax]").each(function ()
    {
        var element = $(this);
        var dataAjax = element.attr('data-ajax');
        var href = element.attr('href');
        var disabled = element.attr('disabled');
        element.removeAttr('data-ajax');

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

    //on press enter
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

    //remove invalid on change
    $('[data-invalid=1]').change(function () 
    {
        //remove data-invalid for element
        $(this).removeAttr('data-invalid');
        
        //remove hint
        $(this).parent().find('.hint.danger').fadeOut(500, function () 
        {
            $(this).remove();
        });
        
        var tab = $(this).parents('.tabBody .item');
        
        //if is inside tab and tab don't has any element with data-invalid
        //remove data-invalid from tab
        if ( tab.length > 0)
        {
            tab = tab.eq(0);
            var hasInvalidInside = tab.find('[data-invalid="1"]').length > 0 ;

            if ( !hasInvalidInside)
            {
                $('#'+tab.attr('id')+'Label').removeAttr('data-invalid');
            }
        }
    });

    //make invalid
    $('[data-invalid=1]').each(function () 
    {
        var element = $(this);
        var title = element.attr('title');
        var tab = element.parents('.tabBody .item');
        
        //inside tab
        if ( tab[0])
        {
            $('#'+$(tab[0]).attr('id')+'Label').attr('data-invalid',1);
        }
        
        //don't create hint for hidden elements
        if (!element.is(':visible'))
        {
            return;
        }
        
        if (invalidHover == true)
        {
            if (title !== undefined)
            {
                element.hover(function ()
                {
                    var position = element.position().left + element.width()
                    var myDiv = $('<div class="hint danger">' + element.attr('title') + '</div>');
                    myDiv.css('left', position);

                    element.parent().append(myDiv);
                },
                function ()
                {
                    $(element).parent().find(".hint").remove();
                });
            }
        } 
        else
        {
            var position = element.position().left + element.width()
            var myDiv = $('<div class="hint danger">' + element.attr('title') + '</div>');
            myDiv.css('left', position);

            element.parent().append(myDiv);
        }
    });

    //make masks work
    if (typeof jQuery().mask == 'function')
    {
        $("input[data-mask]").each(function () {
            $(this).mask($(this).attr("data-mask"));
        });
    }

    //mask functions
    $("input[data-mask-function]").each(function () {
        var maskVar = window[$(this).attr("data-mask-function")];
        $(this).mask(maskVar, {onKeyPress: function (input, e, currentField, options) {
                  $(currentField).mask(maskVar(input), options);
            }});
    });

    //input float and integer
    if (typeof ($('input.float').autoNumeric) === "function")
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

    if (typeof ($('.swipebox').swipebox) === "function")
    {
        $('.swipebox').swipebox();
    }

    //multipleSelect();
    seletMenuItem();
    dateTimeInput();

    //mark form changed on change
    $('input, select, textarea').on('change', function () {
        markFormChanged();
    });

    //on key press, add support for nick editor, and other contenteditable
    $('input, select, textarea, *[contenteditable]').on('keypress', function () {
        markFormChanged();
    });

    $('*[data-form-changed-advice]').on('click', function (event)
    {
        if (formChangedAdvice == false && $('#formChanged').val() == 1)
        {
            return showFormChangedAdvice();
        }
    });
    
    //add system class
    if ( isIos())
    {
        $('body').removeClass('os-ios').addClass('os-ios');
    }
    else if ( isAndroid())
    {
        $('body').removeClass('os-android').addClass('os-android');
    }
    
    //blend slider
    $('.slider').each(function ()
    {
        slide('#' + $(this).attr('id'))
    });
    
    actionList.restore();
    grid.restoreTextSize();    
    hideLoading();

    return false;
}

function dateTimeInputMobile()
{
    $('.dateinput').not('[readonly]').each(function ()
    {
        $(this).mask('99/99/9999');
    });

    $('.datetimeinput').not('[readonly]').each(function ()
    {
        $(this).mask('99/99/9999 99:99:99');
    });

    $('.timeinput').not('[readonly]').each(function ()
    {
        $(this).mask('99:99:99');
    });
}

function dateTimeInputDesktopOnChange(dp,input)
{
    //console.log(dp, input);
}

function dateTimeInputDesktopOnShow(currentTime, input)
{
    //console.log(currentTime, input);
}

//https://xdsoft.net/jqplugins/datetimepicker/
function dateTimeInputDesktop()
{   
    $('.dateinput').not('[readonly]').each(function()
    {
        $(this).datetimepicker({
            onChangeDateTime:dateTimeInputDesktopOnChange,
            onShow: dateTimeInputDesktopOnShow,
            id: 'dialog-date-'+$(this).attr('id'),
            className: 'dialog-date',
            timepicker: false,
            defaultSelect: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            mask: true,
            allowBlank: true,
            format: 'd/m/Y',
            scrollMonth:false,
            scrollTime:false,
            scrollInput:false,
            step: 15
        });
    });

    $('.datetimeinput').not('[readonly]').each(function()
    {
        $(this).datetimepicker(
        {
            onChangeDateTime:dateTimeInputDesktopOnChange,
            onShow: dateTimeInputDesktopOnShow,
            id: 'dialog-datetime-'+$(this).attr('id'),
            className: 'dialog-datetime',
            format: 'd/m/Y H:i:s',
            mask: true,
            defaultSelect: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            allowBlank: true,
            scrollMonth:false,
            scrollTime:false,
            scrollInput:false,
            step: 15
        });
    });

    $('.timeinput').not('[readonly]').datetimepicker(
    {
        onChangeDateTime:dateTimeInputDesktopOnChange,
        onShow: dateTimeInputDesktopOnShow,
        id: 'dialog-time-'+$(this).attr('id'),
        className: 'dialog-time',
        format: 'H:i:s',
        defaultSelect: false,
        datepicker: false,
        validateOnBlur: false,
        closeOnDateSelect: true,
        allowBlank: true,
        mask: true,
        step: 15,
        scrollMonth:false,
        scrollTime:false,
        scrollInput:false,
    });
    
    $('.dateinput,.datetimeinput,.timeinput').on('blur', function () 
    {
        markFormChanged();
    });
    
    //avoid open the keyboard
    $('.dateinput,.datetimeinput,.timeinput').on('click', function () 
    {     
        if (isCellPhone())
        {
            $(this).blur();
        }
    });
    
    //close if click in background
    if (isCellPhone())
    {
        $('.xdsoft_datetimepicker.xdsoft_noselect').click(function()
        {
            $(this).hide();
            event.preventDefault();
            return false;
        });
    }
}

function dateTimeInputFallBackNative()
{
    //fallback to default date of browser
    $('.dateinput').each(function ()
    {
        var element = $(this);
        var value = element.val();

        //don't format
        if (value.indexOf('/') > 0)
        {
            var date = value.split('/').reverse().join('-');
            element.val(date);
        }

        element.prop('type', 'date');
    });

    $('.datetimeinput').each(function () 
    {
        var element = $(this);
        var value = element.val();

        //don't format
        if (value.indexOf('T') < 0)
        {
            var datetime = value.split(' ');
            var date = datetime[0].split('/').reverse().join('-');
            element.val(date + 'T' + datetime[1]);
        }

        element.prop('type', 'datetime-local');
    });
}

function dateTimeInput()
{ 
    if ( isIos() )
    {
        dateTimeInputMobile();
    } 
    else if (isAndroid())
    {
        dateTimeInputFallBackNative();
    }
    else if (typeof $().datetimepicker === 'function')
    {
        dateTimeInputDesktop();
    }
    else
    {
        dateTimeInputFallBackNative();
    }
}

function multipleSelect()
{
    //nao faz se for android ou iphone
    if (isAndroid() || isIos())
    {
        return;
    }
    
    $('select[multiple] option').mousemove(function ()
    {
        return false;
    });
    
    $('select[multiple]').each( function ()
    {
        var element = $(this);
        var originalName = element.attr('name').replace('[','').replace(']','');
        element.find('option[value=""]').html('Multiplos selecionados...');
        
        element.find('option:selected').each( function()
        {
            var option = $(this);
            option.addClass('selected');
            option.removeAttr('selected');
            var select = option.parent();
            var value = option.attr('value');
            var valueName = originalName+'['+value+']';
            var input = '<input type="hidden" id="'+valueName+'"name="'+valueName+'" value="'+value+'" />';
            select.parent().append(input);
        });
        
        element.attr('data-original-name',originalName);
        element.attr('data-multiple',"multiple");
        element.removeAttr('id');
        element.removeAttr('name');
        element.removeAttr('multiple');
        
        element.focus( function() 
        {
            var pos = $(this).offset();
            $(this).css("position","absolute");
            $(this).offset(pos);
            $(this).attr("size","10");
        });
        
        element.blur( function() {
            $(this).css("position","static");
            $(this).attr("size","1");
            $(this).val('');
        });
        
        element.change( function()
        {
            var select = $(this);
            var option = select.find('option:selected');
            var value = option.attr('value');
            var originalName = select.attr('data-original-name');
            var valueName = originalName+'['+value+']';
            var selector = '#'+originalName+'\\['+value+'\\]';
            var optionSelected = $(selector).length > 0;
           
            if (!optionSelected)
            {
                select.parent().append('<input type="hidden" id="'+valueName+'"name="'+valueName+'" value="'+value+'" />');
                option.addClass('selected');
            }
            else
            {
                option.removeClass('selected');
                $(selector).remove();
            }
            
            ///select.val('');  
        });
    });
}

/**
 * Mark for as changed
 * @returns false
 */
function markFormChanged()
{
    //mark form as changed
    $('#formChanged').val(1);
    //enable all save button
    $('.save').removeAttr('disabled');
    //disable advice flag
    formChangedAdvice = false;

    return false;
}

function showFormChangedAdvice()
{
    toast('Os dados foram modificados! Tem certeza que quer realizar esta ação?');
    formChangedAdvice = true;

    event.preventDefault();
    //event.stopImmediatePropagation();

    return false;
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

    //make full url
    if (!startsWith(url, base))
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

    if (focused.data('form-changed-advice') == 1 && $('#formChanged').val() == 1)
    {
        if (formChangedAdvice == false)
        {
            focused.removeAttr('disabled');
            return showFormChangedAdvice();
        }
    }

    //disable focused element, perhaps a button or link
    if (typeof focused.get(0) != 'undefined')
    {
        if (focused.get(0).tagName == 'a' || focused.get(0).tagName == 'button')
        {
            focused.attr('disabled', true);
        }
    }

    showLoading();
    updateEditors();

    var host = $('base').attr('href');
    var url = host + page.replace(host, '');

    //default jquery value https://api.jquery.com/jQuery.ajax/
    var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';

    if (typeof formData === 'undefined' || formData == null )
    {
        if ($('input[type=file]').length > 0)
        {
            contentType = false;
            var formData = new FormData();

            // Adiciona todos arquivos selecionados no campo
            jQuery.each($('input[type=file]'), function (i, element)
            {
                var files = $(element).prop('files');
                formData.append('file-' + i, files[0]);
            });

            // Adiciona demais campos do formulário
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
            $("select[multiple]").each(function () {
                var el = $(this);
                var id = el.attr('id').replace('[', '\\[').replace(']', '\\]');
                var name = el.attr('name').replace('[', '').replace(']', '');

                var value = Array();

                $("#" + id + " :selected").map(function (i, el) {
                    value[i] = $(el).val();
                });

                formData.append(name, value);
            });
        }
        else
        {
            formData = $('form').serialize();

            //add server class to post
            $('[data-server-class]').each(
                    function () {
                        formData += '&data-server-class[' + $(this).attr('id') + ']=' + $(this).data('server-class');
                    }
            )

        }
    } else
    {
        if (formData instanceof FormData)
        {
            contentType = false;
        } 
        else if (typeof formData == 'object')
        {
            formData = $.param(formData);
        }
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
                page = url + '?' + formData;
            }
            
            updateUrl(page);
            //put the js inside body element, to execute
            data.script.replace('\\\"', '\\"');
            $('body').append('<script>' + data.script + '</script>');
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
            } else
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
 * Remove all invalid information, and make it if necessary.
 * @returns boolean false
 */
function removeDataInvalid()
{
    $('[data-invalid=1]').removeAttr('title').unbind('hover').removeAttr('data-invalid');
    $('.hint.danger').remove();

    return false;
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
 * Control all popup behavior.
 *
 * @param action (show, opem, close, destroy).
 * @param selector popups id.
 * @returns Boolean false.
 */
function popup(action, selector)
{
    if (selector + "" === 'undefined')
    {
        selector = '';
    }

    var element = $('.popup' + selector);

    if (action === 'show' || action === 'open')
    {
        $('.makePopupFade').addClass('popupFaded');
        $('body').css('overflow','hidden');

        element.fadeIn(600);
    } 
    else if (action === 'close')
    {
        $('body').css('overflow','auto');
        $('.makePopupFade').removeClass('popupFaded');

        element.find('.inner').animate(
                {
            opacity: 0,
            width: 0,
            minWidth: 0,
            height: 0,
        }, 500, function () 
        {
            element.hide();
            //restore style
            $('.inner').removeAttr('style');
        });
    }
    else if (action === 'destroy')
    {
        $('body').css('overflow','auto');
        //remake popup fade
        $('.makePopupFade').removeClass('popupFaded');
        //remove any action-list that has popup
        $('.action-list-popup').remove();

        //coll animantion
        element.find('.inner').animate({
            opacity: 0,
            width: 0,
            minWidth: 0,
            height: 0,
        }, 300, function ()
        {
            element.remove();
        });
    } 
    else if (action === 'maximize')
    {
        element.find('.inner')
                .css('position', 'fixed')
                .css('left', '50%')
                .css('marginLeft', ($('.inner').width() / 2) * -1);

        element.find('.inner').animate({
            top: 0,
            left: 0,
            margin: 0,
            width: "100%",
            height: "100%",
        }, 500, function () 
        {
            element.find('.body').addClass('maximized');
        });
    }

    return false;
}

/**
 * Update the content of html editor nicEditor and CkEditor
 *
 * @returns void
 */
function updateEditors()
{
    var editor;

    if (typeof nicEditors !== 'undefined' && typeof nicEditors.editors[0] !== 'undefined' && typeof nicEditors.editors[0].nicInstances !== 'undefined')
    {
        for (var i = 0; i < nicEditors.editors[0].nicInstances.length; i++)
        {
            editor = $(nicEditors.editors[0].nicInstances[i].e);
            editor.html(nicEditors.editors[0].nicInstances[i].getContent());
        }
    }

    $('textarea').each(function ()
    {
        if (typeof (nicEditors) !== 'undefined')
        {
            var editor = nicEditors.findEditor($(this).attr('id'));

            if (editor !== undefined && editor !== null)
            {
                $(this).html(editor.getContent());
            }
        }
    });
    
    //add support for ckeditor 4
    if ( typeof CKEDITOR == 'object')
    {
        for ( var instance in CKEDITOR.instances )
        {
          CKEDITOR.instances[instance].updateElement();
        }
    }
}

var timerTypeWatch = 0;
var body = $('body')[0];
//close combos on click outside
if(body)
{
    body.addEventListener("click", function()
    {
        var currentElement = $(document.activeElement);
        var isCombo = currentElement.hasClass('labelValue');

        if ( !isCombo)
        {
            //close all combos
            $('.dropDownContainer').slideUp(50);
        }
        //comboHideDropdown(id);
    }
    , false);
}

function comboInputClick(id,eThis)
{
    //close other popups
    $('.dropDownContainer').not('#'+id).slideUp(50);
    //open this popup
    comboToggleDropdown(id);
}

function comboToggleDropdown(id)
{
    var element = $('#dropDownContainer_' + id);

    if ( $('#labelField_'+id).attr('readonly')  )
    {
        comboHideDropdown(id);
        return;
    }
    
    if(element.is(':visible'))
    {
        comboHideDropdown(id);
    }
    else
    {
        comboShowDropdown(id);
    }
}

function comboShowDropdown(id)
{
    var element = $('#dropDownContainer_' + id);

    //realonly, avoid open dropdown
    if (element.is('[readonly]'))
    {
        comboHideDropdown(id);
        return false;
    }

    //mininum width
    element.css('min-width', $('#labelField_' + id).width() + 'px');
    element.slideDown(30);
}

function comboHideDropdown(id)
{
    $('#dropDownContainer_' + id).slideUp(30);
}

function comboDoSearch(id)
{
    eval($('#labelField_' + id).data('change'));
}

function comboSelectItem(comboId, value, label, eThis)
{
    //remove selected from other tr's
    $(eThis).parent().find('tr').removeClass('selected');
    //mark this as select
    $(eThis).addClass('selected');
    
    //change the value and trigger onchange
    var element = $('#' + comboId);
    element.val(value);
    element.trigger('change');

    //change the value of label field
    var elementLabel = $('#labelField_' + comboId);
    elementLabel.val(label);
    
    //open the dropdrown table
    comboHideDropdown(comboId);  
}

function comboTypeWatch(element, event, callback, ms)
{
    var parente = $(element).parent();
    var id = parente.find('.inputValue').attr('id');

    if ($('#labelField_' + id).is('[readonly]'))
    {
        return false;
    }

    //TAB, is called when enter input, will make work normally, and clear timeout
    if (event.keyCode == 9)
    {
        clearTimeout(timerTypeWatch);
        return true;
    }

    //down
    if (event.keyCode === 40)
    {
        comboShowDropdown(id);

        var next = parente.find('table tr.selected').next();

        if ( next.length > 0 )
        {
            parente.find('table tr.selected').removeClass('selected');
            next.addClass('selected');
        }
        else
        {
            parente.find('table tr').eq(0).addClass('selected');
        }

        return false;
    }
    //up
    else if (event.keyCode === 38)
    {
        comboShowDropdown(id);
        var prev = parente.find('table tr.selected').prev();
        parente.find('table tr.selected').removeClass('selected');
        prev.addClass('selected');

        return false;
    }
    //enter
    else if (event.keyCode === 13)
    {
        //make the selection
        parente.find('table tr.selected').click();
        comboHideDropdown(id);

        return false;
    } 
    else
    {
        clearTimeout(timerTypeWatch);
        timerTypeWatch = setTimeout(callback, ms);
    }
}

function comboModelClick(idInput)
{
    var input = $('#'+idInput);
    var value = input.val();
    var page = input.data('model');
    
    p(page+'/editarpopup/'+value+'&idInput='+idInput);
}

function comboModelClose(idInput)
{
    var iframe = document.getElementById("edit-popup-iframe");
    var doc = iframe.contentWindow.document;
    var editEditId = $(doc).find('#id').val();
    
    //closes popup
    popup('destroy','#edit-popup');
    
    //fill the value on hidden field
    $('#'+idInput).val(editEditId);
    //call the ajax action to fill dropdown, put hideCombo, to make it work properly
    var code = $('#labelField_'+idInput).data('change');
    //run the code fill label value
    eval(code.replace('mountDropDown','fillLabelByValue'));
    //fill dropdown
    setTimeout(function(){eval(code.replace(idInput,idInput+'?hideCombo=true'));},500);
}

/**
 * Bind var func execution on key press.
 *
 * @param {string} key Example : 'F5','Ctrl+Alt+S'
 * @param {function} func function() { alert('this is my function !'); }
 * @returns bool
 */
function addShortcut(key, func)
{
    if (typeof shortcut !== 'undefined')
    {
        return shortcut.add(key, func);
    }

    return false;
}

/**
 * Unbind function execution on key press.
 * @param {string} key
 * @returns Boolean
 */
function removeShortcut(key)
{
    if (typeof shortcut !== 'undefined')
    {
        return shortcut.remove(key);
    }

    return false;
}

/**
 * Máscara de CPF/CNPJ
 *
 * @param {DomElement} input
 * @param {Event} e
 * @param {DomElement} currentField
 * @param {string} options
 * @returns {String}
 */
var maskCNPJCPF = function (input, e, currentField, options)
{
    //tira os caracters estranhos para fazer funcionar a contagm
    var str = input.replace(/[\.\-]/g, '');

    if (str.length > 11)
    {
        return '99.999.999/9999-99';
    }
    else if (str.length > 8)
    {
        return '999.999.999-999999';
    } 
    else
    {
        return '999999999999999999';
    }
};

var maskDateTime = function (input, e, currentField, options)
{
    if (input.length > 9)
    {
        return '99/99/9999 99:99:99';
    } 
    else
    {
        return '99/99/9999';
    }
};

var maskSimpleFone = function (e, r, n, t)
{
    var str = e.replace(/[\.\-]/g, "");

    return str.length > 12 ? "(99)99999-9999" : "(99)9999-99999";
};


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

function selectTab(tabItemId)
{
    tabItemId = tabItemId.replace('#', '');
    var tab = $('#' + tabItemId).parents('.tab').eq(0);

    //atualiza url
    //updateUrl(window.location.href.replace(window.location.hash, '') + '#' + tabItemId);

    //body
    tab.find('.tabBody>.item').hide();
    tab.find('.tabBody #' + tabItemId).show();

    //head
    tab.find('.tabHead>.item').removeClass('selected');
    tab.find('.tabHead #' + tabItemId + 'Label').addClass('selected');
    
    //show actions as tab-group needed
    $('.action-list li').hide();
    $('.action-list li[data-group=""]').show();
    $('.action-list li[data-group="'+tabItemId+'"]').show();

    return false;
}

function getTabFromId(id)
{
    return $('#'+id).parents('.tabBody .item');
}

function getTabLabel(tabId)
{
    if (typeof tabId == 'undefined')
    {
        return null;
    }
    
    return stripTags($('#'+tabId+'Label').html()).replace(/(\r\n|\n|\r)/gm, "");
}

function seletMenuItem()
{
    var currentPage = getCurrentPage();
    //remove class seleted from all items from menu
    $('nav *').removeClass('selected');

    //seleted class in current url
    $('nav *[href=\'' + currentPage + '\']').addClass('selected');
    //seleted class in current url if is a submenu
    $('[href=\'' + currentPage + '\']').parents('li').addClass('selected');

    //hide all sub menu
    $('.subMenu *').click(function () 
    {
        $('.subMenu').hide();
    });
}

/**
 * Open/close main menu
 * 
 * @returns {Boolean}
 */
function menuToggle()
{
    $('body').toggleClass('menu-open');

    return false;
}

/**
 * Close the main menu
 * 
 * @returns {Boolean}
 */
function menuOpen()
{
    menuSearch(''); 
    $('body').addClass('menu-open');
    setTimeout(function(){$('#main-menu-search').focus();}, 200);
    
    return false;
}

/**
 * Close the main menu
 * 
 * @returns {Boolean}
 */
function menuClose()
{
    $('body').removeClass('menu-open');

    return false;
}

function openSubMenu(element)
{
    menuCloseAll();
    element = $(element);
    
    //submenu atual
    var submenu = element.parent().children('div');

    if (submenu.css('display') == 'block')
    {
        submenu.stop().slideUp('fast');
    } 
    else
    {
        submenu.stop().slideDown('fast');
    }

    return false;
}

function menuCloseAll()
{
    $('.subMenu').stop().slideUp('fast');
}

function menuSearch(term)
{
    term = toAscii(term.toLocaleLowerCase())+"";
    
    if (term == '')
    {
        $('.main-menu a').show();
        $('.subMenu').hide();
        $('.menu-submenu-header').show();
        return;
    }
    
    //open all menus
    $('.subMenu').show();
    //hide all header
    $('.menu-submenu-header').hide();
    
    $('.main-menu a').each( function()
    {
        var element = $(this);
        var text = toAscii( element.text().toLocaleLowerCase())+"";
        
        var find = text.indexOf(term) >= 0;
        
        if ( find)
        {
            element.show();
        }
        else
        {
            element.hide();
        }
        
    });
}

function cropCanvas(imgSrc, aspectRatio)
{
    $('#crop-image-handler').attr('src', imgSrc);
    $('#imageHandlerHref').val(imgSrc);

    jQuery(function ($) 
    {
        // Create variables (in this scope) to hold the API and image size
        var jcrop_api,
                boundx,
                boundy;

        $('#crop-image-handler').Jcrop({
            onChange: updatePreview,
            onSelect: updatePreview,
            aspectRatio: aspectRatio,
            boxWidth: 400,
            //boxHeight: 300,
            setSelect: [0, 0, 1920, 300]

        }, function () {
            // Use the API to get the real image size
            var bounds = this.getBounds();
            boundx = bounds[0];
            boundy = bounds[1];
            // Store the API in the jcrop_api variable
            jcrop_api = this;
            $('#crop-image-handler').data('jcrop_api', jcrop_api);
        });
        function updatePreview(c)
        {
            if (parseInt(c.w) > 0)
            {
                $('#x').val(c.x);
                $('#y').val(c.y);
                $('#w').val(c.w);
                $('#h').val(c.h);
            }
        }
        ;
    });

    $('#crop-canvas').addClass('is-visible');
}

function destroyCropCanvas() 
{
    $('#crop-canvas').removeClass('is-visible');
    var jcropApi = $('#crop-image-handler').data('jcrop_api');
    jcropApi.destroy();

    return false;
}

function toolTip(selector, message)
{
    var element = $(selector);
    //remove title
    element.attr('title', '').removeAttr('title');
    //var parent = element.parent();
    var tagName = element.prop('tagName');
    
    if (tagName== 'input'|| tagName == 'select')
    {
        var toolTipHolder = $(document.createElement('div'));
        toolTipHolder.addClass('tooltip');
        toolTipHolder.append('<span class="tooltiptext">' + message + '</span>');
        element.after(toolTipHolder);
        toolTipHolder.prepend(element);
    }
    else
    {
        element.addClass('tooltip');
        element.append('<span class="tooltiptext">' + message + '</span>');
    }
}

/**
 * Make the default tooltip for all elements that has title
 * 
 * @returns void
 */
function defaultTooltTipForAllTitle()
{
    $('[title]').each(function()
    {
        toolTip(this, $(this).attr('title'));
    });
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

function toAscii(text)
{
   return text
        .replace(/[ÀÁÂÃÄÅª]/g,"A")
        .replace(/[àáâãäå]/g,"a")
        .replace(/[ÈÉÊË&]/g,"E")
        .replace(/[éèêë]/, "e")
        .replace(/[ÍÌÎÏ]/, "I")
        .replace(/[íìîï]/, "i")
        .replace(/[ÓÒÔÕÖ]/, "O")
        .replace(/[óòôõöº]/, "o")
        .replace(/[ÚÙÛÜ]/, "U")
        .replace(/[úùûü]/, "u")
        .replace(/[Ñ]/, "N")
        .replace(/[ñ]/, "n")
        .replace(/[Ç]/, "C")
        .replace(/[ç]/, "c")
        //.... all the rest
        .replace(/[^a-z0-9 ]/gi,''); // final clean up
}

function toNumber(number)
{
    if (typeof number == "undefined" || number == '')
    {
        number = '0';
    }

    if (typeof number === 'string')
    {
        number = number + "";

        if (number.indexOf(",") > 0)
        {
            number = number.replace(".", "");
            number = number.replace(",", ".");
        }

        number = onlyNumbersAndPoint(number);
    }

    return Number(number);
}


function onlyNumbersAndPoint(num)
{
    if (typeof num == "number")
    {
        return num;
    }

    return num.replace(/[^0-9.\-]/gi, '');
}

function onlyNumbers(num)
{
    return num.replace(/[^0-9]/gi, '');
}

//adiciona método contains no array
function arrayContains(array, obj)
{
    var i = array.length;

    while (i--)
    {
        if (array[i] == obj)
        {
            return true;
        }
    }

    return false;
}

function preparaVer()
{
    //remove botão de adicionar
    $('#btnInsert').hide().data('hide-by-see');
    //remove filtros
    $('#savedListGroup').hide();

    //adiciona botão de voltar, caso necessáiro
    if ($('#btnVoltar').length == 0)
    {
        $('#btnGroup').append('<button id=\"btnVoltar\" class=\"btn\" onclick=\"history.back(1);\" type=\"button\" title=\"Volta para a listagem!\" data-form-changed-advice=\"1\"><i class=\"fa fa-arrow-left\"></i><span class=\"btn-label\"> Voltar</span></button>');
    }

    //esconde botões de adicionar de mestre-detalhe
    $('[id^="btnAdd"]').hide().data('hide-by-see');
    //esconde botão de salvar
    $('#btnSalvar').hide().data('hide-by-see');

    $('.fa-trash-o,.fa-trash,.fa-edit').each(
            function ()
            {
                var parent = $(this).parent();
                
                if (parent.prop('tagName') == 'A')
                {
                    parent.data('hide-by-see');
                    parent.hide();
                }
            }
    );
    
    //remove clique duplo
    $('[ondblclick]').removeAttr('ondblclick');

    $('input, select, textarea').not('[data-see-not-disable=1]').attr('disabled', 'disabled');

    //add support for autocomplete/combo input
    //TODO avoid setimeout
    setTimeout(function () {
        $('.labelValue').attr('disabled', 'disabled');
        $('input, select, textarea').not('[data-see-not-disable=1]').attr('disabled', 'disabled');
    }, 200);
}

function setCookie(variable, value)
{
    var d = new Date();
    d.setTime(d.getTime() + (1 * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = variable + "=" + value + ";" + expires + ";path=/";
}

function getCookie(variable)
{
    var name = variable + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');

    for (var i = 0; i < ca.length; i++)
    {
        var c = ca[i];

        while (c.charAt(0) == ' ')
        {
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0)
        {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function filterRemove(element)
{
    var element = $(element);
    var parent = element.parent().parent();
    parent.find('input, select').attr('disabled','disabled'); 
    parent.hide('fast', function(){ parent.remove() } );
}

function filterAdd(element)
{
    var element = $(element);
    var parent = element.parent();
    
    var filterBase = parent.find('.filterBase');
    var filterConditionValue = filterBase.find('.filterCondition').val();
    var clone = filterBase.clone().removeClass('filterBase');

    //add remove button
    clone.append('<i class="fa fa-trash trashFilter" onclick="filterTrash(this)"></i>');
    //clear cloned value
    clone.find('.filterInput').val('').removeAttr('data-on-press-enter-converted');
    //restore condition value (clone is not filling it)
    clone.find('.filterCondition').val(filterConditionValue);
    
    //show with animation
    clone.hide()
    parent.append(clone);
    clone.slideDown('fast');
    
    //process ajax fields
    dataAjax();
    
    return false;
}

function filterTrash(element)
{
    var element = $(element);
    var parent = element.parent();
    parent.slideUp('fast',function(){$(this).remove()});
}

function filterChangeText(element)
{
    var val = $(element).val();
    
    var input = $(element).parent().find('.filterInput');
    
    if ( val == 'nullorempty' || val == 'notnullorempty' || val == 'today' )
    { 
        input.val('').hide();
        element.addClass('fullWidth');
    } 
    else 
    { 
        input.show();
        element.removeClass('fullWidth');
    } 
}

function filterChangeInteger(element)
{
    var val = $(element).val();
    var input = $(element).parent().find('.filterInput');
    var inputFinal = $(element).parent().find('.final');
    
    if ( val == 'between') 
    {  
        element.removeClass('fullWidth');
        input.show().addClass('filterInterval');
        inputFinal.removeAttr('disabled').add('filterInterval').show();
    } 
    else if (val == 'nullorempty'|| val == 'notnullorempty')
    {
        input.hide();
        element.addClass('fullWidth');
    }
    else 
    { 
        element.removeClass('fullWidth');
        input.show().removeClass('filterInterval');
        inputFinal.hide().attr('disabled','disabled');
    }
}

function filterChangeDate(element)
{
    var val = $(element).val();
    var input = $(element).parent().find('.filterInput');
    var prefix = $(element).attr('id').replace('Condition', '');
    var elValue = $(element).parent().find('.filterInput');
    var elValueFinal = $(element).parent().find('.final');
    
    if ( val== 'nullorempty' 
            || val == 'notnullorempty'
            || val == 'today' 
            || val == 'yesterday' 
            || val == 'tomorrow' 
            || val == 'currentmonth' 
            || val =='pastmonth' 
            || val == 'nextmonth' 
            || val.indexOf('month-')==0)
    { 
        input.val('').hide();
        element.addClass('fullWidth');
        elValue.value = '';
        elValueFinal.value = '';
    } 
    else if ( val == 'between' ) 
    { 
        input.show();
        element.removeClass('fullWidth');
        elValue.show().addClass('filterInterval');
        elValueFinal.removeAttr('disabled').addClass('filterInterval').show();
    }
    else 
    { 
        input.show();
        element.removeClass('fullWidth');
        elValue.show().removeClass('filterInterval');
        elValueFinal.hide().attr('disabled','disabled').removeClass('filterInterval');
        elValue.value = '';
        elValueFinal.value = '';
    }
}

function filterChangeBoolean(element)
{
    var val = $(element).val();
    var input = $(element).parent().find('.filterInput');

    input.val('').hide();
    element.addClass('fullWidth');
}

//used in grid checkcolumn, need refactor
function selecteChecks(gridName)
{
    $('#'+gridName+'Table .checkBoxcheck').each( function()
    {
        if ( $(this).prop('checked') === true )
        {
            $(this).parent().parent().addClass('select');
        }
        else
        {
            $(this).parent().parent().removeClass('select');
        }
    });
}

//used in grid checkcolumn, need refactor
function selecteCheck(elementId)
{
    var element = $('#' + elementId);
    var checked = !element.prop('checked');
    element.prop('checked', checked);
    
    $('#checkAllcheck').prop('checked', false);
}

function isAndroid()
{
    return navigator.userAgent.toLowerCase().indexOf("android") > -1;
}

function isIos() 
{
    var ua = navigator.userAgent.toLowerCase();
    return ua.indexOf("iphone") > -1 || ua.indexOf("ipad") > -1;
}

function isCellPhone()
{
    return $(document).width() <= 800;
}

/*Create a default dropzone*/
function createDropZone( uploadUrl, acceptedFiles, pageName)
{
    acceptedFiles = acceptedFiles ? acceptedFiles : 'image/*';
    
    var myDropzone = new Dropzone("#myAwesomeDropzone",
    {
        url: uploadUrl,
        acceptedFiles: acceptedFiles,
        addRemoveLinks: true,
        dictRemoveFile : '',
        dictDefaultMessage : 'Arraste arquivos ou clique para upload',
        init: function()
        {
            this.on("queuecomplete", function (file) 
            {
                p( pageName + '/updateImages');
            });
        }
    });
}

function useImageCkEditor(a)
{
    newsrc = a;
    a = window.location.search.match(/(?:[?&]|&)CKEditorFuncNum=([^&]+)/i);
    window.opener.CKEDITOR.tools.callFunction(a && 1 < a.length ? a[1] : null, newsrc);
    window.close();
}

function createCkEditor(id)
{
    if (typeof CKEDITOR == 'undefined')
    {
        setTimeout(function(){createCkEditor(id)},300);
        return;
    }
    
    //ckeditor allready exists, avoid error
    if ( typeof CKEDITOR.instances[id] === 'object')
    {
        //return;
    }

    var editor = CKEDITOR.replace( id );
    
    //active the save button when editor changes
    editor.on('change', function() 
    {
        $('#btnSalvar').removeAttr('disabled');
    });

    editor.addCommand('blendSave', 
    {
        exec : function(editor, data) 
        {
            $('#btnSalvar').click();
        }
    });

    editor.keystrokeHandler.keystrokes[CKEDITOR.CTRL + 83 /*S*/] = 'blendSave';
}

function showValidateErrors(errors)
{
    var html = '';
    
    errors.forEach( function(item, index)
    {
        var str = '<strong>'+item.label + '</strong> |' + item.messages.join(',');
        var tabLabel = getTabLabel(getTabFromId(item.name).attr('id'));
        
        if (tabLabel)
        {
            str = '<strong>'+tabLabel+'</strong> : ' + str;
        }
        
        str += '<br/>';
        
        html+= str;
    });
   
    toast('Verifique o preenchimento dos campos: <br/><br/>'+html+'<br/>','danger');
}

function sortList(ul) 
{
    var list = $(ul);
    var itens = list.find('li').get();
   
    itens.sort(function(a, b) 
    {
        return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
    });
    
    $.each(itens, function(idx, itm) 
    { 
        list.append(itm); 
    });
}

var grid = {};

grid.changeTextSize = function(element)
{
    var fontSize = localStorage.getItem('grid-font-size');
    fontSize = fontSize ? parseInt(fontSize): 0;
    fontSize = fontSize> 30 ? 0 : fontSize += 10;

    $('.table-grid').css("font-size", (fontSize + 100)+'%');
    
    localStorage.setItem('grid-font-size',fontSize);
};

grid.restoreTextSize = function(element)
{
    var fontSize = localStorage.getItem('grid-font-size');
    fontSize = fontSize ? parseInt(fontSize): 0;

    $('.table-grid').css("font-size", (fontSize + 100)+'%');
}

grid.openTrDetail = function(element)
{
    event.preventDefault();
    
    var tr= $(element);
    var grid = tr.parents('.grid');
    var gridId = grid.attr('id').replace(/\\/g,'-');
    var id = tr.data('model-id');
    var link = grid.data('link');
    var detailId = ('grid-detail-'+gridId+'-'+id).toLowerCase();
    var detailElement = $('#'+detailId);
 
    if (detailElement.length > 0)
    {
        detailElement.remove();
    }
    else
    {
        var newTr = $.create('tr');
        newTr.addClass('grid-tr-detail-column-group');
        var newTd = $.create('td',detailId);
        newTd.attr('colspan', grid.find('th').length);
        newTr.append(newTd);
        newTr.insertAfter(tr);
        
        p(link+'/openTrDetail/'+id+'?elementId='+detailId);
    }
    
    return false;
}

function setTableFontSize()
{
    var value = localStorage.getItem('tablegridfontsize');
    if (!value)
    {
        value = 10;
    }

    $('.table-grid').css("font-size", value / 10 + "em");
    $('#tableGridFontSize').val(value);
}

function changeTableFontSize()
{
    var value = $('#tableGridFontSize').val();
    value = value ? value : 10;

    $('.table-grid').css("font-size", value / 10 + "em");
    localStorage.setItem('tablegridfontsize', value);
}

var actionList = {};

actionList.toggle = function()
{
    if ( $('body').hasClass('action-list-open'))
    {
        actionList.close();
    }
    else
    {
        actionList.open();
    }
};

actionList.restore = function()
{
    if ( $('.action-list-toogle').is(':visible') && !isCellPhone() )
    {
        var wasOpen = localStorage.getItem('action-list-open') == 1;
    
        if ( wasOpen )
        {
            actionList.open();
        }
        else
        {
            actionList.close();
        }
    }
    else
    {
        $('body').removeClass('action-list-open');
    }
};

actionList.open = function()
{
    $('body').addClass('action-list-open');
    localStorage.setItem('action-list-open', 1);
};

actionList.close = function()
{
    $('body').removeClass('action-list-open');
    localStorage.setItem('action-list-open', 0);
    
    return false;
};

var printScreenType = 'pdf';
var printScreenCount = 0;
var printScreenBackup = '';

function printScreen(type)
{
    printScreenBackup = $('#divLegal').html();
    printScreenType = type ? type : 'pdf';
    var grids = $('.grid');
    printScreenCount = grids.length;
    
    grids.each( function()
    {
       var dataLink = $(this).attr('data-link');
       
       var split = window.location.href.split('/');
       var id = split[split.length-1];
       
       //update grid with full data
       p(dataLink+'/listar/',{paginationLimit:'9999',v:id}, printScreenFinalize );
    });
  
    return false;
}

function printScreenFinalize()
{
    printScreenCount--;
    
    if (printScreenCount == 0)
    {
        var params = {
            title: stripTags($('#formTitle').html()), 
            content: $('#divLegal').html(),
            type: printScreenType
        };

        setTimeout( function(){ 
            p(getCurrentPage()+'/printScreen',params); 
        });
        
        $('#divLegal').html(printScreenBackup);
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
