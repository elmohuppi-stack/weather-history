import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const primevuePackagePath = path.join(
  __dirname,
  "node_modules",
  "primevue",
  "package.json",
);

console.log("Patching PrimeVue package.json...");

try {
  const packageJson = JSON.parse(fs.readFileSync(primevuePackagePath, "utf8"));

  // Add missing fields and explicit subpath exports for Vite/Rollup
  packageJson.main = "config/config.js";
  packageJson.module = "config/config.esm.js";
  packageJson.exports = {
    ".": {
      import: "./config/config.esm.js",
      require: "./config/config.cjs.js",
    },
    "./resources/*": "./resources/*",
    "./icons/*": {
      import: "./icons/*/index.esm.js",
      require: "./icons/*/index.cjs.js",
    },
    "./*/style": {
      import: "./*/style/*style.esm.js",
      require: "./*/style/*style.cjs.js",
    },
    "./*": {
      import: "./*/*.esm.js",
      require: "./*/*.cjs.js",
    },
  };

  fs.writeFileSync(primevuePackagePath, JSON.stringify(packageJson, null, 2));
  console.log("✅ PrimeVue package.json patched successfully");
} catch (error) {
  console.error("❌ Failed to patch PrimeVue package.json:", error);
  process.exit(1);
}
