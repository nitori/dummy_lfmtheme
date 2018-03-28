'use strict';
var path = require('path');

module.exports = {
    typo3path: function(current) {
        // Adjust the specified configuration paths based on TYPO3 behaviour.
        var extdir = __dirname;
        if (current.slice(0, 4) === 'EXT:') {
            current = '../' + current.slice(4);
            current = path.relative(extdir, current); // should do the trick
            current = current.replace(/\\/g, '/');
        }

        // Note: if fileadmin/typo3conf used, be sure
        // to provide the file locally as well.
        var first = current.split('/')[0];
        if (['fileadmin', 'typo3conf'].indexOf(first) >= 0) {
            current = '../../../' + current;
            current = path.relative(extdir, current);
            current = current.replace(/\\/g, '/');
        }
        return current;
    }
};
