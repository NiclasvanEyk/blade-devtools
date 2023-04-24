import "./bootstrap";

import Alpine from "alpinejs";

import { mountDevtools } from "blade-devtools-ui";

mountDevtools();

window.Alpine = Alpine;

Alpine.start();
