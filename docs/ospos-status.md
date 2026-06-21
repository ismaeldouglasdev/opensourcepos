# Estado do OSPOS (Open Source Point of Sale) no GitHub

**Data:** 11 de Junho de 2026

---

## Problemas (issues)

- **208 issues abertos** (bugs, features, débito técnico)
- **35 pull requests abertos**
- O repositório tem activity contínua — bugs estão a ser corrigidos, mas **nem todos os problemas estão resolvidos**

## Ambientes

| Ambiente | URL | Descrição |
|----------|-----|-----------|
| **Produção (demo)** | https://demo.opensourcepos.org/ | Último master branch, contentorizado, reinicia com novo merge |
| **Desenvolvimento/teste** | https://dev.opensourcepos.org/ | Constrói a cada novo commit, usado para testar código antes de merge no master |
| **Status** | https://status.opensourcepos.org/ | Página de estado dos servidores |

## CI/CD

O projeto usa GitHub Actions com múltiplos workflows:
- Build and Release
- PHPUnit Tests
- PHP Linting
- Coding Standards
- Integration Tests
- Deploy (PR Deploy, Deploy Core)
- Install Script Test

## Conclusão

O ambiente de desenvolvimento (`dev.opensourcepos.org`) está **separado do ambiente de produção** (`demo.opensourcepos.org`). No entanto, **não estão todos os problemas corrigidos** — o projeto continua em desenvolvimento ativo com dezenas de issues em aberto.
