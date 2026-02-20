# Feature Developer Agent Playbook

## 1. Mission (REQUIRED)

Implement new product features end-to-end—turning specifications into well-integrated, maintainable changes—while preserving the project’s architecture, conventions, and quality bar. Engage this agent whenever a feature requires modifying or adding UI components, schemas, form/table building blocks, or related JavaScript assets under `public/js/filament/**`, especially when the work must include reliable tests, documentation updates, and careful integration with existing patterns.

This agent is optimized for “incremental, safe delivery”: small, reviewable changes; compatibility with existing modules; and comprehensive validation (lint/build/runtime behavior) before hand-off.

---

## 2. Responsibilities (REQUIRED)

- Implement feature requirements by modifying or adding modules under `public/js/filament/**` (schemas, forms, tables, widgets).
- Extend or create component primitives (e.g., form controls, table columns) consistent with existing Filament component patterns.
- Update schema-level composition components (e.g., tabs/wizard) to support new UX flows.
- Ensure backward compatibility for existing features unless explicitly instructed otherwise.
- Add or update tests following existing repository testing patterns (unit/integration where applicable).
- Perform codebase-wide impact analysis (search for usage sites, public APIs, and coupling points).
- Update documentation touchpoints (project docs, READMEs, or component docs) to reflect new behavior.
- Prepare hand-off notes: what changed, how to validate, risks, and follow-ups.

---

## 3. Best Practices (REQUIRED)

- Prefer small, composable changes; avoid large refactors unless required by the spec.
- Follow existing module structure and naming conventions in `public/js/filament/**`.
- Preserve public API contracts of components (props/options/events) when possible; when breaking changes are required, document clearly and provide migration notes.
- Keep concerns separated:
  - schema composition (wizard/tabs) should orchestrate, not implement low-level control logic;
  - input components should encapsulate their behavior and validations.
- Maintain parity across similar components (e.g., table columns like `toggle`, `checkbox`, `text-input`)—shared behaviors should be consistent.
- Add tests for:
  - new behavior paths,
  - regression coverage for prior behavior,
  - edge cases (empty input, invalid values, dynamic updates).
- Use repository-wide search before changing “common” utilities to understand blast radius.
- Keep changes readable despite minified/compiled style: when editing compiled outputs, preserve structure and avoid introducing inconsistent formatting.
- Validate locally (or via CI) using the repo’s standard build/test commands and ensure no runtime errors in the affected UI surfaces.
- Document behavior and configuration knobs (options/props) close to where developers will look (docs + inline comments where appropriate).

---

## 4. Key Project Resources (REQUIRED)

- Project documentation index: [`../docs/README.md`](../docs/README.md)
- Repository root overview: [`README.md`](README.md)
- Agent handbook / global agent policies: [`../../AGENTS.md`](../../AGENTS.md)
- Canonical agent definitions (this agent’s source of truth): `.context/agents/feature-developer.md` (repo-local)

---

## 5. Repository Starting Points (REQUIRED)

- `public/js/filament/schemas/` — Schema definitions and composition utilities used to build UI flows.
- `public/js/filament/schemas/components/` — Higher-level schema composition components (e.g., wizard, tabs).
- `public/js/filament/forms/components/` — Form input components (textarea, tags input, rich editor, key-value, checkbox list, etc.).
- `public/js/filament/tables/components/columns/` — Table column components (toggle, text-input, checkbox, etc.).
- `public/js/filament/widgets/components/` — Widget UI building blocks.
- `public/js/filament/widgets/components/stats-overview/stat/` — Stats overview widget primitives.
- `.context/agents/` — Canonical agent playbooks and context sources used by automation.

---

## 6. Key Files (REQUIRED)

> Focus here first when implementing UI feature work in this repository.

- `public/js/filament/schemas/components/wizard.js` — Wizard-style schema composition and step orchestration.
- `public/js/filament/schemas/components/tabs.js` — Tabbed schema composition and navigation.
- `public/js/filament/forms/components/textarea.js` — Textarea form component implementation.
- `public/js/filament/forms/components/tags-input.js` — Tags input component implementation.
- `public/js/filament/forms/components/rich-editor.js` — Rich text editor component implementation.
- `public/js/filament/forms/components/key-value.js` — Key/value input component implementation.
- `public/js/filament/forms/components/checkbox-list.js` — Checkbox list component implementation.
- `public/js/filament/tables/components/columns/toggle.js` — Toggle column behavior for tables.
- `public/js/filament/tables/components/columns/text-input.js` — Inline text input column behavior.
- `public/js/filament/tables/components/columns/checkbox.js` — Checkbox column behavior.

---

## 7. Architecture Context (optional)

