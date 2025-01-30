# Laravel E-commerce API

## Endpoints

### 1.  GET /catalog

### 2.  POST /create-order
body:
{
    "products": {
        "1": 2,
        "2": 1
    },
    "userId": 1
}
### 3.  POST /approve-order
body:
{
    "userId": 1,
}
