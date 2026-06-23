// Redirect from registro-completado to /aula/
(function(){
  try {
    if (window.location.pathname.indexOf('registro-completado') !== -1) {
      window.location.replace('/aula/');
    }
  } catch(e) {}
})();
