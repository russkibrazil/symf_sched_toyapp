import './styles/app.css';
import '../node_modules/bootstrap/dist/css/bootstrap.min.css';
import 'remixicon/fonts/remixicon.css'

import $ from 'jquery';
import 'bootstrap';
import './js/body.js';
import Inputmask from 'inputmask';
// start the Stimulus application
// import './bootstrap';

$(() => {
    Inputmask({
        'removeMaskOnSubmit': true,
        'showMaskOnHover': false
    })
        .mask(document.querySelectorAll("input"));
});