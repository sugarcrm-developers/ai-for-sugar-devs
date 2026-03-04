# Example: REST API Endpoint

**Feature Type:** REST Endpoint
**Module:** Contacts
**Use Case:** Create a custom API endpoint to get contact activity summary

## Use case details

1. Creates a custom REST API endpoint accessible via HTTP GET
2. Endpoint path: `/api/v10/custom/contacts/summary`
3. Returns aggregated activity data for contacts
4. Includes proper authentication and authorization checks
5. Returns structured JSON response

## To Use This Example

1. Copy the request below into your prompt
2. Update these values:
   - `Module`: Your target module
   - `Endpoint`: Your API path (after `/api/v10/`)
   - `Method`: GET, POST, PUT, DELETE, PATCH
   - `Action`: Description of what the endpoint does
   - `Package Name`: Your package name
3. Prompt the AI agent with your customized request
4. AI generates the complete package in `build/<YourPackageName>/`

## How to Prompt the AI

### Structured Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Feature Type: REST Endpoint
Module: Contacts
Endpoint: /custom/contacts/summary
Method: GET
Action: return summary of contact activity
Package Name: Acme_ContactsSummaryAPI
```

---

### Verbose Example

```
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Create a custom REST API endpoint on the Contacts module.
The endpoint is at /custom/contacts/summary and accepts GET requests.
It returns a JSON response with a summary of contact activity.
Include proper authentication and authorization checks.
Package Name: Acme_ContactsSummaryAPI
```

## Expected Output

The AI will generate:
- API class in `custom/clients/base/api/ContactsApi.php`
- Endpoint registration with proper path variables
- Authentication/authorization checks
- Error handling with appropriate exceptions
- pack.php executable for building the installable zip
- version file
- Manifest configuration

## Important Notes

- All API methods must include authentication checks
- Validate all input parameters before processing
- Use appropriate SugarApiException types for errors
- Return consistent array structure with status/data
- Log API calls for audit trail
- Test endpoint with authentication

---

**Reference:** See `prompts/rest_endpoint.md` for complete specifications
