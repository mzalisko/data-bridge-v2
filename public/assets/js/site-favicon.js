/* DataBridge CRM — Site Favicon: hash(name) → oklch color */
(function () {
  function faviconStyle(name) {
    var h = 0;
    for (var i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
    var hue = h % 360;
    return 'background:oklch(0.94 0.04 ' + hue + ');color:oklch(0.4 0.1 ' + hue + ');';
  }

  document.querySelectorAll('[data-site-favicon]').forEach(function (el) {
    el.setAttribute('style', faviconStyle(el.dataset.siteFavicon));
  });
})();
