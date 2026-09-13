(function(){
    'use strict';
    function init(){
        document.querySelectorAll('.statistics-overview').forEach(function(root){
            if(root.dataset.initialized) return;
            root.dataset.initialized='1';
            var charts=[];
            var tabs=Array.from(root.querySelectorAll('[role="tab"]'));
            function select(tab){
                tabs.forEach(function(item){
                    var active=item===tab;
                    item.setAttribute('aria-selected',active?'true':'false');item.tabIndex=active?0:-1;
                    root.querySelector('#'+item.getAttribute('aria-controls')).hidden=!active;
                });
                charts.forEach(function(chart){chart.reflow();});
            }
            tabs.forEach(function(tab,index){
                tab.addEventListener('click',function(){select(tab);});
                tab.addEventListener('keydown',function(event){
                    var next;
                    if(event.key==='ArrowRight') next=tabs[(index+1)%tabs.length];
                    if(event.key==='ArrowLeft') next=tabs[(index+tabs.length-1)%tabs.length];
                    if(event.key==='Home') next=tabs[0];if(event.key==='End') next=tabs[tabs.length-1];
                    if(next){event.preventDefault();select(next);next.focus();}
                });
            });
            root.querySelectorAll('.statistics-chart').forEach(function(chart){
            if(!window.Highcharts){chart.textContent='Graphique indisponible. Les données restent accessibles dans le tableau ci-dessous.';return;}
            var series=JSON.parse(chart.dataset.series).map(function(item){return {name:item.name,color:item.color,data:item.data.map(function(point){return [Date.parse(point[0]+'T00:00:00Z'),point[1]];})};});
            charts.push(new Highcharts.Chart(chart,{
                chart:{type:'line',animation:false,spacing:[24,20,16,16]},title:{text:null},credits:{enabled:false},
                xAxis:{type:'datetime',labels:{format:'{value:%d/%m}'},lineColor:'#dfe7eb'},
                yAxis:{min:0,allowDecimals:false,title:{text:null},gridLineColor:'#edf2f5'},
                legend:{enabled:true},tooltip:{shared:true,xDateFormat:'%d/%m/%Y'},
                plotOptions:{series:{animation:false,marker:{enabled:false},lineWidth:2}},series:series
            }));
            });
        });
    }
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
    if(window.jQuery)jQuery(document).on('nf.load',init);
}());
