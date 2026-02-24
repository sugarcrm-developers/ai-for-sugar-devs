# Example: Logic Hook with Webhook

**Feature Type:** Logic Hook
**Module:** Accounts
**Use Case:** Send account data to external webhook when account is saved

## Use case details

1. Whenever an Accounts record is saved
2. The logic hook checks if `account_type` = 'Customer'
3. If true, sends a POST request to `https://webhooks.com/webhook`
4. Sends the complete account record as JSON payload
5. Logs the response

## To Use This Example

1. Copy the request below into your prompt
2. Update these values:
   - `Module`: Your target module
   - `url`: Your actual webhook URL
   - `Package Name`: Your package name
3. Prompt the AI agent with your customized request
4. AI generates the complete package in `build/<YourPackageName>/`

## How to Prompt the AI

### Structured Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Feature Type: Logic Hook
Module: Accounts
Trigger: after_save
Condition:
  field: account_type = 'Customer'
Action:
  type: webhook
  method: POST
  url: https://webhooks.com/webhook
  payload: full bean
Package Name: Acme_AccountsWebhook
```

---

### Verbose Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Create a Logic Hook on the Accounts module that triggers after_save.
If account_type equals 'Customer', send the full bean as a JSON POST request
to https://webhooks.com/webhook.
Log the webhook response.
Package Name: Acme_AccountsWebhook
```

## Expected Output

The AI will generate:
- Logic hook definition file in `custom/Extension/modules/Accounts/Ext/LogicHooks/`
- Hook implementation class with ExternalResourceClient for HTTP requests
- Language file with labels
- pack.php executable for building the installable zip
- version file
- Manifest configuration

---

**Reference:** See `prompts/logic_hook.md` for complete specifications
