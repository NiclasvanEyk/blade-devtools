import "./bootstrap";

import Alpine from "alpinejs";

import { getAllComments } from "blade-devtools-ui";

window.Alpine = Alpine;

Alpine.start();

setTimeout(function () {
    const tree = getAllComments(document.documentElement);
    console.log(tree);
}, 1000);
