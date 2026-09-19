$(function(){
    var sidebar = document.getElementById('sidebar');
    var toggle = document.getElementById('sidebarCollapse');
    var backdrop = document.querySelector('.sidebar-backdrop');
    var mobile = window.matchMedia('(max-width: 768px)');
    var compact = false;
    if (!sidebar || !toggle) return;
    try { compact = sessionStorage.getItem('admin-sidebar-compact') === '1'; } catch (e) {}
    Array.from(sidebar.querySelectorAll(':scope > .nav > .nav-item > a')).forEach(function(link){
        var label = link.querySelector('.nav-link-title, .hidden-xs');
        link.title = label ? label.textContent.trim() : link.textContent.trim();
        link.setAttribute('aria-label', link.title);
    });
    function closeMenus(){
        sidebar.querySelectorAll('.sidebar-flyout-open').forEach(function(item){
            item.classList.remove('sidebar-flyout-open');
            item.querySelector('a').setAttribute('aria-expanded', 'false');
        });
    }
    function render(open){
        closeMenus();
        sidebar.classList.remove('active');
        sidebar.classList.toggle('is-compact', !mobile.matches && compact);
        sidebar.classList.toggle('is-mobile-open', mobile.matches && !!open);
        backdrop.hidden = !(mobile.matches && open);
        document.body.classList.toggle('admin-menu-open', mobile.matches && !!open);
        toggle.setAttribute('aria-expanded', String(mobile.matches ? !!open : !compact));
        toggle.setAttribute('aria-label', mobile.matches ? (open ? toggle.dataset.labelClose : toggle.dataset.labelOpen) : (compact ? toggle.dataset.labelExpand : toggle.dataset.labelCollapse));
        toggle.title = toggle.getAttribute('aria-label');
        if ('inert' in sidebar) sidebar.inert = mobile.matches && !open;
    }
    toggle.addEventListener('click', function(){
        if (mobile.matches) render(!sidebar.classList.contains('is-mobile-open'));
        else {
            compact = !compact;
            try { sessionStorage.setItem('admin-sidebar-compact', compact ? '1' : '0'); } catch (e) {}
            render(false);
        }
    });
    function closeMobile(){ render(false); toggle.focus(); }
    sidebar.querySelector('.sidebar-close').addEventListener('click', closeMobile);
    backdrop.addEventListener('click', closeMobile);
    document.addEventListener('click', function(event){
        if (mobile.matches || !compact) return;
        var link = event.target.closest('#sidebar > .nav > .nav-item > a[data-toggle="collapse"]');
        if (!link) { if (!sidebar.contains(event.target)) closeMenus(); return; }
        event.preventDefault(); event.stopImmediatePropagation();
        var item = link.parentElement;
        var wasOpen = item.classList.contains('sidebar-flyout-open');
        closeMenus();
        if (!wasOpen) {
            item.classList.add('sidebar-flyout-open');
            link.setAttribute('aria-expanded', 'true');
            var menu = link.nextElementSibling;
            var height = Math.min(menu.scrollHeight, window.innerHeight - 24);
            menu.style.top = Math.max(12, Math.min(link.getBoundingClientRect().top, window.innerHeight - height - 12)) + 'px';
        }
    }, true);
    document.addEventListener('keydown', function(event){
        if (event.key === 'Escape') {
            if (sidebar.classList.contains('is-mobile-open')) closeMobile();
            else closeMenus();
        }
    });
    sidebar.addEventListener('scroll', closeMenus);
    window.addEventListener('resize', closeMenus);
    mobile.addEventListener('change', function(){ render(false); });
    render(false);
});
