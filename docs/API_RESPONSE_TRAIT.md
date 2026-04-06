# API Response Trait - Usage Guide

## Overview
The `ApiResponse` trait provides a standardized way to return JSON responses from API controllers with consistent formatting and HTTP status codes.

## Quick Start

### Using the Trait
```php
namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponse;

class ExampleController
{
    use ApiResponse;

    public function index()
    {
        return $this->successResponse($data, 'Data retrieved successfully');
    }
}
```

---

## Available Methods

### ✅ Success Responses

#### 1. `successResponse()`
Basic successful response
```php
return $this->successResponse(
    data: $user,
    message: 'User retrieved',
    statusCode: 200
);

// Response:
// {
//   "success": true,
//   "message": "User retrieved",
//   "data": { user object }
// }
```

#### 2. `createdResponse()`
Resource created (201)
```php
return $this->createdResponse(
    data: $user,
    message: 'User created successfully'
);

// HTTP 201 Created
```

#### 3. `updatedResponse()`
Resource updated
```php
return $this->updatedResponse(
    data: $user,
    message: 'User updated successfully'
);
```

#### 4. `deletedResponse()`
Resource deleted
```php
return $this->deletedResponse('User deleted successfully');

// {
//   "success": true,
//   "message": "User deleted successfully",
//   "data": null
// }
```

#### 5. `acceptedResponse()`
Request accepted (202)
```php
return $this->acceptedResponse(
    data: ['job_id' => 123],
    message: 'Request accepted for processing'
);
```

#### 6. `noContentResponse()`
No content (204)
```php
return $this->noContentResponse();
```

#### 7. `successPaginatedResponse()`
Success with pagination
```php
$users = User::paginate(15);

return $this->successPaginatedResponse(
    data: $users->items(),
    pagination: $this->getPaginationData($users),
    message: 'Users retrieved'
);

// Response includes pagination metadata
```

#### 8. `listResponse()`
List with optional pagination
```php
// Without pagination
return $this->listResponse(
    items: $items,
    message: 'Items retrieved'
);

// With pagination
return $this->listResponse(
    items: $items,
    message: 'Items retrieved',
    pagination: $this->getPaginationData($paginated)
);
```

---

### ❌ Error Responses

#### 1. `errorResponse()`
Generic error
```php
return $this->errorResponse(
    message: 'Something went wrong',
    statusCode: 400,
    errors: null
);
```

#### 2. `badRequestResponse()` (400)
Bad request
```php
return $this->badRequestResponse(
    message: 'Invalid input',
    errors: ['email' => 'Invalid email format']
);
```

#### 3. `validationErrorResponse()` (422)
Validation error
```php
return $this->validationErrorResponse(
    errors: $validator->errors()->toArray(),
    message: 'Validation failed'
);
```

#### 4. `unprocessableResponse()` (422)
Unprocessable entity
```php
return $this->unprocessableResponse(
    message: 'Cannot process request',
    errors: ['duplicate' => 'Email already exists']
);
```

#### 5. `unauthorizedResponse()` (401)
Unauthorized
```php
return $this->unauthorizedResponse('Token expired');
```

#### 6. `forbiddenResponse()` (403)
Forbidden
```php
return $this->forbiddenResponse('You do not have permission');
```

#### 7. `notFoundResponse()` (404)
Not found
```php
return $this->notFoundResponse('User not found');
```

#### 8. `conflictResponse()` (409)
Conflict
```php
return $this->conflictResponse(
    message: 'Resource already exists',
    errors: ['code' => 'Duplicate entry']
);
```

#### 9. `tooManyRequestsResponse()` (429)
Rate limited
```php
return $this->tooManyRequestsResponse('Rate limit exceeded');
```

#### 10. `serverErrorResponse()` (500)
Server error
```php
return $this->serverErrorResponse('Internal server error');
```

#### 11. `serviceUnavailableResponse()` (503)
Service unavailable
```php
return $this->serviceUnavailableResponse('Service temporarily down');
```

---

### 🔧 Utility Methods

#### 1. `getPaginationData()`
Extract pagination from paginated collection
```php
$users = User::paginate(15);

$pagination = $this->getPaginationData($users);

// Returns:
// {
//   "total": 100,
//   "count": 15,
//   "per_page": 15,
//   "current_page": 1,
//   "last_page": 7,
//   "from": 1,
//   "to": 15,
//   "has_more": true,
//   "next_page_url": "...",
//   "prev_page_url": null
// }
```

#### 2. `buildPagination()`
Build pagination manually
```php
$pagination = $this->buildPagination(
    total: 100,
    perPage: 15,
    currentPage: 1
);
```

#### 3. `customResponse()`
Custom response structure
```php
return $this->customResponse([
    'status' => 'ok',
    'result' => $data,
    'meta' => ['timestamp' => now()]
], 200);
```

---

## Real-World Examples

### Example 1: User CRUD Operations
```php
namespace App\Http\Controllers\Api\V1\User;

use App\Traits\ApiResponse;
use App\Models\User;

class UserController
{
    use ApiResponse;

    /**
     * Get all users
     */
    public function index()
    {
        $users = User::paginate(15);

        return $this->successPaginatedResponse(
            data: $users->items(),
            pagination: $this->getPaginationData($users),
            message: 'Users retrieved successfully'
        );
    }

    /**
     * Get single user
     */
    public function show(User $user)
    {
        return $this->successResponse(
            data: $user,
            message: 'User retrieved successfully'
        );
    }

    /**
     * Create user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create($validated);

        return $this->createdResponse(
            data: $user,
            message: 'User created successfully'
        );
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return $this->updatedResponse(
            data: $user,
            message: 'User updated successfully'
        );
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->deletedResponse('User deleted successfully');
    }
}
```

