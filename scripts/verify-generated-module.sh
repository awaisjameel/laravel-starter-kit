#!/usr/bin/env bash
#
# Puts a freshly generated module through the same gate the application itself has to
# pass. Generator tests assert on rendered strings, which cannot tell whether a stub
# still emits compiling TypeScript, lint-clean Vue, or code the PHP toolchain accepts.
# Generating into the real application is the only way to find that out, so this script
# does exactly that and then restores the working tree to the state it started in.
#
# Usage: scripts/verify-generated-module.sh [ModuleName]

set -euo pipefail

# CI puts php on PATH; Herd on Windows only exposes a .bat shim, so allow both and an
# explicit override.
PHP="${PHP_BINARY:-}"
if [[ -z "${PHP}" ]]; then
    if command -v php >/dev/null 2>&1; then
        PHP=php
    elif command -v php.bat >/dev/null 2>&1; then
        PHP=php.bat
    else
        echo "Could not find php. Set PHP_BINARY to its path." >&2
        exit 1
    fi
fi

MODULE="${1:-Scaffoldgate}"
# A single-word module keeps every derived path trivially predictable for cleanup.
if [[ ! "${MODULE}" =~ ^[A-Z][a-z]+$ ]]; then
    echo "Module name must be a single capitalised word, got '${MODULE}'." >&2
    exit 1
fi

KEBAB="$(echo "${MODULE}" | tr '[:upper:]' '[:lower:]')"
TABLE="${KEBAB}s"

GENERATED_PATHS=(
    "app/Models/${MODULE}.php"
    "app/Modules/${MODULE}"
    "resources/js/modules/${KEBAB}"
    "resources/js/actions/App/Modules/${MODULE}"
    "resources/js/routes/app/${KEBAB}"
    "tests/Feature/${MODULE}"
)

for path in "${GENERATED_PATHS[@]}"; do
    if [[ -e "${path}" ]]; then
        echo "Refusing to run: '${path}' already exists. Pass a different module name." >&2
        exit 1
    fi
done

# The gate rewrites generated contracts, so it compares the tree against how it found
# it rather than demanding a pristine one. That keeps it usable mid-change.
BASELINE="$(git status --porcelain)"

restore() {
    local status=$?
    echo "--- Restoring working tree ---"
    rm -rf "${GENERATED_PATHS[@]}"
    rm -f database/migrations/*_create_"${TABLE}"_table.php
    "${PHP}" artisan modules:cache --no-interaction >/dev/null
    "${PHP}" artisan typescript:transform >/dev/null
    "${PHP}" artisan wayfinder:generate >/dev/null
    # `components.d.ts` is emitted by the Vite component scanner, so it only returns to
    # its previous state once a build runs without the generated module present.
    npm run build >/dev/null

    if [[ "$(git status --porcelain)" != "${BASELINE}" ]]; then
        echo "Generated-module gate did not restore the working tree:" >&2
        diff <(echo "${BASELINE}") <(git status --porcelain) >&2 || true
        exit 1
    fi

    exit "${status}"
}
trap restore EXIT

echo "--- Generating ${MODULE} ---"
"${PHP}" artisan generate:module "${MODULE}" \
    --scaffold=crud-api \
    --route-profile=app \
    --api-route-profile=protected \
    --roles=all \
    --no-file-prompts \
    --no-interaction

echo "--- Regenerating backend-owned contracts ---"
"${PHP}" artisan modules:cache --no-interaction
"${PHP}" artisan typescript:transform
"${PHP}" artisan wayfinder:generate

# Formatting is width-sensitive, so whether Prettier wraps a generated component tag
# depends on how long the module's name is. That is what `composer generate-and-cleanup`
# normalises; this gate asserts the properties a formatter cannot fix for you.
echo "--- PHP gate ---"
"${PHP}" vendor/bin/pint --test
"${PHP}" vendor/bin/rector process --dry-run
"${PHP}" vendor/bin/phpstan analyse --no-progress

echo "--- Frontend gate ---"
npm run typecheck
npm run lint:check

echo "--- Suites and build ---"
"${PHP}" vendor/bin/pest --parallel
npm run test:unit
npm run build:ssr

echo "--- Generated-module gate passed ---"
