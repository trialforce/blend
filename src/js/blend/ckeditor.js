/* global blend, CKEDITOR */

blend.ckeditor = {};
blend.plugins.push(blend.ckeditor);

blend.ckeditor.register = function ()
{
};

blend.ckeditor.start = function ()
{
    dateTimeInput();
};

blend.ckeditor.beforeSubmit = function ()
{
    updateEditors();
};


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
    if (typeof CKEDITOR == 'object')
    {
        for (var instance in CKEDITOR.instances)
        {
            CKEDITOR.instances[instance].updateElement();
            CKEDITOR.disableAutoInline = true;
        }
    }
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

    if (typeof CKEDITOR == 'undefined' || $('#' + id).length == 0)
    {
        setTimeout(function ()
        {
            createCkEditor(id)
        }, 300);
        return;
    }
    
    CKEDITOR.disableAutoInline = true;

    //ckeditor allready exists, avoid error
    if (typeof CKEDITOR.instances[id] === 'object')
    {
        //return;
    }

    var editor = CKEDITOR.replace(id);

    //active the save button when editor changes
    editor.on('change', function ()
    {
        $('#btnSalvar').removeAttr('disabled');
    });

    editor.addCommand('blendSave',
    {
        exec: function (editor, data)
        {
            $('#btnSalvar').click();
        }
    });

    editor.keystrokeHandler.keystrokes[CKEDITOR.CTRL + 83 /*S*/] = 'blendSave';
}