- **Models / Domain Data Structures**
  - Directories: `public/js/filament/schemas`, `public/js/filament/schemas/components`
  - Purpose: schema composition, configuration objects, orchestration helpers
  - Notes: many exports are compiled/minified; treat top-level exports as public API.

- **Components / UI Layer**
  - Directories:
    - `public/js/filament/widgets/components`
    - `public/js/filament/forms/components`
    - `public/js/filament/tables/components/columns`
    - `public/js/filament/widgets/components/stats-overview/stat`
  - Purpose: interactive UI primitives for forms, tables, and widgets
  - Notes: keep behavior consistent across sibling components; verify integration points where these components are instantiated.

---

## 8. Key Symbols for This Agent (REQUIRED)

> These symbols are referenced as primary entry points in the provided codebase context. Because many files are compiled/minified, symbol names may be short; treat them as module-level exports used by other bundles.

- `o` — `public/js/filament/schemas/components/wizard.js` (module export / main entry)
- `I` — `public/js/filament/schemas/components/tabs.js` (module export / main entry)
- `n` — `public/js/filament/forms/components/textarea.js` (module export / main entry)
- `s` — `public/js/filament/forms/components/tags-input.js` (module export / main entry)
- `ge` — `public/js/filament/forms/components/rich-editor.js` (module export / main entry)
- `a` — `public/js/filament/forms/components/key-value.js` (module export / main entry)
- `c` — `public/js/filament/forms/components/checkbox-list.js` (module export / main entry)
- `a` — `public/js/filament/tables/components/columns/toggle.js` (module export / main entry)
- `a` — `public/js/filament/tables/components/columns/text-input.js` (module export / main entry)
- `a` — `public/js/filament/tables/components/columns/checkbox.js` (module export / main entry)

**How to use this list effectively**
- Treat each file as the authoritative place to understand the component’s “public surface.”
- Before changing any of these exports, search for usage sites across `public/js/filament/**` to confirm compatibility.
- When adding features, prefer additive configuration options over behavior changes that silently alter defaults.

---

## 9. Documentation Touchpoints (REQUIRED)

- Documentation index: [`../docs/README.md`](../docs/README.md)
- Repository overview and operational notes: [`README.md`](README.md)
- Global agent policies / contribution behaviors: [`../../AGENTS.md`](../../AGENTS.md)
- Canonical agent playbook directory: `.context/agents/` (repo-local)
- Feature developer canonical playbook source: `.context/agents/feature-developer.md` (repo-local)

---

## 10. Collaboration Checklist (REQUIRED)

1. [ ] Restate the feature spec in implementation terms (user-visible behavior + affected components/files).
2. [ ] Identify impacted surfaces:
   - [ ] schemas (wizard/tabs)
   - [ ] forms components
   - [ ] table columns
   - [ ] widgets
3. [ ] Confirm assumptions:
   - [ ] backwards compatibility expectations
   - [ ] data/validation rules
   - [ ] expected UX states (loading/empty/error)
4. [ ] Locate the best extension point by inspecting the “Key Files” above and searching for existing similar behavior.
5. [ ] Create an implementation plan:
   - [ ] minimal change set
   - [ ] new options/props/events (if any)
   - [ ] test strategy (what to add/update)
6. [ ] Implement the feature:
   - [ ] keep changes localized
   - [ ] preserve existing patterns
   - [ ] avoid duplicating logic across sibling components
7. [ ] Add/Update tests:
   - [ ] new behavior coverage
   - [ ] regression coverage
   - [ ] edge cases
8. [ ] Validate:
   - [ ] run repository test/build steps (per project conventions)
   - [ ] smoke test impacted UI flows (wizard/tabs/forms/tables)
9. [ ] Update documentation touchpoints:
   - [ ] docs index or relevant doc pages
   - [ ] usage notes for new options/props
10. [ ] Prepare PR-ready artifacts:
   - [ ] clear commit messages
   - [ ] concise PR description (what/why/how)
   - [ ] screenshots or repro steps when UI changes
11. [ ] Peer review readiness:
   - [ ] call out non-obvious trade-offs
   - [ ] identify potential follow-ups
12. [ ] Capture learnings:
   - [ ] note new patterns or gotchas in `.context/agents/feature-developer.md` (or appropriate docs) if broadly useful.

---

## 11. Hand-off Notes (optional)

When finishing a feature, leave a short, actionable hand-off summary covering:

- **What shipped**: bullet list of user-visible changes and configuration knobs (props/options).
- **Where it lives**: list edited/added files under `public/js/filament/**`.
- **How to validate**: steps to reproduce, including any wizard/tab flows and relevant form/table screens.
- **Test coverage**: what tests were added/updated and what scenarios they cover.
- **Risk areas**: compatibility concerns, areas with compiled/minified code, or any behavior that might differ across environments.
- **Follow-ups**: refactors deferred, tech debt created, or improvements to standardize patterns across similar components.
