/* global blend */

blend.contentEditable = {};
blend.plugins.push(blend.contentEditable);

blend.contentEditable.beforeSubmit = function()
{
    var nodeList = document.querySelectorAll('.content-editable');
    
    //if don't have any content-editable 
    if (nodeList.length === 0)
    {
        return false;
    }
    
    for (var i= 0 ; i<nodeList.length; i++)
    {
        var element = nodeList[i];
        var input = element.querySelector('input');
        var editable = element.querySelector('[contenteditable]');
        input.value = editable.innerHTML;
    }
};