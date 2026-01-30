# Changelog

## [Unreleased] - 2026-01-30

### Added
- **XML Processing Engine**:
  - Implemented `XmlNfeReaderService` for specialized NFe parsing.
  - Implemented `XmlCteReaderService` for specialized CTe parsing.
  - Replaced legacy `NfeService` and `CteService` with new array-based readers.
- **Filament Resources**:
  - `CategoryTagResource`: New resource for managing "Categorias das etiquetas".
  - `TagsRelationManager`: Integrated tag management within categories.
- **UI Components**:
  - **Livewire**: Custom "sou um componente Livewire" component bridge in Configuration pages.

### Changed
- **CnaeForm**:
  - Enhanced `aliquota` field with Filament v5 decimal masking (supports `10,50`, `0,50`).
  - Implemented raw value stripping before persistence.

### Fixed
- **XML Parsing**: Addressed issues with generic XML reading by strictly typing return structures as associative arrays.
