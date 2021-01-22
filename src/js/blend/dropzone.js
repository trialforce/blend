
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
            this.on("queuecomplete", function (file) 
            {
                p( pageName + '/updateImages');
            });
        }
    });
}