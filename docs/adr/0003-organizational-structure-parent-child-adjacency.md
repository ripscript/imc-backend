# Organizational Structure Parent-Child Hierarchy via Adjacency List

To support multi-tier organizational hierarchies (parent-child relationships) without altering existing relational table schemas or bloating payload sizes, we maintain the existing flat array structure (`groups[].members[]`) using an Adjacency List pattern (`parent_id`). We eliminate the previous hardcoded limitation where all non-root members were forced to point to the first member. For newly created records within the same request that lack persistent database IDs, clients may provide temporary identifiers (`temp_id` and `parent_temp_id`) which are resolved into database IDs in a two-pass persistence step. Metadata flags (`level`, `is_child`, `have_child`) are derived and maintained automatically by the backend to guarantee hierarchical integrity.

## Considered Options

- **Nested Tree Payload (`children: [...]`)**: Rejected because recursive nested JSON payloads significantly increase payload size, require complex client-side tree restructuring, and demand recursive database insertion logic that is prone to stack overflows and circular reference risks.
- **Closure Table / Nested Sets DB Schema**: Rejected because modifying the database schema is unnecessary; the database tables already possess `parent_id`, `level`, `is_child`, and `have_child` columns, making schema migration overhead wasteful.
- **Flat Array with Two-Pass Adjacency Resolution & Auto-Derived Hierarchy**: Selected because it preserves the existing API contract and database schema, minimizes JSON bandwidth, integrates cleanly with frontend graph/chart libraries (OrgChart, D3, ReactFlow), and automatically guarantees consistent `level`, `is_child`, and `have_child` values.
