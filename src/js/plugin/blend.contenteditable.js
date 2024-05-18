/* global blend */
//https://developer.mozilla.org/en-US/docs/Web/API/Document/execCommand#commands
blend.contentEditable = {};
blend.plugins.push(blend.contentEditable);
blend.contentEditable.cssClass= 'content-editable';
blend.contentEditable.menu = {};
blend.contentEditable.menu.buttonCssClass= 'content-editable-button';
blend.contentEditable.menu.selectCssClass= 'content-editable-select';
blend.contentEditable.menu.colorCssClass= 'content-editable-color';
blend.contentEditable.menu.cssClass= 'content-editable-menu';
//blend.contentEditable.buttons = {};
blend.contentEditable.actions = {};

blend.contentEditable.availableFonts = [
        {"value":"","label":'Sem fonte'},
        {"value":"Arial","label":'Arial'},
        {"value":"Arial Black","label":'Arial Black'},
        {"value":"Courier New","label":'Courier New'},
        {"value":"Times New Roman","label":'Times New Roman'}
    ];
    
blend.contentEditable.availableBlocks = [
        {"value":"","label":'Sem formatação'},
        {"value":"h1","label":'Título principal'},
        {"value":"h2","label":'Título 2'},
        {"value":"h3","label":'Título 3'},
        {"value":"h4","label":'Título 4'},
        {"value":"h5","label":'Título 5'},
        {"value":"h6","label":'Título 6'},
        {"value":"pre","label":'Pre'},
        {"value":"p","label":'Parágrafo'},
        {"value":"blockquote","label":'Citação'}
    ];

blend.contentEditable.getNodeList = function()
{
    return document.querySelectorAll('.'+blend.contentEditable.cssClass);
}

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

blend.contentEditable.start = function()
{
    let nodeList = blend.contentEditable.getNodeList();
    
    for (var i= 0 ; i<nodeList.length; i++)
    {
        let element = nodeList[i];
        let parent = element.parentElement;

        //menu already created
        if (parent.querySelectorAll('.'+blend.contentEditable.menu.cssClass).length > 0)
        {
            continue;
        }
        
        let createMenu = element.hasAttribute('data-create-menu');
        
        if (createMenu == true)
        {
            let menu = blend.contentEditable.createMenu(element);
            parent.appendChild(menu);
        }
    }
};


blend.contentEditable.createMenu = function( element )
{
    var menu = document.createElement('div');
    menu.classList.add(blend.contentEditable.menu.cssClass);
    //menu.innerHTML = 'menu';
    menu.appendChild( blend.contentEditable.createButton(element,'broom', 'clear','Limpar'));
    menu.appendChild( blend.contentEditable.createButton(element,'paint-brush', 'removeFormat','Remover formatação'));
    menu.appendChild( blend.contentEditable.createButton(element,'undo', 'undo','Desfazer'));
    menu.appendChild( blend.contentEditable.createButton(element,'redo', 'redo','Refazer'));
    menu.appendChild( blend.contentEditable.createButton(element,'bold', 'bold','Negrito'));
    menu.appendChild( blend.contentEditable.createButton(element,'italic', 'italic','Itálico'));
    menu.appendChild( blend.contentEditable.createButton(element,'underline', 'underline','Underline'));
    menu.appendChild( blend.contentEditable.createButton(element,'link', 'createLink','Criar link'));
    
    menu.appendChild( blend.contentEditable.createButton(element,'align-left', 'alignLeft','Alinhar esquerda'));
    menu.appendChild( blend.contentEditable.createButton(element,'align-center', 'alignCenter','Centralizado'));
    menu.appendChild( blend.contentEditable.createButton(element,'align-right', 'alignRight','Alinhr direita'));
    
    menu.appendChild( blend.contentEditable.createButton(element,'copy', 'copy','Copiar'));
    menu.appendChild( blend.contentEditable.createButton(element,'cut', 'cut','Recortar'));
    menu.appendChild( blend.contentEditable.createButton(element,'paste', 'paste','Colar'));
    
    menu.appendChild( blend.contentEditable.createButton(element,'list-ul', 'insertUnorderedList','Lista'));
    menu.appendChild( blend.contentEditable.createButton(element,'list-ol', 'insertOrderedList','Lista numérica'));
    
    menu.appendChild( blend.contentEditable.createButton(element,'long-arrow-alt-right', 'indent','Identar'));
    menu.appendChild( blend.contentEditable.createButton(element,'long-arrow-alt-left', 'outdent','Unidentar'));
    
    menu.appendChild( blend.contentEditable.createOptions(element,blend.contentEditable.availableBlocks, 'formatBlock','Elemento'));
    menu.appendChild( blend.contentEditable.createOptions(element,blend.contentEditable.availableFonts, 'fontName','Fonte'));
    
    var sizes = [];
    
    for (var i=1; i < 10; i++)
    {
        sizes.push( {"value":i,"label":i+'pt'} );
    }
        
    menu.appendChild( blend.contentEditable.createOptions(element,sizes, 'fontSize','Tamanho'));
    
    menu.appendChild( blend.contentEditable.createColorButton(element, 'foreColor','Tamanho'));
    menu.appendChild( blend.contentEditable.createColorButton(element, 'backColor','Tamanho'));
    
    return menu;
};

