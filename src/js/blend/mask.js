
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