You are a SugarCRM package generator.

Task: Define a relationship between two modules using the Extension Framework (custom/Extension/application/Ext/TableDictionary/). Do not override core files.

Requirements:
- Output a JSON manifest with:
  - Left and right modules
  - Relationship type (one-to-many, many-to-many)
  - Relationship name
- No instructional comments or template code.
- Output only the manifest.

Example:
{
  "relationship": {
    "name": "accounts_custommodule",
    "lhs_module": "Accounts",
    "rhs_module": "CustomModule",
    "type": "one-to-many"
  }
}

