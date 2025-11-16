## Review API Documentation

This document describes the Review endpoints available under the API. All paths are relative to `/api`.

-   Base URL: `/api`
-   Auth: Sanctum (`Bearer <token>`) required for create/update/delete. Public read is allowed.
-   Content-Type: `application/json`

### List Ground Reviews (Public)

-   Method: GET
-   Path: `/grounds/{groundId}/reviews`
-   Auth: Not required
-   Path params:
    -   `groundId` (integer) — Ground ID
-   Response 200:

```json
{
    "success": true,
    "reviews": [
        {
            "id": 10,
            "user_id": 5,
            "ground_id": 3,
            "rating": 4,
            "comment": "Nice turf",
            "created_at": "2025-05-21T09:40:00.000000Z",
            "updated_at": "2025-05-21T09:40:00.000000Z",
            "user": { "id": 5, "name": "John Doe" },
            "replies": [
                {
                    "id": 2,
                    "user_id": 1,
                    "review_id": 10,
                    "comment": "Thanks for the feedback!",
                    "created_at": "2025-05-21T10:00:00.000000Z",
                    "updated_at": "2025-05-21T10:00:00.000000Z",
                    "user": { "id": 1, "name": "Admin" }
                }
            ]
        }
    ],
    "count": 1,
    "userReview": {
        "id": 10,
        "user_id": 5,
        "ground_id": 3,
        "rating": 4,
        "comment": "Nice turf",
        "created_at": "2025-05-21T09:40:00.000000Z",
        "updated_at": "2025-05-21T09:40:00.000000Z",
        "user": { "id": 5, "name": "John Doe" }
    },
    "average_rating": 4.0
}
```

-   Notes:
    -   If authenticated, the current user’s review (if any) is prioritized and returned in `userReview`.
    -   Reviews are sorted: user's review first (if logged in), then newest first.

Example:

```bash
curl -X GET "{{BASE_URL}}/api/grounds/3/reviews"
```

### Get Single Review (Public)

-   Method: GET
-   Path: `/reviews/{id}`
-   Auth: Not required
-   Path params:
    -   `id` (integer) — Review ID
-   Response 200:

```json
{
    "success": true,
    "review": {
        "id": 10,
        "user_id": 5,
        "ground_id": 3,
        "rating": 4,
        "comment": "Nice turf",
        "created_at": "2025-05-21T09:40:00.000000Z",
        "updated_at": "2025-05-21T09:40:00.000000Z",
        "user": { "id": 5, "name": "John Doe" },
        "replies": [
            {
                "id": 2,
                "user_id": 1,
                "review_id": 10,
                "comment": "Thanks for the feedback!",
                "created_at": "2025-05-21T10:00:00.000000Z",
                "updated_at": "2025-05-21T10:00:00.000000Z",
                "user": { "id": 1, "name": "Admin" }
            }
        ]
    }
}
```

Example:

```bash
curl -X GET "{{BASE_URL}}/api/reviews/10"
```

### Create Review (Protected)

-   Method: POST
-   Path: `/reviews`
-   Auth: Required — `Authorization: Bearer <token>`
-   Body (JSON):

```json
{
    "ground_id": 3,
    "rating": 4,
    "comment": "Nice turf"
}
```

-   Validation:
    -   `ground_id`: required, exists in `grounds.id`
    -   `rating`: required, integer, 1..5
    -   `comment`: required, string, 5..500 chars
-   Responses:
    -   201 Created

```json
{
    "success": true,
    "message": "Review submitted successfully!",
    "review": {
        "id": 10,
        "user_id": 5,
        "ground_id": 3,
        "rating": 4,
        "comment": "Nice turf",
        "created_at": "2025-05-21T09:40:00.000000Z",
        "updated_at": "2025-05-21T09:40:00.000000Z",
        "user": { "id": 5, "name": "John Doe" }
    }
}
```

-   409 Conflict (already reviewed)

```json
{
    "success": false,
    "message": "You have already reviewed this ground. Please update your existing review."
}
```

-   422 Unprocessable Entity (validation errors)

```json
{
    "success": false,
    "message": "The rating field is required.",
    "errors": { "rating": ["The rating field is required."] }
}
```

Example:

```bash
curl -X POST "{{BASE_URL}}/api/reviews" \
  -H "Authorization: Bearer {{TOKEN}}" \
  -H "Content-Type: application/json" \
  -d '{"ground_id":3,"rating":4,"comment":"Nice turf"}'
```

### Update Review (Protected)

-   Method: PUT
-   Path: `/reviews/{id}`
-   Auth: Required — `Authorization: Bearer <token>`
-   Path params:
    -   `id` (integer) — Review ID
-   Body (JSON):

```json
{ "rating": 5, "comment": "Updated comment" }
```

-   Validation:
    -   `rating`: required, integer, 1..5
    -   `comment`: required, string, 5..500 chars
-   Responses:
    -   200 OK

```json
{
    "success": true,
    "message": "Review updated successfully!",
    "review": {
        "id": 10,
        "user_id": 5,
        "ground_id": 3,
        "rating": 5,
        "comment": "Updated comment",
        "created_at": "2025-05-21T09:40:00.000000Z",
        "updated_at": "2025-05-21T10:10:00.000000Z",
        "user": { "id": 5, "name": "John Doe" }
    }
}
```

-   403 Forbidden (not owner)

```json
{ "success": false, "message": "You are not authorized to update this review." }
```

-   422 Unprocessable Entity (validation errors)

```json
{
    "success": false,
    "message": "The rating field is required.",
    "errors": { "rating": ["The rating field is required."] }
}
```

Example:

```bash
curl -X PUT "{{BASE_URL}}/api/reviews/10" \
  -H "Authorization: Bearer {{TOKEN}}" \
  -H "Content-Type: application/json" \
  -d '{"rating":5,"comment":"Updated comment"}'
```

### Delete Review (Protected)

-   Method: DELETE
-   Path: `/reviews/{id}`
-   Auth: Required — `Authorization: Bearer <token>`
-   Path params:
    -   `id` (integer) — Review ID
-   Responses:
    -   200 OK

```json
{ "success": true, "message": "Review deleted successfully!" }
```

-   403 Forbidden (not owner)

```json
{ "success": false, "message": "You are not authorized to delete this review." }
```

Example:

```bash
curl -X DELETE "{{BASE_URL}}/api/reviews/10" \
  -H "Authorization: Bearer {{TOKEN}}"
```

### Authentication

-   Uses Laravel Sanctum. Send `Authorization: Bearer <token>` header for protected endpoints.
-   If your mobile/web client is same-origin, session-based auth may also work with Sanctum stateful API.

### Error Shapes

-   Validation: `422` with `{ success: false, message, errors }`
-   Not owner: `403` with `{ success: false, message }`
-   Not found: `404` from underlying `findOrFail`
-   Server error: `500` with `{ success: false, message }`

### Related

-   Review replies have separate endpoints (see web routes) and are included in review payloads under `replies`.
