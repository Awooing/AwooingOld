document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

naja.registerExtension(function () {
    naja.addEventListener('init', function () {
        topbar.config({barColors: {0:"#281483", .3:"#8f6ed5", 1.0:"#d782d9"}})
    });
    naja.addEventListener('start', function () { topbar.show(); }.bind(this));
    naja.addEventListener('complete', function () { topbar.hide(); }.bind(this));
});