## API Documentation

This documentation outlines the available API endpoints for managing categories and expenses.

### Authentication

All endpoints require authentication using JWT (JSON Web Token). Include the token in the `Authorization` header as `Bearer your_token_here`.

### Categories

#### Get all categories

Retrieves all categories belonging to the authenticated user.

*   **URL:** `/api/categories`
*   **Method:** `GET`
*   **Request Body:** None
*   **Response:**
    *   `200 OK`:
```
json
        [
            {
                "id": 1,
                "user_id": 1,
                "name": "Groceries",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z"
            },
            {
                "id": 2,
                "user_id": 1,
                "name": "Utilities",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z"
            }
        ]
        
```
*   `401 Unauthorized`: If the user is not authenticated.

#### Create a new category

Creates a new category for the authenticated user.

*   **URL:** `/api/categories`
*   **Method:** `POST`
*   **Request Body:**
```
json
    {
        "name": "New Category Name"
    }
    
```
*   **Response:**
    *   `201 Created`:
```
json
        {
            "name": "New Category Name",
            "user_id": 1,
            "updated_at": "2023-10-27T10:00:00.000000Z",
            "created_at": "2023-10-27T10:00:00.000000Z",
            "id": 3
        }
        
```
*   `400 Bad Request`: If validation fails (e.g., missing `name`, non-unique name).
    *   `401 Unauthorized`: If the user is not authenticated.

#### Get a specific category

Retrieves a specific category belonging to the authenticated user.

*   **URL:** `/api/categories/{id}`
*   **Method:** `GET`
*   **URL Parameters:**
    *   `id`: The ID of the category.
*   **Request Body:** None
*   **Response:**
    *   `200 OK`:
```
json
        {
            "id": 1,
            "user_id": 1,
            "name": "Groceries",
            "created_at": "2023-10-27T10:00:00.000000Z",
            "updated_at": "2023-10-27T10:00:00.000000Z"
        }
        
```
*   `404 Not Found`: If the category does not exist or does not belong to the authenticated user.
    *   `401 Unauthorized`: If the user is not authenticated.

#### Update a specific category

Updates a specific category belonging to the authenticated user.

*   **URL:** `/api/categories/{id}`
*   **Method:** `PUT`
*   **URL Parameters:**
    *   `id`: The ID of the category.
*   **Request Body:**
```
json
    {
        "name": "Updated Category Name"
    }
    
```
*   **Response:**
    *   `200 OK`:
```
json
        {
            "id": 1,
            "user_id": 1,
            "name": "Updated Category Name",
            "created_at": "2023-10-27T10:00:00.000000Z",
            "updated_at": "2023-10-27T10:00:00.000000Z"
        }
        
```
*   `400 Bad Request`: If validation fails (e.g., missing `name`, non-unique name).
    *   `404 Not Found`: If the category does not exist or does not belong to the authenticated user.
    *   `401 Unauthorized`: If the user is not authenticated.

#### Delete a specific category

Deletes a specific category belonging to the authenticated user.

*   **URL:** `/api/categories/{id}`
*   **Method:** `DELETE`
*   **URL Parameters:**
    *   `id`: The ID of the category.
*   **Request Body:** None
*   **Response:**
    *   `200 OK`:
```
json
        {
            "message": "Category deleted successfully."
        }
        
```
*   `400 Bad Request`: If the category has associated expenses.
    *   `404 Not Found`: If the category does not exist or does not belong to the authenticated user.
    *   `401 Unauthorized`: If the user is not authenticated.

### Expenses

#### Get all expenses

Retrieves all expenses belonging to the authenticated user, with their associated category.

*   **URL:** `/api/expenses`
*   **Method:** `GET`
*   **Request Body:** None
*   **Response:**
    *   `200 OK`:
```
json
        [
            {
                "id": 1,
                "user_id": 1,
                "category_id": 1,
                "product": "Milk",
                "price": "3.50",
                "timestamp": "2023-10-27T10:00:00.000000Z",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z",
                "category": {
                    "id": 1,
                    "user_id": 1,
                    "name": "Groceries",
                    "created_at": "2023-10-27T10:00:00.000000Z",
                    "updated_at": "2023-10-27T10:00:00.000000Z"
                }
            },
            {
                "id": 2,
                "user_id": 1,
                "category_id": null,
                "product": "Electricity Bill",
                "price": "75.00",
                "timestamp": "2023-10-27T10:00:00.000000Z",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z",
                "category": null
            }
        ]
        
```
*   `401 Unauthorized`: If the user is not authenticated.

#### Create a new expense

Creates a new expense for the authenticated user.

*   **URL:** `/api/expenses`
*   **Method:** `POST`
*   **Request Body:**
```
json
    {
        "product": "New Product",
        "price": 10.99,
        "category_id": 1, // Optional, can be null
        "timestamp": "2023-10-27 12:00:00"
    }
    
```
*   **Response:**
    *   `201 Created`:
