(function(){
    function init(){
        var selectors = Array.from(document.querySelectorAll('details.site-language-selector'));
        function position(selector){
            if (!selector.open) return;
            var menu = selector.querySelector('.site-language-menu');
            var rect = selector.querySelector('summary').getBoundingClientRect();
            var width = Math.min(220, window.innerWidth - 16);
            menu.style.position = 'fixed';
            menu.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - width - 8)) + 'px';
            var below = window.innerHeight - rect.bottom - 14;
            var above = rect.top - 14;
            var upwards = below < Math.min(menu.scrollHeight, 180) && above > below;
            menu.style.maxHeight = Math.max(40, upwards ? above : below) + 'px';
            menu.style.top = upwards ? 'auto' : (rect.bottom + 6) + 'px';
            menu.style.bottom = upwards ? (window.innerHeight - rect.top + 6) + 'px' : 'auto';
        }
        selectors.forEach(function(selector){
            selector.addEventListener('toggle', function(){
                if (selector.open) {
                    selectors.forEach(function(other){ if (other !== selector) other.open = false; });
                    position(selector);
                }
            });
            selector.addEventListener('keydown', function(event){
                if (event.key === 'Escape') {
                    selector.open = false; selector.querySelector('summary').focus(); event.stopPropagation();
                }
                if (event.key === 'ArrowDown' && event.target === selector.querySelector('summary')) {
                    event.preventDefault(); selector.open = true; position(selector); selector.querySelector('a').focus();
                }
            });
        });
        document.addEventListener('click', function(event){ selectors.forEach(function(selector){ if (!selector.contains(event.target)) selector.open = false; }); });
        window.addEventListener('resize', function(){ selectors.forEach(position); });
        document.addEventListener('scroll', function(){ selectors.forEach(position); }, true);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
