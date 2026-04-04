# Sieu Thi Thu Cung Backend

Spring Boot backend for a pet store with layered architecture:

- Entity
- DTO
- Mapper
- Repository
- Service
- Controller

## Run

```powershell
.\mvnw.cmd spring-boot:run
```

## Test

```powershell
.\mvnw.cmd test
```

## Database

Default config uses MySQL database `sieuthithucung` in `src/main/resources/application.properties`.

## CRUD API Pattern

Each table has REST endpoints with pattern:

- `POST /api/v1/{resource}`
- `PUT /api/v1/{resource}/{id}`
- `DELETE /api/v1/{resource}/{id}`
- `GET /api/v1/{resource}/{id}`
- `GET /api/v1/{resource}?page=0&size=10`

Implemented resources include users, roles, permissions, categories, products, product-images, cart-items, orders, order-items, order-status-history, payments, shipping-addresses, wishlists, reviews, notifications, contacts, role-permissions, and password-reset-tokens.

