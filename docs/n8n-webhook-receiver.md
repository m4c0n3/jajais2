# n8n Webhook Receiver

## Setup
1) Add an n8n Webhook node (POST).
2) Capture headers and body.

## Signature Verification (JavaScript)
```js
const crypto = require('crypto');

const secret = $env.WEBHOOK_SECRET;
const timestamp = $headers['x-webhook-timestamp'];
const signature = $headers['x-webhook-signature'];
const rawBody = $jsonRawBody || JSON.stringify($json);

if (!timestamp || !signature) {
  throw new Error('Missing webhook signature headers');
}

const now = Math.floor(Date.now() / 1000);
if (Math.abs(now - Number(timestamp)) > 300) {
  throw new Error('Webhook timestamp too old');
}

const base = `v1:${timestamp}:${rawBody}`;
const expected = 'v1=' + crypto.createHmac('sha256', secret).update(base).digest('hex');

if (signature !== expected) {
  throw new Error('Invalid webhook signature');
}

return $json;
```

## Notes
- Store `WEBHOOK_SECRET` in n8n credentials or env.
- Reject requests with stale timestamps to prevent replay.
