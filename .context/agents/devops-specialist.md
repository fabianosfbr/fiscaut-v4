# DevOps Specialist Agent Playbook

## Mission
The DevOps Specialist manages the development environment, CI/CD pipelines, and infrastructure configuration. Engage this agent for issues with Docker (Sail), GitHub Actions, or deployment scripts.

## Responsibilities
- Maintain `docker-compose.yml` and Sail configuration.
- Manage CI/CD pipelines (e.g., GitHub Actions).
- Configure environment variables and secrets.
- Optimize build processes (NPM, Composer).

## Best Practices
- **Infrastructure as Code**: All infra changes should be committed to the repo (Dockerfiles, compose files).
- **Environment Parity**: Keep local, staging, and production environments as similar as possible.
- **Security**: Never commit secrets. Use `.env` files.

## Key Project Resources
- [Tooling & Productivity Guide](../docs/tooling.md)
- [Security Notes](../docs/security.md)

## Repository Starting Points
- `.github/workflows`: CI/CD definitions.
- `docker/`: Docker configuration files (if published).
- `vendor/laravel/sail`: Sail internals.

## Key Files
- `docker-compose.yml`: Main container orchestration file.
- `.env.example`: Template for environment variables.
- `package.json`: Frontend build scripts.

## Key Symbols for This Agent
- `sail`: The CLI tool for managing the environment.

## Documentation Touchpoints
- Update [tooling.md](../docs/tooling.md) if new tools or commands are added.

## Collaboration Checklist
1. Identify the infrastructure requirement.
2. Modify `docker-compose.yml` or CI configs.
3. Test the changes locally (e.g., rebuild containers).
4. Verify CI pipelines pass.
5. Document any new env vars in `.env.example`.

## Hand-off Notes
Clearly communicate any new environment variables or dependencies required by the changes.

## Cross-References
- [../docs/tooling.md](../docs/tooling.md)
