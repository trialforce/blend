/* global blend */
blend.mask = {};
blend.plugins.push(blend.mask);

blend.mask.register = function ()
{
};

blend.mask.start = function ()
{
    applyAllMasks(); 
};

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
    //tira os caracters estranhos para funcionar a contagem
    var str = input.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

    if (str.length > 11)
    {
        // alfanumérico
        return 'AA.AAA.AAA/AAAA-AA';
    }
    else if (str.length > 8)
    {
        return 'AAA.AAA.AAA-AAAAAA';
    }
    
    return 'AAAAAAAAAAAAAAAAAA';
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
    var onlyDigits = e.replace(/\D/g, '');
    
    //0800
    if ( onlyDigits[0] == 0)
    {
        mask = '9999-999-9999';
    }
    //internacional number
    else if ( onlyDigits.length > 11)
    {
        mask = "+9999999999999999999";
    }
    else if ( onlyDigits.length > 10)
    {
        mask = "(99)99999-99999";
    }
    else
    {
        mask = "(99)9999-99999";
    }
    
    return mask;
};


function applyAllMasks()
{
    $("input[data-mask]").each(function () {
        $(this).mask($(this).attr("data-mask"));
    });

    //mask functions
    $("input[data-mask-function]").each(function ()
    {
        var maskVar = window[$(this).attr("data-mask-function")];

        $(this).mask(maskVar, {onKeyPress: function (input, e, currentField, options) 
        {
            $(currentField).mask(maskVar(input), options);
        }});
    });
}