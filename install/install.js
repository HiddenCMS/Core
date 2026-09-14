$(function(){
 var $theme = $('#installer-dark');
 var dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
 try { var saved = localStorage.getItem('hiddencms-installer-theme'); if (saved) dark = saved === 'dark'; } catch (_) {}
 var applyTheme = function(value){
  $('body').attr('data-theme', value ? 'dark' : 'light');
  $theme.prop('checked', value);
  $('.logo img').attr('src', 'dist/images/logo/hiddencms'+(value ? '-light' : '')+'.svg');
 };
 applyTheme(dark);
 $theme.on('change', function(){ applyTheme(this.checked); try { localStorage.setItem('hiddencms-installer-theme', this.checked ? 'dark' : 'light'); } catch (_) {} });
 var $error = $('.installer-error'), retry;
 var showError = function(action){
  retry = action;
  $error.find('span').text($error.data('message'));
  $error.prop('hidden', false);
 };
 var clearError = function(){ $error.prop('hidden', true); };
 $('[data-action="retry"]').on('click', function(){ clearError(); if (retry) retry(); });
 var progress = function($section){
  var stage = $section.data('stage') || $section.data('step') || 'check', reached = false;
  $('.installer-progress li').each(function(){
   var current = $(this).data('stage') === stage;
   $(this).toggleClass('active', current).toggleClass('complete', !reached && !current);
   if (current) { $(this).attr('aria-current', 'step'); reached = true; } else $(this).removeAttr('aria-current');
  });
 };
 var next = function(){
  var $section = $('section:visible');
  $section.fadeOut(150, function(){ var $next = $(this).next('section'); $next.fadeIn(150); progress($next); });
 };
 $('section:first').show(); progress($('section:first'));
 var runChecks = function(){
  clearError();
  $('.first-check-errors').addClass('d-none');
  $('.first-check-errors .list-group-item:not(.d-none)').remove();
  $('.check-init .legend').removeClass('invisible').show();
  $('.check-init .checking').show(); $('.check-init .errors').addClass('d-none');
  $.ajax({ url: 'index.php?step=check', dataType: 'json' }).done(function(data){
   if (!Array.isArray(data)) { showError(runChecks); return; }
   var $template = $('.first-check-errors .list-group-item.d-none'), errors = 0;
   $.each(data, function(_, check){
    var $item = $template.clone().removeClass('d-none');
    var icons = { danger: 'fas fa-times', success: 'fas fa-check-circle', info: 'fas fa-info-circle', warning: 'fas fa-exclamation-triangle' };
    var $content = $('<span>').html(check.title);
    $item.empty().append($('<i>').attr('class', (icons[check.icon] || icons.info)+' icon fa-fw text-'+check.icon)).append($content);
    var $info = $('<ul class="list-inline float-right">');
    $.each(check.info, function(key, value){ $info.append($('<li class="list-inline-item">').html((isNaN(parseInt(key)) ? key+' ' : '')+'<b>'+value+'</b>')); });
    $item.append($info); $template.before($item);
    if (check.icon === 'danger') errors++;
   });
   $('.first-check-errors').removeClass('d-none');
   $('.check-init .checking').hide();
   if (errors) $('.check-init .errors').removeClass('d-none');
   else { $('.check-init .legend').hide(); $('.check-init [data-action="next-step"]').removeClass('invisible').show(); }
  }).fail(function(){ showError(runChecks); });
 };
 // Compatibility checks use the existing server-produced, trusted HTML labels.
 if ($('.check-init').length) runChecks();
 var checkForm = function($form, install){
  clearError();
  var $section = $form.closest('.step'), step = $section.data('step');
  var $button = $form.find('button[type="submit"]'), original = $button.text();
  var $next = $section.find('[data-action="next-step"]');
  $next.hide(); $form.find('.invalid-feedback').remove();
  $form.find('.form-control').removeClass('is-valid is-invalid');
  $button.prop('disabled', true).text($button.data('loading-text'));
  $form.attr('aria-busy', 'true');
  var done = $.Deferred();
  $.ajax({ url: 'index.php?step='+step+(install ? '&install=true' : ''), type: 'POST', dataType: 'json',
   data: new FormData($form[0]), processData: false, contentType: false
  }).done(function(data){
   if (data === 'ok') {
    $form.find('.form-control').addClass('is-valid');
    $next.removeClass('invisible').show();
    done.resolve();
   } else {
    if (install) { $section.siblings('section:visible').hide(); $section.show(); progress($section); }
    $.each(data.errors || {}, function(name, message){
     var $input = $form.find('.form-control').filter(function(){ return this.name === name; });
     $input.addClass(message === 'ok' ? 'is-valid' : 'is-invalid');
     if (message && message !== 'ok') $input.after($('<div class="invalid-feedback">').text(message));
    });
    if (!data.errors) showError(function(){ checkForm($form, install).done(function(){ if (install) location.reload(); else if (step === 'user') next(); }); });
    done.reject();
   }
  }).fail(function(){
   if (install) { $section.siblings('section:visible').hide(); $section.show(); progress($section); }
   showError(function(){ checkForm($form, install).done(function(){ if (install) location.reload(); else if (step === 'user') next(); }); });
   done.reject();
  }).always(function(){ $button.prop('disabled', false).text(original); $form.removeAttr('aria-busy'); });
  return done.promise();
 };
 $('.step[data-step] form').on('submit', function(event){
  event.preventDefault(); var $form = $(this);
  checkForm($form, false).done(function(){ if ($form.closest('.step').data('step') === 'user') next(); });
 });
 $('body').on('click', '[data-action="next-step"]', function(event){
  event.preventDefault();
  var $section = $(this).closest('.step');
  if ($section.data('step') === 'db') {
   var $form = $section.find('form');
   if ($form.attr('aria-busy') === 'true') return;
   next();
   checkForm($form, true).done(function(){ location.reload(); });
  } else next();
 });
 $('.step[data-step] form .form-control').on('input change', function(){ $(this).closest('.step').find('[data-action="next-step"]').hide(); });
});
