(function(){
    'use strict';
    var element=document.getElementById('statistics-tracking-config');
    if(!element)return;
    var config=JSON.parse(element.textContent),sent=false;
    function record(){
        if(sent || !window.HiddenCMSPrivacy || !window.HiddenCMSPrivacy.hasConsent('site_statistics'))return;
        if(/\/(admin|ajax|install|vendor|config|tools|user|files)(\/|$)/i.test(location.pathname))return;
        sent=true;
        var data=new URLSearchParams({token:config.token,consent:'1',path:location.pathname});
        fetch(config.url,{method:'POST',body:data,credentials:'same-origin',keepalive:true}).catch(function(){});
    }
    window.addEventListener('hiddencms:privacychange',record);
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',record);else record();
}());
