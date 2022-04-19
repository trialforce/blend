/* global blend */
blend.contentEditable = {};
blend.plugins.push(blend.contentEditable);
blend.contentEditable.cssClass= 'content-editable';
blend.contentEditable.menu = {};
blend.contentEditable.menu.buttonCssClass= 'content-editable-button';
blend.contentEditable.menu.cssClass= 'content-editable-menu';
blend.contentEditable.buttons = {};
blend.contentEditable.actions = {};

blend.contentEditable.getNodeList = function()
{
    return document.querySelectorAll('.'+blend.contentEditable.cssClass);
}

blend.contentEditable.start = function()
{
    var nodeList = blend.contentEditable.getNodeList();
    
    for (var i= 0 ; i<nodeList.length; i++)
    {
        var element = nodeList[i];
        var parent = element.parentElement;

        //menu already created
        if (parent.querySelectorAll('.'+blend.contentEditable.menu.cssClass).length > 0)
        {
            continue;
        }
        
        var menu = blend.contentEditable.createMenu(element);
        parent.appendChild(menu);
        
        console.log(parent);
    }
};

blend.contentEditable.createMenu = function( element )
{
    var menu = document.createElement('div');
    menu.classList.add(blend.contentEditable.menu.cssClass);
    //menu.innerHTML = 'menu';
    menu.appendChild( blend.contentEditable.createButton(element,'broom', 'clear','Limpar'));
    menu.appendChild( blend.contentEditable.createButton(element,'undo', 'undo','Desfazer'));
    menu.appendChild( blend.contentEditable.createButton(element,'redo', 'redo','Refazer'));
    menu.appendChild( blend.contentEditable.createButton(element,'remove-formaT', 'removeformat','Remover formatação'));
    menu.appendChild( blend.contentEditable.createButton(element,'bold', 'bold','Negrito'));
    menu.appendChild( blend.contentEditable.createButton(element,'italic', 'italic','Itálico'));
    menu.appendChild( blend.contentEditable.createButton(element,'underline', 'underline','Underline'));
    
    return menu;
};

blend.contentEditable.createButton = function(element, icon, action, title)
{
    var button = document.createElement('div');
    button.classList.add(blend.contentEditable.menu.buttonCssClass);
    button.setAttribute('onclick','blend.contentEditable.actions.'+action+'(\'#'+element.id+'\')');
    button.innerHTML = '<i class="fa fa-'+icon+'"></i>';
    button.title= title;
    return button;
};

blend.contentEditable.beforeSubmit = function()
{
    var nodeList = blend.contentEditable.getNodeList();
    
    for (var i= 0 ; i<nodeList.length; i++)
    {
        var element = nodeList[i];
        var input = element.querySelector('input');
        var editable = element.querySelector('[contenteditable]');
        input.value = editable.innerHTML;
    }
};

blend.contentEditable.actions.clear = function(selector)
{
    var element = document.querySelector(selector);
    var editable = element.querySelector('[contenteditable]');
    editable.innerHTML = '';
    element.focus();
}

blend.contentEditable.actions.undo = function(selector)
{
    console.log(selector);
    var editable = document.querySelector(selector+ ' [contenteditable]');
    document.execCommand('undo', false, null);
    editable.focus();
}

blend.contentEditable.actions.redo = function(selector)
{
    var editable = document.querySelector(selector+ ' [contenteditable]');
    document.execCommand('redo', false, null);
    editable.focus();
}

blend.contentEditable.actions.removeFormat = function(selector)
{
    var editable = document.querySelector(selector+ ' [contenteditable]');
    document.execCommand('removeFormat', false, null);
    editable.focus();
}

blend.contentEditable.actions.bold = function(selector)
{
    console.log('bold');
    var editable = document.querySelector(selector+ ' [contenteditable]');
    document.execCommand('bold', false, null);
    editable.focus();
}

blend.contentEditable.actions.italic = function(selector)
{
    var element = document.querySelector(selector);
    document.execCommand('italic', false, null);
    element.focus();
}

blend.contentEditable.actions.underline = function(selector)
{
    var element = document.querySelector(selector);
    document.execCommand('underline', false, null);
    element.focus();
}

//copy
//cut
//paste
//justifycenter;
//justifyright
//insertorderedlist
//insertunorderedlist
//outdent
//indent
//formatblock blockquote, h1 .. p, pre
//createlink $link
//fontname $fontname
//fontsize $size
//forecolor $color
//backcolor $color

//blend.contentEditable.buttons.push();