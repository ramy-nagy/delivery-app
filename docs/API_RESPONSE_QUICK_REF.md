# ApiResponse Trait - Quick Reference

## 🔥 One-Liners

```php
// Success
$this->successResponse($data);
$this->createdResponse($data);
$this->updatedResponse($data);
$this->deletedResponse();

// Error
$this->notFoundResponse();
$this->unauthorizedResponse();
$this->forbiddenResponse();
$this->validationErrorResponse($errors);
$this->serverErrorResponse();

// Pagination
$this->successPaginatedResponse($items, $this->getPaginationData($paginated));
$this->listResponse($items);
```

---

## 📋 All Methods

### Success (8 methods)
```php
$this->successResponse($data, 'message', 200)
$this->successPaginatedResponse($data, $pagination, 'message', 200)
$this->createdResponse($data, 'message')           // 201
$this->updatedResponse($data, 'message')           // 200
$this->deletedResponse('message')                  // 200
$this->acceptedResponse($data, 'message')          // 202
$this->noContentResponse()                         // 204
$this->listResponse($items, 'message', $pagination)
```

### Error (12 methods)
```php
$this->errorResponse('message', 400, $errors)
$this->badRequestResponse('message', $errors)      // 400
$this->unauthorizedResponse('message')             // 401
$this->forbiddenResponse('message')                // 403
$this->notFoundResponse('message')                 // 404
$this->conflictResponse('message', $errors)        // 409
$this->validationErrorResponse($errors, 'message') // 422
$this->unprocessableResponse('message', $errors)   // 422
$this->tooManyRequestsResponse('message')          // 429
$this->serverErrorResponse('message')              // 500
$this->serviceUnavailableResponse('message')       // 503
$this->customResponse($data, 200)
```

### Utilities (2 methods)
```php
$this->getPaginationData($paginatedCollection)
$this->buildPagination($total, $perPage, $page)
```

---

## ✅ Response Codes

| Code | Description |
|------|-------------|
| **2xx Success** |
| 200 | OK |
| 201 | Created |
| 202 | Accepted |
| 204 | No Content |
| **4xx Client Error** |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict |
| 422 | Unprocessable Entity |
| 429 | Too Many Requests |
| **5xx Server Error** |
| 500 | Server Error |
| 503 | Service Unavailable |

---

## 🎯 Use Cases

### Get Single Item
```php
return $this->successResponse($user, 'User retrieved');
```

### Get List
```php
$items = Item::paginate(15);
return $this->successPaginatedResponse(
    $items->items(), 
    $this->getPaginationData($items)
);
```

### Create Item (201)
```php
$item = Item::create($validated);
return $this->createdResponse($item, 'Created');
```

### Update Item
```php
$item->update($validated);
return $this->updatedResponse($item, 'Updated');
```

### Delete Item
```php
$item->delete();
return $this->deletedResponse('Deleted');
```

### Not Found (404)
```php
return $this->notFoundResponse('Item not found');
```

### Validation Error (422)
```php
return $this->validationErrorResponse($validator->errors());
```

### Unauthorized (401)
```php
return $this->unauthorizedResponse('Invalid token');
```

### Forbidden (403)
```php
return $this->forbiddenResponse('No permission');
```

### Conflict (409)
```php
return $this->conflictResponse('Already exists', ['code' => 'duplicate']);
```

### Async Job (202)
```php
$job = dispatch(new ImportJob(.));
return $this->acceptedResponse(['job_id' => $job->id]);
```

### Rate Limited (429)
```php
return $this->tooManyRequestsResponse('Too many requests');
```

### Server Error (500)
```php
return $this->serverErrorResponse('Something went wrong');
```

---

## 📝 Common Patterns

### CRUD Create
```php
try {
    $validated = $request->validate([...]);
    $item = Item::create($validated);
    return $this->createdResponse($item);
} catch (ValidationException $e) {
    return $this->validationErrorResponse($e->errors());
}
```

### CRUD Read (List)
```php
$items = Item::paginate(15);
return $this->successPaginatedResponse(
    $items->items(),
    $this->getPaginationData($items)
);
```

### CRUD Update
```php
$item->update($validated);
return $this->updatedResponse($item);
```

### CRUD Delete
```php
$item->delete();
return $this->deletedResponse();
```

### Authorization Check
```php
if (!auth()->check()) {
    return $this->unauthorizedResponse();
}
if (!auth()->user()->can('action')) {
    return $this->forbiddenResponse();
}
```

### Error Handling
```php
try {
    // code
} catch (ValidationException $e) {
    return $this->validationErrorResponse($e->errors());
} catch (Exception $e) {
    return $this->serverErrorResponse($e->getMessage());
}
```

---

## 🚀 Integration

Add to any controller:
```php
use App\Traits\ApiResponse;

class MyController {
    use ApiResponse;
    
    public function action() {
        return $this->successResponse($data);
    }
}
```

---

## 💾 Response Structure

### Success
```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

### Success with Pagination
```json
{
  "success": true,
  "message": "Success",
  "data": [],
  "pagination": {
    "total": 100,
    "count": 15,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15,
    "has_more": true,
    "next_page_url": "...",
    "prev_page_url": null
  }
}
```

### Error
```json
{
  "success": false,
  "message": "Error occurred",
  "errors": {}
}
```

---

## 📊 Complete Reference Table

| Method | Code | Returns | Use For |
|--------|------|---------|---------|
| successResponse | 200 | success | Generic success |
| createdResponse | 201 | success | Item created |
| updatedResponse | 200 | success | Item updated |
| deletedResponse | 200 | success | Item deleted |
| acceptedResponse | 202 | success | Async accepted |
| noContentResponse | 204 | null | No response body |
| listResponse | 200 | items | List of items |
| badRequestResponse | 400 | false | Bad request |
| unauthorizedResponse | 401 | false | Auth failed |
| forbiddenResponse | 403 | false | No permission |
| notFoundResponse | 404 | false | Not found |
| conflictResponse | 409 | false | Duplicate |
| validationErrorResponse | 422 | false | Invalid data |
| tooManyRequestsResponse | 429 | false | Rate limited |
| serverErrorResponse | 500 | false | Server error |
| serviceUnavailableResponse | 503 | false | Service down |
| getPaginationData | - | array | Get pagination |
| buildPagination | - | array | Build pagination |
| customResponse | custom | array | Custom format |

---

## 🎓 Quick Tips

✅ Always use `createdResponse()` for POST (201)
✅ Always use `notFoundResponse()` for missing items (404)
✅ Always use `validationErrorResponse()` for validation (422)
✅ Always use `forbiddenResponse()` for auth check (403)
✅ Always use pagination for lists
✅ Always include meaningful messages
✅ Always log server errors (500)
✅ Always handle exceptions properly

---

## 📞 Full Documentation

See `docs/API_RESPONSE_TRAIT.md` for complete guide with examples.
