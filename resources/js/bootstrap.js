import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// No se fija X-CSRF-TOKEN desde la etiqueta <meta>: ese valor es el del momento
// de cargar la página (la de login) y queda obsoleto cuando el login regenera
// la sesión, lo que daba 419 en el primer envío. Axios manda el token fresco
// desde la cookie XSRF-TOKEN, que Laravel renueva en cada respuesta.
