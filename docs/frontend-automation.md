# Frontend Automation

## Frontend page scaffolding

Use the generator to scaffold a new module page with schema, route contract, and test:

```bash
npm run generate:frontend-page -- --module=<module-name> --page=<page-name>
```

Example:

```bash
npm run generate:frontend-page -- --module=users --page=InviteUser
```

Generated files:

- `resources/js/modules/<module>/forms/<page>-form-schema.ts`
- `resources/js/modules/<module>/pages/<Page>.vue`
- `resources/js/modules/<module>/routes/<page>-route-contract.ts`
- `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`

## Auto-import source of truth

`frontend-auto-import.config.mjs` is the canonical definition for auto-import symbols and directories.

The following files consume it and must stay aligned:

- `vite.config.ts`
- `vitest.config.ts`
- `eslint.config.js`
