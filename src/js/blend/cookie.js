function setCookie(variable, value)
{
    let d = new Date();
    d.setTime(d.getTime() + (1 * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    //add support for localhost on http
    let secure = window.location.protocol =='https://' ? 'secure' : '';
    document.cookie = variable + "=" + value + ";" + expires + ";path=/;SameSite=Strict;"+secure;
}

function getCookie(variable)
{
    let name = variable + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');

    for (let i = 0; i < ca.length; i++)
    {
        let c = ca[i];

        while (c.charAt(0) == ' ')
        {
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0)
        {
            return c.substring(name.length, c.length);
        }
    }
    
    return "";
}