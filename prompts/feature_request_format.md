# Feature Request Format for Sugar MLP Generator

All feature requests to the AI must use this deterministic, structured format:

```
Feature Type: <Type>            # e.g. Logic Hook, Custom Field, Relationship, REST Endpoint
Module: <Module Name>           # e.g. Accounts, Contacts
Trigger: <Event>                # e.g. after_save, before_delete (if applicable)
Condition:
  <field>: <value>              # Optional, e.g. field: account_type, equals: Customer
Action:
  type: <action_type>           # e.g. webhook, update_field, call_api
  method: <HTTP method>         # e.g. POST, GET (if applicable)
  url: <URL>                    # e.g. https://webhooks.com/mywebhook (if applicable)
  payload: <payload_type>       # e.g. full bean, custom JSON (if applicable)
Package Name: <MLP Name>        # e.g. OOTB_AccountsCustomerWebhook
```

## Example
```
Feature Type: Logic Hook
Module: Accounts
Trigger: after_save
Condition:
  field: account_type
  equals: Customer
Action:
  type: webhook
  method: POST
  url: https://webhooks.com/mywebhook
  payload: full bean
Package Name: OOTB_AccountsCustomerWebhook
```

- All fields are required unless marked optional.
- Use YAML or indented block for nested fields.
- No freeform text or explanations.
