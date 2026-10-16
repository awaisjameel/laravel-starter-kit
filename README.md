# Laravel Modular Inertia Starter Kit

A Laravel 13 + Inertia 3 + Vue 3 starter kit with strict typed contracts from backend DTOs to frontend TypeScript, server-side rendering, Reverb realtime, and a module generator that scaffolds backend and frontend together.

## Quick Start

Install PHP, Composer, Node, and npm at the versions listed in the [stack](docs/architecture/stack.md#current-stack), then:

```bash
composer setup
composer dev
```

The app is served at `http://127.0.0.1:8000`. [Local runtime](docs/architecture/runtime.md) explains what each command does, and [deployment](docs/deployment.md) covers production servers.

## Documentation

[AGENTS.md](AGENTS.md) is the entry point for people and coding agents alike (`CLAUDE.md` imports it). It holds the workflow, standards, and quality gate for every change, and its [document ownership](AGENTS.md#document-ownership) table lists each architecture reference under [docs/architecture](docs/architecture/) with the concern it owns.
