# Logging policy

This document defines the minimum logging rules for the platform.

## Goals

- Provide consistent request tracing across HTTP and background jobs.
- Avoid leaking secrets (tokens, credentials, private keys).
- Keep logs useful for operators without exposing user data.

## Required context

- Every HTTP request must include `X-Request-Id` in the response headers.
- The same `request_id` must be attached to log context for the request.
- Background jobs should carry over the originating `request_id` when dispatched.

## Sensitive data

Never log:

- `Authorization` headers, access tokens, API keys, JWTs.
- Private keys or signing key material.
- Passwords or reset tokens.
- Database URLs with embedded credentials.

## Where to log

- Use `AuditService` for security-relevant actions (admin, licensing, RBAC).
- Use application logs for diagnostics, errors, and operational events.

## Operational guidance

- Prefer structured log context over string concatenation.
- Keep error messages user-safe; include details in logs only when needed.
