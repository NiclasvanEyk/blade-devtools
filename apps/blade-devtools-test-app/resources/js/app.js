import "./bootstrap";

import Alpine from "alpinejs";

import { iterateComponentsTags } from "blade-devtools-ui";

window.Alpine = Alpine;

Alpine.start();

document.addEventListener("load", function () {
    const tree = iterateComponentsTags(document.documentElement);

    console.log(tree);
});
