window.addEventListener('unload', function (e) {
    navigator.sendBeacon('/logout');
});
