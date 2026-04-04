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

## Environment Variables

Use these variables when deploying or before pushing to shared environments:

- DB_URL
- DB_USERNAME
- DB_PASSWORD
- JPA_DDL_AUTO
- APP_CORS_ALLOWED_ORIGIN
- APP_UPLOAD_DIR
- MULTIPART_MAX_FILE_SIZE
- MULTIPART_MAX_REQUEST_SIZE

Admin bootstrap (optional):

- APP_ADMIN_SEED_ENABLED
- APP_ADMIN_SEED_EMAIL
- APP_ADMIN_SEED_PASSWORD
- APP_ADMIN_SEED_NAME

Notes:

- Admin seed is disabled by default.
- To auto-create admin account, set APP_ADMIN_SEED_ENABLED=true and provide APP_ADMIN_SEED_EMAIL + APP_ADMIN_SEED_PASSWORD.
- Runtime uploads are ignored by git via uploads/ in .gitignore.

## Dummy Data SQL

Dummy data file is available at `dummy-data.sql`.

Import with MySQL client:

```powershell
mysql -u root -D sieuthithucung -e "source dummy-data.sql"
```

The script uses id range `9001+` and `ON DUPLICATE KEY UPDATE`, so it can be re-run safely.

## CRUD API Pattern

Each table has REST endpoints with pattern:

- `POST /api/v1/{resource}`
- `PUT /api/v1/{resource}/{id}`
- `DELETE /api/v1/{resource}/{id}`
- `GET /api/v1/{resource}/{id}`
- `GET /api/v1/{resource}?page=0&size=10`

Implemented resources include users, roles, permissions, categories, products, product-images, cart-items, orders, order-items, order-status-history, payments, shipping-addresses, wishlists, reviews, notifications, contacts, role-permissions, and password-reset-tokens.

