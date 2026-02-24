# Examples - Community-Contributed Prompts

This folder contains example feature requests that you can use as templates and copy/paste starting points for your own packages.

## Purpose

These examples demonstrate how to:
1. Format feature requests correctly
2. Customize requests for your specific use case
3. Prompt the AI agent to generate packages
4. Understand what each feature type does

**These are NOT core prompts.** They're copy/paste templates to help you get started quickly.

## Available Examples

| File | Feature Type | Use Case |
|------|--------------|----------|
| `01_logic_hook_webhook.md` | Logic Hook | Webhook integration when records are saved |
| `02_custom_field_varchar.md` | Custom Field | Add simple varchar field to module |
| `03_relationship_many_to_many.md` | Relationship | Link two modules with many-to-many relationship |
| `04_rest_endpoint_api.md` | REST Endpoint | Create custom API endpoint |

## How to Use

There are two ways to request features:

### Option 1: Structured Format (from examples)

Copy the structured format directly from an example:

```
Feature Type: Custom Field
Module: Accounts
Field Name: customer_priority
Field Type: varchar
Label: Customer Priority
Package Name: Acme_AccountsPriority
```

### Option 2: Natural Language (describe what you want)

Or simply describe what you need in plain English:

> "A custom varchar field named **customer_priority** is added to the Accounts module under the package **Test_AccountsCustomField**, labeled "Customer Priority," and designed as a highlight field that allows users to choose background and foreground colors in Studio."

**Both formats work equally well!** Use whichever is more natural for you.

### Complete Workflow

1. **Pick an example** that matches your use case
2. **Either:**
   - Copy the structured request and customize it, OR
   - Describe your needs in natural language
3. **Prepend the prompt header:**
   ```
   Read and follow AGENTS.md strictly.
   Read and follow prompts/feature_generator.md strictly.
   No explanations. No markdown. Output raw file entries only.
   ```
4. **Paste to AI agent**
5. **Get your generated package** in `build/<PackageName>/`

## Contributing Your Own Examples

We welcome community contributions! To add your own example:

1. **Copy an existing example** as a template
2. **Update the content** for your feature type and use case
3. **Follow the same format** as existing examples:
   - Title with feature type and use case
   - Your Request section with the feature definition
   - What Happens section explaining the behavior
   - To Use This Example section with customization points
   - How to Prompt the AI section with the exact prompt text
   - Expected Output section
   - Important Notes section
   - Reference to the core prompt

4. **Name the file** with pattern: `NN_feature_type_description.md`
   - Use next number (05, 06, etc.)
   - Use lowercase with underscores
   - Be descriptive

5. **Submit a pull request** with your example

## Examples for Other Feature Types

Want examples for other feature types? Here are the core prompts you can reference:

- **Logic Hooks**: `prompts/logic_hook.md`
- **Custom Fields**: `prompts/custom_field.md`
- **Complex Field Types**: `prompts/custom_field_type.md`
- **Relationships**: `prompts/relationship.md`
- **REST Endpoints**: `prompts/rest_endpoint.md`
- **Scheduler Jobs**: `prompts/scheduler.md`
- **UI Customizations**: `prompts/ui_customization.md`

Create an example following the same structure as the ones in this folder and contribute it!

## Tips for Creating Good Examples

✅ **DO:**
- Use real-world scenarios (e.g., "send webhook when customer account is created")
- Provide clear customization points
- Explain what the feature does
- Include the exact prompt text to copy/paste
- Reference the core prompt for more details

❌ **DON'T:**
- Create examples that duplicate existing ones
- Use vague or overly simple scenarios
- Skip the "How to Prompt the AI" section
- Make examples too long or complicated

## Questions?

- **How do I format requests?** → See `prompts/feature_request_format.md`
- **What are the core specs?** → See `prompts/<feature_type>.md`
- **How do agents work?** → See `AGENTS.md`
- **How do I install packages?** → See `README.md`

---

**Last Updated:** February 24, 2026