### Example 2: Order Management with Errors
```php
namespace App\Http\Controllers\Api\V1\Order;

use App\Traits\ApiResponse;
use App\Models\Order;

class OrderController
{
    use ApiResponse;

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'items' => 'required|array',
            'delivery_address' => 'required|string',
        ]);

        // Check for conflicts
        if (Order::where('customer_id', auth()->id())
            ->where('status', 'pending')
            ->exists()) {
            return $this->conflictResponse(
                message: 'You already have a pending order',
                errors: ['order' => 'Cannot place multiple pending orders']
            );
        }

        // Create order
        $order = Order::create($validated + ['customer_id' => auth()->id()]);

        return $this->createdResponse(
            data: $order,
            message: 'Order placed successfully'
        );
    }

    public function cancel(Order $order)
    {
        // Check permission
        if ($order->customer_id !== auth()->id()) {
            return $this->forbiddenResponse('You cannot cancel this order');
        }

        // Check status
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return $this->unprocessableResponse(
                message: 'Order cannot be cancelled',
                errors: ['status' => 'Order is ' . $order->status]
            );
        }

        $order->update(['status' => 'cancelled']);

        return $this->updatedResponse(
            data: $order,
            message: 'Order cancelled successfully'
        );
    }
}
```

### Example 3: Authentication with Validation
```php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;

class AuthController
{
    use ApiResponse;

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed',
                'phone' => 'required|unique:users',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        }

        $user = User::create($validated);
        $token = $user->createToken('auth')->plainTextToken;

        return $this->createdResponse(
            data: [
                'user' => $user,
                'token' => $token,
            ],
            message: 'User registered successfully'
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($credentials)) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        $user = auth()->user();
        $token = $user->createToken('auth')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Logged in successfully');
    }
}
```

### Example 4: Pagination Example
```php
public function index(Request $request)
{
    $perPage = $request->integer('per_page', 15);
    $page = $request->integer('page', 1);

    // Get paginated results
    $products = Product::paginate($perPage);

    return $this->successPaginatedResponse(
        data: $products->items(),
        pagination: $this->getPaginationData($products),
        message: 'Products retrieved'
    );

    // Or using listResponse:
    return $this->listResponse(
        items: $products->items(),
        pagination: $this->getPaginationData($products)
    );
}
```

### Example 5: Async Processing
```php
public function importData(Request $request)
{
    $file = $request->file('file');

    // Dispatch job
    $jobId = dispatch(new ImportDataJob($file))->id;

    return $this->acceptedResponse(
        data: [
            'job_id' => $jobId,
            'status_url' => route('jobs.status', $jobId),
        ],
        message: 'Import started. Check status URL for updates.'
    );
}
```

---

## Response Examples

### Success Response (200)
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Created Response (201)
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

### Paginated Response (200)
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    { "id": 1, "name": "John" },
    { "id": 2, "name": "Jane" }
  ],
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

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "User not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## HTTP Status Codes Reference

| Code | Method | Use Case |
|------|--------|----------|
| 200 | `successResponse()` | GET, PUT, PATCH success |
| 201 | `createdResponse()` | POST successful create |
| 202 | `acceptedResponse()` | Async job accepted |
| 204 | `noContentResponse()` | DELETE with no response |
| 400 | `badRequestResponse()` | Invalid request format |
| 401 | `unauthorizedResponse()` | Missing/invalid auth |
| 403 | `forbiddenResponse()` | Permission denied |
| 404 | `notFoundResponse()` | Resource not found |
| 409 | `conflictResponse()` | Resource conflict |
| 422 | `validationErrorResponse()` | Validation errors |
| 429 | `tooManyRequestsResponse()` | Rate limited |
| 500 | `serverErrorResponse()` | Server error |
| 503 | `serviceUnavailableResponse()` | Service down |

---

## Best Practices

1. **Always use the trait** for consistent API responses
2. **Use appropriate status codes** - don't use 200 for errors
3. **Provide meaningful messages** - help clients understand what happened
4. **Include errors on failure** - be specific about validation/conflict errors
5. **Use pagination for lists** - avoid overwhelming responses
6. **Log errors** - especially 5xx errors
7. **Test responses** - ensure format consistency

---

## Implementation in Existing Controller

Update your `FCMNotificationController`:

```php
use App\Traits\ApiResponse;

class FCMNotificationController extends Controller
{
    use ApiResponse;

    public function registerDeviceToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|min:100',
            'device_type' => 'nullable|in:ios,android,web',
        ]);

        $deviceToken = $this->fcmRepository->registerDevice($request->user(), $validated['fcm_token']);

        return $this->createdResponse(
            data: $deviceToken,
            message: 'Device registered successfully'
        );
    }

    public function getDeviceTokens()
    {
        $tokens = $this->fcmRepository->getUserAllTokens(auth()->user());

        return $this->successResponse(
            data: $tokens,
            message: 'Devices retrieved successfully'
        );
    }
}
```

All methods are production-ready and fully documented! ✨
