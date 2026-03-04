# Example: Custom Relationship (Many-to-Many)

**Feature Type:** Custom Relationship
**Modules:** Accounts, Projects
**Use Case:** Link multiple accounts to multiple projects

## Use case details

1. Creates a relationship between Accounts and Projects modules
2. Many-to-many type (accounts can have multiple projects, projects can have multiple accounts)
3. Creates a join table (`accounts_projects_c`) to store relationships
4. Creates metadata and TableDictionary files
5. Automatically handles indices and constraints

## To Use This Example

1. Copy the request below into your prompt
2. Update these values:
   - `Relationship Name`: Your relationship identifier
   - `Left Module`: First module (typically parent)
   - `Right Module`: Second module (typically child)
   - `Type`: one-to-many or many-to-many
   - `Package Name`: Your package name
3. Prompt the AI agent with your customized request
4. AI generates the complete package in `build/<YourPackageName>/`

## How to Prompt the AI

### Structured Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Feature Type: Custom Relationship
Relationship Name: accounts_projects
Left Module: Accounts
Right Module: Projects
Type: many-to-many
Package Name: Acme_AccountsProjects
```

---

### Verbose Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Create a many-to-many relationship between the Accounts and Projects modules.
Call the relationship accounts_projects.
This allows multiple accounts to be linked to multiple projects.
Package Name: Acme_AccountsProjects
```

## Expected Output

The AI will generate:
- Metadata file in `custom/metadata/accounts_projects_cMetaData.php`
- TableDictionary entry in `custom/Extension/application/Ext/TableDictionary/`
- pack.php executable for building the installable zip
- version file
- Manifest configuration

## Important Notes

- Many-to-many creates a join table with `_c` suffix
- Join table name follows pattern: `<relationship_name>_c`
- One-to-many uses foreign key instead of join table
- After installation, run Quick Repair and Rebuild
- Relationship will appear in Studio for both modules

---

**Reference:** See `prompts/relationship.md` for complete specifications
