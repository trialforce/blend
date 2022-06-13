/* global blend, Notification */

/**
 * Simple blend js plugin
 * 
 */

blend.notification = {};
blend.plugins.push(blend.notification);
blend.notification.defaultIcon = '';

blend.notification.register = function ()
{
    blend.notification.requestPermission();
};
 
blend.notification.requestPermission = async function()
{
    if (!("Notification" in window)) 
    {
        console.error('Esse browser não suporta notificações desktop');
        return false;
    } 
    else 
    {
        if (Notification.permission !== 'denied') 
        {
            return await Notification.requestPermission();
        }
    }
}

blend.notification.new = async function (titulo, conteudo, link, icon)
{
    var okay = true;
    
    if (!Notification.permission === 'granted') 
    {
        okay = await blend.notification.requestPermission();
    }
    
    if (typeof stripTags== 'function')
    {
        conteudo = stripTags(conteudo);
    }
    
    if ( typeof icon == 'undefined' || !icon || icon == '')
    {
        icon = blend.notification.defaultIcon;
    }
    
    const notification = new Notification(titulo, 
    {
      body: conteudo,
      icon: icon
    });
    
    if ( typeof link == 'undefined' || !link || link == '')
    {
        link = document.querySelector('base').getAttribute('href');
    }
    
    notification.onclick = (e) => {
      e.preventDefault();
      window.open(link);
      notification.close();
    }
};