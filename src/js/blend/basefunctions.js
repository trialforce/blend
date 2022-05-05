/* global blend */

blend.baseFunctions = {};
blend.plugins.push(blend.baseFunctions);

blend.baseFunctions.register = function ()
{
};

blend.baseFunctions.start = function ()
{
    //add system class
    if (typeof isIos == "function" && isIos())
    {
        $('body').removeClass('os-ios').addClass('os-ios');
    }
    else if (typeof isAndroid == "function" && isAndroid())
    {
        $('body').removeClass('os-android').addClass('os-android');
    }
};

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

        //pt-br
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
