
/**
 * Bind var func execution on key press.
 *
 * @param {string} key Example : 'F5','Ctrl+Alt+S'
 * @param {function} func function() { alert('this is my function !'); }
 * @returns bool
 */
function addShortcut(key, func)
{
    if (typeof shortcut !== 'undefined')
    {
        return shortcut.add(key, func);
    }

    return false;
}

/**
 * Unbind function execution on key press.
 * @param {string} key
 * @returns Boolean
 */
function removeShortcut(key)
{
    if (typeof shortcut !== 'undefined')
    {
        return shortcut.remove(key);
    }

    return false;
}