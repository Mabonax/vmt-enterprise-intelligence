# Conversation Architecture

The conversation system is provider-independent and centered on `ConversationManager`.

## Core Entities

- `conversations`
- `conversation_messages`
- `conversation_attachments`
- `conversation_exports`
- `provider_usage`

## Lifecycle

1. Create a conversation with title, user, provider, model, status, and metadata.
2. Add messages through `ConversationManager`.
3. Persist usage records whenever a message carries usage data.
4. Rename, archive, switch provider, or soft delete through the manager.
5. Export historical conversation state through `ConversationExportService`.

## Design Intent

- Unlimited history stays in storage.
- Runtime trimming happens in `TokenManager`, not by deleting records.
- Usage tracking is historical and separate from conversation messages.
