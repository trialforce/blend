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