```
json
        {
            "product": "New Product",
            "price": "10.99",
            "category_id": 1,
            "timestamp": "2023-10-27T12:00:00.000000Z",
            "user_id": 1,
            "updated_at": "2023-10-27T10:00:00.000000Z",
            "created_at": "2023-10-27T10:00:00.000000Z",
            "id": 3,
             "category": {
                "id": 1,
                "user_id": 1,
                "name": "Groceries",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z"
            }
        }
        
```
*   `400 Bad Request`: If validation fails (e.g., missing required fields, invalid data types).
    *   `401 Unauthorized`: If the user is not authenticated.

#### Get a specific expense

Retrieves a specific expense belonging to the authenticated user, with its associated category.

*   **URL:** `/api/expenses/{id}`
*   **Method:** `GET`
*   **URL Parameters:**
    *   `id`: The ID of the expense.
*   **Request Body:** None
*   **Response:**
    *   `200 OK`:
```
json
        {
            "id": 1,
            "user_id": 1,
            "category_id": 1,
            "product": "Milk",
            "price": "3.50",
            "timestamp": "2023-10-27T10:00:00.000000Z",
            "created_at": "2023-10-27T10:00:00.000000Z",
            "updated_at": "2023-10-27T10:00:00.000000Z",
            "category": {
                "id": 1,
                "user_id": 1,
                "name": "Groceries",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z"
            }
        }
        
```
*   `404 Not Found`: If the expense does not exist or does not belong to the authenticated user.
    *   `401 Unauthorized`: If the user is not authenticated.

#### Update a specific expense

Updates a specific expense belonging to the authenticated user.

*   **URL:** `/api/expenses/{id}`
*   **Method:** `PUT`
*   **URL Parameters:**
    *   `id`: The ID of the expense.
*   **Request Body:**
```
json
    {
        "product": "Updated Product",
        "price": 99.99,
        "category_id": 2, // Can update or set to null
        "timestamp": "2023-10-28 09:00:00"
    }
    
```
*   **Response:**
    *   `200 OK`:
```
json
        {
            "id": 1,
            "user_id": 1,
            "category_id": 2,
            "product": "Updated Product",
            "price": "99.99",
            "timestamp": "2023-10-28T09:00:00.000000Z",
            "created_at": "2023-10-27T10:00:00.000000Z",
            "updated_at": "2023-10-27T10:00:00.000000Z",
             "category": {
                "id": 2,
                "user_id": 1,
                "name": "Utilities",
                "created_at": "2023-10-27T10:00:00.000000Z",
                "updated_at": "2023-10-27T10:00:00.000000Z"
            }
        }
        
```
*   `400 Bad Request`: If validation fails (e.g., missing required fields, invalid data types).
    *   `404 Not Found`: If the expense does not exist or does not belong to the authenticated user.
    *   `401 Unauthorized`: If the user is not authenticated.

#### Delete a specific expense

Deletes a specific expense belonging to the authenticated user.

*   **URL:** `/api/expenses/{id}`
*   **Method:** `DELETE`
*   **URL Parameters:**
    *   `id`: The ID of the expense.
*   **Request Body:** None
*   **Response:**
    *   `200 OK`:
```
json
        {
            "message": "Expense deleted successfully"
        }
        
```
*   `404 Not Found`: If the expense does not exist or does not belong to the authenticated user.
    *   `401 Unauthorized`: If the user is not authenticated.



## Postman cURL Examples

Here are some cURL examples to test the API endpoints. Replace `<your_token>` with a valid JWT token obtained from your authentication process and `<base_url>` with the base URL of your API.

### Categories

**Create a new category:**
```
bash
curl --location '<base_url>/api/categories' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>' \
--header 'Content-Type: application/json' \
--data '{
    "name": "Groceries"
}'
```
**Get all categories:**
```
bash
curl --location '<base_url>/api/categories' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>'
```
**Get a specific category (replace `<category_id>` with the actual category ID):**
```
bash
curl --location '<base_url>/api/categories/<category_id>' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>'
```
**Update a category (replace `<category_id>` with the actual category ID):**
```
bash
curl --location '<base_url>/api/categories/<category_id>' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>' \
--header 'Content-Type: application/json' \
--request PUT \
--data '{
    "name": "Updated Category Name"
}'
```
**Delete a category (replace `<category_id>` with the actual category ID):**
```
bash
curl --location '<base_url>/api/categories/<category_id>' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>' \
--request DELETE
```
### Expenses

**Create a new expense:**
```
bash
curl --location '<base_url>/api/expenses' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>' \
--header 'Content-Type: application/json' \
--data '{
    "product": "Milk",
    "price": 3.50,
    "category_id": 1,
    "timestamp": "2023-10-26 10:00:00"
}'
```
**Get all expenses:**
```
bash
curl --location '<base_url>/api/expenses' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>'
```
**Get a specific expense (replace `<expense_id>` with the actual expense ID):**
```
bash
curl --location '<base_url>/api/expenses/<expense_id>' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>'
```
**Update an expense (replace `<expense_id>` with the actual expense ID):**
```
bash
curl --location '<base_url>/api/expenses/<expense_id>' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token>' \
--header 'Content-Type: application/json' \
--request PUT \
--data '{
    "product": "Organic Milk",
    "price": 4.00,
    "category_id": 1,
    "timestamp": "2023-10-26 10:30:00"
}'
```
**Delete an expense (replace `<expense_id>` with the actual expense ID):**