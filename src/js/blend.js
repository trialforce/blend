"use strict";
//handle the back and forward buttons
var formChangedAdvice = false;
var invalidHover = true;
var lastUrl = correctUrl(window.location.href);
var avoidUrlRegister = false;
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
        var href = window.location.href;
        avoidUrlRegister = true;
        p(href, true);
    });
}

/*window.popstate = function (event)
{
    var href = window.location.href;
    avoidUrlRegister = true;
    console.log(href);
    p(href, true);
};*/

//destroy popup on esc
document.keyup = function (e)
{
    if (e.which === 27)
    {
        popup('destroy');
    }
};

//Loading without ajax
window.onload =  function ()
{
    dataAjax();
        
    /**
     * Add support to play method in jquery
     *
     * @returns {jQuery.fn@call;each}
     */
    jQuery.fn.play = function () {
        return this.each(function () {
            
            if (typeof this.play === 'function')
            {
                this.play();
            }
        });
    };
};


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
    blendJs();
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
            } else if (dataAjax === 'noFormData')
            {
                element.click(function () {
                    return g(href, '');
                });
            } else
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

        //mask as converted
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
    $('[data-invalid=1]').change(function () {
        $(this).parent().find('.hint.danger').fadeOut(500, function () {
            $(this).remove();
        });
    });

    //make invalid
    $('[data-invalid=1]').each(function () {
        var element = $(this);
        var title = element.attr('title');

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
        } else
        {
            var position = element.position().left + element.width()
            var myDiv = $('<div class="hint danger">' + element.attr('title') + '</div>');
            myDiv.css('left', position);

            element.parent().append(myDiv);
        }
    });

    //make masks work
    $("input[data-mask]").each(function () {
        $(this).mask($(this).attr("data-mask"));
    });

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

    if (isAndroid() || isIos())
    {
        $('.dateinput').not('[readonly]').each(function () {
            $(this).mask('99/99/9999');
        });

        $('.datetimeinput').not('[readonly]').each(function () {
            $(this).mask('99/99/9999 99:99:99');
        });

        $('.timeinput').not('[readonly]').each(function () {
            $(this).mask('99:99:99');
        });
    } 
    else if (typeof $().datetimepicker === 'function')
    {
        $('.dateinput').not('[readonly]').datetimepicker({
            timepicker: false,
            defaultSelect: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            mask: true,
            allowBlank: true,
            format: 'd/m/Y',
            step: 15
        });

        $('.datetimeinput').not('[readonly]').datetimepicker({
            format: 'd/m/Y H:i:s',
            mask: true,
            defaultSelect: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            allowBlank: true,
            step: 15
        });

        $('.timeinput').not('[readonly]').datetimepicker({
            format: 'H:i:s',
            defaultSelect: false,
            datepicker: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            allowBlank: true,
            mask: true,
            step: 15
        });
    } 
    else
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

        $('.datetimeinput').each(function () {
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

    //mark form changed on change
    $('input, select, textarea').on('change', function () {
        markFormChanged();
    });

    //on key press
    $('input, textarea').on('keypress', function () {
        markFormChanged();
    });

    //add support for nick editor, and other contenteditable
    $('*[contenteditable]').on('keypress', function () {
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
    
    hideLoading();

    return false;
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
        avoidUrlRegister = false;
        return false;
    }

    if (avoidUrlRegister)
    {
        avoidUrlRegister = false;
        return false;
    }

    var urlToRegister = correctUrl(page);

    if (urlToRegister != lastUrl)
    {
        window.history.pushState({url: urlToRegister}, "", urlToRegister);
        lastUrl = urlToRegister;
        avoidUrlRegister = false;
        return true;
    }

    avoidUrlRegister = false;
    return false;
}

function correctUrl(url)
{
    var bases = document.getElementsByTagName('base');
    var base = '';
    
    if ( bases )
    {
        base = bases[0].href;
    }

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
                page = url + '/?' + formData;
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

function getJson(page, formData, showLoading, callBack)
{
    var host = $('base').attr('href');
    var url = host + page.replace(host, '');

    if (showLoading)
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
            if (typeof response.script == 'string')
            {
                response.script.replace('\\\"', '\\"');
                $('body').append('<script>' + response.script + '</script>');
            } else
            {
                callBack(response);
            }
        }
        , error: function (xhr, ajaxOptions, thrownError)
        {
            hideLoading();

            if (xhr.responseText === '')
            {
                toast('Sem resposta do servidor! Verifique sua conexão!', 'alert');
            } else
            {
                toast('Impossível ler JSON!');
            }
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
function toast(msg, type, duration) {
    duration = duration === undefined ? 3000 : duration;
    $("<div class='toast " + type + "'>" + msg + "<strong style=\"float:right;cursor:pointer;\" onclick=\"$(this).parent().remove();\">X</strong></div>")
            .appendTo('body')
            .animate({top: 50, opacity: 1}, 500)
            .delay(1500)
            .fadeOut(duration, function () {
                $(this).remove();
            });

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

        element.fadeIn(600);
    } 
    else if (action === 'close')
    {
        $('.makePopupFade').removeClass('popupFaded');

        element.find('.inner').animate({
            opacity: 0,
            width: 0,
            minWidth: 0,
            height: 0,
        }, 500, function () {
            element.hide();
            //restore style
            $('.inner').removeAttr('style');
        });
    }
    else if (action === 'destroy')
    {
        $('.makePopupFade').removeClass('popupFaded');

        //coll animantion
        element.find('.inner').animate({
            opacity: 0,
            width: 0,
            minWidth: 0,
            height: 0,
        }, 300, function () {
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
        }, 500, function () {
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

function comboShowDropdown(id)
{
    var element = $('#dropDownContainer_' + id);

    if (element.is('[readonly]'))
    {
        comboHideDropdown(id);
        return false;
    }

    //mininum width
    element.css('min-width', $('#labelField_' + id).width() + 'px');
    //show
    element.fadeIn('fast');
}

function comboHideDropdown(id)
{
    $('#dropDownContainer_' + id).fadeOut('fast');
}

function comboDoSearch(id)
{
    eval($('#labelField_' + id).data('change'));
}

function comboSelectItem(id, value, label, eThis)
{
    $(eThis).parent().find('tr').removeClass('selected');
    $(eThis).addClass('selected');
    var element = $('#' + id);
    element.val(value);
    element.trigger('change');

    var elementLabel = $('#labelField_' + id);
    elementLabel.val(label);
}

var timerTypeWatch = 0;

/*Inspect type in some input*/

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

    //up
    if (event.keyCode === 40)
    {
        comboShowDropdown(id);

        if (parente.find('table tr.selected').length === 0)
        {
            parente.find('table tr').eq(0).click();
        } else
        {
            parente.find('table tr.selected').next().click();
        }

        return false;
    }
    //down
    else if (event.keyCode === 38)
    {
        comboShowDropdown(id);

        parente.find('table tr.selected').prev().click();

        return false;
    }
    //enter
    else if (event.keyCode === 13)
    {
        comboHideDropdown(id);

        return false;
    } else
    {
        clearTimeout(timerTypeWatch);
        timerTypeWatch = setTimeout(callback, ms);
    }
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
    } else if (str.length > 8)
    {
        return '999.999.999-999999';
    } else
    {
        return '999999999999999999';
    }
};

var maskDateTime = function (input, e, currentField, options)
{
    if (input.length > 9)
    {
        return '99/99/9999 99:99:99';
    } else
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
    $('.subMenu *').click(function () {
        $('.subMenu').hide();
    });
}

function selectTab(tabItemId)
{
    tabItemId = tabItemId.replace('#', '');
    var tab = $('#' + tabItemId).parents('.tab').eq(0);

    //atualiza url
    updateUrl(window.location.href.replace(window.location.hash, '') + '#' + tabItemId);

    //body
    tab.find('.tabBody>.item').hide();
    tab.find('.tabBody #' + tabItemId).show();

    //head
    tab.find('.tabHead>.item').removeClass('selected');
    tab.find('.tabHead #' + tabItemId + 'Label').addClass('selected');

    return false;
}

function openSubMenu(element)
{
    element = $(element);
    //esconde todos menus
    //submenu atual
    var submenu = element.parent().children('div');

    if (submenu.css('display') == 'block')
    {
        submenu.stop().slideUp('fast');
    } else
    {
        submenu.stop().slideDown('fast');
    }

    return false;
}

function cropCanvas(imgSrc, aspectRatio)
{
    $('#crop-image-handler').attr('src', imgSrc);
    $('#imageHandlerHref').val(imgSrc);

    jQuery(function ($) {
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
    element.attr('title', '');
    //var parent = element.parent();
    //element.append('body');
    var toolTipHolder = $(document.createElement('div'));
    toolTipHolder.addClass('tooltip');
    //toolTipHolder.append(element);
    toolTipHolder.append('<span class="tooltiptext">' + message + '</span>');
    element.after(toolTipHolder);
    toolTipHolder.prepend(element);
}

function addScriptOnce(src, callBack)
{
    var list = document.getElementsByTagName('script');
    var i = list.length, flag = false;
    var flag = false;

    while (i--)
    {
        if (list[i].src === src)
        {
            flag = true;
            break;
        }
    }

    // if we didn't already find it on the page, add it
    if (!flag)
    {
        var script = document.createElement('script');
        script.src = src;
        script.onload = callBack;
        document.getElementsByTagName('body')[0].appendChild(script);
    } else
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

    $('.fa-trash-o').each(
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

    //coloca todos campos como readonly e disabled
    $('input, select, textarea').each(
            function ()
            {
                $(this).attr('disabled', 'disabled');
                //$(this).attr('readonly', 'readonly');
            }
    );
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
    console.log(filterBase);
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
            this.on("queuecomplete", function (file) {
            p( pageName + '/updateImages');
          })
        }
    });
}

function createCkEditor(id)
{
    var editor = CKEDITOR.replace( id ,{extraPlugins: 'blend'});
    
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

    // Then, we set up the key combination
    editor.keystrokeHandler.keystrokes[CKEDITOR.CTRL + 83 /*S*/] = 'blendSave';
}
        