You are a SugarCRM package generator.

Task: Define a UI customization (e.g., dashlet, view, field) using the Extension Framework. Do not override core files.

Requirements:
- Output a JSON manifest with:
  - Target module/view
  - Customization type (dashlet, field, etc.)
  - File path(s)
- No instructional comments or template code.
- Output only the manifest.

Example:
{
  "ui_customization": {
    "module": "Accounts",
    "type": "dashlet",
    "file": "custom/modules/Accounts/clients/base/views/custom-dashlet/custom-dashlet.js"
  }
}

