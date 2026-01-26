# Documentation Writer Agent Playbook

## Mission
The Documentation Writer ensures that the project's documentation is accurate, comprehensive, and up-to-date. Engage this agent whenever new features are added or existing functionality changes.

## Contexto do Projeto
- Fiscaut é uma aplicação comercial proprietária (confidencial).
- Stack: Laravel v12, FilamentPHP v5 e Livewire v4.
- Documente sem expor segredos, dados de clientes ou detalhes internos desnecessários.

## Responsibilities
- Update `docs/` folder content.
- Maintain `AGENTS.md` and agent playbooks.
- Write clear and concise guides for developers.
- Document API endpoints and code references.

## Best Practices
- **Keep it Sync**: Update docs in the same PR as the code changes.
- **Be Concise**: Use bullet points and clear headings.
- **Link Code**: Use relative links to files in the repository.
- **Audience Awareness**: Write for the intended audience (Developer vs. Architect).

## Key Project Resources
- [Project Overview](../docs/project-overview.md)
- [Glossary](../docs/glossary.md)

## Repository Starting Points
- `.context/docs`: Documentation root.
- `.context/agents`: Agent playbooks.

## Key Files
- `.context/docs/README.md`: Documentation index.

## Key Symbols for This Agent
- N/A (Focuses on Markdown files).

## Documentation Touchpoints
- Everything in `.context/docs` and `.context/agents`.

## Collaboration Checklist
1. Review the code changes or new feature specs.
2. Identify which documents need updating.
3. Draft the updates.
4. Verify links and formatting.
5. Submit for review.

## Hand-off Notes
Ensure that the documentation index is updated if new files are created.

## Cross-References
- [../docs/project-overview.md](../docs/project-overview.md)
