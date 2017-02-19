System.config({
  baseURL: "./htdocs/",
  defaultJSExtensions: true,
  transpiler: "babel",
  babelOptions: {
    "optional": [
      "runtime",
      "optimisation.modules.system"
    ]
  },
  paths: {
    "github:*": "jspm_packages/github/*",
    "npm:*": "jspm_packages/npm/*"
  },

  map: {
    "@material/card": "npm:@material/card@0.1.3",
    "babel": "npm:babel-core@5.8.38",
    "babel-runtime": "npm:babel-runtime@5.8.38",
    "breakpoint-sass": "npm:breakpoint-sass@2.7.0",
    "core-js": "npm:core-js@1.2.6",
    "eurostar-action": "npm:eurostar-action@1.0.27",
    "eurostar-base-styles": "npm:eurostar-base-styles@1.0.30",
    "eurostar-card": "npm:eurostar-card@1.0.27",
    "eurostar-card-board": "npm:eurostar-card-board@1.0.28",
    "normalize-scss": "npm:normalize-scss@5.0.3",
    "picturefill": "npm:picturefill@3.0.2",
    "susy": "npm:susy@2.2.12",
    "github:jspm/nodelibs-assert@0.1.0": {
      "assert": "npm:assert@1.4.1"
    },
    "github:jspm/nodelibs-buffer@0.1.0": {
      "buffer": "npm:buffer@3.6.0"
    },
    "github:jspm/nodelibs-path@0.1.0": {
      "path-browserify": "npm:path-browserify@0.0.0"
    },
    "github:jspm/nodelibs-process@0.1.2": {
      "process": "npm:process@0.11.9"
    },
    "github:jspm/nodelibs-util@0.1.0": {
      "util": "npm:util@0.10.3"
    },
    "github:jspm/nodelibs-vm@0.1.0": {
      "vm-browserify": "npm:vm-browserify@0.0.4"
    },
    "npm:@material/card@0.1.3": {
      "@material/elevation": "npm:@material/elevation@0.1.2",
      "@material/theme": "npm:@material/theme@0.1.1",
      "@material/typography": "npm:@material/typography@0.1.1"
    },
    "npm:@material/elevation@0.1.2": {
      "@material/animation": "npm:@material/animation@0.1.3"
    },
    "npm:assert@1.4.1": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "buffer": "github:jspm/nodelibs-buffer@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "util": "npm:util@0.10.3"
    },
    "npm:babel-runtime@5.8.38": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:breakpoint-sass@2.7.0": {
      "path": "github:jspm/nodelibs-path@0.1.0"
    },
    "npm:buffer@3.6.0": {
      "base64-js": "npm:base64-js@0.0.8",
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "ieee754": "npm:ieee754@1.1.8",
      "isarray": "npm:isarray@1.0.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:core-js@1.2.6": {
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "systemjs-json": "github:systemjs/plugin-json@0.1.2"
    },
    "npm:eurostar-base-styles@1.0.30": {
      "breakpoint-sass": "npm:breakpoint-sass@2.7.0",
      "normalize-scss": "npm:normalize-scss@5.0.3",
      "picturefill": "npm:picturefill@3.0.2",
      "susy": "npm:susy@2.2.12"
    },
    "npm:eurostar-card-board@1.0.28": {
      "classlist-polyfill": "npm:classlist-polyfill@1.0.3",
      "lory.js": "npm:lory.js@2.2.1"
    },
    "npm:inherits@2.0.1": {
      "util": "github:jspm/nodelibs-util@0.1.0"
    },
    "npm:lory.js@2.2.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:path-browserify@0.0.0": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:picturefill@3.0.2": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0"
    },
    "npm:process@0.11.9": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "vm": "github:jspm/nodelibs-vm@0.1.0"
    },
    "npm:susy@2.2.12": {
      "path": "github:jspm/nodelibs-path@0.1.0"
    },
    "npm:util@0.10.3": {
      "inherits": "npm:inherits@2.0.1",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:vm-browserify@0.0.4": {
      "indexof": "npm:indexof@0.0.1"
    }
  }
});
