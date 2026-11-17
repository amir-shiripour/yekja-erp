---
trigger: always_on
description: Consult the graphify knowledge graph at graphify-out/ for codebase and architecture questions, especially ResellerHub and cross-module interactions.
---

## graphify Knowledge Graph Reference Rule

This project maintains a persistent graphify knowledge graph located at `graphify-out/`.

### Core Mandate:
- **Always refer to the graph (`graphify-out/graph.json`) on every incoming user prompt** involving code, architecture, modules, or features—especially `Modules/ResellerHub` and its connections with core `app/` (Tenancy, Models, Services) and other modules (`Modules/*`).
- Use `python -m graphify query "<question>"` or NetworkX inspect on `graphify-out/graph.json` for context, relationships, call paths, and dependencies before proposing or executing changes.
- Use `python -m graphify path "<NodeA>" "<NodeB>"` to trace paths between components (e.g., between `TenantProvisioningService` and `Tenant`, or `ResellerInvoice` and `WorkflowEngine`).
- Use `python -m graphify explain "<Node>"` for in-depth analysis of specific services, actions, controllers, or models.
- If code files are modified during a session, ensure `python -m graphify update .` is executed (or planned) to keep the knowledge graph up to date.

