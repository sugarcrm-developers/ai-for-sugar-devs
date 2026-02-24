# Example: Custom Field (Varchar)

**Feature Type:** Custom Field
**Module:** Accounts
**Use Case:** Add a priority field for customer accounts

## Use case details

1. Adds a new custom field to the Accounts module
2. Field name becomes `customer_priority_c` (with _c suffix)
3. Field type is varchar (text up to 255 characters)
4. Field is reportable and audited
5. Creates language strings for the label

## To Use This Example

1. Copy the request below into your prompt
2. Update these values:
   - `Module`: Your target module
   - `Field Name`: Your field name (will get _c suffix added)
   - `Field Type`: varchar, int, bool, date, datetime, decimal, etc.
   - `Label`: User-friendly field label
   - `Package Name`: Your package name
3. Prompt the AI agent with your customized request
4. AI generates the complete package in `build/<YourPackageName>/`

## How to Prompt the AI

### Structured Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Feature Type: Custom Field
Module: Accounts
Field Name: customer_priority
Field Type: varchar
Label: Customer Priority
Length: 255
Package Name: Acme_AccountsPriority
```

---

### Verbose Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

A custom varchar field named customer_priority is added to the Accounts module.
The field is labeled "Customer Priority", is 255 characters long,
and is designed to be reportable and audited.
Package Name: Acme_AccountsPriority
```

## Expected Output

The AI will generate:
- Vardef file in `custom/Extension/modules/Accounts/Ext/Vardefs/`
- Language file with field label and help text
- pack.php executable for building the installable zip
- version file
- Manifest configuration

---

**Reference:** See `prompts/custom_field.md` for complete specifications
