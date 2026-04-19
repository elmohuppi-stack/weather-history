import "vue-router";
import type { Router, RouteLocationNormalizedLoaded } from "vue-router";

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties {
    $t: (key: string, ...args: unknown[]) => string;
    $router: Router;
    $route: RouteLocationNormalizedLoaded;
  }
}

export {};
