/**
 * Created with JetBrains PhpStorm.
 * User: larjohns
 * Date: 25/3/2013
 * Time: 1:00 πμ
 * To change this template use File | Settings | File Templates.
 */
jsCache = {
    // http://es5.github.com/#x7.8.4
    // Table 4 — String Single Character Escape Sequences
    '\b': '\\b',
    '\t': '\\t',
    '\n': '\\n',
    '\v': '\\x0b', // In IE < 9, '\v' == 'v'
    '\f': '\\f',
    '\r': '\\r',
    // escape double quotes, \u2028, and \u2029 too, as they break input
    '\"': '\\\"',
    '\u2028': '\\u2028',
    '\u2029': '\\u2029',
    // we’re wrapping the string in single quotes, so escape those too
    '\'': '\\\'',
    '\\': '\\\\'
},
    // http://mathiasbynens.be/notes/localstorage-pattern
    storage = (function() {
        var uid = new Date,
            storage,
            result;
        try {
            (storage = window.localStorage).setItem(uid, uid);
            result = storage.getItem(uid) == uid;
            storage.removeItem(uid);
            return result && storage;
        } catch(e) {}
    }());

function encode(string) {
    // URL-encode some more characters to avoid issues when using permalink URLs in Markdown
    return encodeURIComponent(string).replace(/['()_*]/g, function(character) {
        return '%' + character.charCodeAt(16).toString();
    });
}

function forEach(array, fn) {
    var length = array.length;
    while (length--) {
        fn(array[length]);
    }
}

function map(array, fn) {
    var length = array.length;
    while (length--) {
        array[length] = fn(array[length]);
    }
    return array;
}

// http://mathiasbynens.be/notes/css-escapes
function cssEscape(string, escapeNonASCII) {
    // Based on `ucs2decode` from http://mths.be/punycode
    var firstChar = string.charAt(0),
        output = '',
        counter = 0,
        length = string.length,
        value,
        character,
        charCode,
        surrogatePairCount = 0,
        extraCharCode; // low surrogate

    while (counter < length) {
        character = string.charAt(counter++);
        charCode = character.charCodeAt();
        // if it’s a non-ASCII character and those need to be escaped
        if (escapeNonASCII && (charCode < 32 || charCode > 126)) {
            if ((charCode & 0xF800) == 0xD800) {
                surrogatePairCount++;
                extraCharCode = string.charCodeAt(counter++);
                if ((charCode & 0xFC00) != 0xD800 || (extraCharCode & 0xFC00) != 0xDC00) {
                    throw Error('UCS-2(decode): illegal sequence');
                }
                charCode = ((charCode & 0x3FF) << 10) + (extraCharCode & 0x3FF) + 0x10000;
            }
            value = '\\' + charCode.toString(16) + ' ';
        } else {
            // \r is already tokenized away at this point
            // `:` can be escaped as `\:`, but that fails in IE < 8
            if (/[\t\n\v\f:]/.test(character)) {
                value = '\\' + charCode.toString(16) + ' ';
            } else if (/[ !"#$%&'()*+,./;<=>?@\[\\\]^`{|}~]/.test(character)) {
                value = '\\' + character;
            } else {
                value = character;
            }
        }
        output += value;
    }

    if (/^_/.test(output)) { // Prevent IE6 from ignoring the rule altogether
        output = '\\_' + output.slice(1);
    }
    if (/^-[-\d]/.test(output)) {
        output = '\\-' + output.slice(1);
    }
    if (/\d/.test(firstChar)) {
        output = '\\3' + firstChar + ' ' + output.slice(1);
    }

    return {
        'surrogatePairCount': surrogatePairCount,
        'output': output
    };
}

// Taken from mothereff.in/js-escapes
function jsEscape(str) {
    return str.replace(/[\s\S]/g, function(character) {
        var charCode = character.charCodeAt(),
            hexadecimal = charCode.toString(16),
            longhand = hexadecimal.length > 2,
            escape;
        if (/[\x20-\x26\x28-\x5b\x5d-\x7e]/.test(character)) {
            // it’s a printable ASCII character that is not `'` or `\`; don’t escape it
            return character;
        }
        if (jsCache[character]) {
            return jsCache[character];
        }
        escape = jsCache[character] = '\\' + (longhand ? 'u' : 'x') + ('0000' + hexadecimal).slice(longhand ? -4 : -2);
        return escape;
    });
}

function doubleSlash(str) {
    return str.replace(/['\n\u2028\u2029\\]/g, function(chr) {
        return jsCache[chr];
    });
}