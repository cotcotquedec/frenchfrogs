/**
 *
 *
 * @source https://paulund.co.uk/capitalize-first-letter-string-javascript
 *
 * @param string
 * @returns {string}
 */
function ucfirst(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}