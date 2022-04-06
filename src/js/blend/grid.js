/* global blend */

var grid = {};
blend.grid = {};
blend.plugins.push(blend.grid);

blend.grid.register = function ()
{
};

blend.grid.start = function ()
{
    grid.restoreTextSize();
};

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
    $('#checkAllcheck').prop('checked', false);
}