blend.contentEditable.createButton = function(element, icon, action, title)
{
    var button = document.createElement('div');
    button.title= title;
    
    button.classList.add(blend.contentEditable.menu.buttonCssClass);
    button.classList.add('fa');
    button.classList.add('fa-'+icon);
    button.setAttribute('onmousedown','event.preventDefault();');
    button.setAttribute('onclick','blend.contentEditable.actions.'+action+'(\'#'+element.id+'\')');
    
    return button;
};

blend.contentEditable.createOptions = function(element, options, action, title)
{
    //create "null element
    if ( options.length == 0)
    {
        var element = document.createElement('div');
        element.style.display= "none";
        return element;
    }
    
    var select = document.createElement('select');
    select.title= title;
    select.classList.add(blend.contentEditable.menu.selectCssClass);
    select.setAttribute('onchange','blend.contentEditable.actions.'+action+'(\'#'+element.id+'\',this)');
    
    for (var i=0; i<options.length; i++)
    {
        var option = options[i];
        var opt = document.createElement('option');
        opt.value = option.value;
        opt.innerHTML = option.label;
        select.appendChild(opt);
    }
    
    return select;
}

blend.contentEditable.createColorButton = function(element, action, title)
{
    var button = document.createElement('input');
    button.type= 'color';
    button.title= title;
    
    button.classList.add(blend.contentEditable.menu.colorCssClass);
    button.setAttribute('onchange','blend.contentEditable.actions.'+action+'(\'#'+element.id+'\',this)');
    
    return button;
};

blend.contentEditable.actions.clear = function(selector)
{
    var element = document.querySelector(selector);
    var editable = element.querySelector('[contenteditable]');
    //register to undo
    document.execCommand('insertText', false, '');
    editable.innerHTML = '';
};

blend.contentEditable.actions.undo = function(selector)
{
    document.execCommand('undo', false, null);
};

blend.contentEditable.actions.redo = function(selector)
{
    document.execCommand('redo', false, null);
};

blend.contentEditable.actions.removeFormat = function(selector)
{
    document.execCommand('removeFormat', false, null);
};

blend.contentEditable.actions.bold = function(selector)
{
    document.execCommand('bold', false, null);
};

blend.contentEditable.actions.italic = function(selector)
{
    document.execCommand('italic', false, null);
};

blend.contentEditable.actions.underline = function(selector)
{
    document.execCommand('underline', false, null);
};

blend.contentEditable.actions.alignLeft = function(selector)
{
    document.execCommand('justifyleft', false, null);
};

blend.contentEditable.actions.alignCenter = function(selector)
{
    document.execCommand('justifycenter', false, null);
};

blend.contentEditable.actions.alignRight = function(selector)
{
    document.execCommand('justifyright', false, null);
};

blend.contentEditable.actions.copy = function(selector)
{
    document.execCommand('copy', false, null);
};

blend.contentEditable.actions.cut = function(selector)
{
    document.execCommand('cut', false, null);
};

blend.contentEditable.actions.paste = function(selector)
{
    document.execCommand('paste', false, null);
};

blend.contentEditable.actions.formatBlock = function(selector, element)
{
    var tag = element[element.selectedIndex].value;
   
    if ( tag )
    {
        document.execCommand('formatBlock', false, tag);
    }
    else
    {
        const selection = window.getSelection()
        
        if (!selection.isCollapsed)
        {
            selection.anchorNode.parentNode.replaceWith(selection.anchorNode)
        } 
    }
};

blend.contentEditable.actions.fontName = function(selector, element)
{
    var font = element[element.selectedIndex].value;
   
    if ( font )
    {
        document.execCommand('fontname', false, font);
    }
    else
    {
        const selection = window.getSelection()
        
        if (!selection.isCollapsed)
        {
            selection.anchorNode.parentNode.replaceWith(selection.anchorNode)
        } 
    }
};

blend.contentEditable.actions.fontSize = function(selector, element)
{
    var size = element[element.selectedIndex].value;
   
    document.execCommand('fontsize', false, size);    
};

blend.contentEditable.actions.foreColor = function(selector, element)
{
    var color = element.value;
    document.execCommand('foreColor', false, color);
};

blend.contentEditable.actions.backColor = function(selector, element)
{
    var color = element.value;
    document.execCommand('backColor', false, color);
};

blend.contentEditable.actions.insertOrderedList = function(selector, element)
{
    console.log('order');
    document.execCommand('insertorderedlist', false, null);
};

blend.contentEditable.actions.insertUnorderedList = function(selector, element)
{
    console.log('unorder');
    document.execCommand('insertunorderedlist', false, null);
};

blend.contentEditable.actions.outdent = function(selector, element)
{
    document.execCommand('outdent', false, null);
};

blend.contentEditable.actions.indent = function(selector, element)
{
    document.execCommand('indent', false, null);
};

blend.contentEditable.actions.createLink = function(selector, element)
{
    var sLnk = prompt('Link', 'http:\/\/');
    
    if (sLnk && sLnk != '' && sLnk != 'http://')
    {
        document.execCommand('createlink', false, sLnk)
    }
}

blend.contentEditable.insertText = function(selector, text)
{
    //inserText
    document.execCommand('inserHtml', false, text)
}